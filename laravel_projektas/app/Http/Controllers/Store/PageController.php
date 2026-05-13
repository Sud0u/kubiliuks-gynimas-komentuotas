<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Product;

class PageController extends Controller
{
    public function home()
    {
        $popularProducts = Product::query()
            ->where('is_active', true)
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        return view('home', compact('popularProducts'));
    }

    public function dashboard()
    {
        if (auth()->check() && auth()->user()->is_admin) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('home');
    }

    public function products()
    {
        return view('prekes');
    }

    public function contact()
    {
        return view('contact');
    }

    public function buildTub()
    {
        return view('build_tub');
    }

    public function cart()
    {
        return view('cart');
    }

    public function checkout()
    {
        return view('checkout');
    }

    public function privacy()
    {
        return view('privacy');
    }

    public function cookies()
    {
        return view('cookies');
    }

    public function terms()
    {
        return view('terms');
    }
}