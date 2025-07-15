<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartItemResource;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CartController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $cartItems = CartItem::with('product.category')
            ->where('user_id', Auth::id())
            ->get();

        return CartItemResource::collection($cartItems);
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        if ($product->stock_quantity < $validated['quantity']) {
            return response()->json([
                'message' => 'Insufficient stock quantity'
            ], 400);
        }

        $existingCartItem = CartItem::where('user_id', Auth::id())
            ->where('product_id', $validated['product_id'])
            ->first();

        if ($existingCartItem) {
            $newQuantity = $existingCartItem->quantity + $validated['quantity'];
            
            if ($product->stock_quantity < $newQuantity) {
                return response()->json([
                    'message' => 'Insufficient stock quantity'
                ], 400);
            }
            
            $existingCartItem->update(['quantity' => $newQuantity]);
            $cartItem = $existingCartItem;
        } else {
            $cartItem = CartItem::create([
                'user_id' => Auth::id(),
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity'],
            ]);
        }

        $cartItem->load('product.category');

        return response()->json([
            'message' => 'Product added to cart',
            'cart_item' => new CartItemResource($cartItem)
        ]);
    }

    public function update(Request $request, CartItem $cartItem): \Illuminate\Http\JsonResponse
    {
        if ($cartItem->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $product = $cartItem->product;
        if ($product->stock_quantity < $validated['quantity']) {
            return response()->json([
                'message' => 'Insufficient stock quantity'
            ], 400);
        }

        $cartItem->update($validated);
        $cartItem->load('product.category');

        return response()->json([
            'message' => 'Cart item updated',
            'cart_item' => new CartItemResource($cartItem)
        ]);
    }

    public function destroy(CartItem $cartItem): \Illuminate\Http\JsonResponse
    {
        if ($cartItem->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $cartItem->delete();

        return response()->json(['message' => 'Item removed from cart']);
    }

    public function clear(): \Illuminate\Http\JsonResponse
    {
        CartItem::where('user_id', Auth::id())->delete();

        return response()->json(['message' => 'Cart cleared']);
    }

    public function count(): \Illuminate\Http\JsonResponse
    {
        $count = CartItem::where('user_id', Auth::id())->sum('quantity');

        return response()->json(['count' => $count]);
    }
}