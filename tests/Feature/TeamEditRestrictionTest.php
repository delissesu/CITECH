<?php

namespace Tests\Feature;

use App\Models\DokumenRegistrasi;
use App\Models\Tim;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamEditRestrictionTest extends TestCase
{
    use RefreshDatabase;

    public function test_peserta_can_edit_team_when_registration_status_is_not_berhasil()
    {
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user);

        // Make a post request to store/edit team data
        $response = $this->post(route('peserta.tim.store'), [
            'nama_tim' => 'Test Team',
            'universitas' => 'Test University',
            'nim_ketua' => '111111',
            'jurusan_ketua' => 'Computer Science',
            'nama_anggota1' => 'Member One',
            'nim_anggota1' => '222222',
            'jurusan_anggota1' => 'Information Systems',
        ]);

        $response->assertRedirect(route('peserta.tim'));
        $this->assertDatabaseHas('tim', [
            'nama_tim' => 'Test Team',
            'universitas' => 'Test University',
        ]);
    }

    public function test_peserta_cannot_edit_team_when_registration_status_is_berhasil()
    {
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user);

        // Create team
        $tim = Tim::create([
            'id_user' => $user->id_user,
            'nama_tim' => 'Old Team Name',
            'universitas' => 'Old University',
            'status_seleksi' => 'belum_seleksi',
            'batch' => 1,
        ]);

        // Add leader member
        $tim->members()->create([
            'nama_peserta' => $user->name,
            'nim_peserta' => '111111',
            'jurusan' => 'Computer Science',
            'role' => 'ketua',
        ]);

        // Add member 1
        $tim->members()->create([
            'nama_peserta' => 'Member One',
            'nim_peserta' => '222222',
            'jurusan' => 'Information Systems',
            'role' => 'anggota',
        ]);

        // Create approved registration document
        DokumenRegistrasi::create([
            'id_tim' => $tim->id_tim,
            'link_file_registrasi' => 'test.pdf',
            'status_registrasi' => 'berhasil',
            'uploaded_at' => now(),
        ]);

        // Attempt to edit team data
        $response = $this->post(route('peserta.tim.store'), [
            'nama_tim' => 'New Team Name',
            'universitas' => 'New University',
            'nim_ketua' => '111111',
            'jurusan_ketua' => 'Computer Science',
            'nama_anggota1' => 'Member One',
            'nim_anggota1' => '222222',
            'jurusan_anggota1' => 'Information Systems',
        ]);

        $response->assertSessionHasErrors(['nama_tim']);
        $this->assertDatabaseHas('tim', [
            'id_tim' => $tim->id_tim,
            'nama_tim' => 'Old Team Name', // Name should not change
        ]);
    }
}
