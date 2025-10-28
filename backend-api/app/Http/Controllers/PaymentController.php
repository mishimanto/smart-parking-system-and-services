<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WalletTransaction;
use App\Models\User;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Services\EmailService;

class PaymentController extends Controller
{
    protected $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    public function getPaymentMethods()
    {
        try {
            $methods = PaymentMethod::all();

            return response()->json([
                'success' => true,
                'data' => $methods
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Initiate topup transaction
    public function initiateTopup(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'amount' => 'required|numeric|min:1000|max:50000',
                'payment_method' => 'required|string|in:bkash,nagad,rocket',
                'mobile_number' => 'required|string|size:11',
                'pin' => 'required|string|min:4|max:5'
            ]);

            $user = auth()->user();
            
            // Generate unique transaction ID and verification code
            $transactionId = 'TXN' . date('YmdHis') . Str::random(6);
            $verificationCode = strtoupper(Str::random(6));
            
            // **প্রথমে Database-এ Save করুন**
            $transaction = WalletTransaction::create([
                'user_id' => $user->id,
                'type' => 'topup',
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'mobile_number' => $request->mobile_number,
                'pin' => $request->pin,
                'status' => 'pending',
                'generated_transaction_id' => $transactionId,
                'verification_code' => $verificationCode, // **এখানে Save হচ্ছে**
                'description' => 'Wallet topup via ' . $request->payment_method
            ]);

            DB::commit(); // **প্রথমে Database Commit করুন**

            // **তারপর Email পাঠান**
            $emailSent = $this->emailService->sendVerificationCode($user, $transaction, $verificationCode);
            
            if (!$emailSent) {
                Log::warning('Verification email failed to send for transaction: ' . $transactionId);
                // Email fail হলেও transaction continue করবে, user কে manually code দিতে হবে
            }

            return response()->json([
                'success' => true,
                'message' => $emailSent 
                    ? 'Transaction initiated. Verification code sent to your email.' 
                    : 'Transaction initiated. Please check your email for verification code.',
                'data' => [
                    'transaction_id' => $transactionId,
                    'amount' => $request->amount,
                    'payment_method' => $request->payment_method,
                    'status' => 'pending',
                    'verification_code' => $verificationCode // Development এর জন্য, production এ remove করুন
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Topup initiation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Transaction initiation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function verifyTransaction(Request $request)
    {
        DB::beginTransaction();
        try {
            Log::info('=== VERIFY TRANSACTION START ===');
            Log::info('Request data:', $request->all());

            $request->validate([
                'transaction_id' => 'required|string',
                'verification_code' => 'required|string'
            ]);

            // Find transaction
            $transaction = WalletTransaction::where('generated_transaction_id', $request->transaction_id)
                ->where('status', 'pending')
                ->first();

            Log::info('Transaction found:', [$transaction ? $transaction->toArray() : 'NOT FOUND']);

            if (!$transaction) {
                Log::warning('Transaction not found or not pending');
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction not found or already processed'
                ], 404);
            }

            // Check verification code
            Log::info('Verification code check:', [
                'provided' => $request->verification_code,
                'expected' => $transaction->verification_code,
                'match' => $transaction->verification_code === $request->verification_code
            ]);

            if ($transaction->verification_code !== $request->verification_code) {
                Log::warning('Verification code mismatch');
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid verification code'
                ], 400);
            }

            // Update transaction status
            $transaction->update([
                'status' => 'verified',
                'verified_at' => now()
            ]);

            DB::commit();

            Log::info('=== VERIFY TRANSACTION SUCCESS ===');

            return response()->json([
                'success' => true,
                'message' => 'Transaction verified successfully. Waiting for admin approval.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('=== VERIFY TRANSACTION ERROR ===');
            Log::error('Error message: ' . $e->getMessage());
            Log::error('Error trace: ' . $e->getTraceAsString());
            Log::error('=== END ERROR ===');
            
            return response()->json([
                'success' => false,
                'message' => 'Verification failed: ' . $e->getMessage()
            ], 500);
        }
    }
}