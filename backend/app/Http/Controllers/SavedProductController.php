<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\SavedProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SavedProductController extends Controller
{
    /**
     * Get all saved products for the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $user = Auth::user();
	$savedProducts = $user->savedProducts()
    	    ->with(['product.category', 'product.images', 'product.user', 'product.displayImage'])
    	    ->get();

	return response()->json(['saved_products' => $savedProducts], 200);
    }

    /**
     * Add a product to saved products.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
                $request->validate([
            	    'product_id' => 'required|exists:products,id',
		]);

		$productId = $request->input('product_id');
        	$user = Auth::user();

		$existingSavedProduct = SavedProduct::where('user_id', $user->id)
		    ->where('product_id', $productId)
		    ->first();

		if ($existingSavedProduct) {
		    return response()->json(['message' => 'Product already saved'], 409);
		}

		$savedProduct = SavedProduct::create([
		    'user_id' => $user->id,
		    'product_id' => $productId,
		]);

		return response()->json([
			'message' => 'Product saved successfully.',
			'product' => $savedProduct
		]);
        } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
        }
	    

    }        

    /**
     * Remove a product from saved products.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($productId)
    {
	    try {
		$user = Auth::user();

		// Retrieve the saved product
		$savedProduct = SavedProduct::where('user_id', $user->id)
					    ->where('product_id', $productId)
					    ->first();

		// Check if the saved product exists
		if (!$savedProduct) {
		    return response()->json(['error' => 'Product not found in saved products'], 404);
		}

		// Delete the saved product
		$savedProduct->delete();

		return response()->json(['message' => 'Product removed from saved products'], 200);
	    } catch (\Exception $e) {
		// Handle any exceptions
                return response()->json(['error' => $e->getMessage()], 500);
	    }	    
    }


    /**
     * Delete all saved products for the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyAll()
    {
        $user = Auth::user();
        $user->savedProducts()->delete();

        return response()->json(['message' => 'All saved products deleted successfully'], 200);
    }
}
