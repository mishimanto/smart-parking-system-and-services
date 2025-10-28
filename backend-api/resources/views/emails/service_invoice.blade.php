<!DOCTYPE html>
<html>
<head>
    <title>Invoice - {{ $invoiceData['invoice_number'] }}</title>
    <style>
        body { 
            font-family: 'DejaVu Sans', Arial, sans-serif; 
            margin: 20px; 
            line-height: 1.4;
            font-size: 12px;
        }
        .header { 
            text-align: center; 
            border-bottom: 2px solid #333; 
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .invoice-number { 
            font-size: 16px; 
            font-weight: bold; 
            color: #2c5aa0; 
            margin-top: 10px;
        }
        .details { 
            margin: 20px 0; 
        }
        .detail-row { 
            margin: 8px 0; 
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
        .label { 
            font-weight: bold; 
            color: #555;
            width: 120px;
            display: inline-block;
        }
        .summary {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            color: #666;
            font-size: 10px;
        }
        .thank-you {
            text-align: center;
            margin: 15px 0;
            padding: 10px;
            background: #e9ecef;
            border-radius: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin:0; color: #2c5aa0; font-size: 20px;">MONARK</h1>
        <p style="margin:5px 0; color: #666;">Professional Car Care Solutions</p>
    </div>
    
    <div class="details">
        <h3 style="color: #2c5aa0; border-bottom: 1px solid #ddd; padding-bottom: 5px; font-size: 14px;">Invoice Details</h3>
        
        <div class="detail-row">
            <span class="label">Invoice Number:</span> {{ $invoiceData['invoice_number'] }}
        </div>
        <!-- <div class="detail-row">
            <span class="label">Order ID:</span> #{{ $invoiceData['order_id'] }}
        </div> -->
        <div class="detail-row">
            <span class="label">Customer Name:</span> {{ $invoiceData['customer_name'] }}
        </div>
        <div class="detail-row">
            <span class="label">Email:</span> {{ $invoiceData['customer_email'] }}
        </div>
        <div class="detail-row">
            <span class="label">Service:</span> {{ $invoiceData['service_name'] }}
        </div>
        <div class="detail-row">
            <span class="label">Description:</span> {{ $invoiceData['service_description'] }}
        </div>
        <div class="detail-row">
            <span class="label">Booking Time:</span> {{ date('F j, Y g:i A', strtotime($invoiceData['booking_time'])) }}
        </div>
        <div class="detail-row">
            <span class="label">Completed At:</span> {{ date('F j, Y g:i A', strtotime($invoiceData['completed_at'])) }}
        </div>
        <div class="detail-row">
            <span class="label">Duration:</span> {{ $invoiceData['duration'] }}
        </div>
        <div class="detail-row">
            <span class="label">Mechanic:</span> {{ $invoiceData['mechanic_name'] }}
        </div>
    </div>

    <div class="summary">
        <h3 style="color: #2c5aa0; margin-top: 0; font-size: 14px;">Payment Summary</h3>
        
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $invoiceData['service_name'] }}</td>
                    <td>BDT {{ number_format($invoiceData['service_price'], 2) }}</td>
                </tr>
                <!-- <tr>
                    <td>Tax (0%)</td>
                    <td>BDT 0.00</td>
                </tr> -->
                <tr style="font-weight: bold; background-color: #f8f9fa;">
                    <td>Total Amount</td>
                    <td>BDT {{ number_format($invoiceData['service_price'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="footer">        
        <p>Email: info@carwash.com | Website: www.carwash.com | Phone: +880 XXXX-XXXXXX</p>
        <p>&copy; MONARK</p>
    </div>
</body>
</html>