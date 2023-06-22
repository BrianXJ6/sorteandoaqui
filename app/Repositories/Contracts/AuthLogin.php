<?php

namespace App\Repositories\Contracts;

use Illuminate\Foundation\Auth\User as Authenticatable;

interface AuthLogin
{
    /**
     * Set last login for user
     *
     * @param int|\Illuminate\Foundation\Auth\User $target
     *
     * @return bool
     */
    public function setLastLogin(int|Authenticatable $target): bool;
}
