<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusPembayaran;
use App\Enums\StatusRegistrasi;
use App\Enums\StatusSeleksi;
use App\Http\Controllers\Controller;
use App\Models\DokumenRegistrasi;
use App\Models\DokumenSubmission;
use App\Models\Pembayaran;
use App\Models\Sponsor;
use App\Models\Tim;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Render stats for admin dashboard.
     */
    public function index(): Response
    {
        $totalTim = Tim::count();
        $totalSubmission = DokumenSubmission::count();
        $totalTimVerified = Tim::whereIn('status_seleksi', StatusSeleksi::qualified())->count();
        $persyaratanPending = DokumenRegistrasi::where('status_registrasi', StatusRegistrasi::Pending->value)->count();
        $pembayaranPending = Pembayaran::where('status_pembayaran', StatusPembayaran::Pending->value)->count();
        $totalSponsor = Sponsor::count();

        $latestPembayaran = Pembayaran::with('tim')
            ->where('status_pembayaran', StatusPembayaran::Pending->value)
            ->orderBy('uploaded_at', 'desc')
            ->take(5)
            ->get();

        $latestPersyaratan = DokumenRegistrasi::with('tim')
            ->where('status_registrasi', StatusRegistrasi::Pending->value)
            ->orderBy('uploaded_at', 'desc')
            ->take(5)
            ->get();

        return Inertia::render('admin/Dashboard', [
            'statistics' => [
                'totalTim' => $totalTim,
                'totalSubmission' => $totalSubmission,
                'totalTimVerified' => $totalTimVerified,
                'persyaratanPending' => $persyaratanPending,
                'pembayaranPending' => $pembayaranPending,
                'totalSponsor' => $totalSponsor,
            ],
            'latestPembayaran' => $latestPembayaran,
            'latestPersyaratan' => $latestPersyaratan,
        ]);
    }
}
