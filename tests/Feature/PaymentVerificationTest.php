<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Tim;
use App\Models\DokumenRegistrasi;
use App\Models\Pembayaran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_peserta_can_upload_payment_proof()
    {
        Storage::fake('public');

        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user);

        // Create team
        $tim = Tim::create([
            'id_user' => $user->id_user,
            'nama_tim' => 'Test Team',
            'universitas' => 'Test University',
            'status_seleksi' => 'belum_seleksi',
            'batch' => 1,
        ]);

        $file = UploadedFile::fake()->create('proof.png', 1024);

        $response = $this->post(route('peserta.tim.pembayaran.store'), [
            'bukti_pembayaran' => $file,
        ]);

        $response->assertRedirect();
        
        // Assert record exists in database
        $this->assertDatabaseHas('pembayaran', [
            'id_tim' => $tim->id_tim,
            'status_pembayaran' => 'pending',
        ]);

        // Assert file exists in storage
        $pembayaran = Pembayaran::where('id_tim', $tim->id_tim)->first();
        Storage::disk('public')->assertExists($pembayaran->bukti_pembayaran);
    }

    public function test_peserta_can_cancel_payment_proof()
    {
        Storage::fake('public');

        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user);

        $tim = Tim::create([
            'id_user' => $user->id_user,
            'nama_tim' => 'Test Team',
            'universitas' => 'Test University',
            'status_seleksi' => 'belum_seleksi',
            'batch' => 1,
        ]);

        $filePath = Storage::disk('public')->put('bukti_pembayaran', UploadedFile::fake()->create('proof.png', 1024));

        $pembayaran = Pembayaran::create([
            'id_tim' => $tim->id_tim,
            'bukti_pembayaran' => $filePath,
            'status_pembayaran' => 'pending',
            'uploaded_at' => now(),
        ]);

        Storage::disk('public')->assertExists($filePath);

        $response = $this->delete(route('peserta.tim.pembayaran.destroy'));

        $response->assertRedirect();
        
        // Assert record is deleted from database
        $this->assertDatabaseMissing('pembayaran', [
            'id_pembayaran' => $pembayaran->id_pembayaran,
        ]);

        // Assert file is deleted from storage
        Storage::disk('public')->assertMissing($filePath);
    }

    public function test_admin_can_approve_payment_and_updates_team_status_to_penyisihan()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_admin' => false]);

        $tim = Tim::create([
            'id_user' => $user->id_user,
            'nama_tim' => 'Test Team',
            'universitas' => 'Test University',
            'status_seleksi' => 'belum_seleksi',
            'batch' => 1,
        ]);

        // Create approved registration document (required before team can advance)
        DokumenRegistrasi::create([
            'id_tim' => $tim->id_tim,
            'link_file_registrasi' => 'dokumen_registrasi/test.pdf',
            'status_registrasi' => 'berhasil',
            'uploaded_at' => now(),
        ]);

        $pembayaran = Pembayaran::create([
            'id_tim' => $tim->id_tim,
            'bukti_pembayaran' => 'bukti_pembayaran/test.png',
            'status_pembayaran' => 'pending',
            'uploaded_at' => now(),
        ]);

        $this->actingAs($admin);

        $response = $this->post(route('admin.pembayaran.update', $pembayaran->id_pembayaran), [
            'status' => 'berhasil',
        ]);

        $response->assertRedirect();

        // Assert payment is approved
        $this->assertDatabaseHas('pembayaran', [
            'id_pembayaran' => $pembayaran->id_pembayaran,
            'status_pembayaran' => 'berhasil',
        ]);

        // Assert team status is updated to penyisihan
        $this->assertDatabaseHas('tim', [
            'id_tim' => $tim->id_tim,
            'status_seleksi' => 'penyisihan',
        ]);
    }

    public function test_admin_approve_payment_does_not_advance_team_without_approved_document()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_admin' => false]);

        $tim = Tim::create([
            'id_user' => $user->id_user,
            'nama_tim' => 'Test Team No Doc',
            'universitas' => 'Test University',
            'status_seleksi' => 'belum_seleksi',
            'batch' => 1,
        ]);

        // Create a PENDING (not approved) registration document
        DokumenRegistrasi::create([
            'id_tim' => $tim->id_tim,
            'link_file_registrasi' => 'dokumen_registrasi/pending.pdf',
            'status_registrasi' => 'pending',
            'uploaded_at' => now(),
        ]);

        $pembayaran = Pembayaran::create([
            'id_tim' => $tim->id_tim,
            'bukti_pembayaran' => 'bukti_pembayaran/test2.png',
            'status_pembayaran' => 'pending',
            'uploaded_at' => now(),
        ]);

        $this->actingAs($admin);

        $response = $this->post(route('admin.pembayaran.update', $pembayaran->id_pembayaran), [
            'status' => 'berhasil',
        ]);

        $response->assertRedirect();

        // Assert payment is approved
        $this->assertDatabaseHas('pembayaran', [
            'id_pembayaran' => $pembayaran->id_pembayaran,
            'status_pembayaran' => 'berhasil',
        ]);

        // Assert team status remains belum_seleksi (document not yet approved)
        $this->assertDatabaseHas('tim', [
            'id_tim' => $tim->id_tim,
            'status_seleksi' => 'belum_seleksi',
        ]);
    }

    public function test_admin_can_reject_payment_with_notes()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_admin' => false]);

        $tim = Tim::create([
            'id_user' => $user->id_user,
            'nama_tim' => 'Test Team',
            'universitas' => 'Test University',
            'status_seleksi' => 'belum_seleksi',
            'batch' => 1,
        ]);

        $pembayaran = Pembayaran::create([
            'id_tim' => $tim->id_tim,
            'bukti_pembayaran' => 'bukti_pembayaran/test.png',
            'status_pembayaran' => 'pending',
            'uploaded_at' => now(),
        ]);

        $this->actingAs($admin);

        $response = $this->post(route('admin.pembayaran.update', $pembayaran->id_pembayaran), [
            'status' => 'ditolak',
            'catatan' => 'Nominal transfer tidak sesuai.',
        ]);

        $response->assertRedirect();

        // Assert payment is rejected with notes
        $this->assertDatabaseHas('pembayaran', [
            'id_pembayaran' => $pembayaran->id_pembayaran,
            'status_pembayaran' => 'ditolak',
            'catatan_pembayaran' => 'Nominal transfer tidak sesuai.',
        ]);

        // Assert team status is still belum_seleksi
        $this->assertDatabaseHas('tim', [
            'id_tim' => $tim->id_tim,
            'status_seleksi' => 'belum_seleksi',
        ]);
    }
}
