<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class IngredientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ingredients = Ingredient::all();
        return response()->json($ingredients);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:50',
            'img' => 'image|max:2048',

        ]);

        if ($request->hasFile('img')) {
            $validated['img'] = $request->file('img')->store('upload/ingredients', 'public');
        }

        $ingredient = Ingredient::create($validated);

        return response()->json($ingredient, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $ingredient = Ingredient::find($id);

        if (!$ingredient) {
            return response()->json(['error' => 'Ingredient not found'], 404);
        }

        return response()->json($ingredient);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ingredient $ingredient)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        // Find the dish by ID or fail
        $ingredient = Ingredient::findOrFail($id);

        // 1. Handle File Upload (if present)
        if ($request->hasFile('img')) {
            // Delete the old image if it exists
            if ($ingredient->img) {
                Storage::disk('public')->delete($ingredient->img);
            }

            // Store the new image and update the ingredient's image path
            $imagePath = $request->file('img')->store('upload/ingredients', 'public');
            $ingredient->img = $imagePath; // Update the ingredient's image directly
        }

        // 2. Validate and update other fields
        $validated = $request->validate([
            'name' => 'nullable|max:50',
        ]);

        // Update the dish with validated fields
        $ingredient->update($validated);

        // Refresh the dish to get the latest data (including the updated image path)
        $ingredient->refresh();

        // Return the updated dish as JSON
        return response()->json($ingredient, 200)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $ingredient = Ingredient::find($id);

        // Check if the dish exists
        if (!$ingredient) {
            return response()->json(['message' => 'Ingredient not found'], 404);
        }

        // Delete the associated image file if it exists
        if ($ingredient->img && Storage::disk('public')->exists($ingredient->img)) {
            Storage::disk('public')->delete($ingredient->img);
        }

        // Delete the ingredient
        $ingredient->delete();

        // Return a success response
        return response()->json($ingredient, 200);
    }
}
