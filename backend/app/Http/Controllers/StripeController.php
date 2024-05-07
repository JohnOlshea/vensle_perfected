<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use Stripe\Stripe;


class StripeController extends Controller
{
    public function payment(Request $request)
    {
	//TODO:payment method in its own table
        try {
            $validator = Validator::make(
                $request->all(), [
            	    'order_items' => 'required|array',
            	    'order_items.*.product_id' => 'required|exists:products,id',
            	    'order_items.*.quantity' => 'required|integer|min:1',	
            	    'payment_method' => 'required|string',//Should be enum
                    'total_price' => 'required|numeric',
                ]
            );

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            $orderItems = $request->order_items;
            $lineItems = [];

            //$userId = Auth::id();
	    $user = Auth::user();
	    $userId = $user->id;

	    // Delete old order and orderItems because user can only have one order at a time
            Order::where('user_id', $userId)->where('status', 'Inactive')->delete();

	    // Create Order
            $order = Order::create(
                [
                    'user_id' => $userId,
                    'stripe_session_id' => null,
                    //'stripe_session_id' => $session->id,
                    'payment_method' => $request->payment_method,
		    'paid' => false,
                    'status' => 'Inactive',
                    'total_price' => $request->total_price,
                ]
            );


	    foreach ($orderItems as $item) {
                $product = Product::find($item['product_id']);
		$orderImageName = NULL; 
		
                if (!$product) {
                    return response()->json(['error' => 'Product not found'], 404);
		}
		$priceInCents = is_numeric($product->price) ? $product->price * 100 : 0;

		if ($product->displayImage) {
			$name = $product->displayImage->name;
			//TODO: create folder for each user
			$newName = $product->user_id . "_" . $product->displayImage->name;

			$fromPath = public_path('uploads/' . $name);
			$toPath = public_path('uploads/orders/' . $newName);

			//TODO: improve this
			if (File::exists($fromPath)) {
				// Ensure the directory for the target path exists
				File::ensureDirectoryExists(dirname($toPath));
				// Copy the file
				File::copy($fromPath, $toPath);
				$orderImageName = $name;
			}
		}

            // Create Order Item TODO: Put in function
            $orderItem = new OrderItem;
            $orderItem->order_id = $order->id;
            $orderItem->buyer_id = $userId;
            $orderItem->seller_id = $product->user_id;
            $orderItem->product_id = $item['product_id'];
	    $orderItem->name = $product->name;
	    $categoryName = $product->category->name;
	    $orderItem->category_name = $categoryName;
	    $orderItem->description = $product->description;
            $orderItem->quantity = $item['quantity'];
	    $orderItem->price = $product->price;
            $orderItem->currency = $product->currency;
	    $orderItem->image = $orderImageName;
            $orderItem->status = 'Ongoing';

            $orderItem->save();		

		//TODO: if currency == $ or £ or ...
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'usd',
                        'unit_amount' => $priceInCents,
                        'product_data' => [
                            'name' => $product->name,
                            'description' => $product->description,
                        ],
                    ],
                    'quantity' => $item['quantity'],
                ];

	    	// Increment the 'sold' field
        	$product->increment('sold');

            }

            //dd($lineItems);
            
            Stripe::setApiKey(config('stripe.secret_key'));

            $session = Session::create([
                'payment_method_types' => ['card', 'cashapp', 'us_bank_account'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => 'https://nominet.vensle.com/payment/success',
                'cancel_url' => 'https://nominet.vensle.com/payment/cancel',
            ]);
            

        $order->update([
            'stripe_session_id' => $session->id,
            'status' => 'Ongoing',
            'paid' => true
	]);


	//Create notification
        $user->createAlertIfEnabled(
            'Order created',
            'Your order has been created successfully',
            'order_created',
	    'order/' . $order->id
        );

            return response()->json(['url' => $session->url]);
            //return response()->json(['url' => 'https://nominet.vensle.com/payment/success']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function paymentSuccessful(Request $request)
    {
        try {
            $userId = Auth::id();

            // Find the user's existing order
            $order = Order::where('user_id', $userId)->firstOrFail();

            // If an order exists, update its status to "completed"
            $order->status = 'completed';
            $order->save();

            return response()->json(
                [
                'message' => 'Payment successful. Order status updated.',
                'product_ids' => $order->product_ids
                ]
            );
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
