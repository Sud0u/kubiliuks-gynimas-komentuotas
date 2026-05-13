<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Product;

class ProductPageController extends Controller
{
    public function show(Product $product)
    {
        if (!$product->is_active) {
            abort(404);
        }

        $product->load('category');

        $similarProducts = Product::query()
            ->where('is_active', true)
            ->where('id', '!=', $product->id)
            ->when($product->category_id, fn($q) => $q->where('category_id', $product->category_id))
            ->orderByDesc('created_at')
            ->limit(4)
            ->get();

        return view('store.product_show', compact('product', 'similarProducts'));
    }
}