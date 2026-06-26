<?php

namespace Tests\Feature\Settings;

use App\Models\DokumenRegistrasi;
use App\Models\MemberTim;
use App\Models\Tim;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('profile.edit'));

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch(route('profile.update'), [
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

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit'));

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete(route('profile.destroy'), [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('profile.edit'))
            ->delete(route('profile.destroy'), [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrors('password')
            ->assertRedirect(route('profile.edit'));

        $this->assertNotNull($user->fresh());
    }

    public function test_profile_update_updates_team_leader_name_when_document_not_approved()
    {
        $user = User::factory()->create();
        $tim = Tim::create([
            'id_user' => $user->id_user,
            'nama_tim' => 'Test Team 123',
            'universitas' => 'Universitas Indonesia',
            'status_seleksi' => 'belum_seleksi',
            'batch' => 1,
        ]);
        $member = MemberTim::create([
            'id_tim' => $tim->id_tim,
            'nama_peserta' => $user->name,
            'nim_peserta' => '12345678',
            'jurusan' => 'Informatika',
            'role' => 'ketua',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'New Leader Name',
                'email' => $user->email,
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('member_tim', [
            'id_member' => $member->id_member,
            'nama_peserta' => 'New Leader Name',
        ]);
    }

    public function test_profile_update_does_not_update_team_leader_name_when_document_approved()
    {
        $user = User::factory()->create();
        $originalName = $user->name;
        $tim = Tim::create([
            'id_user' => $user->id_user,
            'nama_tim' => 'Test Team 456',
            'universitas' => 'Universitas Indonesia',
            'status_seleksi' => 'belum_seleksi',
            'batch' => 1,
        ]);
        DokumenRegistrasi::create([
            'id_tim' => $tim->id_tim,
            'link_file_registrasi' => 'test.pdf',
            'status_registrasi' => 'berhasil',
            'uploaded_at' => now(),
        ]);
        $member = MemberTim::create([
            'id_tim' => $tim->id_tim,
            'nama_peserta' => $user->name,
            'nim_peserta' => '12345678',
            'jurusan' => 'Informatika',
            'role' => 'ketua',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'New Leader Name',
                'email' => $user->email,
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('member_tim', [
            'id_member' => $member->id_member,
            'nama_peserta' => $originalName,
        ]);
    }
}
