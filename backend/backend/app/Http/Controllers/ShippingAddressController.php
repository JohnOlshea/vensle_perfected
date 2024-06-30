<?php

namespace App\Http\Controllers;

use App\Models\ShippingAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShippingAddressController extends Controller
{
    public function index()
    {
        // Fetch all shipping addresses for the authenticated user
        $user = Auth::user();
        $shippingAddresses = $user->shippingAddresses()->get();

        return response()->json(['shipping_addresses' => $shippingAddresses]);
    }

    public function store(Request $request)
    {        
        $request->validate([
            'name' => 'nullable|string',
            'address_line_1' => 'nullable|string',
            'address_line_2' => 'nullable|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'country' => 'nullable|string',
        ]);
        
        $user = Auth::user();

        // Check if user already has 3 addresses
        if ($user->shippingAddresses()->count() >= 3) {
            return response()->json(['error' => 'Maximum limit of 3 shipping addresses reached'], 400);
        }

        $shippingAddress = new ShippingAddress([
            'user_id' => $user->id,
            'name' => $request->input('name'),
            'address_line_1' => $request->input('address_line_1'),
            'address_line_2' => $request->input('address_line_2'),
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
            'city' => $request->input('city'),
            'state' => $request->input('state'),
            'postal_code' => $request->input('postal_code'),
            'country' => $request->input('country'),
        ]);
        $shippingAddress->save();

        return response()->json(['message' => 'Shipping address created successfully', 'shipping_address' => $shippingAddress]);
    }

    public function update(Request $request, $id)
    {
	    $user = Auth::user();

	    // Find the shipping address
	    $shippingAddress = ShippingAddress::findOrFail($id);

	    // Check if the authenticated user owns the shipping address
	    if ($shippingAddress->user_id !== $user->id) {
		return response()->json(['error' => 'You do not have permission to update this shipping address'], 403);
	    }	    

       $request->validate([
            'name' => 'required|string',
            'address_line_1' => 'required|string',
            'address_line_2' => 'nullable|string',
            'city' => 'required|string',
            'state' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'country' => 'nullable|string',
        ]);	    

        $shippingAddress->name = $request->input('name');
        $shippingAddress->address_line_1 = $request->input('address_line_1');
        $shippingAddress->address_line_2 = $request->input('address_line_2');
        $shippingAddress->city = $request->input('city');
        $shippingAddress->state = $request->input('state');
        $shippingAddress->postal_code = $request->input('postal_code');
        $shippingAddress->country = $request->input('country');
        $shippingAddress->save();

        return response()->json(['message' => 'Shipping address updated successfully', 'shipping_address' => $shippingAddress]);
    }

    public function destroy($id) {
	    $user = Auth::user();

	    // Find the shipping address
	    $shippingAddress = ShippingAddress::findOrFail($id);

	    // Check if the authenticated user owns the shipping address
	    if ($shippingAddress->user_id !== $user->id) {
		return response()->json(['error' => 'You do not have permission to delete this shipping address'], 403);
	    }

	    // Delete the shipping address
	    $shippingAddress->delete();

	    return response()->json(['message' => 'Shipping address deleted successfully']);
   }
}
