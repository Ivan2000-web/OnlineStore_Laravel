<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(): AnonymousResourceCollection
    {
        $orders = Order::with(['orderItems.product'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return OrderResource::collection($orders);
    }

    public function show(Order $order): OrderResource
    {
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $order->load(['orderItems.product.category']);

        return new OrderResource($order);
    }

    public function store(Request $request): OrderResource
    {
        $validated = $request->validate([
            'shipping_address' => 'required|array',
            'shipping_address.first_name' => 'required|string|max:255',
            'shipping_address.last_name' => 'required|string|max:255',
            'shipping_address.address_line_1' => 'required|string|max:255',
            'shipping_address.address_line_2' => 'nullable|string|max:255',
            'shipping_address.city' => 'required|string|max:255',
            'shipping_address.state' => 'required|string|max:255',
            'shipping_address.postal_code' => 'required|string|max:20',
            'shipping_address.country' => 'required|string|max:255',
            'billing_address' => 'required|array',
            'payment_method' => 'required|string|in:credit_card,paypal,bank_transfer',
        ]);

        $cartItems = CartItem::with('product')
            ->where('user_id', Auth::id())
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 400);
        }

        // Проверка наличия товаров на складе
        foreach ($cartItems as $cartItem) {
            if ($cartItem->product->stock_quantity < $cartItem->quantity) {
                return response()->json([
                    'message' => "Insufficient stock for product: {$cartItem->product->name}"
                ], 400);
            }
        }

        DB::beginTransaction();

        try {
            // Создание заказа
            $totalAmount = $cartItems->sum(function ($item) {
                return $item->quantity * $item->product->getCurrentPrice();
            });

            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => Auth::id(),
                'total_amount' => $totalAmount,
                'shipping_address' => $validated['shipping_address'],
                'billing_address' => $validated['billing_address'],
                'payment_method' => $validated['payment_method'],
            ]);

            // Создание элементов заказа
            foreach ($cartItems as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->product->getCurrentPrice(),
                ]);

                // Уменьшение количества товара на складе
                $cartItem->product->decrement('stock_quantity', $cartItem->quantity);
            }

            // Очистка корзины
            CartItem::where('user_id', Auth::id())->delete();

            DB::commit();

            $order->load(['orderItems.product']);

            return new OrderResource($order);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Order creation failed'], 500);
        }
    }

    public function update(Request $request, Order $order): OrderResource
    {
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'status' => 'sometimes|in:pending,processing,shipped,delivered,cancelled',
            'shipping_address' => 'sometimes|array',
            'billing_address' => 'sometimes|array',
        ]);

        $order->update($validated);
        $order->load(['orderItems.product']);

        return new OrderResource($order);
    }

    public function destroy(Order $order): \Illuminate\Http\JsonResponse
    {
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        if ($order->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending orders can be cancelled'
            ], 400);
        }

        DB::beginTransaction();

        try {
            // Возврат товаров на склад
            foreach ($order->orderItems as $orderItem) {
                $orderItem->product->increment('stock_quantity', $orderItem->quantity);
            }

            $order->update(['status' => 'cancelled']);

            DB::commit();

            return response()->json(['message' => 'Order cancelled successfully']);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Order cancellation failed'], 500);
        }
    }
}