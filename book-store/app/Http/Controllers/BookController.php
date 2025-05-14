<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BookController extends Controller
{
    public function index()
    {
        try {
            $books = Book::orderBy('id', 'desc')->get();

            // Ensure we always return an array, even if empty
            return response()->json($books->toArray());
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve books',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'genre' => 'required|string|max:255',
            'year' => 'required|integer|min:1800|max:' . (date('Y') + 1),
            'cover' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $coverPath = null;
            if ($request->hasFile('cover')) {
                $coverPath = $request->file('cover')->store('covers', 'public');
            }

            $book = Book::create([
                'title' => $request->title,
                'author' => $request->author,
                'genre' => $request->genre,
                'published_year' => $request->year,
                'cover_photo' => $coverPath,
            ]);

            return response()->json([
                'message' => 'Book added successfully',
                'book' => $book
            ], 201);
        } catch (\Exception $e) {
            // Delete uploaded file if book creation fails
            if (isset($coverPath)) {
                Storage::disk('public')->delete($coverPath);
            }

            return response()->json([
                'message' => 'Failed to add book',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'genre' => 'required|string|max:255',
            'year' => 'required|integer|min:1800|max:' . (date('Y') + 1),
            'cover' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $book = Book::findOrFail($id);
            $data = [
                'title' => $request->title,
                'author' => $request->author,
                'genre' => $request->genre,
                'published_year' => $request->year,
            ];

            if ($request->hasFile('cover')) {
                // Delete old cover if exists
                if ($book->cover_photo) {
                    Storage::disk('public')->delete($book->cover_photo);
                }
                $data['cover_photo'] = $request->file('cover')->store('covers', 'public');
            }

            $book->update($data);

            return response()->json([
                'message' => 'Book updated successfully',
                'book' => $book
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update book',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $book = Book::findOrFail($id);

            if ($book->cover_photo) {
                Storage::disk('public')->delete($book->cover_photo);
            }

            $book->delete();

            return response()->json([
                'message' => 'Book deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete book',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
