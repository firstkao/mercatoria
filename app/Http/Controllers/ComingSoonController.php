<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ComingSoonController extends Controller
{
    /**
     * Placeholder jujur untuk fitur yang belum dibangun (antrean).
     */
    public function __invoke(): View
    {
        return view('coming-soon');
    }
}
