<?php

namespace Tests\Feature;

use App\Models\DokumenRegistrasi;
use App\Models\Pembayaran;
use App\Models\Tim;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/settings/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/settings/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit'));

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/settings/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit'));

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/settings/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/settings/profile')
            ->delete('/settings/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrors('password')
            ->assertRedirect('/settings/profile');

        $this->assertNotNull($user->fresh());
    }

    public function test_delete_account_cleans_up_team_files(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $tim = Tim::create([
            'id_user' => $user->id_user,
            'nama_tim' => 'Test Team',
            'universitas' => 'Test University',
            'status_seleksi' => 'belum_seleksi',
            'batch' => 1,
        ]);

        $docFile = UploadedFile::fake()->create('doc.pdf', 512, 'application/pdf');
        $docPath = $docFile->store('dokumen_registrasi', 'public');

        $paymentFile = UploadedFile::fake()->create('payment.png', 512, 'image/png');
        $paymentPath = $paymentFile->store('bukti_pembayaran', 'public');

        DokumenRegistrasi::create([
            'id_tim' => $tim->id_tim,
            'link_file_registrasi' => $docPath,
            'status_registrasi' => 'pending',
            'uploaded_at' => now(),
        ]);

        Pembayaran::create([
            'id_tim' => $tim->id_tim,
            'bukti_pembayaran' => $paymentPath,
            'status_pembayaran' => 'pending',
            'uploaded_at' => now(),
        ]);

        Storage::disk('public')->assertExists($docPath);
        Storage::disk('public')->assertExists($paymentPath);

        $this->actingAs($user)
            ->delete('/settings/profile', [
                'password' => 'password',
            ])
            ->assertRedirect('/');

        Storage::disk('public')->assertMissing($docPath);
        Storage::disk('public')->assertMissing($paymentPath);
    }

    public function test_delete_account_removes_team_data(): void
    {
        $user = User::factory()->create();
        $tim = Tim::create([
            'id_user' => $user->id_user,
            'nama_tim' => 'Test Team',
            'universitas' => 'Test University',
            'status_seleksi' => 'belum_seleksi',
            'batch' => 1,
        ]);

        $tim->members()->create([
            'nama_peserta' => $user->name,
            'nim_peserta' => '111111',
            'jurusan' => 'Computer Science',
            'role' => 'ketua',
        ]);

        DokumenRegistrasi::create([
            'id_tim' => $tim->id_tim,
            'link_file_registrasi' => 'test.pdf',
            'status_registrasi' => 'pending',
            'uploaded_at' => now(),
        ]);

        Pembayaran::create([
            'id_tim' => $tim->id_tim,
            'bukti_pembayaran' => 'test.png',
            'status_pembayaran' => 'pending',
            'uploaded_at' => now(),
        ]);

        $this->actingAs($user)
            ->delete('/settings/profile', [
                'password' => 'password',
            ])
            ->assertRedirect('/');

        $this->assertDatabaseMissing('users', ['id_user' => $user->id_user]);
        $this->assertDatabaseMissing('tim', ['id_tim' => $tim->id_tim]);
        $this->assertDatabaseMissing('member_tim', ['id_tim' => $tim->id_tim]);
        $this->assertDatabaseMissing('dokumen_registrasi', ['id_tim' => $tim->id_tim]);
        $this->assertDatabaseMissing('pembayaran', ['id_tim' => $tim->id_tim]);
    }
}
