<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Dish;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;


class RelationshipController extends Controller
{
    public function dishIngredients($id)
    {
        $dish = Dish::with('ingredients')->find($id);
        return response()->json([
            'dish' => $dish,
            'ingredients' => $dish->ingredients,
        ]);
    }
    public function allDishIngredients()
    {
        $dishes = Dish::with('ingredients')->get();
        return response()->json($dishes);
    }

    public function showDishWithCategory($id)
    {
        // Fetch the dish with its related category
        $dish = Dish::with('categories')->find($id);
        $category = Category::find($dish->category_id);
        // Check if the dish exists
        if (!$dish) {
            return response()->json([
                'message' => 'Dish not found',
            ], 404);
        }

        // Return the dish and its category as JSON
        return response()->json([
            'dish' => $dish,
            'category' => $category,
        ]);
    }
    public function showCategoryWithDishes($id)
    {
        // Fetch the category with its related dishes
        $category = Category::with('dishes')->find($id);

        // Check if the category exists
        if (!$category) {
            return response()->json([
                'message' => 'Category not found',
            ], 404);
        }

        // Return the category and its dishes as JSON
        return response()->json([
            'category' => $category,
        ]);
    }

    public function showAllCategoriesWithDishes()
    {
        // Fetch all categories with their related dishes
        $categories = Category::whereHas('dishes')->get();

        // Customize the array structure
        $categoriesArray = $categories->map(function ($category) {
            return [
                'category_id' => $category->id,
                'category_name' => $category->name,
                'dishes' => $category->dishes->map(function ($dish) {
                    return [
                        'dish_id' => $dish->id,
                        'dish_name' => $dish->name,
                        'dish_price' => $dish->price,
                        'dish_description' => $dish->description,
                        'dish_img' => $dish->img
                    ];
                })
            ];
        });

        // Return the customized array as JSON
        return response()->json($categoriesArray);
    }

    public function ingredientDishes($id)
    {
        $ingredient = Ingredient::with('dishes')->find($id);
        return response()->json([
            'ingredient' => $ingredient,
            'dishes' => $ingredient->dishes,
        ]);
    }
    public function showCategoriesWithParam(Request $request)
    {
        // Check if the 'category' query parameter is present
        if ($request->has('category')) {
            // Get the category query parameter
            $categoryParam = $request->query('category');

            // Split the category parameter into an array of categories
            $categories = explode(',', $categoryParam);

            // Fetch all categories that match the provided IDs or names
            $categoriesWithDishes = Category::whereIn('id', $categories)
                ->orWhereIn('name', $categories)
                ->with('dishes')
                ->get();

            // Customize the response for multiple categories
            $response = $categoriesWithDishes->map(function ($category) {
                return [
                    'category_id' => $category->id,
                    'category_name' => $category->name,
                    'dishes' => $category->dishes->map(function ($dish) {
                        return [
                            'dish_id' => $dish->id,
                            'dish_name' => $dish->name,
                            'dish_price' => $dish->price,
                            'dish_description' => $dish->description,
                            'dish_img' => $dish->img
                        ];
                    })
                ];
            });

            // Return the response as JSON
            return response()->json($response);
        } else {
            // If no category query parameter is provided, return all categories with dishes
            $categories = Category::whereHas('dishes')->with('dishes')->get();

            // Customize the response for all categories
            $response = $categories->map(function ($category) {
                return [
                    'category_id' => $category->id,
                    'category_name' => $category->name,
                    'dishes' => $category->dishes->map(function ($dish) {
                        return [
                            'dish_id' => $dish->id,
                            'dish_name' => $dish->name,
                            'dish_price' => $dish->price,
                            'dish_description' => $dish->description,
                            'dish_img' => $dish->img
                        ];
                    })
                ];
            });

            // Return the response as JSON
            return response()->json($response);
        }
    }


