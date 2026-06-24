<?php

use App\Enums\StatusPembayaran;
use App\Enums\StatusRegistrasi;
use App\Enums\StatusSeleksi;
use App\Http\Controllers\Admin\SponsorController;
use App\Models\DokumenRegistrasi;
use App\Models\DokumenSubmission;
use App\Models\MemberTim;
use App\Models\Pembayaran;
use App\Models\Sponsor;
use App\Models\Tim;
use App\Models\Timeline;
use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
        'activeTimeline' => Timeline::currentActive(),
        'allTimelines' => Timeline::orderBy('tanggal_mulai', 'asc')->get(),
        'sponsors' => Sponsor::where('is_active', true)->orderBy('order', 'asc')->orderBy('created_at', 'desc')->get(),
    ]);
})->name('home');

Route::get('/dashboard', function () {
    /** @var User $authUser */
    $authUser = auth()->user();
    if ($authUser->is_admin) {
        return redirect()->route('admin.dashboard');
    }

    $user = $authUser->load('tim.members', 'tim.dokumen_registrasi', 'tim.pembayaran', 'tim.submission');

    return Inertia::render('Dashboard', [
        'activeTimeline' => Timeline::currentActive(),
        'allTimelines' => Timeline::orderBy('tanggal_mulai', 'asc')->get(),
        'userTeam' => $user->tim,
        'teamMembers' => $user->tim ? $user->tim->members : [],
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

// Peserta Routes
Route::middleware(['auth', 'verified', 'peserta'])->group(function () {
    Route::get('/dashboard/tim', function () {
        /** @var User $authUser */
        $authUser = auth()->user();
        $user = $authUser->load('tim.members', 'tim.dokumen_registrasi', 'tim.pembayaran', 'tim.submission');

        return Inertia::render('peserta/Tim', [
            'userTeam' => $user->tim,
            'teamMembers' => $user->tim ? $user->tim->members : [],
            'isSubmissionOpen' => Timeline::isOpenForTahap('pendaftaran_b2'),
        ]);
    })->name('peserta.tim');

    Route::post('/dashboard/tim', function (Request $request) {
        /** @var User $user */
        $user = auth()->user();
        /** @var Tim|null $existingTim */
        $existingTim = $user->tim;

        if ($existingTim && $existingTim->dokumen_registrasi) {
            $statusReg = $existingTim->dokumen_registrasi->status_registrasi;
            if ($statusReg === StatusRegistrasi::Berhasil->value) {
                throw ValidationException::withMessages([
                    'nama_tim' => 'Data tim tidak dapat diubah karena berkas persyaratan pendaftaran sudah disetujui.',
                ]);
            }
            if ($statusReg === StatusRegistrasi::Pending->value) {
                throw ValidationException::withMessages([
                    'nama_tim' => 'Data tim tidak dapat diubah saat berkas persyaratan pendaftaran sedang diverifikasi oleh panitia.',
                ]);
            }
        }

        $request->validate([
            'nama_tim' => [
                'required',
                'string',
                'max:50',
                Rule::unique('tim', 'nama_tim')->ignore($existingTim?->id_tim, 'id_tim'),
            ],
            'universitas' => 'required|string|max:255',
            // Ketua details
            'nim_ketua' => 'required|string|max:255',
            'jurusan_ketua' => 'required|string|max:255',
            // Anggota 1
            'nama_anggota1' => 'required|string|max:255',
            'nim_anggota1' => 'required|string|max:255',
            'jurusan_anggota1' => 'required|string|max:255',
            // Anggota 2
            'nama_anggota2' => 'nullable|string|max:255',
            'nim_anggota2' => 'nullable|string|max:255',
            'jurusan_anggota2' => 'nullable|string|max:255',
        ]);

        $universitas = $request->universitas;
        $memberIds = $existingTim ? $existingTim->members->pluck('id_member')->toArray() : [];

        // Check if NIM is unique within the same university (cross-team validation)
        $checkNimUnique = function ($nim, $universitas, $field, $label) use ($memberIds) {
            if (! $nim) {
                return;
            }
            $exists = MemberTim::where('nim_peserta', $nim)
                ->whereNotIn('id_member', $memberIds)
                ->whereHas('tim', function ($query) use ($universitas) {
                    $query->where('universitas', $universitas);
                })
                ->exists();
            if ($exists) {
                throw ValidationException::withMessages([
                    $field => 'NIM '.$nim.' sudah terdaftar untuk '.$label.' dari universitas ini di tim lain.',
                ]);
            }
        };

        $checkNimUnique($request->nim_ketua, $universitas, 'nim_ketua', 'ketua');
        $checkNimUnique($request->nim_anggota1, $universitas, 'nim_anggota1', 'anggota 1');
        $checkNimUnique($request->nim_anggota2, $universitas, 'nim_anggota2', 'anggota 2');

        // Check duplicate NIM entries within the same team
        if ($request->nim_ketua === $request->nim_anggota1) {
            throw ValidationException::withMessages([
                'nim_anggota1' => 'NIM anggota 1 tidak boleh sama dengan NIM ketua.',
            ]);
        }
        if ($request->nim_anggota2) {
            if ($request->nim_ketua === $request->nim_anggota2) {
                throw ValidationException::withMessages([
                    'nim_anggota2' => 'NIM anggota 2 tidak boleh sama dengan NIM ketua.',
                ]);
            }
            if ($request->nim_anggota1 === $request->nim_anggota2) {
                throw ValidationException::withMessages([
                    'nim_anggota2' => 'NIM anggota 2 tidak boleh sama dengan NIM anggota 1.',
                ]);
            }
        }

        DB::transaction(function () use ($request, $user, $existingTim) {
            $tim = Tim::updateOrCreate(
                ['id_user' => $user->id_user],
                [
                    'nama_tim' => $request->nama_tim,
                    'universitas' => $request->universitas,
                    'status_seleksi' => $existingTim ? $existingTim->status_seleksi : StatusSeleksi::BelumSeleksi->value,
                    'batch' => $existingTim ? $existingTim->batch : 1,
                ]
            );

            // Recreate members safely
            $tim->members()->delete();

            // Ketua (Leader)
            $tim->members()->create([
                'nama_peserta' => $user->name,
                'nim_peserta' => $request->nim_ketua,
                'jurusan' => $request->jurusan_ketua,
                'role' => 'ketua',
            ]);

            // Anggota 1 (Member 1)
            $tim->members()->create([
                'nama_peserta' => $request->nama_anggota1,
                'nim_peserta' => $request->nim_anggota1,
                'jurusan' => $request->jurusan_anggota1,
                'role' => 'anggota',
            ]);

            // Anggota 2 (Member 2 - optional)
            if ($request->nama_anggota2 && $request->nim_anggota2 && $request->jurusan_anggota2) {
                $tim->members()->create([
                    'nama_peserta' => $request->nama_anggota2,
                    'nim_peserta' => $request->nim_anggota2,
                    'jurusan' => $request->jurusan_anggota2,
                    'role' => 'anggota',
                ]);
            }
        });

        return redirect()->route('peserta.tim')->with('success', 'Data tim berhasil disimpan!');
    })->name('peserta.tim.store');

    Route::post('/dashboard/tim/dokumen', function (Request $request) {
        /** @var User $user */
        $user = auth()->user();
        /** @var Tim|null $tim */
        $tim = $user->tim;
        if (! $tim) {
            throw ValidationException::withMessages([
                'file_dokumen' => 'Anda harus membuat tim terlebih dahulu.',
            ]);
        }

        if ($tim->dokumen_registrasi && $tim->dokumen_registrasi->status_registrasi === StatusRegistrasi::Berhasil->value) {
            throw ValidationException::withMessages([
                'file_dokumen' => 'Berkas persyaratan pendaftaran sudah disetujui dan tidak dapat diubah.',
            ]);
        }

        $request->validate([
            'file_dokumen' => 'required|file|mimes:pdf|max:5120',
        ], [
            'file_dokumen.required' => 'Berkas persyaratan wajib diunggah.',
            'file_dokumen.file' => 'Berkas yang diunggah harus berupa file.',
            'file_dokumen.mimes' => 'Berkas harus dalam format PDF.',
            'file_dokumen.max' => 'Ukuran berkas maksimal adalah 5MB.',
        ]);

        if ($tim->dokumen_registrasi) {
            Storage::disk('public')->delete($tim->dokumen_registrasi->link_file_registrasi);
        }

        $path = $request->file('file_dokumen')->store('dokumen_registrasi', 'public');

        DokumenRegistrasi::updateOrCreate(
            ['id_tim' => $tim->id_tim],
            [
                'link_file_registrasi' => $path,
                'status_registrasi' => StatusRegistrasi::Pending->value,
                'catatan_registrasi' => null,
                'uploaded_at' => now(),
            ]
        );

        return redirect()->back()->with('success', 'Berkas persyaratan berhasil diunggah!');
    })->name('peserta.tim.dokumen.store');

    Route::delete('/dashboard/tim/dokumen', function () {
        /** @var User $user */
        $user = auth()->user();
        /** @var Tim|null $tim */
        $tim = $user->tim;
        if (! $tim || ! $tim->dokumen_registrasi) {
            throw ValidationException::withMessages([
                'file_dokumen' => 'Dokumen tidak ditemukan.',
            ]);
        }

        if ($tim->dokumen_registrasi->status_registrasi === StatusRegistrasi::Berhasil->value) {
            throw ValidationException::withMessages([
                'file_dokumen' => 'Berkas persyaratan pendaftaran sudah disetujui dan tidak dapat dibatalkan.',
            ]);
        }

        Storage::disk('public')->delete($tim->dokumen_registrasi->link_file_registrasi);
        $tim->dokumen_registrasi->delete();

        return redirect()->back()->with('success', 'Unggahan berkas berhasil dibatalkan.');
    })->name('peserta.tim.dokumen.destroy');

    Route::post('/dashboard/tim/pembayaran', function (Request $request) {
        /** @var User $user */
        $user = auth()->user();
        /** @var Tim|null $tim */
        $tim = $user->tim;
        if (! $tim) {
            throw ValidationException::withMessages([
                'bukti_pembayaran' => 'Anda harus membuat tim terlebih dahulu.',
            ]);
        }

        if ($tim->pembayaran && $tim->pembayaran->status_pembayaran === StatusPembayaran::Berhasil->value) {
            throw ValidationException::withMessages([
                'bukti_pembayaran' => 'Bukti pembayaran sudah disetujui dan tidak dapat diubah.',
            ]);
        }

        $request->validate([
            'bukti_pembayaran' => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120',
        ], [
            'bukti_pembayaran.required' => 'Bukti pembayaran wajib diunggah.',
            'bukti_pembayaran.file' => 'Berkas yang diunggah harus berupa file.',
            'bukti_pembayaran.mimes' => 'Berkas harus dalam format JPG, PNG, atau PDF.',
            'bukti_pembayaran.max' => 'Ukuran berkas maksimal adalah 5MB.',
        ]);

        if ($tim->pembayaran) {
            Storage::disk('public')->delete($tim->pembayaran->bukti_pembayaran);
        }

        $path = $request->file('bukti_pembayaran')->store('bukti_pembayaran', 'public');

        Pembayaran::updateOrCreate(
            ['id_tim' => $tim->id_tim],
            [
                'bukti_pembayaran' => $path,
                'status_pembayaran' => StatusPembayaran::Pending->value,
                'catatan_pembayaran' => null,
                'uploaded_at' => now(),
            ]
        );

        return redirect()->back()->with('success', 'Bukti pembayaran berhasil diunggah!');
    })->name('peserta.tim.pembayaran.store');

    Route::delete('/dashboard/tim/pembayaran', function () {
        /** @var User $user */
        $user = auth()->user();
        /** @var Tim|null $tim */
        $tim = $user->tim;
        if (! $tim || ! $tim->pembayaran) {
            throw ValidationException::withMessages([
                'bukti_pembayaran' => 'Data pembayaran tidak ditemukan.',
            ]);
        }

        // Guard: prevent cancellation if payment already approved
        if ($tim->pembayaran->status_pembayaran === StatusPembayaran::Berhasil->value) {
            throw ValidationException::withMessages([
                'bukti_pembayaran' => 'Bukti pembayaran sudah disetujui dan tidak dapat dibatalkan.',
            ]);
        }

        Storage::disk('public')->delete($tim->pembayaran->bukti_pembayaran);
        $tim->pembayaran->delete();

        return redirect()->back()->with('success', 'Unggahan bukti pembayaran berhasil dibatalkan.');
    })->name('peserta.tim.pembayaran.destroy');

    Route::post('/dashboard/tim/submission', function (Request $request) {
        /** @var User $user */
        $user = auth()->user();
        /** @var Tim|null $tim */
        $tim = $user->tim;
        if (! $tim) {
            throw ValidationException::withMessages([
                'link_file_submission' => 'Anda harus membuat tim terlebih dahulu.',
            ]);
        }

        if ($tim->submission) {
            throw ValidationException::withMessages([
                'link_file_submission' => 'Anda sudah pernah mengumpulkan proposal.',
            ]);
        }

        if ($tim->status_seleksi !== StatusSeleksi::Penyisihan->value) {
            throw ValidationException::withMessages([
                'link_file_submission' => 'Anda hanya dapat mengumpulkan proposal setelah verifikasi berkas dan pembayaran disetujui oleh panitia.',
            ]);
        }

        if (! Timeline::isOpenForTahap('pendaftaran_b2')) {
            throw ValidationException::withMessages([
                'link_file_submission' => 'Batas waktu pengumpulan proposal (Batch 2) telah berakhir.',
            ]);
        }

        $request->validate([
            'link_file_submission' => 'required|url|max:1000',
        ], [
            'link_file_submission.required' => 'Link Google Drive wajib diisi.',
            'link_file_submission.url' => 'Format link harus berupa URL valid.',
            'link_file_submission.max' => 'Link maksimal 1000 karakter.',
        ]);

        DokumenSubmission::create([
            'id_tim' => $tim->id_tim,
            'link_file_submission' => $request->link_file_submission,
            'uploaded_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Proposal berhasil dikumpulkan!');
    })->name('peserta.tim.submission.store');

    Route::get('/dashboard/profil', function () {
        return Inertia::render('peserta/Profil');
    })->name('peserta.profil');
});

// Admin Routes
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        $totalTim = Tim::count();
        $totalSubmission = DokumenSubmission::count();
        $totalTimVerified = Tim::whereIn('status_seleksi', StatusSeleksi::qualified())->count();
        $persyaratanPending = DokumenRegistrasi::where('status_registrasi', StatusRegistrasi::Pending->value)->count();
        $pembayaranPending = Pembayaran::where('status_pembayaran', StatusPembayaran::Pending->value)->count();

        return Inertia::render('admin/Dashboard', [
            'statistics' => [
                'totalTim' => $totalTim,
                'totalSubmission' => $totalSubmission,
                'totalTimVerified' => $totalTimVerified,
                'persyaratanPending' => $persyaratanPending,
                'pembayaranPending' => $pembayaranPending,
            ],
        ]);
    })->name('admin.dashboard');

    Route::get('/konfirmasi-persyaratan', function () {
        $teams = Tim::with(['members', 'dokumen_registrasi'])->get();

        return Inertia::render('admin/KonfirmasiPersyaratan', [
            'teams' => $teams,
        ]);
    })->name('admin.persyaratan');

    Route::post('/konfirmasi-persyaratan/{id_registrasi}/status', function (Request $request, $id_registrasi) {
        $request->validate([
            'status' => 'required|in:berhasil,ditolak',
            'catatan' => 'required_if:status,ditolak|nullable|string|max:1000',
        ], [
            'status.required' => 'Status konfirmasi harus ditentukan.',
            'status.in' => 'Status konfirmasi tidak valid.',
            'catatan.required_if' => 'Catatan penolakan wajib diisi jika status ditolak.',
            'catatan.max' => 'Catatan penolakan maksimal 1000 karakter.',
        ]);

        /** @var DokumenRegistrasi $dokumen */
        $dokumen = DokumenRegistrasi::findOrFail($id_registrasi);

        // Guard: prevent double-processing — only allow changes when status is still pending
        if ($dokumen->status_registrasi !== StatusRegistrasi::Pending->value) {
            throw ValidationException::withMessages([
                'status' => 'Dokumen ini sudah diproses sebelumnya dan tidak dapat diubah lagi.',
            ]);
        }

        $dokumen->update([
            'status_registrasi' => $request->status,
            'catatan_registrasi' => $request->status === StatusRegistrasi::Ditolak->value ? $request->catatan : null,
        ]);

        return redirect()->back()->with('success', 'Status dokumen berhasil diperbarui!');
    })->name('admin.persyaratan.update');

    Route::get('/konfirmasi-pembayaran', function () {
        $teams = Tim::with(['members', 'pembayaran'])->get();

        return Inertia::render('admin/KonfirmasiPembayaran', [
            'teams' => $teams,
        ]);
    })->name('admin.pembayaran');

    Route::post('/konfirmasi-pembayaran/{id_pembayaran}/status', function (Request $request, $id_pembayaran) {
        $request->validate([
            'status' => 'required|in:berhasil,ditolak',
            'catatan' => 'required_if:status,ditolak|nullable|string|max:1000',
        ], [
            'status.required' => 'Status konfirmasi harus ditentukan.',
            'status.in' => 'Status konfirmasi tidak valid.',
            'catatan.required_if' => 'Catatan penolakan wajib diisi jika status ditolak.',
            'catatan.max' => 'Catatan penolakan maksimal 1000 karakter.',
        ]);

        /** @var Pembayaran $pembayaran */
        $pembayaran = Pembayaran::findOrFail($id_pembayaran);

        // Guard: prevent double-processing — only allow changes when status is still pending
        if ($pembayaran->status_pembayaran !== StatusPembayaran::Pending->value) {
            throw ValidationException::withMessages([
                'status' => 'Pembayaran ini sudah diproses sebelumnya dan tidak dapat diubah lagi.',
            ]);
        }

        DB::transaction(function () use ($pembayaran, $request) {
            $pembayaran->update([
                'status_pembayaran' => $request->status,
                'catatan_pembayaran' => $request->status === StatusPembayaran::Ditolak->value ? $request->catatan : null,
            ]);

            // If approved, update team status_seleksi to 'penyisihan'
            // but only if registration document is also approved
            if ($request->status === StatusPembayaran::Berhasil->value) {
                /** @var Tim $tim */
                $tim = $pembayaran->tim;
                $dokumenApproved = $tim->dokumen_registrasi
                    && $tim->dokumen_registrasi->status_registrasi === StatusRegistrasi::Berhasil->value;

                if ($dokumenApproved) {
                    $tim->update([
                        'status_seleksi' => StatusSeleksi::Penyisihan->value,
                    ]);
                }
            }
        });

        return redirect()->back()->with('success', 'Status pembayaran berhasil diperbarui!');
    })->name('admin.pembayaran.update');

    Route::get('/tim-terdaftar', function () {
        $teams = Tim::with(['members', 'dokumen_registrasi', 'pembayaran'])
            ->whereIn('status_seleksi', StatusSeleksi::qualified())
            ->get();

        return Inertia::render('admin/TimTerdaftar', [
            'teams' => $teams,
        ]);
    })->name('admin.tim-terdaftar');

    Route::get('/submission', function () {
        $teams = Tim::with(['members', 'submission'])
            ->whereHas('submission')
            ->get();

        return Inertia::render('admin/Submission', [
            'teams' => $teams,
        ]);
    })->name('admin.submission');

    Route::get('/atur-tanggal', function () {
        $timelines = Timeline::orderByRaw(
            "FIELD(tahap, 'pendaftaran_b1', 'pendaftaran_b2', 'penyisihan', 'final', 'awarding')"
        )->get();

        return Inertia::render('admin/AturTanggal', [
            'timelines' => $timelines,
        ]);
    })->name('admin.atur-tanggal');

    Route::post('/atur-tanggal', function (Request $request) {
        /** @var User $user */
        $user = auth()->user();
        $tahaps = ['pendaftaran_b1', 'pendaftaran_b2', 'penyisihan', 'final', 'awarding'];

        $rules = [];
        foreach ($tahaps as $tahap) {
            $rules["tanggal_mulai_{$tahap}"] = 'required|date';
            $rules["tanggal_selesai_{$tahap}"] = "required|date|after:tanggal_mulai_{$tahap}";
        }

        $messages = [];
        foreach ($tahaps as $tahap) {
            $messages["tanggal_selesai_{$tahap}.after"] = "Tanggal selesai harus setelah tanggal mulai untuk tahap {$tahap}.";
        }

        $request->validate($rules, $messages);

        DB::transaction(function () use ($request, $user, $tahaps) {
            foreach ($tahaps as $tahap) {
                Timeline::updateOrCreate(
                    ['tahap' => $tahap],
                    [
                        'tanggal_mulai' => $request->input("tanggal_mulai_{$tahap}"),
                        'tanggal_selesai' => $request->input("tanggal_selesai_{$tahap}"),
                        'updated_by' => $user->id_user,
                    ]
                );
            }
        });

        return redirect()->back()->with('success', 'Timeline berhasil diperbarui!');
    })->name('admin.atur-tanggal.update');

    Route::get('/kelola-sponsor', [SponsorController::class, 'index'])->name('admin.kelola-sponsor');
    Route::post('/kelola-sponsor', [SponsorController::class, 'store'])->name('admin.kelola-sponsor.store');
    Route::post('/kelola-sponsor/{id}', [SponsorController::class, 'update'])->name('admin.kelola-sponsor.update');
    Route::delete('/kelola-sponsor/{id}', [SponsorController::class, 'destroy'])->name('admin.kelola-sponsor.destroy');
});

require __DIR__.'/auth.php';
require __DIR__.'/settings.php';
