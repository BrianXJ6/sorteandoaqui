<?php

namespace App\Providers;

use App\Models\{
    User,
    Admin,
};

use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Password;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Gates
        $this->defineGates();

        // Password default setting
        $this->passwordDefaultsSetting();
    }

    /**
     * Handle creation of gates in the application
     *
     * @return void
     */
    protected function defineGates(): void
    {
        // Only for admins
        Gate::define('adminOnly', fn (Authenticatable $logged) => $logged instanceof Admin);

        // Only for users
        Gate::define('userOnly', fn (Authenticatable $logged) => $logged instanceof User);

        // Only users with unverified email
        Gate::define('unverified-email', fn (User $user) => !$user->hasVerifiedEmail());
    }

    /**
     * Handle default setting for passwords
     *
     * @return void
     */
    protected function passwordDefaultsSetting(): void
    {
        Password::defaults(function () {
            $rule = Password::min(8);

            return $this->app->isProduction()
                ? $rule->symbols()->mixedCase()->letters()->numbers()
                : $rule;
        });
    }
}
