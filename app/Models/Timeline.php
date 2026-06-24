<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Timeline extends Model
{
    /** @use HasFactory<Factory<Timeline>> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'timeline';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id_timeline';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'tanggal_mulai',
        'tanggal_selesai',
        'tahap',
        'updated_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'datetime',
            'tanggal_selesai' => 'datetime',
        ];
    }

    /**
     * Scope: only timelines that have not yet ended.
     *
     * @param  Builder<Timeline>  $query
     * @return Builder<Timeline>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('tanggal_selesai', '>', now())
            ->orderBy('tanggal_selesai', 'asc');
    }

    /**
     * Scope: filter by tahap (stage) name.
     *
     * @param  Builder<Timeline>  $query
     * @return Builder<Timeline>
     */
    public function scopeTahap(Builder $query, string $tahap): Builder
    {
        return $query->where('tahap', $tahap);
    }

    /**
     * Get the current active timeline, falling back to the most recently ended one.
     */
    public static function currentActive(): ?self
    {
        return static::active()->first()
            ?? static::orderBy('tanggal_selesai', 'desc')->first();
    }

    /**
     * Get the current active timeline for a specific tahap.
     */
    public static function currentForTahap(string $tahap): ?self
    {
        return static::tahap($tahap)->first();
    }

    /**
     * Check whether the given tahap is still open (not past its end date).
     */
    public static function isOpenForTahap(string $tahap): bool
    {
        $timeline = static::currentForTahap($tahap);

        return $timeline ? now()->lte($timeline->tanggal_selesai) : false;
    }

    /**
     * Get the user who last updated this timeline event.
     *
     * @return BelongsTo<User, $this>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id_user');
    }
}
