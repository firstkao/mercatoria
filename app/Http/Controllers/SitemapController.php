<?php

namespace App\Http\Controllers;

use App\Services\Content\SitemapBuilder;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(SitemapBuilder $builder): Response
    {
        return response($builder->render())->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
