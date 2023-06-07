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
}
