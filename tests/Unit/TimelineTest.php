<?php

namespace Tests\Unit;

use App\Models\Timeline;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimelineTest extends TestCase
{
    use RefreshDatabase;

    private function createTimeline(string $tahap, string $startDate, string $endDate): Timeline
    {
        $user = User::factory()->create(['is_admin' => true]);

        return Timeline::create([
            'tanggal_mulai' => $startDate,
            'tanggal_selesai' => $endDate,
            'tahap' => $tahap,
            'updated_by' => $user->id_user,
        ]);
    }

    public function test_active_scope_filters_non_expired(): void
    {
        $this->createTimeline('pendaftaran_b1', now()->subDays(10)->toDateString(), now()->addDays(5)->toDateString());
        $this->createTimeline('pendaftaran_b2', now()->subDays(20)->toDateString(), now()->subDays(1)->toDateString());
        $this->createTimeline('penyisihan', now()->addDays(1)->toDateString(), now()->addDays(30)->toDateString());

        $active = Timeline::active()->get();

        $this->assertCount(2, $active);
        $this->assertEquals('pendaftaran_b1', $active->first()->tahap);
        $this->assertEquals('penyisihan', $active->last()->tahap);
    }

    public function test_tahap_scope_filters_by_stage(): void
    {
        $this->createTimeline('pendaftaran_b1', now()->subDays(10)->toDateString(), now()->addDays(5)->toDateString());
        $this->createTimeline('pendaftaran_b2', now()->subDays(10)->toDateString(), now()->addDays(5)->toDateString());
        $this->createTimeline('penyisihan', now()->subDays(10)->toDateString(), now()->addDays(5)->toDateString());

        $result = Timeline::tahap('pendaftaran_b2')->get();

        $this->assertCount(1, $result);
        $this->assertEquals('pendaftaran_b2', $result->first()->tahap);
    }

    public function test_current_active_returns_active_or_latest(): void
    {
        // No active timelines (all expired) — should return the most recent expired one
        $this->createTimeline('pendaftaran_b1', now()->subDays(30)->toDateString(), now()->subDays(10)->toDateString());
        $this->createTimeline('pendaftaran_b2', now()->subDays(20)->toDateString(), now()->subDays(5)->toDateString());

        $result = Timeline::currentActive();

        $this->assertNotNull($result);
        $this->assertEquals('pendaftaran_b2', $result->tahap);

        // Now add an active timeline — should return that instead
        $active = $this->createTimeline('penyisihan', now()->subDay()->toDateString(), now()->addDays(10)->toDateString());

        $result = Timeline::currentActive();

        $this->assertNotNull($result);
        $this->assertEquals('penyisihan', $result->tahap);
    }

    public function test_current_active_returns_null_when_no_timelines_exist(): void
    {
        $result = Timeline::currentActive();

        $this->assertNull($result);
    }

    public function test_is_open_for_tahap_returns_true_when_within_deadline(): void
    {
        $this->createTimeline('pendaftaran_b2', now()->subDays(5)->toDateString(), now()->addDays(5)->toDateString());

        $this->assertTrue(Timeline::isOpenForTahap('pendaftaran_b2'));
    }

    public function test_is_open_for_tahap_returns_false_when_expired(): void
    {
        $this->createTimeline('pendaftaran_b2', now()->subDays(10)->toDateString(), now()->subDay()->toDateString());

        $this->assertFalse(Timeline::isOpenForTahap('pendaftaran_b2'));
    }

    public function test_is_open_for_tahap_returns_false_when_tahap_does_not_exist(): void
    {
        $this->assertFalse(Timeline::isOpenForTahap('nonexistent_tahap'));
    }
}
