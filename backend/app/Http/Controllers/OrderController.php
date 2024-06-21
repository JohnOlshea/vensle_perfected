<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use App\Models\OrderTrail;
use App\Models\Feedback;
use App\Models\Message;
use App\Models\DriverLocation;
use App\Models\OrderDriverActivity;
use App\Models\ShippingAddress;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with('user')->get();
        return response()->json(['orders' => $orders]);
    }

    /**
     * Get orders for the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserOrders()
    {
        try {
            $user = Auth::user();
	    //dd($user);
            //$orders = Order::with('products')
            //$orders = Order::all(); 
            $orders = Order::where('user_id', $user->id)
		->where('status', '!=', 'Pending')
		->with('items.product.displayImage')
                ->get();

            return response()->json($orders);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

/**
 * Get the first incoming order for the authenticated driver.
 *
 * @return \Illuminate\Http\JsonResponse
 */
public function getIncomingOrder()
{
    try {
        $driver = Auth::user();
        $incomingOrder = Order::where('driver_id', $driver->id)
            ->where('status', 'Pending')
            ->with('items.product.displayImage')
            ->first();

        if ($incomingOrder) {
            return response()->json($incomingOrder);
        } else {
            return response()->json(['message' => 'No pending orders found'], 404);
        }
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

/**
 * Get ongoing order for the authenticated driver.
 *
 * @return \Illuminate\Http\JsonResponse
 */
public function getOncomingOrder()
{
    try {
        $driver = Auth::user();
        $incomingOrder = Order::where('driver_id', $driver->id)
            ->where('status', 'Ongoing')
            ->with('items.product.displayImage')
            ->first();

        if ($incomingOrder) {
            return response()->json($incomingOrder);
        } else {
            return response()->json(['message' => 'No Ongoing orders found'], 404);
        }
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}


    public function getDriverDashboardData(Request $request)
    {
        $driver = Auth::user();    

	$totalOrders = $driver->orders()->count();

        $totalAcceptedOrders = $driver->orders()
                                      ->whereHas('driverActivities', function ($query) {
                                         $query->where('action', 'accepted');
                                      })
                                      ->distinct()
                                      ->count();

        $totalRejectedOrders = $driver->orders()
                                      ->whereHas('driverActivities', function ($query) {
                                         $query->where('action', 'rejected');
                                      })
                                      ->distinct()
				      ->count();
	$totalEarnings = $driver->orders()
                         ->where('driver_id', $driver->id)
			 ->sum('total_price');

	return response()->json([
          'total_orders' => $totalOrders,
          'total_accepted_orders' => $totalAcceptedOrders,
          'total_rejected_orders' => $totalRejectedOrders,
          'total_earnings' => $totalEarnings,
	]);
    }

    /**
     * Create a new order.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
	//TODO:payment method in its own table
        try {
            $validator = Validator::make($request->all(), [
               'order_items' => 'required|array',
                'order_items.*.product_id' => 'required|exists:products,id',
                'order_items.*.quantity' => 'required|integer|min:1',
                'payment_method' => 'required|string', // Should be enum
                'total_price' => 'required|numeric',
                'shipping_address_id' => 'required_without:new_shipping_address|nullable|exists:shipping_addresses,id',
                'new_shipping_address' => 'required_without:shipping_address_id|array',
                'new_shipping_address.name' => 'required_with:new_shipping_address',
                'new_shipping_address.address_line_1' => 'required_with:new_shipping_address',
                'new_shipping_address.address_line_2' => 'nullable|string',
                'new_shipping_address.city' => 'required_with:new_shipping_address',
                'new_shipping_address.state' => 'nullable|string',
                'new_shipping_address.postal_code' => 'nullable|string',
                'new_shipping_address.country' => 'nullable|string',		    
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            $orderItems = $request->order_items;
            $storedItems = [];

	    $user = Auth::user();
	    $userId = $user->id;


	    // Delete old order and orderItems because user can only have one order at a time
            Order::where('user_id', $userId)->where('status', 'Inactive')->delete();
            // Create Order
            $order = Order::create(
                [
                    'user_id' => $userId,
                    'stripe_session_id' => null,
                    'payment_method' => $request->payment_method,
                    'paid' => false,
                    'status' => 'Inactive',
                    'total_price' => $request->total_price,
                ]
            );

	    // Create Order

        // Handle shipping address
        if ($request->has('shipping_address_id')) {
            $shippingAddressId = $request->input('shipping_address_id');
        } else {
            // Check if user already has 3 addresses
            if ($user->shippingAddresses()->count() >= 3) {
                return response()->json(['error' => 'Maximum limit of 3 shipping addresses reached'], 400);
            }

            // Create new shipping address
            $newShippingAddress = $request->input('new_shipping_address');
            $shippingAddress = new ShippingAddress([
                'user_id' => $user->id,
                'name' => $newShippingAddress['name'],
                'address_line_1' => $newShippingAddress['address_line_1'],
                'address_line_2' => $newShippingAddress['address_line_2'] ?? null,
                'city' => $newShippingAddress['city'],
                'state' => $newShippingAddress['state'] ?? null,
                'postal_code' => $newShippingAddress['postal_code'] ?? null,
                'country' => $newShippingAddress['country'] ?? null,
            ]);
            $shippingAddress->save();
            $shippingAddressId = $shippingAddress->id;
        }
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
		    $orderItem->status = 'Pending';

		    $orderItem->save();		
		    
		    // Increment the 'sold' field
		    $product->increment('sold');

		    $storedItems[] = $orderItem;
            }

	    	//Create notification
		$user->createAlertIfEnabled(
		    'Order created',
		    'Your order has been created successfully',
		    'order_created',
		    'order/' . $order->id
		);

            // Assign pending orders to closest driver that is online and free
	    // This should be done after payment
            $closestDriver = $this->findClosestDriver(9.0627, 7.4635);
	    if ($closestDriver) {
                $order->driver_id = $closestDriver->user_id;
                $order->status = 'Ongoing';
                $order->save();

		$closestDriver->status = 'assigned';
                $closestDriver->save();
                //TODO:Notify driver
	    }

	    return response()->json([
		    'order' => $order,
		    'items' => $storedItems
	    ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    //TODO: place in helper function
    private function findClosestDriver($latitude, $longitude)
    {
	$drivers = DriverLocation::where('is_online', true)->where('status', 'free')->get();

        $closestDriver = null;
        $minDistance = PHP_INT_MAX;

        foreach ($drivers as $driver) {
            $distance = $this->haversine($latitude, $longitude, $driver->latitude, $driver->longitude);
            if ($distance < $minDistance) {
                $minDistance = $distance;
                $closestDriver = $driver;
            }
        }

        return $closestDriver;
    }

    //TODO:Place in helper function
    private function haversine($lat1, $lon1, $lat2, $lon2)
    {
        $earth_radius = 6371; // Earth radius in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $earth_radius * $c;
    }
    
    public function acceptOrder(Request $request, Order $order)
    {
        // Check if order exists
        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        // Check if order is pending
        if ($order->status !== 'Pending') {
            return response()->json(['error' => 'Order cannot be accepted at this stage'], 400);
        }

	$driver_id = Auth::id();

        // Update driver_id and status
        $order->driver_id = $driver_id;
        $order->status = 'Ongoing';
        $order->save();

	//update driver status in DriverLocation status
	$driverLocation = DriverLocation::where('user_id', $driver_id)->firstOrFail();
	$driverLocation->status = 'assigned';
	$driverLocation->save();


	// Create driver activity for order
        OrderDriverActivity::create([
            'driver_id' => $driver_id,
            'order_id' => $order->id,
            'action' => 'accepted',
        ]);
	
	
        OrderTrail::create([
            'order_id' => $order->id,
            'name' => 'Delivery Accepted',
        ]);

	//send notification to seller

        return response()->json(['message' => 'Order accepted successfully', 'order' => $order]);
    }

    //TODO: merge with acceptorder, not DRY
    public function rejectOrder(Request $request, Order $order)
    {
        $request->validate([
            'reason' => 'required|string',
        ]);
        // TODO: Check if order exists, current implementation not working
        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        // Check if order is pending
        if ($order->status !== 'Pending') {
            return response()->json(['error' => 'Order cannot be rejected at this stage'], 400);
        }

        $driver_id = Auth::id();

        // Create driver activity for order
        OrderDriverActivity::create([
            'driver_id' => $driver_id,
            'order_id' => $order->id,
	    'reason' => $request->input('reason'),
            'action' => 'rejected',
        ]);

        $rejectedDriverIds = $order->rejected_driver_ids ?? [];
        $rejectedDriverIds[] = $driver_id;

        // Update the order with the new rejected driver IDs
        $order->rejected_driver_ids = $rejectedDriverIds;
        $order->save();

        //send notification to seller

        return response()->json(['message' => 'Order declined successfully']);
    }

    public function completeOrder(Request $request, Order $order)
    {
        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        $validationRules = [];
	//Check delivery type buyer choose when making order
        if ($order->proof_type === 'picture') {
            $validationRules['delivery_proof_image'] = 'required|image|mimes:jpeg,png,jpg,gif|max:2048';
        } else {
            $validationRules['delivery_code'] = 'required';
	}

        if ($request->has('driver_feedback')) {
            $validationRules['driver_feedback.*.vendor_id'] = 'required';
            $validationRules['driver_feedback.*.rating'] = 'sometimes|nullable|integer|min:1|max:5';
            $validationRules['driver_feedback.*.feedback'] = 'sometimes|nullable|string|max:255';
        }	

        if ($request->has('message')) {
     	    $validationRules['receiver_id'] = 'required|exists:users,id';
	}

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }	
	    
	$driver_id = Auth::id();
        // Check if driver is authorized to complete
        if ($order->driver_id !== $driver_id) {
            return response()->json(['error' => 'Unauthorized to complete this order'], 403);
        }

        // Check if order can be completed (e.g., not already completed)
        if ($order->status !== 'Ongoing') {
            return response()->json(['error' => 'Order cannot be completed at this stage'], 400);
        }

	// Stored Seller rating and feedback
	$storedFeedback = [];
        if ($request->has('driver_feedback')) {
            foreach ($request->input('driver_feedback', []) as $feedbackData) {
                if (empty($feedbackData['vendor_id'])) {
                    return response()->json(['error' => 'User ID is required in driver feedback'], 400);
		}

                $feedback = new Feedback([
                    'user_id' => $feedbackData['vendor_id'],
                    'content' => $feedbackData['feedback'] ?? null,
                    'rating' => $feedbackData['rating'] ?? null,
                ]);

                $feedback->save();		
		$storedFeedback[] = $feedback->toArray();
            }
        }	

	// Proof type can either be picture or code
        if ($order->proof_type === 'picture') {
            $imageFile = $request->file('delivery_proof_image');

            if ($imageFile) {  // Check if image file is uploaded
                $extension = $imageFile->getClientOriginalExtension();
                $imageName = Str::random(32) . '.' . $extension;
                $imageFile->storeAs('public/delivery_proofs', $imageName);
                $order->delivery_proof_image = $imageName;
            }
        } else {
            $deliveryCode = $request->input('delivery_code');
            $validDeliveryCode = Order::where('delivery_code', $deliveryCode)
                ->where('id', $order->id)
                ->exists();

            if (!$validDeliveryCode) {
                return response()->json(['error' => 'Invalid delivery code'], 400);
            }	    
        }


        if ($request->has('message')) {
		
		$message = Message::create(
		    [
		        'sender_id' => $driver_id,
		        'receiver_id' => $order->user_id,
		        'content' => $request->input('message'),
		        //TODO: order_id
		        //'product_id' => $request->input('product_id'),
		        'read' => false,
		    ]
		);
	}


	$order->status = 'Completed';

        $order->save();

	$driver_id = Auth::id();
	//update driver status in DriverLocation status
	$driverLocation = DriverLocation::where('user_id', $driver_id)->firstOrFail();
	$driverLocation->status = 'free';
	$driverLocation->save();

        return response()->json([
            'message_status' => 'Order completed successfully',
            'order' => $order,
            'feedback' => $storedFeedback,
	    'message' => $message,
        ]);	
    }

    public function completeOrderItem(Request $request, OrderItem $orderItem)
    {
        // Check if the authenticated user is the driver of the order associated with this order item
        if ($orderItem->order->driver_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if order item can be completed (e.g., not already completed)
        if ($orderItem->status !== 'Ongoing') {
            return response()->json(['error' => 'Order item cannot be completed at this stage'], 400);
        }

        $orderItem->status = 'Completed';
        $orderItem->save();

        OrderTrail::create([
            'order_id' => $orderItem->order_id,
            'name' => 'Item Pickup: ' . $orderItem->name,
        ]);

	//TODO: Send notification to seller

	return response()->json(['message' => 'Order item completed successfully', 'order_item' => $orderItem]);
    }

    /**
     * Get customer order items for the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCustomerOrderItems()
    {
        try {
            $user = Auth::user();
            $order_items = OrderItem::where('seller_id', $user->id)
		//->where('status', '!=', 'Pending')
                ->get();

            return response()->json($order_items);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function orderDetails($orderId)
    {
	    $order = Order::with('items.seller')->find($orderId);

	    if (!$order) {
		return response()->json(['error' => 'Order not found'], 404);
	    }

	    return response()->json(['order' => $order], 200);
    }
    
    /**
     * Get details of a specific order by ID.
     *
     * @param  int $orderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrderDetails($orderId)
    {
        try {
            $user = auth::user();

            //$order = order::with(['items.product.displayimage', 'user'])
            //    ->where('user_id', $user->id)
            //    ->find($orderid);

        $order = order::with(['items.product.displayimage', 'user', 'ordertrails' => function ($query) {
            $query->orderby('created_at', 'desc');
        }])
        ->where('user_id', $user->id)
        ->find($orderid);

            if (!$order) {
                return response()->json(['error' => 'order not found'], 404);
            }

        $shippingFee = 100;
        $serviceCharge = 0;
        $discount = 0;
        $subtotal = 100;

        // Mock vendor details
        $vendor = [
            'Name' => 'John Doe',
            'Phone number' => '+44 690 30 50',
            'email' => 'john@doe.com',
            'shipping address' => '2 rodeo street',
        ];

        // Mock driver details
        $driver = [
            'Name' => 'Peter Shaun',
            'phone number' => '+44 323 850 42',
            'ratings' => 3,
            'Number of rides' => 4,
            'car type' => 'Toyota',
            'plate number' => 'AOR145',
        ];

        $order->shipping_fee = $shippingFee;
        $order->service_charge = $serviceCharge;
        $order->discount = $discount;
        $order->subtotal = $subtotal;
        $order->vendor = $vendor;
        $order->driver = $driver;	    

            return response()->json($order);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    //public function getTotalOrders()
    //{
        //$user = Auth::user();
        //$totalOrders = Order::where('user_id', $user->id)->count();
        //return response()->json(['totalOrders' => $totalOrders]);
    //}

}
