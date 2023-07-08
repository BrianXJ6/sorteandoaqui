<?php

use App\Http\Controllers\Web\User\{
    GuestController as UserGuestController,
    DashboardController as UserDashboardController,
};

use App\Http\Controllers\Web\Admin\{
    GuestController as AdminGuestController,
    DashboardController as AdminDashboardController,
};

use App\Http\Controllers\Auth\{
    User\AuthController as UserAuthController,
    Admin\AuthController as AdminAuthController,
};

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\WebController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::controller(WebController::class)->group(function () {
    Route::get('/', 'home')->name('home');

    /*
    |--------------------------------------------------------------------------
    | User Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('cliente')->name('user.')->group(function () {
        Route::middleware(['guest:user'])->controller(UserGuestController::class)->group(function () {
            Route::get('login', 'signInWeb')->name('signIn');
            Route::get('cadastro/{referral_code?}', 'signUpWeb')->name('signUp');
            Route::get('senha/redefinir', 'requestPassordReset')->name('password.request');
            Route::get('senha/resetar/{token}', 'passordReset')->name('password.reset');
        });
        Route::prefix('painel')->name('dashboard.')->middleware(['auth:user'])->controller(UserDashboardController::class)->group(function () {
            Route::get('/', 'home')->name('home');
            Route::prefix('rifas')->name('raffles.')->middleware('verified:web.user.dashboard.home')->group(function () {
                Route::get('nova', 'newRaffle')->name('new');
            });
            Route::prefix('minha-conta')->name('my-account.')->group(function () {
                Route::get('dados-pessoais', 'personalData')->name('personal-data');
                Route::get('alterar-senha', 'accessData')->name('access-data');
                Route::get('enderecos', 'addresses')->name('addresses');
                Route::get('enderecos/{address}', 'editAddress')->name('address.edit');
            });

            // System routes
            Route::prefix('sys')->group(function () {
                Route::get('email/verificar/{id}/{hash}', 'emailVerify')->middleware(['signed', 'can:unverified-email'])->name('email.verify');
            });
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Admin routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::middleware(['guest:admin'])->controller(AdminGuestController::class)->group(function () {
            Route::get('login', 'signInWeb')->name('signIn');
            Route::get('cadastro', 'signUpWeb')->name('signUp');
            Route::get('senha/redefinir', 'requestPassordReset')->name('password.request');
            Route::get('senha/resetar/{token}', 'passordReset')->name('password.reset');
        });
        Route::prefix('painel')->name('dashboard.')->middleware(['auth:admin'])->controller(AdminDashboardController::class)->group(function () {
            Route::get('/', 'home')->name('home');
            Route::prefix('minha-conta')->name('my-account.')->group(function () {
                Route::get('dados-pessoais', 'personalData')->name('personal-data');
            });
        });
    });
});

/*
|--------------------------------------------------------------------------
| Actions Routes
|--------------------------------------------------------------------------
*/

Route::prefix('action')->name('action.')->group(function () {
    /*
    |--------------------------------------------------------------------------
    | User Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('user')->name('user.')->controller()->group(function () {
        /*
        |--------------------------------------------------------------------------
        | Auth Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('auth')->name('auth.')->controller(UserAuthController::class)->group(function () {
            Route::middleware(['guest:user', 'throttle:5,1'])->group(function () {
                Route::post('signin', 'signInWeb')->name('signIn');
                Route::post('signup', 'signUpWeb')->name('signUp');
                Route::post('password/email', 'sendResetLinkEmail')->name('password.email');
                Route::post('password/update', 'passwordUpdate')->name('password.update');
            });
            Route::middleware(['auth:user'])->group(function () {
                Route::post('signout', 'signOutWeb')->name('signOut');
                Route::prefix('email')->name('email.')->middleware(['throttle:5,1'])->group(function () {
                    Route::post('verify', 'emailVerify')->name('verify');
                    Route::post('verify/resend', 'emailVerifyResend')->name('verify.resend');
                });
            });
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')->name('admin.')->controller()->group(function () {
        /*
        |--------------------------------------------------------------------------
        | Auth Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('auth')->name('auth.')->controller(AdminAuthController::class)->group(function () {
            Route::middleware(['guest:admin', 'throttle:5,1'])->group(function () {
                Route::post('signin', 'signInWeb')->name('signIn');
                Route::post('signup', 'signUpWeb')->name('signUp');
                Route::post('password/email', 'sendResetLinkEmail')->name('password.email');
                Route::post('password/update', 'passwordUpdate')->name('password.update');
            });
            Route::middleware(['auth:admin'])->group(function () {
                Route::post('signout', 'signOutWeb')->name('signOut');
            });
        });
    });
});
