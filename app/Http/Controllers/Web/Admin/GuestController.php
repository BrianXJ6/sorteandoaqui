<?php

namespace App\Http\Controllers\Web\Admin;

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
        return Inertia::render('Web/Admin/signIn', [
            'title' => generateTitle($label = 'login'),
            'label' => $label,
            'description' => 'Description...',
            'keywords' => 'keywords...',
        ]);
    }

    /**
     * signUp
     *
     * @return \Inertia\Response
     */
    public function signUpWeb(): Response
    {
        return Inertia::render('Web/Admin/SignUp', [
            'title' => generateTitle($label = 'Cadastro'),
            'label' => $label,
            'description' => 'Description...',
            'keywords' => 'keywords...',
        ]);
    }

    /**
     * Request password reset
     *
     * @return \Inertia\Response
     */
    public function requestPassordReset(): Response
    {
        return Inertia::render('Web/Admin/RequestPasswordReset', [
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
        return Inertia::render('Web/Admin/PasswordReset', [
            'title' => generateTitle($label = 'Resetar senha'),
            'label' => $label,
            'description' => 'Description...',
            'keywords' => 'keywords...',
            'token' => $request->route()->parameter('token'),
            'email' => $request->email,
        ]);
    }
}