    public function filterDishes(Request $request)
    {
        // Initialize the query for dishes
        $dishesQuery = Dish::query();
        $categoryParam = $request->query('category');
        $ingredientsParam = $request->query('ingredients');
        $searchParam = $request->query('search'); // New parameter for search

        // Check if the 'category' query parameter is present
        if ($request->has('category')) {
            // Split the category parameter into an array of categories
            $categories = explode(',', $categoryParam);

            // Filter dishes by categories
            $dishesQuery->whereHas('category', function ($q) use ($categories) {
                $q->whereIn('categories.id', $categories) // Qualify with table name
                    ->orWhereIn(
                        'categories.name',
                        $categories
                    ); // Qualify with table name
            });
        }

        // Check if the 'ingredients' query parameter is present
        if ($request->has('ingredients')) {
            // Split the ingredients parameter into an array
            $ingredients = explode(',', $ingredientsParam);

            // Filter dishes by ingredients (many-to-many relationship)
            $dishesQuery->whereHas('ingredients', function ($q) use ($ingredients) {
                $q->whereIn('ingredients.id', $ingredients) // Qualify with table name
                    ->orWhereIn('ingredients.name', $ingredients); // Qualify with table name
            });
        }

        // Check if the 'search' query parameter is present
        if ($request->has('search')) {
            // Filter dishes by name or description using the search parameter
            $dishesQuery->where(function ($query) use ($searchParam) {
                $query->where('dishes.name', 'like', '%' . $searchParam . '%')
                    ->orWhere('dishes.description', 'like', '%' . $searchParam . '%');
            });
        }

        // Eager load category and ingredients for each dish
        $dishesQuery->with(['category', 'ingredients']);

        // Fetch the filtered dishes
        $dishes = $dishesQuery->get();

        // If no query parameters are provided, return all categories with their dishes
        if (!$request->has('category') && !$request->has('ingredients') && !$request->has('search')) {
            $categories = Category::with('dishes.ingredients')->get();

            // Customize the response for all categories
            $response = $categories->map(function ($category) {
                return [
                    'category_id' => $category->id,
                    'category_name' => $category->name,
                    'dishes' => $category->dishes->map(function ($dish) {
                        return [
                            'dish_id' => $dish->id,
                            'dish_name' => $dish->name,
                            'dish_price' => $dish->price,
                            'dish_description' => $dish->description,
                            'dish_img' => $dish->img,
                            'ingredients' => $dish->ingredients->map(function ($ingredient) {
                                return [
                                    'ingredient_id' => $ingredient->id,
                                    'ingredient_name' => $ingredient->name,
                                    'ingredient_img' => $ingredient->img,
                                ];
                            })
                        ];
                    })
                ];
            });

            // Return the response as JSON
            return response()->json($response);
        }

        // Group dishes by category
        $groupedDishes = $dishes->groupBy('category_id');

        // Customize the response for dishes grouped by category
        $response = $groupedDishes->map(function ($dishes, $categoryId) {
            $category = $dishes->first()->category; // Get the category from the first dish
            return [
                'category_id' => $category->id,
                'category_name' => $category->name,
                'dishes' => $dishes->map(function ($dish) {
                    return [
                        'dish_id' => $dish->id,
                        'dish_name' => $dish->name,
                        'dish_price' => $dish->price,
                        'dish_description' => $dish->description,
                        'dish_img' => $dish->img,
                        'ingredients' => $dish->ingredients->map(function ($ingredient) {
                            return [
                                'ingredient_id' => $ingredient->id,
                                'ingredient_name' => $ingredient->name,
                                'ingredient_img' => $ingredient->img,
                            ];
                        })
                    ];
                })
            ];
        })->values(); // Reset keys to ensure the response is an array

        // Return the response as JSON
        return response()->json($response);
    }
    public function showUserOrders($id)
    {
        // Fetch all orders for the user with their items and dishes
        $orders = Order::where('user_id', $id)
            ->with(['items.dish']) // Eager load items and their dishes
            ->get();

        // Return the response
        return response()->json([
            $orders,
        ]);
    }
}
