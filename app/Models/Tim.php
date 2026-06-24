<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id_tim
 * @property int $id_user
 * @property string $nama_tim
 * @property string $universitas
 * @property string $status_seleksi
 * @property int $batch
 */
class Tim extends Model
{
    /** @use HasFactory<Factory<Tim>> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tim';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id_tim';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id_user',
        'nama_tim',
        'universitas',
        'status_seleksi',
        'batch',
    ];

    /**
     * Get the user that owns the team (usually the team leader).
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    /**
     * Get the members of the team.
     *
     * @return HasMany<MemberTim, $this>
     */
    public function members(): HasMany
    {
        return $this->hasMany(MemberTim::class, 'id_tim', 'id_tim');
    }

    /**
     * Get the registration document for the team.
     *
     * @return HasOne<DokumenRegistrasi, $this>
     */
    public function dokumen_registrasi(): HasOne
    {
        return $this->hasOne(DokumenRegistrasi::class, 'id_tim', 'id_tim');
    }

    /**
     * Get the payment details for the team.
     *
     * @return HasOne<Pembayaran, $this>
     */
    public function pembayaran(): HasOne
    {
        return $this->hasOne(Pembayaran::class, 'id_tim', 'id_tim');
    }

    /**
     * Get the single submission document for the team.
     *
     * @return HasOne<DokumenSubmission, $this>
     */
    public function submission(): HasOne
    {
        return $this->hasOne(DokumenSubmission::class, 'id_tim', 'id_tim');
    }
}
