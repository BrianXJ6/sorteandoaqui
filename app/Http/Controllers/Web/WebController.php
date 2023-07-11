<?php

namespace App\Http\Controllers\Web;

use Inertia\{
    Inertia,
    Response,
};

use App\Http\Controllers\Controller;

class WebController extends Controller
{
    /**
     * Home page
     *
     * @return \Inertia\Response
     */
    public function home(): Response
    {
        return Inertia::render('Web/Home', [
            'title' => generateTitle($label = 'Início'),
            'label' => $label,
            'description' => 'Description...',
            'keywords' => 'Keywords...',
        ]);
    }

    /**
     * Contact page
     *
     * @return \Inertia\Response
     */
    public function contact(): Response
    {
        return Inertia::render('Web/Contact', [
            'title' => generateTitle($label = 'Contato'),
            'label' => $label,
            'description' => 'Description...',
            'keywords' => 'Keywords...',
        ]);
    }
}
