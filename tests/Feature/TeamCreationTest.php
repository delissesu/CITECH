<?php

namespace Tests\Feature;

use App\Enums\StatusRegistrasi;
use App\Enums\StatusSeleksi;
use App\Models\DokumenRegistrasi;
use App\Models\Tim;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamCreationTest extends TestCase
{
    use RefreshDatabase;

    private function teamData(array $overrides = []): array
    {
        return array_merge([
            'nama_tim' => 'Test Team',
            'universitas' => 'Universitas Indonesia',
            'nim_ketua' => '100001',
            'jurusan_ketua' => 'Ilmu Komputer',
            'nama_anggota1' => 'Anggota Satu',
            'nim_anggota1' => '100002',
            'jurusan_anggota1' => 'Sistem Informasi',
        ], $overrides);
    }

    public function test_peserta_can_create_team_with_members(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)
            ->post(route('peserta.tim.store'), $this->teamData([
                'nama_anggota2' => 'Anggota Dua',
                'nim_anggota2' => '100003',
                'jurusan_anggota2' => 'Teknik Informatika',
            ]));

        $response->assertRedirect(route('peserta.tim'));

        $this->assertDatabaseHas('tim', [
            'id_user' => $user->id_user,
            'nama_tim' => 'Test Team',
            'universitas' => 'Universitas Indonesia',
            'status_seleksi' => StatusSeleksi::BelumSeleksi->value,
            'batch' => 1,
        ]);

        $tim = Tim::where('id_user', $user->id_user)->first();
        $this->assertNotNull($tim);
        $this->assertCount(3, $tim->members);

        $this->assertDatabaseHas('member_tim', [
            'id_tim' => $tim->id_tim,
            'nim_peserta' => '100001',
            'role' => 'ketua',
        ]);
        $this->assertDatabaseHas('member_tim', [
            'id_tim' => $tim->id_tim,
            'nim_peserta' => '100002',
            'role' => 'anggota',
        ]);
        $this->assertDatabaseHas('member_tim', [
            'id_tim' => $tim->id_tim,
            'nim_peserta' => '100003',
            'role' => 'anggota',
        ]);
    }

    public function test_team_name_must_be_unique(): void
    {
        $user1 = User::factory()->create(['is_admin' => false]);
        $user2 = User::factory()->create(['is_admin' => false]);

        // Create team for user1
        $this->actingAs($user1)
            ->post(route('peserta.tim.store'), $this->teamData());

        // Attempt to create team with same name for user2
        $response = $this->actingAs($user2)
            ->post(route('peserta.tim.store'), $this->teamData());

        $response->assertSessionHasErrors(['nama_tim']);
        $this->assertDatabaseMissing('tim', [
            'id_user' => $user2->id_user,
        ]);
    }

    public function test_nim_must_be_unique_within_same_university(): void
    {
        $user1 = User::factory()->create(['is_admin' => false]);
        $user2 = User::factory()->create(['is_admin' => false]);

        // Create team for user1 with NIM 100001 as ketua
        $this->actingAs($user1)
            ->post(route('peserta.tim.store'), $this->teamData());

        // Attempt to create team for user2 at same university with duplicate NIM
        $response = $this->actingAs($user2)
            ->post(route('peserta.tim.store'), $this->teamData([
                'nama_tim' => 'Another Team',
                'nim_ketua' => '100001', // Same NIM as user1's ketua
            ]));

        $response->assertSessionHasErrors(['nim_ketua']);
    }

    public function test_nim_is_allowed_across_different_universities(): void
    {
        $user1 = User::factory()->create(['is_admin' => false]);
        $user2 = User::factory()->create(['is_admin' => false]);

        // Create team for user1 at university A
        $this->actingAs($user1)
            ->post(route('peserta.tim.store'), $this->teamData());

        // Create team for user2 at university B with same NIM — should be allowed
        $response = $this->actingAs($user2)
            ->post(route('peserta.tim.store'), $this->teamData([
                'nama_tim' => 'Another Team',
                'universitas' => 'Universitas Gadjah Mada',
                'nim_ketua' => '100001', // Same NIM, different university
            ]));

        $response->assertRedirect(route('peserta.tim'));
        $this->assertDatabaseHas('tim', ['id_user' => $user2->id_user]);
    }

    public function test_duplicate_nim_within_same_team_rejected(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        // Ketua and anggota1 have the same NIM
        $response = $this->actingAs($user)
            ->post(route('peserta.tim.store'), $this->teamData([
                'nim_anggota1' => '100001', // Same as nim_ketua
            ]));

        $response->assertSessionHasErrors(['nim_anggota1']);
    }

    public function test_duplicate_nim_anggota2_with_ketua_rejected(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)
            ->post(route('peserta.tim.store'), $this->teamData([
                'nama_anggota2' => 'Anggota Dua',
                'nim_anggota2' => '100001', // Same as nim_ketua
                'jurusan_anggota2' => 'Teknik',
            ]));

        $response->assertSessionHasErrors(['nim_anggota2']);
    }

    public function test_peserta_cannot_edit_team_when_status_is_pending(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $tim = Tim::create([
            'id_user' => $user->id_user,
            'nama_tim' => 'Original Team',
            'universitas' => 'Universitas Indonesia',
            'status_seleksi' => 'belum_seleksi',
            'batch' => 1,
        ]);

        $tim->members()->create([
            'nama_peserta' => $user->name,
            'nim_peserta' => '100001',
            'jurusan' => 'Ilmu Komputer',
            'role' => 'ketua',
        ]);
        $tim->members()->create([
            'nama_peserta' => 'Anggota Satu',
            'nim_peserta' => '100002',
            'jurusan' => 'Sistem Informasi',
            'role' => 'anggota',
        ]);

        // Create pending registration document
        DokumenRegistrasi::create([
            'id_tim' => $tim->id_tim,
            'link_file_registrasi' => 'test.pdf',
            'status_registrasi' => StatusRegistrasi::Pending->value,
            'uploaded_at' => now(),
        ]);

        // Attempt to edit team data
        $response = $this->actingAs($user)
            ->post(route('peserta.tim.store'), $this->teamData([
                'nama_tim' => 'Updated Team',
            ]));

        $response->assertSessionHasErrors(['nama_tim']);
        $this->assertDatabaseHas('tim', [
            'id_tim' => $tim->id_tim,
            'nama_tim' => 'Original Team',
        ]);
    }
}
