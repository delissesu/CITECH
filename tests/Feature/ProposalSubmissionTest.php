<?php

namespace Tests\Feature;

use App\Models\DokumenSubmission;
use App\Models\Tim;
use App\Models\Timeline;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProposalSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_peserta_can_submit_proposal_link_when_status_is_penyisihan_and_before_deadline()
    {
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user);

        $tim = Tim::create([
            'id_user' => $user->id_user,
            'nama_tim' => 'Test Team',
            'universitas' => 'Test University',
            'status_seleksi' => 'penyisihan',
            'batch' => 2,
        ]);

        // Create active batch 2 timeline
        Timeline::create([
            'tanggal_mulai' => now()->subDay(),
            'tanggal_selesai' => now()->addDay(),
            'tahap' => 'pendaftaran_b2',
            'updated_by' => $user->id_user,
        ]);

        $response = $this->post(route('peserta.tim.submission.store'), [
            'link_file_submission' => 'https://drive.google.com/drive/folders/test-folder-id',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('dokumen_submission', [
            'id_tim' => $tim->id_tim,
            'link_file_submission' => 'https://drive.google.com/drive/folders/test-folder-id',
        ]);
    }

    public function test_peserta_cannot_submit_proposal_when_status_is_not_penyisihan()
    {
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user);

        $tim = Tim::create([
            'id_user' => $user->id_user,
            'nama_tim' => 'Test Team',
            'universitas' => 'Test University',
            'status_seleksi' => 'belum_seleksi', // Not penyisihan yet
            'batch' => 2,
        ]);

        // Create active batch 2 timeline
        Timeline::create([
            'tanggal_mulai' => now()->subDay(),
            'tanggal_selesai' => now()->addDay(),
            'tahap' => 'pendaftaran_b2',
            'updated_by' => $user->id_user,
        ]);

        $response = $this->post(route('peserta.tim.submission.store'), [
            'link_file_submission' => 'https://drive.google.com/drive/folders/test-folder-id',
        ]);

        $response->assertSessionHasErrors(['link_file_submission']);
        $this->assertDatabaseMissing('dokumen_submission', [
            'id_tim' => $tim->id_tim,
        ]);
    }

    public function test_peserta_cannot_submit_proposal_twice()
    {
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user);

        $tim = Tim::create([
            'id_user' => $user->id_user,
            'nama_tim' => 'Test Team',
            'universitas' => 'Test University',
            'status_seleksi' => 'penyisihan',
            'batch' => 2,
        ]);

        Timeline::create([
            'tanggal_mulai' => now()->subDay(),
            'tanggal_selesai' => now()->addDay(),
            'tahap' => 'pendaftaran_b2',
            'updated_by' => $user->id_user,
        ]);

        // Create first submission
        DokumenSubmission::create([
            'id_tim' => $tim->id_tim,
            'link_file_submission' => 'https://drive.google.com/drive/folders/first-folder',
            'uploaded_at' => now(),
        ]);

        // Attempt second submission
        $response = $this->post(route('peserta.tim.submission.store'), [
            'link_file_submission' => 'https://drive.google.com/drive/folders/second-folder',
        ]);

        $response->assertSessionHasErrors(['link_file_submission']);
        $this->assertDatabaseMissing('dokumen_submission', [
            'link_file_submission' => 'https://drive.google.com/drive/folders/second-folder',
        ]);
    }

    public function test_peserta_cannot_submit_proposal_after_deadline()
    {
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user);

        $tim = Tim::create([
            'id_user' => $user->id_user,
            'nama_tim' => 'Test Team',
            'universitas' => 'Test University',
            'status_seleksi' => 'penyisihan',
            'batch' => 2,
        ]);

        // Create expired batch 2 timeline
        Timeline::create([
            'tanggal_mulai' => now()->subDays(5),
            'tanggal_selesai' => now()->subDay(), // Expired yesterday
            'tahap' => 'pendaftaran_b2',
            'updated_by' => $user->id_user,
        ]);

        $response = $this->post(route('peserta.tim.submission.store'), [
            'link_file_submission' => 'https://drive.google.com/drive/folders/expired-folder',
        ]);

        $response->assertSessionHasErrors(['link_file_submission']);
        $this->assertDatabaseMissing('dokumen_submission', [
            'link_file_submission' => 'https://drive.google.com/drive/folders/expired-folder',
        ]);
    }
}
