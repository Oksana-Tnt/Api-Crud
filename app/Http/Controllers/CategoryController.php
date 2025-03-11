<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return response()->json($categories);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:50',
            'img' => 'image|max:2048',

        ]);

        if ($request->hasFile('img')) {
            $validated['img'] = $request->file('img')->store('upload/categories', 'public');
        }

        $category = Category::create($validated);

        return response()->json($category, 201);
    }
    public function show(int $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['error' => 'Ingredient not found'], 404);
        }

        return response()->json($category);
    }
    public function update(Request $request, int $id)
    {
        // Find the category by ID or fail
        $category = Category::findOrFail($id);

        // 1. Handle File Upload (if present)
        if ($request->hasFile('img')) {
            // Delete the old image if it exists
            if ($category->img) {
                Storage::disk('public')->delete($category->img);
            }

            // Store the new image and update the category's image path
            $imagePath = $request->file('img')->store('upload/categories', 'public');
            $category->img = $imagePath; // Update the category's image directly
        }

        // 2. Validate and update other fields
        $validated = $request->validate([
            'name' => 'nullable|max:50',
        ]);

        // Update the category with validated fields
        $category->update($validated);

        // Refresh the category to get the latest data (including the updated image path)
        $category->refresh();

        // Return the updated category as JSON
        return response()->json($category, 200)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
    }
    public function destroy(int $id)
    {
        $category = Category::find($id);

        // Check if the category exists
        if (!$category) {
            return response()->json(['message' => 'Ingredient not found'], 404);
        }

        // Delete the associated image file if it exists
        if ($category->img && Storage::disk('public')->exists($category->img)) {
            Storage::disk('public')->delete($category->img);
        }

        // Delete the category
        $category->delete();

        // Return a success response
        return response()->json($category, 200);
    }
}
