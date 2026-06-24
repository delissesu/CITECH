<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cannot_access_peserta_routes(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)
            ->get(route('peserta.tim'));

        $response->assertForbidden();
    }

    public function test_peserta_cannot_access_admin_routes(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $adminRoutes = [
            'admin.dashboard',
            'admin.persyaratan',
            'admin.pembayaran',
            'admin.tim-terdaftar',
            'admin.submission',
            'admin.atur-tanggal',
            'admin.kelola-sponsor',
        ];

        foreach ($adminRoutes as $routeName) {
            $response = $this->actingAs($user)
                ->get(route($routeName));

            $response->assertForbidden();
        }
    }

    public function test_admin_dashboard_redirect_from_main_dashboard(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)
            ->get(route('dashboard'));

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_unauthenticated_users_redirected_to_login_for_peserta_routes(): void
    {
        $response = $this->get(route('peserta.tim'));

        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_users_redirected_to_login_for_admin_routes(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_access_admin_payment_verification(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)
            ->get(route('admin.pembayaran'));

        $response->assertForbidden();
    }

    public function test_peserta_can_access_peserta_routes(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)
            ->get(route('peserta.tim'));

        $response->assertOk();
    }

    public function test_admin_can_access_admin_routes(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)
            ->get(route('admin.dashboard'));

        $response->assertOk();
    }
}
