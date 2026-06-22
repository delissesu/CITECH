<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Tim;
use App\Models\DokumenSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminViewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_admin_views()
    {
        $peserta = User::factory()->create(['is_admin' => false]);
        $this->actingAs($peserta);

        // Access registered teams
        $response1 = $this->get(route('admin.tim-terdaftar'));
        $response1->assertStatus(403);

        // Access submissions
        $response2 = $this->get(route('admin.submission'));
        $response2->assertStatus(403);
    }

    public function test_admin_can_access_tim_terdaftar_view()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $peserta = User::factory()->create(['is_admin' => false]);

        // Team 1: status_seleksi = penyisihan (should appear)
        $tim1 = Tim::create([
            'id_user' => $peserta->id_user,
            'nama_tim' => 'Tim Terdaftar A',
            'universitas' => 'Univ A',
            'status_seleksi' => 'penyisihan',
            'batch' => 1,
        ]);

        // Team 2: status_seleksi = belum_seleksi (should NOT appear)
        $tim2 = Tim::create([
            'id_user' => User::factory()->create(['is_admin' => false])->id_user,
            'nama_tim' => 'Tim Belum Terdaftar B',
            'universitas' => 'Univ B',
            'status_seleksi' => 'belum_seleksi',
            'batch' => 1,
        ]);

        $this->actingAs($admin);
        $response = $this->get(route('admin.tim-terdaftar'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('admin/TimTerdaftar')
            ->has('teams', 1)
            ->where('teams.0.id_tim', $tim1->id_tim)
        );
    }

    public function test_admin_can_access_submission_view()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $peserta = User::factory()->create(['is_admin' => false]);

        // Team 1: has submission (should appear)
        $tim1 = Tim::create([
            'id_user' => $peserta->id_user,
            'nama_tim' => 'Tim A Submission',
            'universitas' => 'Univ A',
            'status_seleksi' => 'penyisihan',
            'batch' => 2,
        ]);
        DokumenSubmission::create([
            'id_tim' => $tim1->id_tim,
            'link_file_submission' => 'https://drive.google.com/drive/folders/test-submission-url',
            'uploaded_at' => now(),
        ]);

        // Team 2: no submission (should NOT appear)
        $tim2 = Tim::create([
            'id_user' => User::factory()->create(['is_admin' => false])->id_user,
            'nama_tim' => 'Tim B No Submission',
            'universitas' => 'Univ B',
            'status_seleksi' => 'penyisihan',
            'batch' => 2,
        ]);

        $this->actingAs($admin);
        $response = $this->get(route('admin.submission'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('admin/Submission')
            ->has('teams', 1)
            ->where('teams.0.id_tim', $tim1->id_tim)
        );
    }
}
