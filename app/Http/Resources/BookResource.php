<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'isbn' => $this->isbn,
            'description' => $this->description,
            'publisher' => $this->publisher,
            'published_at' => $this->published_at,
            'price' => $this->price,
            'stock' => $this->stock,
            'cover' => $this->cover,

            'author' => [
                'id' => $this->author->id,
                'name' => $this->author->name,
                'slug' => $this->author->slug,
            ],

            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ],

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}