<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GamesController extends Controller
{
    /**
     * Display the games page
     */
    public function index(): Response
    {
        return Inertia::render('Games');
    }
}
