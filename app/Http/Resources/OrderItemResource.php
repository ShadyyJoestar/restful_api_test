<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'subtotal' => $this->subtotal,

            'book' => [
                'id' => $this->book->id,
                'title' => $this->book->title,
                'slug' => $this->book->slug,
            ],
        ];
    }
}