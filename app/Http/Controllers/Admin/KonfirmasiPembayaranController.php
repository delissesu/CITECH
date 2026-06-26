<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusPembayaran;
use App\Enums\StatusRegistrasi;
use App\Enums\StatusSeleksi;
use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Tim;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class KonfirmasiPembayaranController extends Controller
{
    /**
     * Display the payment confirmation page.
     */
    public function index(): Response
    {
        $teams = Tim::with(['members', 'pembayaran'])->get();

        return Inertia::render('admin/KonfirmasiPembayaran', [
            'teams' => $teams,
        ]);
    }

    /**
     * Update status (approve/reject) of payment proof.
     */
    public function update(Request $request, int $id_pembayaran): RedirectResponse
    {
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
                    && strtolower($tim->dokumen_registrasi->status_registrasi) === StatusRegistrasi::Berhasil->value;

                if ($dokumenApproved) {
                    $tim->update([
                        'status_seleksi' => StatusSeleksi::Penyisihan->value,
                    ]);
                }
            }
        });

        return redirect()->back()->with('success', 'Status pembayaran berhasil diperbarui!');
    }
}
