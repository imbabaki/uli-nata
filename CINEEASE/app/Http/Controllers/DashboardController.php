<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Movie;

class DashboardController extends Controller
{
    public function index()
    {
        // Fetch movies from the database
        $movies = Movie::all();

        // Pass movies data to the view
        return view('dashboard', compact('movies'));
    }

    public function create()
    {
        return view('task.create');
    }
}
