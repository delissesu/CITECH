<?php

namespace Tests\Feature;

use App\Enums\StatusRegistrasi;
use App\Models\DokumenRegistrasi;
use App\Models\Tim;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RegistrationDocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_peserta_can_upload_registration_document(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['is_admin' => false]);
        $tim = Tim::create([
            'id_user' => $user->id_user,
            'nama_tim' => 'Test Team',
            'universitas' => 'Universitas Indonesia',
            'status_seleksi' => 'belum_seleksi',
            'batch' => 1,
        ]);

        $file = UploadedFile::fake()->create('dokumen.pdf', 1024, 'application/pdf');

        $response = $this->actingAs($user)
            ->post(route('peserta.tim.dokumen.store'), [
                'file_dokumen' => $file,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('dokumen_registrasi', [
            'id_tim' => $tim->id_tim,
            'status_registrasi' => StatusRegistrasi::Pending->value,
        ]);

        $doc = DokumenRegistrasi::where('id_tim', $tim->id_tim)->first();
        $this->assertNotNull($doc);
        Storage::disk('public')->assertExists($doc->link_file_registrasi);
    }

    public function test_peserta_can_cancel_pending_registration_document(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['is_admin' => false]);
        $tim = Tim::create([
            'id_user' => $user->id_user,
            'nama_tim' => 'Test Team',
            'universitas' => 'Universitas Indonesia',
            'status_seleksi' => 'belum_seleksi',
            'batch' => 1,
        ]);

        $file = UploadedFile::fake()->create('dokumen.pdf', 1024, 'application/pdf');
        $path = $file->store('dokumen_registrasi', 'public');

        DokumenRegistrasi::create([
            'id_tim' => $tim->id_tim,
            'link_file_registrasi' => $path,
            'status_registrasi' => StatusRegistrasi::Pending->value,
            'uploaded_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->delete(route('peserta.tim.dokumen.destroy'));

        $response->assertRedirect();
        $this->assertDatabaseMissing('dokumen_registrasi', [
            'id_tim' => $tim->id_tim,
        ]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_peserta_cannot_cancel_approved_registration_document(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['is_admin' => false]);
        $tim = Tim::create([
            'id_user' => $user->id_user,
            'nama_tim' => 'Test Team',
            'universitas' => 'Universitas Indonesia',
            'status_seleksi' => 'belum_seleksi',
            'batch' => 1,
        ]);

        $file = UploadedFile::fake()->create('dokumen.pdf', 1024, 'application/pdf');
        $path = $file->store('dokumen_registrasi', 'public');

        DokumenRegistrasi::create([
            'id_tim' => $tim->id_tim,
            'link_file_registrasi' => $path,
            'status_registrasi' => StatusRegistrasi::Berhasil->value,
            'uploaded_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->delete(route('peserta.tim.dokumen.destroy'));

        $response->assertSessionHasErrors(['file_dokumen']);
        $this->assertDatabaseHas('dokumen_registrasi', [
            'id_tim' => $tim->id_tim,
            'status_registrasi' => StatusRegistrasi::Berhasil->value,
        ]);
    }

    public function test_peserta_cannot_upload_when_document_already_approved(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['is_admin' => false]);
        $tim = Tim::create([
            'id_user' => $user->id_user,
            'nama_tim' => 'Test Team',
            'universitas' => 'Universitas Indonesia',
            'status_seleksi' => 'penyisihan',
            'batch' => 1,
        ]);

        DokumenRegistrasi::create([
            'id_tim' => $tim->id_tim,
            'link_file_registrasi' => 'existing.pdf',
            'status_registrasi' => StatusRegistrasi::Berhasil->value,
            'uploaded_at' => now(),
        ]);

        $file = UploadedFile::fake()->create('dokumen.pdf', 1024, 'application/pdf');

        $response = $this->actingAs($user)
            ->post(route('peserta.tim.dokumen.store'), [
                'file_dokumen' => $file,
            ]);

        $response->assertSessionHasErrors(['file_dokumen']);
    }

    public function test_upload_requires_team_to_exist(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['is_admin' => false]);

        $file = UploadedFile::fake()->create('dokumen.pdf', 1024, 'application/pdf');

        $response = $this->actingAs($user)
            ->post(route('peserta.tim.dokumen.store'), [
                'file_dokumen' => $file,
            ]);

        $response->assertSessionHasErrors(['file_dokumen']);
    }

    public function test_file_must_be_pdf_and_under_5mb(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['is_admin' => false]);
        Tim::create([
            'id_user' => $user->id_user,
            'nama_tim' => 'Test Team',
            'universitas' => 'Universitas Indonesia',
            'status_seleksi' => 'belum_seleksi',
            'batch' => 1,
        ]);

        // Test non-PDF file
        $pngFile = UploadedFile::fake()->create('image.png', 1024, 'image/png');

        $response = $this->actingAs($user)
            ->post(route('peserta.tim.dokumen.store'), [
                'file_dokumen' => $pngFile,
            ]);

        $response->assertSessionHasErrors(['file_dokumen']);

        // Test file over 5MB
        $largeFile = UploadedFile::fake()->create('large.pdf', 6000, 'application/pdf');

        $response = $this->actingAs($user)
            ->post(route('peserta.tim.dokumen.store'), [
                'file_dokumen' => $largeFile,
            ]);

        $response->assertSessionHasErrors(['file_dokumen']);
    }

    public function test_peserta_can_reupload_after_rejection(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['is_admin' => false]);
        $tim = Tim::create([
            'id_user' => $user->id_user,
            'nama_tim' => 'Test Team',
            'universitas' => 'Universitas Indonesia',
            'status_seleksi' => 'belum_seleksi',
            'batch' => 1,
        ]);

        $oldFile = UploadedFile::fake()->create('old.pdf', 512, 'application/pdf');
        $oldPath = $oldFile->store('dokumen_registrasi', 'public');

        DokumenRegistrasi::create([
            'id_tim' => $tim->id_tim,
            'link_file_registrasi' => $oldPath,
            'status_registrasi' => StatusRegistrasi::Ditolak->value,
            'catatan_registrasi' => 'Dokumen tidak jelas',
            'uploaded_at' => now(),
        ]);

        $newFile = UploadedFile::fake()->create('new.pdf', 1024, 'application/pdf');

        $response = $this->actingAs($user)
            ->post(route('peserta.tim.dokumen.store'), [
                'file_dokumen' => $newFile,
            ]);

        $response->assertRedirect();
        Storage::disk('public')->assertMissing($oldPath);

        $doc = DokumenRegistrasi::where('id_tim', $tim->id_tim)->first();
        $this->assertEquals(StatusRegistrasi::Pending->value, $doc->status_registrasi);
        Storage::disk('public')->assertExists($doc->link_file_registrasi);
    }
}
