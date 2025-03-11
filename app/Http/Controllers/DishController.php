<?php

namespace App\Http\Controllers;

use App\Models\Dish;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;



class DishController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $dishes = Dish::all();
        return response()->json($dishes);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:50',
            'description' => 'required|max:255',
            'price' => 'required|numeric|min:0',
            'img' => 'image|max:2048',
            'available' => 'required',
            'ingredients' => 'required|string',
            'category_id' => 'required|numeric'

        ]);

        if ($request->hasFile('img')) {
            $validated['img'] = $request->file('img')->store('upload', 'public');
        }

        $ingredients = explode(',', $request->ingredients);

        $dish = Dish::create($validated);

        foreach ($ingredients as $item) {
            $dish->ingredients()->attach($item);
        }

        return response()->json($dish, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $dish = Dish::find($id);

        if (!$dish) {
            return response()->json(['error' => 'Dish not found'], 404);
        }

        return response()->json($dish);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        // Find the dish by ID or fail
        $dish = Dish::findOrFail($id);

        // 1. Handle File Upload (if present)
        if ($request->hasFile('img')) {
            // Delete the old image if it exists
            if ($dish->img) {
                Storage::disk('public')->delete($dish->img);
            }

            // Store the new image and update the dish's image path
            $imagePath = $request->file('img')->store('uploads', 'public');
            $dish->img = $imagePath; // Update the dish's image directly
        }

        // 2. Validate and update other fields
        $validated = $request->validate([
            'name' => 'nullable|max:50',
            'description' => 'nullable|max:255',
            'price' => 'nullable|numeric|min:0',
            'available' => 'nullable|numeric',
            'ingredients' => 'nullable|string',
            'category_id' => 'nullable|numeric'
        ]);

        // Update the dish with validated fields
        $dish->update($validated);

        // 3. Handle Ingredients (if provided)
        if ($request->has('ingredients')) {
            $ingredients = explode(',', $request->ingredients);
            $dish->ingredients()->sync($ingredients); // Sync ingredients with the dish
        }

        // Refresh the dish to get the latest data (including the updated image path)
        $dish->refresh();

        // Load the ingredients relationship for the response
        $dish->load('ingredients');

        // Return the updated dish as JSON
        return response()->json($dish, 200)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $dish = Dish::find($id);

        // Check if the dish exists
        if (!$dish) {
            return response()->json(['message' => 'Dish not found'], 404);
        }

        // Delete the associated image file if it exists
        if ($dish->img && Storage::disk('public')->exists($dish->img)) {
            Storage::disk('public')->delete($dish->img);
        }

        // Detach all ingredients associated with the dish
        $dish->ingredients()->detach();

        // Delete the dish
        $dish->delete();

        // Return a success response
        return response()->json($dish, 200);
    }
}
