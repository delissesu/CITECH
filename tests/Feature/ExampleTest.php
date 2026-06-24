<?php

namespace Tests\Feature;

use App\Models\Timeline;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_a_successful_response()
    {
        $response = $this->get('/');

        $response->assertOk();
    }

    public function test_landing_page_renders_with_timeline_data()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        Timeline::create([
            'tanggal_mulai' => now()->subDays(5)->toDateString(),
            'tanggal_selesai' => now()->addDays(10)->toDateString(),
            'tahap' => 'pendaftaran_b1',
            'updated_by' => $admin->id_user,
        ]);

        Timeline::create([
            'tanggal_mulai' => now()->addDays(11)->toDateString(),
            'tanggal_selesai' => now()->addDays(30)->toDateString(),
            'tahap' => 'penyisihan',
            'updated_by' => $admin->id_user,
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Welcome')
            ->has('activeTimeline')
            ->has('allTimelines', 2)
        );
    }
}
