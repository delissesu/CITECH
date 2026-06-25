<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class ProfilController extends Controller
{
    /**
     * Display admin profile.
     */
    public function index(): Response
    {
        return Inertia::render('peserta/Profil');
    }
}
