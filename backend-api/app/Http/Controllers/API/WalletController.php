<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\WalletTransaction;
use App\Models\User;

class WalletController extends Controller
{
    // WalletController.php - ensure proper response structure
public function getBalance(Request $request)
{
    try {
        $user = $request->user();
        
        return response()->json([
            'success' => true,
            'balance' => $user->wallet_balance ?: 0, // Ensure balance is always set
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ]
        ]);
    } catch (\Exception $e) {
        \Log::error('Wallet balance error: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch wallet balance',
            'error' => $e->getMessage(),
            'balance' => 0 // Default balance on error
        ], 500);
    }
}

    public function topup(Request $request)
    {
        try {
            $request->validate([
                'amount' => 'required|numeric|min:10|max:10000',
                'payment_method' => 'required|in:bkash,nagad,card',
                'transaction_id' => 'required|string|max:255'
            ]);

            return DB::transaction(function () use ($request) {
                $user = $request->user();
                
                // Create transaction record
                $transaction = WalletTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'topup',
                    'amount' => $request->amount,
                    'payment_method' => $request->payment_method,
                    'transaction_id' => $request->transaction_id,
                    'status' => 'completed',
                    'description' => 'Wallet topup via ' . $request->payment_method
                ]);

                // Update user balance
                $user->wallet_balance += $request->amount;
                $user->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Topup successful',
                    'new_balance' => $user->wallet_balance,
                    'transaction' => $transaction
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Topup failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function transactions(Request $request)
    {
        try {
            $user = $request->user();
            
            $transactions = WalletTransaction::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'transactions' => $transactions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch transactions',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}