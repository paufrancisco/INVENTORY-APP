<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        $products = Product::orderBy('created_at', 'desc')->get();
        return response()->json($products);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'price'    => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        $product = Product::create($request->only('name', 'category', 'price', 'quantity'));

        return response()->json($product, 201);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'price'    => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        $product->update($request->only('name', 'category', 'price', 'quantity'));

        return response()->json($product);
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();
        return response()->json(null, 204);
    }
}