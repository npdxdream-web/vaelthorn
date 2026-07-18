<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Thread;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $cities = City::with('villages')->where('is_active', true)->get();

        $recentThreads = Thread::with('village')
            ->where('status', 'open')
            ->withCount('posts')
            ->latest('updated_at')
            ->take(3)
            ->get();

        return view('home', compact('cities', 'recentThreads'));
    }
}