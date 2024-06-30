<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrderTrail;

class OrderTrailController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'name' => 'required|string',
        ]);

        $orderTrail = OrderTrail::create($data);

        return response()->json($orderTrail, 201);
    }

    public function index($orderId)
    {
    	$orderTrails = OrderTrail::where('order_id', $orderId)
                    ->orderBy('created_at', 'desc')
                    ->get();

    return response()->json($orderTrails);	    
    }
}
