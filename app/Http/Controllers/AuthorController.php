<?php

namespace App\Http\Controllers;

use App\Http\Resources\AuthorResource;
use App\Models\Author;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    public function index()
    {
        return AuthorResource::collection(
            Author::latest()->paginate(15)
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:authors,slug'],
            'biography' => ['nullable', 'string'],
        ]);

        $author = Author::create($validated);

        return new AuthorResource($author);
    }

    public function show(Author $author)
    {
        return new AuthorResource($author);
    }

    public function update(Request $request, Author $author)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                'unique:authors,slug,' . $author->id,
            ],
            'bio' => ['nullable', 'string'],
        ]);

        $author->update($validated);

        return new AuthorResource($author->fresh());
    }

    public function destroy(Author $author)
    {
        $author->delete();

        return response()->json([
            'message' => 'Author deleted successfully.',
        ]);
    }
}