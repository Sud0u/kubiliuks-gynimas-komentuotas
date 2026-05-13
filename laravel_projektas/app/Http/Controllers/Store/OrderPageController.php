<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Order;

class OrderPageController extends Controller
{
    public function index()
    {
        $orders = Order::query()
            ->where('user_id', auth()->id())
            ->with('payment')
            ->latest()
            ->get();

        return view('orders.index', compact('orders'));
    }

    public function show(int $id)
    {
        $order = Order::query()
            ->with(['items', 'payment'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        return view('orders.show', compact('order'));
    }
}