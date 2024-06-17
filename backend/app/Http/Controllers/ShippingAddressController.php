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
        $user = Auth::user();

	// Check if user already has 3 addresses
	if ($user->shippingAddresses()->count() >= 3) {
	    return response()->json(['error' => 'Maximum limit of 3 shipping addresses reached'], 400);
	}

        $request->validate([
            'name' => 'required',
            'address_line_1' => 'required',
            'city' => 'required',
            'state' => 'required',
            'postal_code' => 'required',
            'country' => 'required',
        ]);

        $shippingAddress = new ShippingAddress([
            'user_id' => $user->id,
            'name' => $request->input('name'),
            'address_line_1' => $request->input('address_line_1'),
            'address_line_2' => $request->input('address_line_2'),
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
        $request->validate([
            'name' => 'required',
            'address_line_1' => 'required',
            'city' => 'required',
            'state' => 'required',
            'postal_code' => 'required',
            'country' => 'required',
        ]);

        $shippingAddress = ShippingAddress::findOrFail($id);
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

    public function destroy($id)
    {
        $shippingAddress = ShippingAddress::findOrFail($id);
        $shippingAddress->delete();

        return response()->json(['message' => 'Shipping address deleted successfully']);
    }
}
