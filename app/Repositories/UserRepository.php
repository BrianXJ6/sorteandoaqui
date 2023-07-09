<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Collection;
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

    /**
     * Get notifications list
     *
     * @return \Illuminate\Support\Collection
     */
    public function notificationsList(): Collection
    {
        /** @var \App\Models\User */
        $user = auth()->user();

        return $user->notifications()->select(['id', 'data', 'read_at', 'created_at'])->latest()->get();
    }

    /**
     * Mark notification as read
     *
     * @param string $notificationId
     *
     * @return void
     */
    public function markRead(string $notificationId): void
    {
        /** @var \App\Models\User */
        $user = auth()->user();

        $user->notifications()->where('id', $notificationId)->update(['read_at' => now()]);
    }
}
