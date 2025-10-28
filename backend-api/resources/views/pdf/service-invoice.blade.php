<!DOCTYPE html>
<html>
<head>
    <title>Invoice - {{ $invoiceData['invoice_number'] ?? 'N/A' }}</title>
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
        <div class="invoice-number">{{ $invoiceData['invoice_number'] ?? 'N/A' }}</div>
    </div>
    
    <div class="details">
        <h3 style="color: #2c5aa0; border-bottom: 1px solid #ddd; padding-bottom: 5px; font-size: 14px;">Invoice Details</h3>
        
        <div class="detail-row">
            <span class="label">Invoice Number:</span> {{ $invoiceData['invoice_number'] ?? 'N/A' }}
        </div>
        <div class="detail-row">
            <span class="label">Order ID:</span> #{{ $invoiceData['order_id'] ?? 'N/A' }}
        </div>
        <div class="detail-row">
            <span class="label">Customer Name:</span> {{ $invoiceData['customer_name'] ?? 'N/A' }}
        </div>
        <div class="detail-row">
            <span class="label">Email:</span> {{ $invoiceData['customer_email'] ?? 'N/A' }}
        </div>
        <div class="detail-row">
            <span class="label">Service:</span> {{ $invoiceData['service_name'] ?? 'N/A' }}
        </div>
        <div class="detail-row">
            <span class="label">Description:</span> {{ $invoiceData['service_description'] ?? 'N/A' }}
        </div>
        <div class="detail-row">
            <span class="label">Booking Time:</span> 
            @if(isset($invoiceData['booking_time']))
                {{ date('F j, Y g:i A', strtotime($invoiceData['booking_time'])) }}
            @else
                N/A
            @endif
        </div>
        <div class="detail-row">
            <span class="label">Completed At:</span> 
            @if(isset($invoiceData['completed_at']))
                {{ date('F j, Y g:i A', strtotime($invoiceData['completed_at'])) }}
            @else
                {{ date('F j, Y g:i A') }}
            @endif
        </div>
        <div class="detail-row">
            <span class="label">Duration:</span> {{ $invoiceData['duration'] ?? 'N/A' }}
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
                    <td>{{ $invoiceData['service_name'] ?? 'Service' }}</td>
                    <td>BDT {{ isset($invoiceData['service_price']) ? number_format($invoiceData['service_price'], 2) : '0.00' }}</td>
                </tr>
                <tr style="font-weight: bold; background-color: #f8f9fa;">
                    <td>Total Amount</td>
                    <td>BDT {{ isset($invoiceData['service_price']) ? number_format($invoiceData['service_price'], 2) : '0.00' }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="footer">        
        <p>Email: info@carwash.com | Phone: +880 XXXX-XXXXXX</p>
        <p>&copy; {{ date('Y') }} MONARK. All rights reserved.</p>
    </div>
</body>
</html>