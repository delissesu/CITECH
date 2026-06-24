<?php

namespace Tests\Feature;

use App\Models\Sponsor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SponsorManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_non_admin_cannot_access_or_modify_sponsors()
    {
        $peserta = User::factory()->create(['is_admin' => false]);
        $this->actingAs($peserta);

        // Cannot view
        $response = $this->get(route('admin.kelola-sponsor'));
        $response->assertStatus(403);

        // Cannot store
        $response = $this->post(route('admin.kelola-sponsor.store'), [
            'nama_sponsor' => 'Sponsor Jahat',
            'logo_sponsor' => UploadedFile::fake()->image('logo.png'),
        ]);
        $response->assertStatus(403);

        // Cannot delete
        $sponsor = Sponsor::create([
            'nama_sponsor' => 'Sponsor Asli',
            'logo_sponsor' => 'sponsors/asli.png',
        ]);
        $response = $this->delete(route('admin.kelola-sponsor.destroy', $sponsor->id_sponsor));
        $response->assertStatus(403);
    }

    public function test_admin_can_access_sponsor_list()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        Sponsor::create([
            'nama_sponsor' => 'Brand A',
            'logo_sponsor' => 'sponsors/a.png',
            'order' => 1,
            'is_active' => true,
        ]);

        $response = $this->get(route('admin.kelola-sponsor'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('admin/KelolaSponsor')
            ->has('sponsors', 1)
            ->where('sponsors.0.nama_sponsor', 'Brand A')
        );
    }

    public function test_admin_can_store_new_sponsor_with_logo()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $file = UploadedFile::fake()->image('techcorp_logo.png');

        $response = $this->post(route('admin.kelola-sponsor.store'), [
            'nama_sponsor' => 'TechCorp',
            'logo_sponsor' => $file,
            'link_sponsor' => 'https://techcorp.com',
            'order' => 5,
            'is_active' => true,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('sponsors', [
            'nama_sponsor' => 'TechCorp',
            'link_sponsor' => 'https://techcorp.com',
            'order' => 5,
            'is_active' => true,
        ]);

        $sponsor = Sponsor::first();
        Storage::disk('public')->assertExists($sponsor->logo_sponsor);
    }

    public function test_admin_can_update_sponsor_and_clean_up_old_logo()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        // Upload initial logo
        $oldFile = UploadedFile::fake()->image('old_logo.png');
        $oldPath = $oldFile->store('sponsors', 'public');

        $sponsor = Sponsor::create([
            'nama_sponsor' => 'Sponsor Lama',
            'logo_sponsor' => $oldPath,
            'link_sponsor' => 'https://old.com',
            'order' => 1,
            'is_active' => true,
        ]);

        Storage::disk('public')->assertExists($oldPath);

        // Update with new logo
        $newFile = UploadedFile::fake()->image('new_logo.png');

        $response = $this->post(route('admin.kelola-sponsor.update', $sponsor->id_sponsor), [
            'nama_sponsor' => 'Sponsor Baru',
            'logo_sponsor' => $newFile,
            'link_sponsor' => 'https://new.com',
            'order' => 2,
            'is_active' => false,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('sponsors', [
            'id_sponsor' => $sponsor->id_sponsor,
            'nama_sponsor' => 'Sponsor Baru',
            'link_sponsor' => 'https://new.com',
            'order' => 2,
            'is_active' => false,
        ]);

        $sponsor->refresh();

        // Old logo should be deleted
        Storage::disk('public')->assertMissing($oldPath);
        // New logo should exist
        Storage::disk('public')->assertExists($sponsor->logo_sponsor);
    }

    public function test_admin_can_delete_sponsor_and_clean_up_logo()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $file = UploadedFile::fake()->image('logo.png');
        $path = $file->store('sponsors', 'public');

        $sponsor = Sponsor::create([
            'nama_sponsor' => 'Sponsor Hapus',
            'logo_sponsor' => $path,
        ]);

        Storage::disk('public')->assertExists($path);

        $response = $this->delete(route('admin.kelola-sponsor.destroy', $sponsor->id_sponsor));
        $response->assertRedirect();

        $this->assertDatabaseMissing('sponsors', [
            'id_sponsor' => $sponsor->id_sponsor,
        ]);

        Storage::disk('public')->assertMissing($path);
    }

    public function test_active_sponsors_appear_on_landing_page_ordered_correctly()
    {
        // 1. Active sponsor with order 2
        Sponsor::create([
            'nama_sponsor' => 'Brand Order 2',
            'logo_sponsor' => 'sponsors/2.png',
            'order' => 2,
            'is_active' => true,
        ]);

        // 2. Active sponsor with order 1
        Sponsor::create([
            'nama_sponsor' => 'Brand Order 1',
            'logo_sponsor' => 'sponsors/1.png',
            'order' => 1,
            'is_active' => true,
        ]);

        // 3. Inactive sponsor
        Sponsor::create([
            'nama_sponsor' => 'Brand Inactive',
            'logo_sponsor' => 'sponsors/inactive.png',
            'order' => 0,
            'is_active' => false,
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);

        $response->assertInertia(fn ($page) => $page
            ->component('Welcome')
            ->has('sponsors', 2)
            // Should be sorted by 'order' ascending, so Order 1 comes first
            ->where('sponsors.0.nama_sponsor', 'Brand Order 1')
            ->where('sponsors.1.nama_sponsor', 'Brand Order 2')
        );
    }
}
