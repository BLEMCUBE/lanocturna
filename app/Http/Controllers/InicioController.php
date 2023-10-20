<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class InicioController extends Controller
{
    public function index()
    {

        return Inertia::render('Inicio');
    }
}
