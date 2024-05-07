<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Cart;

class CartController extends Controller
{

    public function index()
    {
	    $user = auth()->user();
	    $cartItems = $user->carts()->with('product.displayImage')->get();

	    $responseData = [];
	    foreach ($cartItems as $item) {
		$product = $item->product;
		if ($product) {
		    $product->quantity = $item->quantity;
		    $responseData[] = $item;
		}
	    }

	    return response()->json(['cart' => $responseData]);	    
    }

    public function mergeCart(Request $request)
    {
        $user_id = auth()->id();
        $cartItems = $request->input('cart');

        // Check if the cart is not null and is an array
        if (!is_array($cartItems) || empty($cartItems)) {
            return response()->json(['error' => 'Invalid cart data'], 400);
        }

        foreach ($cartItems as $item) {
            // Check if the item is an array
            if (!is_array($item)) {
                return response()->json(['error' => 'Invalid item data'], 400);
            }

            // Extract product_id and quantity from the item
            $product_id = $item['id'];
            $quantity = $item['quantity'];

            // Check if the item exists in the user's cart
            $existingCartItem = Cart::where('user_id', $user_id)
                ->where('product_id', $product_id)
                ->first();

            if ($existingCartItem) {
                $new_quantity = $existingCartItem->quantity + $quantity;
                // If the item exists, update the quantity
                $existingCartItem->update(['quantity' => $new_quantity]);
            } else {
                // If the item doesn't exist, add it to the cart
                Cart::create(
                    [
                    'user_id' => $user_id,
                    'product_id' => $product_id,
                    'quantity' => $quantity,
                    ]
                );
            }
        }

        // Retrieve the updated cart for the user
        $updatedCart = Cart::where('user_id', $user_id)->get();

        return response()->json(['message' => 'Cart merged successfully', 'cart' => $updatedCart], 200);
    }






    public function addToCart(Request $request)
    {
        $productId = $request->input('id');
        $quantity = $request->input('quantity');


        $user = auth()->user();
        $cartItem = $user->carts()->where('product_id', $productId)->first();

        if ($cartItem) {
            $cartItem->update(['quantity' => $cartItem->quantity + $quantity]);
        } else {
            $cartItem = $user->carts()->create(
                [
                'product_id' => $productId,
                'quantity' => $quantity,
                ]
            );
        }

        return response()->json(['message' => 'Item added to cart successfully', 'cartItem' => $cartItem]);
    }

    public function removeFromCart(Request $request)
    {
        $productId = $request->input('productId');

        $user = auth()->user();

        $user->carts()->where('product_id', $productId)->delete();

        return response()->json(['message' => 'Item removed from cart successfully', 'cartItem' => null]);
    }

    public function updateCart(Request $request)
    {
        $productId = $request->input('productId');
        $quantity = $request->input('quantity');

        $user = auth()->user();

        $user->carts()->where('product_id', $productId)->update(['quantity' => $quantity]);

        return response()->json(['message' => 'Cart updated successfully', 'cartItem' => null]);
    }

    public function clearCart()
    {
        $user = auth()->user();

        $user->carts()->delete();

        return response()->json(['message' => 'Cart cleared successfully', 'cartItem' => null]);
    }




    //Legacy
    public function mergeCarts(Request $request)
    {
        $user = auth()->user();

        // Assume $request->input('unauthenticatedCart') is an array of cart items
        $unauthenticatedCart = $request->input('unauthenticatedCart');

        foreach ($unauthenticatedCart as $cartItem) {
            $existingCartItem = $user->cart()->where('product_id', $cartItem['product_id'])->first();

            if ($existingCartItem) {
                // Update quantity if the product is already in the cart
                $existingCartItem->update(['quantity' => $existingCartItem->quantity + $cartItem['quantity']]);
            } else {
                // Otherwise, create a new cart item
                $user->cart()->create(
                    [
                    'product_id' => $cartItem['product_id'],
                    'quantity' => $cartItem['quantity'],
                    ]
                );
            }
        }

        // Return the updated cart along with a success message
        $mergedCart = $user->cart()->get();

        return response()->json(['message' => 'Carts merged successfully', 'cart' => $mergedCart]);
    }





}
