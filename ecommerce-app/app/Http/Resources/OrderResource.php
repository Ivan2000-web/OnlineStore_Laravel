<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status,
            'total_amount' => $this->total_amount,
            'shipping_address' => $this->shipping_address,
            'billing_address' => $this->billing_address,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'shipped_at' => $this->shipped_at,
            'delivered_at' => $this->delivered_at,
            'order_items' => OrderItemResource::collection($this->whenLoaded('orderItems')),
            'items_count' => $this->when($this->relationLoaded('orderItems'), $this->orderItems->count()),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}