<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'items' => 'required|array',
            'items.*.dish_id' => 'required|exists:dishes,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        // Create a single order
        $order = Order::create([
            'user_id' => $request->user_id,
            'data' => now(), // Assuming 'data' is the order date
        ]);

        // Add multiple items to the order
        foreach ($request->items as $item) {
            OrderItem::create([
                'order_id' => $order->id, // Associate with the same order
                'dish_id' => $item['dish_id'],
                'quantity' => $item['quantity'],
            ]);
        }

        // Return a response
        return response()->json([
            'message' => 'Order created successfully',
            'order' => $order->load('items'), // Load the items relationship
        ], 201);
    }
}
