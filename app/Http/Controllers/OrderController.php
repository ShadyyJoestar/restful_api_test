<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use App\Models\Book;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with('items.book')
            ->latest()
            ->paginate(15);

        return OrderResource::collection($orders);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => [
                'required',
                'string',
                'max:255',
            ],

            'customer_email' => [
                'required',
                'email',
                'max:255',
            ],

            'customer_phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.book_id' => [
                'required',
                'integer',
                'exists:books,id',
            ],

            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);

        $order = DB::transaction(function () use ($validated) {
            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper(uniqid()),
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'] ?? null,
                'status' => 'pending',
                'total_amount' => 0,
            ]);

            $total = 0;

            foreach ($validated['items'] as $item) {
                $book = Book::lockForUpdate()->findOrFail(
                    $item['book_id']
                );

                if ($book->stock < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => [
                            "Stock for {$book->title} is insufficient.",
                        ],
                    ]);
                }

                $price = $book->price;
                $subtotal = $price * $item['quantity'];

                $order->items()->create([
                    'book_id' => $book->id,
                    'quantity' => $item['quantity'],
                    'price' => $price,
                    'subtotal' => $subtotal,
                ]);

                $book->decrement(
                    'stock',
                    $item['quantity']
                );

                $total += $subtotal;
            }

            $order->update([
                'total_amount' => $total,
            ]);

            return $order;
        });

        $order->load('items.book');

        return new OrderResource($order);
    }

    public function show(Order $order)
    {
        $order->load('items.book');

        return new OrderResource($order);
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => [
                'required',
                'in:pending,paid,cancelled,completed',
            ],
        ]);

        $order->update($validated);

        $order->load('items.book');

        return new OrderResource($order);
    }

    public function destroy(Order $order)
    {
        $order->delete();

        return response()->json([
            'message' => 'Order deleted successfully.',
        ]);
    }
}