<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class ServiceCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $invoiceData;

    public function __construct($order, $invoiceData)
    {
        $this->order = $order;
        $this->invoiceData = $invoiceData;
    }

    public function build()
    {
        // Generate PDF invoice
        $pdf = PDF::loadView('emails.service_invoice', [
            'invoiceData' => $this->invoiceData,
            'order' => $this->order
        ]);

        return $this->subject('Car Wash Service Completed - Invoice #' . $this->invoiceData['invoice_number'])
                    ->view('emails.service-completed')
                    ->attachData($pdf->output(), 
                                'invoice-' . $this->invoiceData['invoice_number'] . '.pdf', 
                                ['mime' => 'application/pdf'])
                    ->with([
                        'order' => $this->order,
                        'invoice' => $this->invoiceData
                    ]);
    }
}