<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReviewResource;
use App\Models\Book;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReviewController extends Controller
{
    public function index(Book $book)
    {
        $reviews = $book->reviews()
            ->latest()
            ->paginate(15);

        return ReviewResource::collection($reviews);
    }

    public function store(Request $request, Book $book): JsonResponse
    {
        $validated = $request->validate([
            'reviewer_name' => [
                'required',
                'string',
                'max:255',
            ],

            'rating' => [
                'required',
                'integer',
                'min:1',
                'max:5',
            ],

            'comment' => [
                'nullable',
                'string',
            ],
        ]);

        $review = $book->reviews()->create($validated);

        return response()->json([
            'message' => 'Review created successfully.',
            'data' => new ReviewResource(
                $review->load('book')
            ),
        ], 201);
    }

    public function show(Book $book, Review $review): ReviewResource
    {
        $review->load('book');

        return new ReviewResource($review);
    }

    public function update(
        Request $request,
        Book $book,
        Review $review
    ): JsonResponse {
        $validated = $request->validate([
            'reviewer_name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'rating' => [
                'sometimes',
                'required',
                'integer',
                'min:1',
                'max:5',
            ],

            'comment' => [
                'sometimes',
                'nullable',
                'string',
            ],
        ]);

        $review->update($validated);

        return response()->json([
            'message' => 'Review updated successfully.',
            'data' => new ReviewResource(
                $review->fresh()->load('book')
            ),
        ]);
    }

    public function destroy(Book $book, Review $review): Response
    {
        $review->delete();

        return response()->noContent();
    }
}