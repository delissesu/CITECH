<?php

namespace Tests\Feature;

use App\Enums\StatusRegistrasi;
use App\Models\DokumenRegistrasi;
use App\Models\Tim;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRegistrationVerificationTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function createTeamWithDocument(string $status = 'pending'): array
    {
        $user = User::factory()->create(['is_admin' => false]);
        $tim = Tim::create([
            'id_user' => $user->id_user,
            'nama_tim' => 'Test Team',
            'universitas' => 'Universitas Indonesia',
            'status_seleksi' => 'belum_seleksi',
            'batch' => 1,
        ]);
        $doc = DokumenRegistrasi::create([
            'id_tim' => $tim->id_tim,
            'link_file_registrasi' => 'test.pdf',
            'status_registrasi' => $status,
            'uploaded_at' => now(),
        ]);

        return [$user, $tim, $doc];
    }

    public function test_admin_can_approve_registration_document(): void
    {
        $admin = $this->createAdmin();
        [$user, $tim, $doc] = $this->createTeamWithDocument();

        $response = $this->actingAs($admin)
            ->post(route('admin.persyaratan.update', $doc->id_registrasi), [
                'status' => 'berhasil',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('dokumen_registrasi', [
            'id_registrasi' => $doc->id_registrasi,
            'status_registrasi' => StatusRegistrasi::Berhasil->value,
        ]);
    }

    public function test_admin_can_reject_registration_with_required_notes(): void
    {
        $admin = $this->createAdmin();
        [$user, $tim, $doc] = $this->createTeamWithDocument();

        $response = $this->actingAs($admin)
            ->post(route('admin.persyaratan.update', $doc->id_registrasi), [
                'status' => 'ditolak',
                'catatan' => 'Dokumen tidak lengkap dan tidak memenuhi syarat.',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('dokumen_registrasi', [
            'id_registrasi' => $doc->id_registrasi,
            'status_registrasi' => StatusRegistrasi::Ditolak->value,
            'catatan_registrasi' => 'Dokumen tidak lengkap dan tidak memenuhi syarat.',
        ]);
    }

    public function test_admin_reject_without_notes_fails_validation(): void
    {
        $admin = $this->createAdmin();
        [$user, $tim, $doc] = $this->createTeamWithDocument();

        $response = $this->actingAs($admin)
            ->post(route('admin.persyaratan.update', $doc->id_registrasi), [
                'status' => 'ditolak',
                // Missing 'catatan'
            ]);

        $response->assertSessionHasErrors(['catatan']);
        $this->assertDatabaseHas('dokumen_registrasi', [
            'id_registrasi' => $doc->id_registrasi,
            'status_registrasi' => StatusRegistrasi::Pending->value,
        ]);
    }

    public function test_admin_cannot_reprocess_already_processed_document(): void
    {
        $admin = $this->createAdmin();
        [$user, $tim, $doc] = $this->createTeamWithDocument(StatusRegistrasi::Berhasil->value);

        $response = $this->actingAs($admin)
            ->post(route('admin.persyaratan.update', $doc->id_registrasi), [
                'status' => 'ditolak',
                'catatan' => 'Changed my mind.',
            ]);

        $response->assertSessionHasErrors(['status']);
        $this->assertDatabaseHas('dokumen_registrasi', [
            'id_registrasi' => $doc->id_registrasi,
            'status_registrasi' => StatusRegistrasi::Berhasil->value,
        ]);
    }

    public function test_non_admin_cannot_access_registration_verification(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)
            ->get(route('admin.persyaratan'));

        $response->assertForbidden();
    }

    public function test_admin_can_view_registration_verification_page(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->get(route('admin.persyaratan'));

        $response->assertOk();
    }

    public function test_admin_approve_clears_catatan_field(): void
    {
        $admin = $this->createAdmin();
        [$user, $tim, $doc] = $this->createTeamWithDocument();

        $response = $this->actingAs($admin)
            ->post(route('admin.persyaratan.update', $doc->id_registrasi), [
                'status' => 'berhasil',
                'catatan' => 'This should be ignored',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('dokumen_registrasi', [
            'id_registrasi' => $doc->id_registrasi,
            'status_registrasi' => StatusRegistrasi::Berhasil->value,
            'catatan_registrasi' => null,
        ]);
    }
}
