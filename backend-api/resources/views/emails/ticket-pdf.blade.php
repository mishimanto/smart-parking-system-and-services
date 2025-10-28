<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Parking Ticket - {{ $ticketNumber }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        .ticket { border: 2px solid #333; padding: 20px; max-width: 400px; margin: 0 auto; }
        .header { text-align: center; background: #f8f9fa; padding: 10px; margin-bottom: 20px; }
        .ticket-number { font-size: 24px; font-weight: bold; color: #007bff; margin: 10px 0; }
        .details { margin: 15px 0; }
        .detail-row { display: flex; justify-content: space-between; margin: 8px 0; }
        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
        .barcode { text-align: center; margin: 20px 0; font-family: 'Libre Barcode 128', monospace; font-size: 36px; }
        .status-badge { background: #28a745; color: white; padding: 5px 10px; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="header">
            <h2>Parking Ticket</h2>
            <div class="ticket-number">{{ $ticketNumber }}</div>
            <div class="status-badge">COMPLETED</div>
        </div>

        <div class="barcode">
            ***{{ $ticketNumber }}***
        </div>

        <div class="details">
            <div class="detail-row">
                <strong>User Name:</strong>
                <span>{{ $userName }}</span>
            </div>
            <div class="detail-row">
                <strong>Parking:</strong>
                <span>{{ $parkingName }}</span>
            </div>
            <div class="detail-row">
                <strong>Slot:</strong>
                <span>{{ $slotCode }}</span>
            </div>
            <div class="detail-row">
                <strong>Duration:</strong>
                <span>{{ $hours }} hour(s)</span>
            </div>
            <div class="detail-row">
                <strong>Total Amount:</strong>
                <span>BDT {{ number_format($totalPrice, 2) }}</span>
            </div>
            <div class="detail-row">
                <strong>Extra Charges:</strong>
                <span>BDT {{ number_format($extraCharges, 2) }}</span>
            </div>
            <div class="detail-row">
                <strong>Final Amount:</strong>
                <span><strong>BDT {{ number_format($finalAmount, 2) }}</strong></span>
            </div>
            <div class="detail-row">
                <strong>Check-in Time:</strong>
                <span>{{ $checkinTime }}</span>
            </div>
            <div class="detail-row">
                <strong>Check-out Time:</strong>
                <span>{{ $checkoutTime }}</span>
            </div>
        </div>

        <div class="footer">
            <p>Thank you for using our parking service!</p>
            <p>Generated on: {{ $generatedAt }}</p>
        </div>
    </div>
</body>
</html>