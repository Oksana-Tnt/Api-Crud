<?php

use App\Http\Controllers\PizzaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DishController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\RelationshipController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);


Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('/dishes', DishController::class)->except(['index', 'show']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('/ingredients', IngredientController::class)->except(['index', 'show']);
    Route::apiResource('/categories', CategoryController::class)->except(['index', 'show']);
});

Route::get('/ingredients', [IngredientController::class, 'index']);
Route::get('/ingredients/{id}', [IngredientController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);


Route::get('/dishes/ingredients', [RelationshipController::class, 'allDishIngredients']);
Route::get('/dishes/{id}/ingredients', [RelationshipController::class, 'dishIngredients']);
Route::get('/ingredient/{id}/dishes', [RelationshipController::class, 'ingredientDishes']);
Route::get('/dishes/{id}/categories', [RelationshipController::class, 'showDishWithCategory']);
Route::get('/dishes', [DishController::class, 'index']); //pubblico
Route::get('/dishes/{id}', [DishController::class, 'show']);
Route::post('/dishes', [DishController::class, 'store']);
Route::get('/categories', [CategoryController::class, 'index']); //pubblico
Route::get('/categories/{id}/dishes', [RelationshipController::class, 'showCategoryWithDishes']);
Route::get('/categories-with-dishes', [RelationshipController::class, 'showAllCategoriesWithDishes']);
Route::get('/categories-with-param', [RelationshipController::class, 'showCategoriesWithParam']);
Route::get('/dishes-with-params', [RelationshipController::class, 'filterDishes']);
Route::get('/users/{id}/orders', [RelationshipController::class, 'showUserOrders']);
Route::post('/orders', [OrderController::class, 'store']);
