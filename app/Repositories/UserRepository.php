<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\AuthLogin;
use Illuminate\Foundation\Auth\User as Authenticatable;

class UserRepository extends BaseRepository implements AuthLogin
{
    /**
     * Model belonging to this class
     *
     * @return string
     */
    public function modelClass(): string
    {
        return User::class;
    }

    /**
     * Set last login for user
     *
     * @param int|\Illuminate\Foundation\Auth\User
     *
     * @return bool
     */
    public function setLastLogin(int|Authenticatable $target): bool
    {
        $user = $this->resolveTarget($target);

        return $user->forceFill(['last_login' => now()])->save();
    }

    /**
     * Register new customer custom flow
     *
     * @param array $attributes
     *
     * @return \App\Models\User
     */
    public function signup(array $attributes): User
    {
        $user = $this->newInstance()->forceFill($attributes);
        $user->save();

        return $user;
    }

    /**
     * Get user by their referral code
     *
     * @param string $referralCode
     *
     * @return \App\Models\User
     */
    public function getUserByReferralCode(string $referralCode): User
    {
        return $this->newInstance()->where('referral_code', $referralCode)->firstOrFail();
    }

    /**
     * Update password.
     *
     * @param string $password
     * @param int|\App\Models\User $target
     *
     * @return bool
     */
    public function updatePassword(string $password, int|User $target): bool
    {
        $user = $this->resolveTarget($target);

        return $user->forceFill(['password' => $password, 'last_login' => now()])->save();
    }
}
