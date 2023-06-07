<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\{
    HasMany,
    BelongsTo,
};

use App\Notifications\User\{
    VerifyEmailNotification,
    ResetPasswordNotification,
};

use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'cpf',
        'nick',
        'name',
        'email',
        'phone',
        'referral_code',
        'password',
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'admin_id',
        'referred_id',
        'email_verified_at',
        'phone_verified_at',
        'last_login',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'admin_id' => 'integer',
        'referred_id' => 'integer',
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_login' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Boot
    |--------------------------------------------------------------------------
    */

    /**
     * The "booted" method of the user model.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::creating(function (User $user) {
            $user->nick = explode(' ', $user->name)[0];
            $user->referral_code = (new self)->generateRefferalCode();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Mutators
    |--------------------------------------------------------------------------
    */

    /**
     * Interact with the user's name.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function name(): Attribute
    {
        return Attribute::make(set: fn (string $value) => Str::of($value)->replaceMatches('/[^a-zA-Zà-úÀ-Ú\s\.]+/', '')->squish()->title()->value());
    }

    /**
     * Interact with the user's nick.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function nick(): Attribute
    {
        return Attribute::make(set: fn (?string $value) => Str::substr($value ?? explode(' ', $this->name)[0], 0, 10));
    }

    /**
     * Interact with the user's email.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function email(): Attribute
    {
        return Attribute::make(set: fn (string $value) => strtolower($value));
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get the responsible admin.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Get the referred User.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function referred(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the all users referrals for this model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function referrals(): HasMany
    {
        return $this->hasMany(User::class, 'referred_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */

    /**
     * Send the password reset notification.
     *
     * @param string $token
     *
     * @return void
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification);
    }

    /*
    |--------------------------------------------------------------------------
    | Functions
    |--------------------------------------------------------------------------
    */

    /**
     * Automatic generation of valid referral code
     *
     * @return string
     */
    public function generateRefferalCode(): string
    {
        do $referralCode = Str::random(10);
        while (!is_null(self::where('referral_code', $referralCode)->first()));

        return $referralCode;
    }

    /**
     * Mark the given user's email as unverified.
     *
     * @return bool
     */
    public function markEmailAsUnverified(): bool
    {
        return $this->forceFill(['email_verified_at' => null])->save();
    }
}
