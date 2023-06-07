<?php

namespace App\Http\Controllers\Web\Admin;

use Inertia\{
    Inertia,
    Response,
};

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
        return Inertia::render('Admin/Home', [
            'title' => generateTitle($label = 'Painel administrativo'),
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
        return Inertia::render('Admin/Account/PersonalData', [
            'title' => generateTitle($label = 'Meus dados'),
            'label' => $label,
        ]);
    }
}
