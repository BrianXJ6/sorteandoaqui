<?php

namespace App\Http\Controllers\Web\User;

use Inertia\{
    Inertia,
    Response,
};

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GuestController extends Controller
{
    /**
     * SignIn
     *
     * @return \Inertia\Response
     */
    public function signInWeb(): Response
    {
        return Inertia::render('Web/User/signIn', [
            'title' => generateTitle($label = 'Login'),
            'label' => $label,
            'description' => 'Description...',
            'keywords' => 'keywords...',
        ]);
    }

    /**
     * signUp
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Inertia\Response
     */
    public function signUpWeb(Request $request): Response
    {
        return Inertia::render('Web/User/SignUp', [
            'title' => generateTitle($label = 'Cadastro'),
            'label' => $label,
            'description' => 'Description...',
            'keywords' => 'keywords...',
            'referral_code' => $request->route()->parameter('referral_code'),
        ]);
    }

    /**
     * Request password reset
     *
     * @return \Inertia\Response
     */
    public function requestPassordReset(): Response
    {
        return Inertia::render('Web/User/RequestPasswordReset', [
            'title' => generateTitle($label = 'Redefinir senha'),
            'label' => $label,
            'description' => 'Description...',
            'keywords' => 'keywords...',
        ]);
    }

    /**
     * Password reset
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Inertia\Response
     */
    public function passordReset(Request $request): Response
    {
        return Inertia::render('Web/User/PasswordReset', [
            'title' => generateTitle($label = 'Resetar senha'),
            'label' => $label,
            'description' => 'Description...',
            'keywords' => 'keywords...',
            'token' => $request->route()->parameter('token'),
            'email' => $request->email,
        ]);
    }
}
