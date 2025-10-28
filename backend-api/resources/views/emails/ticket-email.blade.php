<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Parking Ticket</title>
</head>
<body>
    <h2>Hello {{ $booking->user->name }},</h2>
    
    <p>Your parking session has been completed successfully. Please find your ticket attached.</p>
    
    <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0;">
        <h3>Booking Summary:</h3>
        <p><strong>Ticket Number:</strong> {{ $ticketNumber }}</p>
        <p><strong>Parking:</strong> {{ $booking->parking->name }}</p>
        <p><strong>Slot:</strong> {{ $booking->slot->slot_code }}</p>
        <p><strong>Duration:</strong> {{ $booking->hours }} hour(s)</p>
        <p><strong>Total Amount:</strong> BDT {{ number_format($booking->total_price + ($booking->extra_charges ?? 0), 2) }}</p>
        <p><strong>Status:</strong> Completed</p>
    </div>

    <p>Thank you for using our parking service!</p>

    <p>Best regards,<br>
    Parking Management System</p>
</body>
</html>