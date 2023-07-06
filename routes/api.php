<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\User\AuthController as UserAuthController;
use App\Http\Controllers\Auth\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Api\User\AccountController as UserAccountController;
use App\Http\Controllers\Api\Admin\AccountController as AdminAccountController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ...

/*
|--------------------------------------------------------------------------
| User Routes
|--------------------------------------------------------------------------
*/

Route::prefix('user')->name('user.')->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Open Routes
    |--------------------------------------------------------------------------
    */
    // Auth Routes
    Route::prefix('auth')->middleware(['throttle:5,1'])->controller(UserAuthController::class)->group(function () {
        Route::post('signin', 'signInApi')->name('signIn');
        Route::post('signup', 'signUpApi')->name('signUp');
        Route::post('password/email', 'sendResetLinkEmail')->name('password.email');
    });


    /*
    |--------------------------------------------------------------------------
    | Closed Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum', 'abilities:user', 'can:userOnly'])->group(function () {
        // Auth routes
        Route::prefix('auth')->middleware(['throttle:5,1'])->controller(UserAuthController::class)->group(function () {
            Route::post('signout', 'signOutApi')->name('signOut');
            Route::post('email/verify/resend', 'emailVerifyResend')->name('email.verify.resend');
        });

        // Account routes
        Route::prefix('acc')->name('acc.')->controller(UserAccountController::class)->group(function () {
            Route::put('update-personal-data', 'updatePersonalData')->name('update-personal-data');
            Route::put('update-access-data', 'updateAccessData')->name('update-access-data');
        });
    });
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Open Routes
    |--------------------------------------------------------------------------
    */
    // Auth routes
    Route::prefix('auth')->middleware(['throttle:5,1'])->controller(AdminAuthController::class)->group(function () {
        Route::post('signin', 'signInApi')->name('signIn');
        Route::post('signup', 'signUpApi')->name('signUp');
        Route::post('password/email', 'sendResetLinkEmail')->name('password.email');
    });

    /*
    |--------------------------------------------------------------------------
    | Closed Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum', 'abilities:admin', 'can:adminOnly'])->group(function () {
        // Auth routes
        Route::prefix('auth')->controller(AdminAuthController::class)->group(function () {
            Route::post('signout', 'signOutApi')->name('signOut');
        });

        // Account routes
        Route::prefix('acc')->name('acc.')->controller(AdminAccountController::class)->group(function () {
            Route::put('update-personal-data', 'updatePersonalData')->name('update-personal-data');
            Route::put('update-access-data', 'updateAccessData')->name('update-access-data');
        });
    });
});