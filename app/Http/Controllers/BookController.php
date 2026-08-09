<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $query = Book::with(['author', 'category']);

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('isbn', 'like', "%{$search}%")
                    ->orWhereHas('author', function ($authorQuery) use ($search) {
                        $authorQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('category')) {
            $query->whereHas('category', function ($categoryQuery) use ($request) {
                $categoryQuery->where('slug', $request->category);
            });
        }

        if ($request->filled('author')) {
            $query->whereHas('author', function ($authorQuery) use ($request) {
                $authorQuery->where('slug', $request->author);
            });
        }

        if ($request->filled('sort')) {
            $sort = $request->sort;

            $allowedSorts = [
                'title',
                'price',
                'stock',
                'published_at',
                'created_at',
            ];

            $direction = 'asc';

            if (str_starts_with($sort, '-')) {
                $direction = 'desc';
                $sort = ltrim($sort, '-');
            }

            if (in_array($sort, $allowedSorts)) {
                $query->orderBy($sort, $direction);
            }
        } else {
            $query->latest();
        }

        $books = $query->paginate(
            $request->integer('per_page', 15)
        )->withQueryString();

        return BookResource::collection($books);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => [
                'required',
                'integer',
                'exists:categories,id',
            ],

            'author_id' => [
                'required',
                'integer',
                'exists:authors,id',
            ],

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'required',
                'string',
                'max:255',
                'unique:books,slug',
            ],

            'isbn' => [
                'required',
                'string',
                'max:255',
                'unique:books,isbn',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'publisher' => [
                'nullable',
                'string',
                'max:255',
            ],

            'published_at' => [
                'nullable',
                'date',
            ],

            'price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'stock' => [
                'required',
                'integer',
                'min:0',
            ],

            'cover' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $book = Book::create($validated);

        $book->load(['author', 'category']);

        return new BookResource($book);
    }

    public function show(Book $book)
    {
        $book->load(['author', 'category']);

        return new BookResource($book);
    }

    public function update(Request $request, Book $book)
    {
        $validated = $request->validate([
            'category_id' => [
                'sometimes',
                'integer',
                'exists:categories,id',
            ],

            'author_id' => [
                'sometimes',
                'integer',
                'exists:authors,id',
            ],

            'title' => [
                'sometimes',
                'string',
                'max:255',
            ],

            'slug' => [
                'sometimes',
                'string',
                'max:255',
                'unique:books,slug,' . $book->id,
            ],

            'isbn' => [
                'sometimes',
                'string',
                'max:255',
                'unique:books,isbn,' . $book->id,
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'publisher' => [
                'nullable',
                'string',
                'max:255',
            ],

            'published_at' => [
                'nullable',
                'date',
            ],

            'price' => [
                'sometimes',
                'numeric',
                'min:0',
            ],

            'stock' => [
                'sometimes',
                'integer',
                'min:0',
            ],

            'cover' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $book->update($validated);

        $book->load(['author', 'category']);

        return new BookResource($book);
    }

    public function destroy(Book $book)
    {
        $book->delete();

        return response()->json([
            'message' => 'Book deleted successfully.',
        ]);
    }
}