<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureModels();
        $this->configureUrls();
    }

    /**
     * Configure default behaviors for production-ready applications.
     *
     * @return void
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(
            fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    /**
     * Make Eloquent fail loudly outside production (lazy loading, discarded
     * and missing attributes).
     *
     * @return void
     */
    protected function configureModels(): void
    {
        Model::shouldBeStrict(!app()->isProduction());
    }

    /**
     * Force HTTPS URLs in production.
     *
     * @return void
     */
    protected function configureUrls(): void
    {
        URL::forceHttps(app()->isProduction());
    }
}
