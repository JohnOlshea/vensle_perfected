<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use App\Models\OrderTrail;
use App\Models\OrderDriverActivity;
use App\Models\ShippingAddress;
use App\Models\Feedback;
use App\Models\Message;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
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
	//TODO:payment method in its own table, method to big, add Separation of Concerns
        try {
            $validator = Validator::make(
                $request->all(), [
		    'order_items' => 'required|array',
		    'order_items.*.product_id' => 'required|exists:products,id',
		    'order_items.*.quantity' => 'required|integer|min:1',
		    'payment_method' => 'required|string',
		    'total_price' => 'required|numeric',
		    'shipping_address_id' => 'required_without:new_shipping_address|nullable|exists:shipping_addresses,id',
		    'new_shipping_address' => 'required_without:shipping_address_id|array',
		    'new_shipping_address.name' => 'required_with:new_shipping_address',
		    'new_shipping_address.address_line_1' => 'required_with:new_shipping_address',
		    'new_shipping_address.city' => 'required_with:new_shipping_address',
		    'new_shipping_address.state' => 'required_with:new_shipping_address',
???LINES MISSING
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
	
	    //DB::commit(); move below?

	    	//Create notification
		$user->createAlertIfEnabled(
		    'Order created',
		    'Your order has been created successfully',
		    'order_created',
		    'order/' . $order->id
		);

	    //Find closest driver to assign order
	    $closestDriver = $this->findClosestDriver($product->latitude, $product->longitude);

	    if ($closestDriver) {
		    $product->assigned_driver_id = $closestDriver->driver_id;
		    $product->status = 'assigned';
		    $product->save();

		    // Notify driver
		    //$this->notifyDriver($closestDriver->driver_id, $product->id);
	    }

	    return response()->json([
		    'message' => 'Order created successfully',
		    'order' => $order,
		    'items' => $storedItems
	    ]);
	    //} catch (\Exception $e) {
	    //DB::rollBack();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function findClosestDriver($latitude, $longitude)
    {
        $drivers = DB::table('drivers')
            ->join('driver_locations', 'drivers.id', '=', 'driver_locations.driver_id')
            ->where('drivers.is_online', true)
            ->select('drivers.id as driver_id', 'driver_locations.latitude', 'driver_locations.longitude')
            ->get();

        $closestDriver = null;
        $minDistance = PHP_INT_MAX;

        foreach ($drivers as $driver) {
            $distance = GeoHelper::haversine($latitude, $longitude, $driver->latitude, $driver->longitude);
            if ($distance < $minDistance) {
                $minDistance = $distance;
                $closestDriver = $driver;
            }
        }

        return $closestDriver;
    }    

    public function acceptOrder(Request $request, Order $order)
    {
        // TODO: Check if order exists, current implementation not working
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

	// Create driver activity for order
	OrderDriverActivity::create([
	    'driver_id' => $driver_id,
            'order_id' => $order->id,
	    'action' => 'accepted',
	]);

	// Create order tail
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
	    'action' => 'rejected',
	]);

        $rejectedDriverIds = $order->rejected_driver_ids ?? [];
        $rejectedDriverIds[] = $driver_id;
	
	// Update the order with the new rejected driver IDs
	$order->rejected_driver_ids = $rejectedDriverIds;
	$order->save();

	//send notification to seller

        return response()->json(['message' => 'Order declined successfully', 'order' => $order]);
    }
    
    public function completeOrder(Request $request, Order $order)
    {
        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        $validationRules = [];
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

	// Store Seller rating and feedback
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

        return response()->json([
            'message_status' => 'Order completed successfully',
            'order' => $order,
            'feedback' => $storedFeedback,
	    'message' => $message,
        ]);	
    }

    public function completeOrderItem(Request $request, OrderItem $orderItem)
    {
???LINES MISSING
        //$totalOrders = Order::where('user_id', $user->id)->count();
        //return response()->json(['totalOrders' => $totalOrders]);
    //}

}
