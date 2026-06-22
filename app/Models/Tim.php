<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tim extends Model
{
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
     * @var array<int, string>
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
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    /**
     * Get the members of the team.
     */
    public function members(): HasMany
    {
        return $this->hasMany(MemberTim::class, 'id_tim', 'id_tim');
    }

    /**
     * Get the registration document for the team.
     */
    public function dokumenRegistrasi(): HasOne
    {
        return $this->hasOne(DokumenRegistrasi::class, 'id_tim', 'id_tim');
    }

    /**
     * Get the payment details for the team.
     */
    public function pembayaran(): HasOne
    {
        return $this->hasOne(Pembayaran::class, 'id_tim', 'id_tim');
    }

    /**
     * Get the submission documents for the team.
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(DokumenSubmission::class, 'id_tim', 'id_tim');
    }

    /**
     * Get the single submission document for the team.
     */
    public function submission(): HasOne
    {
        return $this->hasOne(DokumenSubmission::class, 'id_tim', 'id_tim');
    }
}
