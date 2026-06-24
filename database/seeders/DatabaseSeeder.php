<?php

namespace Database\Seeders;

use App\Models\Timeline;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Citech',
            'email' => 'admin@citech.com',
            'password' => Hash::make('password'),
            'is_admin' => true,
        ]);

        User::factory()->create([
            'name' => 'Peserta Citech',
            'email' => 'peserta@citech.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
        ]);

        Timeline::create([
            'tanggal_mulai' => '2026-06-27 00:00:00',
            'tanggal_selesai' => '2026-07-18 23:59:59',
            'tahap' => 'pendaftaran_b1',
            'updated_by' => $admin->id_user,
        ]);

        Timeline::create([
            'tanggal_mulai' => '2026-07-19 00:00:00',
            'tanggal_selesai' => '2026-08-01 23:59:59',
            'tahap' => 'pendaftaran_b2',
            'updated_by' => $admin->id_user,
        ]);

        Timeline::create([
            'tanggal_mulai' => '2026-06-27 00:00:00',
            'tanggal_selesai' => '2026-08-01 23:59:59',
            'tahap' => 'penyisihan',
            'updated_by' => $admin->id_user,
        ]);

        Timeline::create([
            'tanggal_mulai' => '2026-08-11 00:00:00',
            'tanggal_selesai' => '2026-08-21 23:59:59',
            'tahap' => 'final',
            'updated_by' => $admin->id_user,
        ]);

        Timeline::create([
            'tanggal_mulai' => '2026-08-23 00:00:00',
            'tanggal_selesai' => '2026-08-23 23:59:59',
            'tahap' => 'awarding',
            'updated_by' => $admin->id_user,
        ]);
    }
}
