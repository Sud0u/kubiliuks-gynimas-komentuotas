<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class OrderPageController extends Controller
{
    public function index()
    {
        return view('admin.orders.index');
    }

    public function show($id)
    {
        return view('admin.orders.show', [
            'orderId' => (int) $id,
        ]);
    }
}