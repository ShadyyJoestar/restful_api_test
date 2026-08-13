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

            'customer' => [
                'name' => $this->customer_name,
                'email' => $this->customer_email,
                'phone' => $this->customer_phone,
            ],

            'status' => $this->status,
            'total_amount' => $this->total_amount,

            'items' => OrderItemResource::collection(
                $this->whenLoaded('items')
            ),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}