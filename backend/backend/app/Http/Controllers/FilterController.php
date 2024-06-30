<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

/**
 * Class FilterController
 *
 * @package App\Http\Controllers
 */

class FilterController extends Controller
{
    public function test(Request $request)
    {
        try {
            $categories = Category::all();

            $query = Product::query();

            if(isset($request->title) && ($request->title != null)) {
                $query->where('name', $request->title);
            }

            if(isset($request->min) && ($request->min != null)) {
                $query->where('price', '>=', $request->min);
            }

            if(isset($request->max) && ($request->max != null)) {
                $query->where('price', '<=', $request->max);
            }

            if(isset($request->category) && ($request->category != null)) {
                $query->whereHas(
                    'category', function ($q) use ($request) {
                        $q->whereIn('id', $request->category);
                    }
                );
            }

            $products = $query->get();

            return response()->json(['products' => $products, 'categories' => $categories]);
        } catch (\Exception $e) {
            Log::error('Error fetching products: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
