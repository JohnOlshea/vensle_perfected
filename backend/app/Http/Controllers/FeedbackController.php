<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Feedback;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

/**
 * @group Feedback Management
 *
 * APIs to manage feedback
 */
class FeedbackController extends Controller
{
    public function index()
    {
        $feedbacks = Feedback::with('user')->get();

        return response()->json($feedbacks);
    }

    public function getProductFeedback($product_id)
    {
        $feedback = Feedback::where('product_id', $product_id)
            ->with(['user', 'product', 'parent'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($feedback);
    }
    public function store(Request $request)
    {
        $request->validate(
            [
            'content' => 'required|string',
            'rating' => 'nullable|integer|min:1|max:5',
            'product_id' => 'required|exists:products,id',
            'parent_id' => 'nullable|exists:feedback,id',
            ]
        );

        $user = Auth::user();

        // Check if it's a reply or a new feedback
        if ($request->filled('parent_id')) {
            // It's a reply, so create a new feedback entry with the parent_id
            $feedback = Feedback::with('user')->create(
                [
                'content' => $request->input('content'),
                'user_id' => $user->id,
                'product_id' => $request->input('product_id'),
                'parent_id' => $request->input('parent_id'),
                ]
            );
        } else {
            // It's a new feedback entry
            $feedback = Feedback::with('user')->create(
                [
                'content' => $request->input('content'),
                'rating' => $request->input('rating'),
                'user_id' => $user->id,
                'product_id' => $request->input('product_id'),
                ]
            );

            // Update the product rating directly only if it's not a reply
            $this->updateProductRating($request->input('product_id'));
        }

        $feedback->user = $user;

        return response()->json($feedback, 201);
    }

    private function updateProductRating($productId)
    {
        $product = Product::find($productId);

        // Calculate the new average rating
        $averageRating = Feedback::where('product_id', $productId)->avg('rating');

        // Update the product rating field
        $product->update(['ratings' => $averageRating]);
    }

}
