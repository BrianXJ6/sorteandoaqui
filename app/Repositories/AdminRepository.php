<?php

namespace App\Repositories;

use App\Models\Admin;
use App\Repositories\Contracts\AuthLogin;
use Illuminate\Foundation\Auth\User as Authenticatable;

class AdminRepository extends BaseRepository implements AuthLogin
{
    /**
     * Model belonging to this class
     *
     * @return string
     */
    public function modelClass(): string
    {
        return Admin::class;
    }

    /**
     * Set last login for admin
     *
     * @param int|\Illuminate\Foundation\Auth\User
     *
     * @return bool
     */
    public function setLastLogin(int|Authenticatable $target): bool
    {
        $admin = $this->resolveTarget($target);

        return $admin->forceFill(['last_login' => now()])->save();
    }

    /**
     * Update password.
     *
     * @param string $password
     * @param int|\App\Models\Admin $target
     *
     * @return bool
     */
    public function updatePassword(string $password, int|Admin $target): bool
    {
        $admin = $this->resolveTarget($target);

        return $admin->forceFill(['password' => $password, 'last_login' => now()])->save();
    }
}
