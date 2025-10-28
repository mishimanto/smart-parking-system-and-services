<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TransactionApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $transaction;
    public $user;

    public function __construct($transaction, $user)
    {
        $this->transaction = $transaction;
        $this->user = $user;
    }

    public function build()
    {
        return $this->subject('🎉 Wallet Topup Approved - MONARK')
                    ->view('emails.transaction-approved')
                    ->with([
                        'transaction' => $this->transaction,
                        'user' => $this->user
                    ]);
    }
}