<?php

namespace App\Http\Controllers\Web\User;

use Inertia\{
    Inertia,
    Response,
};

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    /**
     * Dashobard
     *
     * @return \Inertia\Response
     */
    public function home(): Response
    {
        return Inertia::render('User/Home', [
            'title' => generateTitle($label = 'Painel de acesso'),
            'label' => $label,
        ]);
    }

    /**
     * Personal data
     *
     * @return \Inertia\Response
     */
    public function personalData(): Response
    {
        return Inertia::render('User/Account/PersonalData', [
            'title' => generateTitle($label = 'Meus dados'),
            'label' => $label,
        ]);
    }

    /**
     * Email verify page
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Inertia\Response
     */
    public function emailVerify(Request $request): Response
    {
        return Inertia::render('User/Sys/EmailVerify', [
            'title' => generateTitle($label = 'Verificar endereço de e-mail'),
            'label' => $label,
            'id' => $request->route()->parameter('id'),
            'hash' => $request->route()->parameter('hash'),
        ]);
    }

    /**
     * Email verify page
     *
     * @return \Inertia\Response
     */
    public function myRaffles(): Response
    {
        return Inertia::render('User/Raffles/MyRaffles', [
            'title' => generateTitle($label = 'Minhas rifas'),
            'label' => $label,
        ]);
    }

    /**
     * Email verify page
     *
     * @return \Inertia\Response
     */
    public function newRaffle(): Response
    {
        return Inertia::render('User/Raffles/NewRaffle', [
            'title' => generateTitle($label = 'Nova rifa'),
            'label' => $label,
        ]);
    }
}
