<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\CartItems;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class CartItemsController extends Controller
{

    function add(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'product_id' => 'required',
        ]);

        // Check if product is already in cart
        $existingCartItem = DB::table('cart_items')
            ->where('user_id', $request->user_id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($existingCartItem) {
            // If product is already in cart, increment quantity
            DB::table('cart_items')
                ->where('id', $existingCartItem->id)
                ->update(['quantity' => $existingCartItem->quantity + 1]);
        } else {
            // If product is not in cart, add it with quantity 1
            DB::table('cart_items')->insert([
                'user_id' => $request->user_id,
                'product_id' => $request->product_id,
                'quantity' => 1,
            ]);
        }

        return response()->json([
            'response' => 'Product added to cart',
        ], 200);
    }
    function remove(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'product_id' => 'required',
        ]);
        $existingCartItem = DB::table('cart_items')
            ->where('user_id', $request->user_id)
            ->where('product_id', $request->product_id)
            ->first();
        if ($existingCartItem) {
            if ($existingCartItem->quantity > 1) {
                DB::table('cart_items')
                    ->where('id', $existingCartItem->id)
                    ->update(['quantity' => $existingCartItem->quantity - 1]);
            } else {
                DB::table('cart_items')
                    ->where('id', $existingCartItem->id)
                    ->delete();
            }
        }
        return response()->json([
            'response' => 'Product deleted from cart',
        ], 200);
    }
    function delete(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'product_id' => 'required',
        ]);
        DB::table('cart_items')
            ->where('user_id', $request->user_id)
            ->where('product_id', $request->product_id)
            ->delete();
        return response()->json([
            'response' => 'Product deleted from cart',
        ], 200);
    }


    function getUserCart($id)
    {
        $cartItems = DB::table('cart_items')
            ->join('products', 'cart_items.product_id', '=', 'products.id')
            ->where('cart_items.user_id', $id)
            ->select('cart_items.id', 'cart_items.user_id', 'cart_items.product_id', 'cart_items.quantity')
            ->get();

        $cartItemsWithProduct = $cartItems->map(function ($item) {
            $product = Product::where('id', $item->product_id)->with('Images')->where('status', '=', 'published')->first();
            return [
                'id' => $item->id,
                'user_id' => $item->user_id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'product_details' => $product,
            ];

        });


        return response()->json([
            'response' => $cartItemsWithProduct,
        ], 200);
    }

    function showAll()
    {
        $show = DB::table('cart_items')->get();
        return response()->json([
            'response' => $show,
        ], 200);
    }
}
