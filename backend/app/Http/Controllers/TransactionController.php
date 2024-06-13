<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
	     $transactions = Transaction::with('user:id,name')
                ->orderBy('created_at', 'desc')
                ->get();
	     return response()->json([
                'status' => 'success',
                'message' => 'Transactions retrieved successfully',
                'data' => $transactions
            ]);
        } catch (\Exception $e) {
	    return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch transactions',
                'error' => $e->getMessage()
            ], 500);
        }
    }	    

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'transaction_description' => 'required|string',
            'amount' => 'required|numeric',
	    'status' => 'nullable|string|in:pending,paid,declined',
        ]);

        try {
            $transaction = Transaction::create([
                'user_id' => Auth::id(),
                'transaction_no' => Transaction::generateTransactionNo(),
                'transaction_description' => $request->transaction_description,
                'amount' => $request->amount,
		'status' => $request->status ?? 'pending',
            ]);

	    return response()->json([
                'status' => 'success',
                'message' => 'Transaction created successfully',
                'data' => $transaction
            ], 201);
        } catch (\Exception $e) {
	    return response()->json([
                'status' => 'error',
                'message' => 'Failed to create transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $transaction_id)
    {
        try {
            $transaction = Transaction::findOrFail($transaction_id);
	    return response()->json([
                'status' => 'success',
                'message' => 'Transaction retrieved successfully',
                'data' => $transaction
            ]);
            return response()->json($transaction);
        } catch (\Exception $e) {
	    return response()->json([
                'status' => 'error',
                'message' => 'Transaction not found',
                'error' => $e->getMessage()
            ], 404);
        }        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $transaction_id)
    {
        $request->validate([
            'transaction_description' => 'string',
            'amount' => 'numeric',
	    'status' => 'nullable|string|in:pending,declined,paid',
        ]);

        try {
            $transaction = Transaction::findOrFail($transaction_id);
            $transaction->update($request->all());

	    return response()->json([
                'status' => 'success',
                'message' => 'Transaction updated successfully',
                'data' => $transaction
            ]);
        } catch (\Exception $e) {
	    return response()->json([
                'status' => 'error',
                'message' => 'Failed to update transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $transaction_id)
    {
        try {
            $transaction = Transaction::findOrFail($transaction_id);
            $transaction->delete();

	    return response()->json([
                'status' => 'success',
                'message' => 'Transaction deleted successfully',
                'data' => null
            ], 204);
        } catch (\Exception $e) {
	    return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
