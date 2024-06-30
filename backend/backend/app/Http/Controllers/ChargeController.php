<?php

namespace App\Http\Controllers;

use App\Models\Charge;
use Illuminate\Http\Request;

class ChargeController extends Controller
{
    /**
     * Display a listing of the charges.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $charges = Charge::all();

        return response()->json(['charges' => $charges], 200);
    }

    /**
     * Store a newly created charge in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'percentage' => 'required|numeric|min:0',
        ]);

        $charge = Charge::create([
            'name' => $request->name,
            'percentage' => $request->percentage,
        ]);

        return response()->json(['message' => 'Charge created successfully', 'charge' => $charge], 201);
    }
}
