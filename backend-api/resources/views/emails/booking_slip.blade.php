<!DOCTYPE html>
<html>
<head>
    <title>Booking Slip - {{ $slipData['slip_number'] }}</title>
    <style>
        body { 
            font-family: 'DejaVu Sans', Arial, sans-serif; 
            margin: 20px; 
            line-height: 1.4;
        }
        .header { 
            text-align: center; 
            border-bottom: 2px solid #333; 
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .slip-number { 
            font-size: 20px; 
            font-weight: bold; 
            color: #2c5aa0; 
            margin-top: 10px;
        }
        .details { 
            margin: 25px 0; 
        }
        .detail-row { 
            margin: 10px 0; 
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
        .label { 
            font-weight: bold; 
            color: #555;
            width: 200px;
            display: inline-block;
        }
        .instructions { 
            background: #f8f9fa; 
            padding: 15px; 
            border-radius: 8px; 
            margin-top: 25px;
            border-left: 4px solid #2c5aa0;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        .qr-code-placeholder {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin:0; color: #2c5aa0;">MONARK</h1>
        <p style="margin:5px 0; color: #666;">Professional Car Wash Service</p>
        <div class="slip-number">{{ $slipData['slip_number'] }}</div>
    </div>
    
    <div class="details">
        <h3 style="color: #2c5aa0; border-bottom: 1px solid #ddd; padding-bottom: 8px;">Booking Details</h3>
        
        <!-- <div class="detail-row">
            <span class="label">Order ID:</span> #{{ $slipData['order_id'] ?? $order->id }}
        </div> -->
        <div class="detail-row">
            <span class="label">Customer Name:</span> {{ $slipData['customer_name'] }}
        </div>
        <div class="detail-row">
            <span class="label">Email:</span> {{ $slipData['customer_email'] ?? $order->user->email }}
        </div>
        <div class="detail-row">
            <span class="label">Service:</span> {{ $slipData['service_name'] }}
        </div>
        <div class="detail-row">
            <span class="label">Description:</span> {{ $slipData['service_description'] ?? $order->service->description }}
        </div>
        <div class="detail-row">
            <span class="label">Booking Time:</span> {{ date('F j, Y g:i A', strtotime($slipData['booking_time'])) }}
        </div>
        <div class="detail-row">
            <span class="label">Duration:</span> {{ $slipData['duration'] }}
        </div>
        <div class="detail-row">
            <span class="label">Price:</span> <strong>BDT {{ number_format($slipData['price'], 2) }}</strong>
        </div>
        <div class="detail-row">
            <span class="label">Slip Generated:</span> {{ $slipData['generated_at'] }}
        </div>
    </div>

    <!-- QR Code Placeholder (You can implement QR code later) -->
    <div class="qr-code-placeholder">
        <strong>Present this slip at our service center</strong>
    </div>
    
    <div class="instructions">
        <h3 style="margin-top:0; color: #2c5aa0;">Important Instructions:</h3>
        <p>{{ $slipData['instructions'] }}</p>
        <p><strong>Contact Info:</strong> {{ $slipData['contact_info'] ?? 'Call us at +880 XXXX-XXXXXX for any queries' }}</p>
        <p><strong>Location:</strong> Our Car Wash Service Center, Main Road, Dhaka</p>
    </div>
    
    <div class="footer">
        <p>Thank you for choosing our service! We look forward to serving you.</p>
        <p>&copy; Monark</p>
    </div>
</body>
</html>