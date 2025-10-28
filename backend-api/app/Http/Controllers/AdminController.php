<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Parking; 
use App\Models\Slot;
use App\Models\Booking;
use App\Models\WalletTransaction; 
use App\Models\Service; 
use App\Models\ServiceOrder; 
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Mail\TicketMail;
use App\Mail\TransactionApprovedMail;
use App\Mail\TransactionRejectedMail;
use App\Mail\BookingConfirmedMail;
use App\Mail\ServiceCompletedMail;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function createAdmin(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'admin',
            'email_verified_at' => now(), 
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Admin user created successfully',
            'user' => $user
        ], 201);
    }

    public function getUsers(Request $request)
    {
        try {
            $query = User::where('role', 'user');
            
            if ($request->has('q') && $request->q) {
                $search = $request->q;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }
            
            $query->withCount('bookings');
            
            $perPage = $request->per_page ?? 10;
            $users = $query->latest()->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'data' => $users
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function blockUser(User $user)
    {
        try {
            if ($user->role !== 'user') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only regular users can be blocked'
                ], 403);
            }

            $user->update(['is_blocked' => true]);
            
            return response()->json([
                'success' => true,
                'message' => 'User blocked successfully',
                'data' => $user
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to block user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function unblockUser(User $user)
    {
        try {
            $user->update(['is_blocked' => false]);
            
            return response()->json([
                'success' => true,
                'message' => 'User unblocked successfully',
                'data' => $user
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to unblock user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteUser(User $user)
    {
        try {
            if ($user->role !== 'user') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only regular users can be deleted'
                ], 403);
            }

            $user->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getParkings()
    {
        try {
            // Simple query without relationships first
            $parkings = Parking::all();

            return response()->json([
                'success' => true,
                'data' => $parkings
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error in getParkings: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch parkings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function addParking(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'price_per_hour' => 'required|numeric|min:0',
                'distance' => 'required|string|max:255',
                'total_slots' => 'required|integer|min:1',
                'image' => 'nullable|string'
            ]);

            $parking = Parking::create([
                'name' => $request->name,
                'description' => $request->description,
                'price_per_hour' => $request->price_per_hour,
                'distance' => $request->distance,
                'total_slots' => $request->total_slots,
                'available_slots' => $request->total_slots,
                'image' => $request->image
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Parking added successfully',
                'data' => $parking
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add parking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateParking(Request $request, Parking $parking)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'price_per_hour' => 'required|numeric|min:0',
                'distance' => 'required|string|max:255',
                'total_slots' => 'required|integer|min:1',
                'image' => 'nullable|string'
            ]);

            $parking->update([
                'name' => $request->name,
                'description' => $request->description,
                'price_per_hour' => $request->price_per_hour,
                'distance' => $request->distance,
                'total_slots' => $request->total_slots,
                'image' => $request->image
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Parking updated successfully',
                'data' => $parking
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update parking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteParking(Parking $parking)
    {
        try {
            // Delete associated slots first
            $parking->slots()->delete();
            
            // Then delete the parking
            $parking->delete();

            return response()->json([
                'success' => true,
                'message' => 'Parking deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete parking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getSlots()
    {
        try {
            $slots = Slot::with('parking')->latest()->get();

            return response()->json([
                'success' => true,
                'data' => $slots
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch slots',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function addSlot(Request $request)
    {
        try {
            $request->validate([
                'parking_id' => 'required|exists:parkings,id',
                'slot_code' => 'required|string|max:10|unique:slots,slot_code',
                'type' => 'required|in:Standard,Large',
                'available' => 'boolean'
            ]);

            $slot = Slot::create([
                'parking_id' => $request->parking_id,
                'slot_code' => $request->slot_code,
                'type' => $request->type,
                'available' => $request->available ?? true
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Slot added successfully',
                'data' => $slot
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add slot',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateSlot(Request $request, Slot $slot)
    {
        try {
            $request->validate([
                'parking_id' => 'required|exists:parkings,id',
                'slot_code' => 'required|string|max:10|unique:slots,slot_code,' . $slot->id,
                'type' => 'required|in:Standard,Large',
                'available' => 'boolean'
            ]);

            $slot->update([
                'parking_id' => $request->parking_id,
                'slot_code' => $request->slot_code,
                'type' => $request->type,
                'available' => $request->available
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Slot updated successfully',
                'data' => $slot
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update slot',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function toggleSlotAvailability(Request $request, Slot $slot)
    {
        try {
            $slot->update([
                'available' => $request->available
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Slot availability updated successfully',
                'data' => $slot
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update slot availability',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteSlot(Slot $slot)
    {
        try {
            // Check if slot has any active bookings
            if ($slot->bookings()->whereIn('status', ['pending', 'confirmed', 'active'])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete slot with active bookings'
                ], 400);
            }

            $slot->delete();

            return response()->json([
                'success' => true,
                'message' => 'Slot deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete slot',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function uploadImage(Request $request)
    {
        try {
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120' // 5MB max
            ]);

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                
                // Store in public storage
                $path = $image->storeAs('images/parkings', $imageName, 'public');
                
                /*$url = asset('storage/' . $path);*/

                return response()->json([
                    'success' => true,
                    'message' => 'Image uploaded successfully',
                    'url' => 'storage/' . $path,
                    'path' => $path
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No image file found'
            ], 400);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload image',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getWalletTransactions(Request $request)
    {
        try {
            $query = WalletTransaction::with('user');
            
            // Search functionality
            if ($request->has('q') && $request->q) {
                $search = $request->q;
                $query->where(function($q) use ($search) {
                    $q->where('transaction_id', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhereHas('user', function($userQuery) use ($search) {
                          $userQuery->where('name', 'like', "%{$search}%")
                                   ->orWhere('email', 'like', "%{$search}%");
                      });
                });
            }
            
            // Filter by type
            if ($request->has('type') && $request->type) {
                $query->where('type', $request->type);
            }
            
            // Filter by status
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }
            
            // Pagination
            $perPage = $request->per_page ?? 10;
            $transactions = $query->latest()->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'data' => $transactions
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch wallet transactions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function exportWalletTransactions(Request $request)
    {
        try {
            $query = WalletTransaction::with('user');
            
            // Apply filters same as get method
            if ($request->has('q') && $request->q) {
                $search = $request->q;
                $query->where(function($q) use ($search) {
                    $q->where('transaction_id', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhereHas('user', function($userQuery) use ($search) {
                          $userQuery->where('name', 'like', "%{$search}%")
                                   ->orWhere('email', 'like', "%{$search}%");
                      });
                });
            }
            
            if ($request->has('type') && $request->type) {
                $query->where('type', $request->type);
            }
            
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }
            
            $transactions = $query->latest()->get();
            
            $fileName = 'wallet-transactions-' . date('Y-m-d') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ];
            
            $callback = function() use ($transactions) {
                $file = fopen('php://output', 'w');
                
                // Add CSV headers
                fputcsv($file, [
                    'Transaction ID',
                    'User Name',
                    'User Email', 
                    'Type',
                    'Amount',
                    'Payment Method',
                    'Status',
                    'Description',
                    'Transaction ID',
                    'Created At'
                ]);
                
                // Add data rows
                foreach ($transactions as $transaction) {
                    fputcsv($file, [
                        $transaction->id,
                        $transaction->user->name ?? 'N/A',
                        $transaction->user->email ?? 'N/A',
                        ucfirst($transaction->type),
                        $transaction->amount,
                        $transaction->payment_method ? ucfirst($transaction->payment_method) : 'N/A',
                        ucfirst($transaction->status),
                        $transaction->description ?? 'N/A',
                        $transaction->transaction_id ?? 'N/A',
                        $transaction->created_at
                    ]);
                }
                
                fclose($file);
            };
            
            return response()->stream($callback, 200, $headers);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export transactions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getBookings(Request $request)
    {
        try {
            $query = Booking::with(['user', 'parking', 'slot']);
            
            // Search functionality
            if ($request->has('q') && $request->q) {
                $search = $request->q;
                $query->where(function($q) use ($search) {
                    $q->where('id', 'like', "%{$search}%")
                      ->orWhereHas('user', function($userQuery) use ($search) {
                          $userQuery->where('name', 'like', "%{$search}%")
                                   ->orWhere('email', 'like', "%{$search}%");
                      })
                      ->orWhereHas('parking', function($parkingQuery) use ($search) {
                          $parkingQuery->where('name', 'like', "%{$search}%");
                      })
                      ->orWhereHas('slot', function($slotQuery) use ($search) {
                          $slotQuery->where('slot_code', 'like', "%{$search}%");
                      });
                });
            }
            
            // Filter by status
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }
            
            // Pagination
            $perPage = $request->per_page ?? 15;
            $bookings = $query->latest()->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'data' => $bookings
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error in getBookings: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch bookings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get booking reports and analytics - UPDATED VERSION
     */
    public function getReports(Request $request)
    {
        try {
            $range = $request->get('range', 'monthly');
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');

            \Log::info('Reports API called', [
                'range' => $range,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);

            // Set default date range based on selected range
            if (!$startDate || !$endDate) {
                switch ($range) {
                    case 'daily':
                        $startDate = Carbon::today();
                        $endDate = Carbon::today();
                        break;
                    case 'monthly':
                        $startDate = Carbon::now()->startOfMonth();
                        $endDate = Carbon::now()->endOfMonth();
                        break;
                    case 'yearly':
                        $startDate = Carbon::now()->startOfYear();
                        $endDate = Carbon::now()->endOfYear();
                        break;
                    default:
                        $startDate = Carbon::now()->startOfMonth();
                        $endDate = Carbon::now()->endOfMonth();
                }
            } else {
                $startDate = Carbon::parse($startDate);
                $endDate = Carbon::parse($endDate);
            }

            \Log::info('Date range calculated', [
                'start' => $startDate,
                'end' => $endDate
            ]);

            // Total bookings
            $totalBookings = Booking::whereBetween('created_at', [$startDate, $endDate])->count();

            // Total revenue (only from completed bookings)
            $totalRevenue = Booking::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'completed')
                ->sum('total_price');

            // Average booking value
            $completedBookings = Booking::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'completed')
                ->count();
            
            $avgBookingValue = $completedBookings > 0 ? $totalRevenue / $completedBookings : 0;

            // Occupancy rate calculation
            $totalSlots = Slot::count();
            $bookedSlots = Booking::whereBetween('created_at', [$startDate, $endDate])
                ->whereIn('status', ['confirmed', 'completed'])
                ->count();
            $occupancyRate = $totalSlots > 0 ? round(($bookedSlots / $totalSlots) * 100, 2) : 0;

            // Revenue by parking
            $revenueByParking = Booking::join('parkings', 'bookings.parking_id', '=', 'parkings.id')
                ->whereBetween('bookings.created_at', [$startDate, $endDate])
                ->where('bookings.status', 'completed')
                ->select(
                    'parkings.name as parking_name',
                    DB::raw('COUNT(bookings.id) as bookings'),
                    DB::raw('SUM(bookings.total_price) as revenue')
                )
                ->groupBy('parkings.id', 'parkings.name')
                ->get();

            \Log::info('Revenue by parking calculated', [
                'count' => $revenueByParking->count()
            ]);

            // Booking status distribution
            $bookingStatus = Booking::whereBetween('created_at', [$startDate, $endDate])
                ->select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->get()
                ->map(function ($item) use ($totalBookings) {
                    $item->percentage = $totalBookings > 0 ? round(($item->count / $totalBookings) * 100, 2) : 0;
                    return $item;
                });

            // Booking trends
            $bookingTrends = $this->getBookingTrends($range, $startDate, $endDate);

            // Top users
            $topUsers = Booking::join('users', 'bookings.user_id', '=', 'users.id')
                ->whereBetween('bookings.created_at', [$startDate, $endDate])
                ->where('bookings.status', 'completed')
                ->select(
                    'users.name',
                    'users.email',
                    DB::raw('COUNT(bookings.id) as total_bookings'),
                    DB::raw('SUM(bookings.total_price) as total_spent')
                )
                ->groupBy('users.id', 'users.name', 'users.email')
                ->orderBy('total_spent', 'DESC')
                ->limit(10)
                ->get();

            \Log::info('Reports data generated successfully');

            return response()->json([
                'success' => true,
                'data' => [
                    'total_bookings' => $totalBookings,
                    'total_revenue' => number_format($totalRevenue, 2),
                    'avg_booking_value' => number_format($avgBookingValue, 2),
                    'occupancy_rate' => $occupancyRate,
                    'revenue_by_parking' => $revenueByParking,
                    'booking_status' => $bookingStatus,
                    'booking_trends' => $bookingTrends,
                    'top_users' => $topUsers,
                    'date_range' => [
                        'start' => $startDate->format('Y-m-d'),
                        'end' => $endDate->format('Y-m-d')
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in getReports: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate reports',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get booking trends based on time range
     */
    private function getBookingTrends($range, $startDate, $endDate)
    {
        $query = Booking::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed');

        switch ($range) {
            case 'daily':
                $trends = $query->select(
                    DB::raw('DATE(created_at) as period'),
                    DB::raw('COUNT(*) as bookings'),
                    DB::raw('SUM(total_price) as revenue'),
                    DB::raw('AVG(hours) as avg_hours')
                )
                ->groupBy('period')
                ->orderBy('period')
                ->get();
                break;

            case 'monthly':
                $trends = $query->select(
                    DB::raw('YEAR(created_at) as year'),
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw('CONCAT(YEAR(created_at), "-", MONTH(created_at)) as period'),
                    DB::raw('COUNT(*) as bookings'),
                    DB::raw('SUM(total_price) as revenue'),
                    DB::raw('AVG(hours) as avg_hours')
                )
                ->groupBy('year', 'month', 'period')
                ->orderBy('year')
                ->orderBy('month')
                ->get();
                break;

            case 'yearly':
                $trends = $query->select(
                    DB::raw('YEAR(created_at) as period'),
                    DB::raw('COUNT(*) as bookings'),
                    DB::raw('SUM(total_price) as revenue'),
                    DB::raw('AVG(hours) as avg_hours')
                )
                ->groupBy('period')
                ->orderBy('period')
                ->get();
                break;

            default:
                $trends = collect();
        }

        return $trends;
    }

    /**
     * Export reports
     */
    public function exportReports(Request $request)
    {
        try {
            $range = $request->get('range', 'monthly');
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');
            $format = $request->get('format', 'csv');

            // Get reports data
            $reportsResponse = $this->getReports($request);
            $reportsData = json_decode($reportsResponse->getContent(), true);

            if (!$reportsData['success']) {
                throw new \Exception('Failed to generate reports data');
            }

            $reports = $reportsData['data'];

            if ($format === 'csv') {
                return $this->exportReportsToCsv($reports);
            } else {
                return $this->exportReportsToPdf($reports);
            }

        } catch (\Exception $e) {
            \Log::error('Error in exportReports: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to export reports',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export reports to CSV
     */
    private function exportReportsToCsv($reports)
    {
        $fileName = 'parking-reports-' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        $callback = function() use ($reports) {
            $file = fopen('php://output', 'w');
            
            // Summary Section
            fputcsv($file, ['PARKING MANAGEMENT SYSTEM - REPORTS']);
            fputcsv($file, ['Date Range', $reports['date_range']['start'] . ' to ' . $reports['date_range']['end']]);
            fputcsv($file, []);
            fputcsv($file, ['SUMMARY']);
            fputcsv($file, ['Total Bookings', $reports['total_bookings']]);
            fputcsv($file, ['Total Revenue', '$' . $reports['total_revenue']]);
            fputcsv($file, ['Average Booking Value', '$' . $reports['avg_booking_value']]);
            fputcsv($file, ['Occupancy Rate', $reports['occupancy_rate'] . '%']);
            fputcsv($file, []);
            
            // Revenue by Parking
            fputcsv($file, ['REVENUE BY PARKING']);
            fputcsv($file, ['Parking', 'Bookings', 'Revenue']);
            foreach ($reports['revenue_by_parking'] as $parking) {
                fputcsv($file, [
                    $parking['parking_name'],
                    $parking['bookings'],
                    '$' . number_format($parking['revenue'], 2)
                ]);
            }
            fputcsv($file, []);
            
            // Booking Status
            fputcsv($file, ['BOOKING STATUS']);
            fputcsv($file, ['Status', 'Count', 'Percentage']);
            foreach ($reports['booking_status'] as $status) {
                fputcsv($file, [
                    ucfirst($status['status']),
                    $status['count'],
                    $status['percentage'] . '%'
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export reports to PDF (basic implementation)
     */
    private function exportReportsToPdf($reports)
    {
        // For PDF export, you might want to use a library like DomPDF
        // This is a basic implementation that returns CSV for now
        return $this->exportReportsToCsv($reports);
    }

    public function getDashboardStats()
    {
        try {
            $totalParkings = Parking::count();
            $totalSlots = Slot::count();
            $availableSlots = Slot::where('available', true)->count();
            
            $activeBookings = Booking::whereIn('status', ['confirmed', 'active'])->count();
            $completedBookings = Booking::where('status', 'completed')->count();
            $pendingBookings = Booking::where('status', 'pending')->count();
            $cancelledBookings = Booking::where('status', 'cancelled')->count();
            
            $totalUsers = User::where('role', 'user')->count(); // শুধু users count করছি
            
            // Today's revenue
            $todayRevenue = Booking::where('status', 'completed')
                ->whereDate('created_at', Carbon::today())
                ->sum('total_price');
            
            // Monthly revenue
            $monthlyRevenue = Booking::where('status', 'completed')
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->sum('total_price');
            
            // Recent bookings
            $recentBookings = Booking::with(['user', 'parking', 'slot'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->map(function($booking) {
                    return [
                        'id' => $booking->id,
                        'user' => [
                            'name' => $booking->user->name ?? 'N/A'
                        ],
                        'parking' => [
                            'name' => $booking->parking->name ?? 'N/A'
                        ],
                        'slot' => [
                            'slot_code' => $booking->slot->slot_code ?? 'N/A'
                        ],
                        'total_price' => $booking->total_price,
                        'status' => $booking->status,
                        'created_at' => $booking->created_at
                    ];
                });
            
            return response()->json([
                'success' => true,
                'data' => [
                    'totalParkings' => $totalParkings,
                    'totalSlots' => $totalSlots,
                    'availableSlots' => $availableSlots,
                    'activeBookings' => $activeBookings,
                    'totalUsers' => $totalUsers,
                    'todayRevenue' => (float) $todayRevenue,
                    'monthlyRevenue' => (float) $monthlyRevenue,
                    'completedBookings' => $completedBookings,
                    'pendingBookings' => $pendingBookings,
                    'cancelledBookings' => $cancelledBookings
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Dashboard stats error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // AdminController.php এ এই method যোগ করুন
    public function getTotalRevenue()
    {
        try {
            $totalRevenue = Booking::where('status', 'completed')->sum('total_price');
            
            return response()->json([
                'success' => true,
                'data' => [
                    'total_revenue' => (float) $totalRevenue
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch total revenue'
            ], 500);
        }
    }
    

    public function getPendingCheckouts(Request $request)
    {
        try {
            $query = Booking::with(['user', 'parking', 'slot'])
                ->whereIn('status', ['checkout_requested', 'checkout_approved', 'checkout_paid'])
                ->where(function ($q) {
                    // Include any booking that requested checkout but not fully approved
                    $q->where('checkout_requested', 1)
                      ->orWhere('status', 'checkout_paid');
                });

            // Optional search by booking ID, user, or parking
            if ($request->has('q') && $request->q) {
                $search = $request->q;
                $query->where(function ($q) use ($search) {
                    $q->where('id', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                      ->orWhere('email', 'like', "%{$search}%");
                        })
                        ->orWhereHas('parking', function ($parkingQuery) use ($search) {
                            $parkingQuery->where('name', 'like', "%{$search}%");
                        });
                });
            }

            $perPage = $request->per_page ?? 10;
            $checkouts = $query->latest()->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $checkouts
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getPendingCheckouts: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch pending checkouts'
            ], 500);
        }
    }


    /**
     * Approve checkout and generate ticket
     */
    public function approveCheckout(Request $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            try {
                \Log::info('Approve checkout started for booking: ' . $id);
                
                $booking = Booking::with(['user', 'parking', 'slot'])
                    ->where('id', $id)
                    ->whereIn('status', ['checkout_requested', 'checkout_paid'])
                    ->first();

                if (!$booking) {
                    \Log::error('Booking not found: ' . $id);
                    return response()->json([
                        'success' => false,
                        'message' => 'Checkout request not found or already processed'
                    ], 404);
                }

                \Log::info('Booking found: ', [$booking->toArray()]);

                // Update slot availability
                $slot = Slot::find($booking->slot_id);
                if ($slot) {
                    $slot->available = true;
                    $slot->save();
                    \Log::info('Slot updated: ' . $slot->id);
                }

                // Update parking available slots
                $parking = Parking::find($booking->parking_id);
                if ($parking) {
                    $parking->available_slots = $parking->available_slots;
                    $parking->save();
                    \Log::info('Parking updated: ' . $parking->id);
                }

                // Generate professional ticket number
                $ticketNumber = 'PKT' . date('ymd') . '-' . str_pad($booking->id, 5, '0', STR_PAD_LEFT);

                // Complete the booking
                $booking->update([
                    'status' => 'completed',
                    'checkout_approved' => true,
                    'ticket_generated' => true,
                    'actual_end_time' => now(),
                    'ticket_number' => $ticketNumber
                ]);

                \Log::info('Booking completed: ' . $booking->id);

                // Send email with PDF ticket (optional - if you have mail setup)
                try {
                    Mail::to($booking->user->email)->send(new TicketMail($booking, $ticketNumber));
                    \Log::info('Ticket email sent to: ' . $booking->user->email);
                } catch (\Exception $emailError) {
                    \Log::error('Email sending failed: ' . $emailError->getMessage());
                    // Continue even if email fails
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Checkout approved successfully',
                    'data' => [
                        'booking' => $booking,
                        'ticket_number' => $ticketNumber
                    ]
                ]);

            } catch (\Exception $e) {
                \Log::error('Error in approveCheckout: ' . $e->getMessage());
                \Log::error('Stack trace: ' . $e->getTraceAsString());
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to approve checkout: ' . $e->getMessage()
                ], 500);
            }
        });
    }

    public function rejectCheckout(Request $request, $id)
    {
        try {
            $booking = Booking::with(['user', 'parking', 'slot'])
                ->where('id', $id)
                ->whereIn('status', ['checkout_requested', 'checkout_paid'])
                ->first();

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Checkout request not found'
                ], 404);
            }

            // Update booking status to rejected
            $booking->update([
                'status' => 'rejected',
                'checkout_approved' => false,
                'actual_end_time' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Checkout rejected successfully',
                'data' => [
                    'booking' => $booking
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in rejectCheckout: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject checkout'
            ], 500);
        }
    }


    // AdminController.php-এ এই মেথডটি যোগ করুন
    public function getCheckoutStats()
    {
        try {
            \Log::info('getCheckoutStats called');
            
            // Simple counts first
            $pending_requests = Booking::where(function($query) {
                $query->where('status', 'checkout_requested')
                      ->orWhere('status', 'checkout_paid');
            })->count();

            $awaiting_payment = Booking::where('status', 'checkout_requested')
                ->where('extra_charges', '>', 0)
                ->count();

            $ready_for_approval = Booking::where('status', 'checkout_paid')->count();

            // Calculate revenue safely
            $total_revenue = Booking::where(function($query) {
                $query->where('status', 'completed');
            })->get()->sum(function($booking) {
                return ($booking->total_price ?? 0) + ($booking->extra_charges ?? 0);
            });

            \Log::info('Stats calculated successfully');

            return response()->json([
                'success' => true,
                'data' => [
                    'pending_requests' => $pending_requests,
                    'awaiting_payment' => $awaiting_payment,
                    'ready_for_approval' => $ready_for_approval,
                    'total_revenue' => (float) $total_revenue
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in getCheckoutStats: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch checkout stats'
            ], 500);
        }
    }

    // In AdminController.php


    public function getPendingTransactions(Request $request)
    {
        try {
            $status = $request->get('status', 'verified'); // Default verified
            
            $query = WalletTransaction::with('user')
                ->where('type', 'topup');

            // Status filter
            if ($status !== 'all') {
                $query->where('status', $status);
            }

            // Search functionality
            if ($request->has('q') && $request->q) {
                $search = $request->q;
                $query->where(function($q) use ($search) {
                    $q->where('generated_transaction_id', 'like', "%{$search}%")
                      ->orWhere('mobile_number', 'like', "%{$search}%")
                      ->orWhereHas('user', function($userQuery) use ($search) {
                          $userQuery->where('name', 'like', "%{$search}%")
                                   ->orWhere('email', 'like', "%{$search}%");
                      });
                });
            }

            $perPage = $request->per_page ?? 15;
            $transactions = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $transactions
            ]);

        } catch (\Exception $e) {
            \Log::error('Get pending transactions error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch transactions'
            ], 500);
        }
    }

    public function approveTransaction(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $transaction = WalletTransaction::with('user')
                ->where('id', $id)
                ->where('status', 'verified')
                ->first();

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction not found or not in verified state'
                ], 404);
            }

            // Update user wallet balance
            $user = User::find($transaction->user_id);
            $user->wallet_balance += $transaction->amount;
            $user->save();

            // Update transaction status
            $transaction->update([
                'status' => 'completed',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'transaction_id' => $request->transaction_id ?? $transaction->generated_transaction_id
            ]);

            // Send approval email
            try {
                Mail::to($user->email)->send(new TransactionApprovedMail($transaction, $user));
                \Log::info("Approval email sent to: {$user->email}");
            } catch (\Exception $emailException) {
                \Log::error("Failed to send approval email: " . $emailException->getMessage());
                // Email fail হলেও transaction continue করবে
            }

            DB::commit();

            \Log::info("Transaction approved: {$transaction->id}, User: {$user->id}, Amount: {$transaction->amount}");

            return response()->json([
                'success' => true,
                'message' => 'Transaction approved successfully',
                'data' => [
                    'transaction' => $transaction,
                    'new_balance' => $user->wallet_balance
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Approve transaction error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve transaction: ' . $e->getMessage()
            ], 500);
        }
    }

    public function rejectTransaction(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $transaction = WalletTransaction::with('user')
                ->where('id', $id)
                ->where('status', 'verified')
                ->first();

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction not found or not in verified state'
                ], 404);
            }

            $reason = $request->reason ?? 'No reason provided';
            
            $transaction->update([
                'status' => 'failed',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'description' => $transaction->description . ' (Rejected: ' . $reason . ')'
            ]);

            // Send rejection email
            try {
                Mail::to($transaction->user->email)->send(new TransactionRejectedMail($transaction, $reason));
                \Log::info("Rejection email sent to: {$transaction->user->email}");
            } catch (\Exception $emailException) {
                \Log::error("Failed to send rejection email: " . $emailException->getMessage());
                // Email fail হলেও transaction continue করবে
            }

            DB::commit();

            \Log::info("Transaction rejected: {$transaction->id}, Reason: {$reason}");

            return response()->json([
                'success' => true,
                'message' => 'Transaction rejected successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Reject transaction error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject transaction: ' . $e->getMessage()
            ], 500);
        }
    }

    // AdminController.php - এই methods গুলো যোগ করুন

/**
 * Get all service orders for admin
 */
public function getServiceOrders(Request $request)
{
    try {
        $query = ServiceOrder::with(['user', 'service']);

        // Search functionality
        if ($request->has('q') && $request->q) {
            $search = $request->q;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('service', function($serviceQuery) use ($search) {
                      $serviceQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Pagination
        $perPage = $request->per_page ?? 15;
        $orders = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);

    } catch (\Exception $e) {
        \Log::error('Error in getServiceOrders: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch service orders',
            'error' => $e->getMessage()
        ], 500);
    }
}

/**
 * Update service order status
 */
public function updateServiceOrderStatus(Request $request, $id)
{
    try {
        $request->validate([
            'status' => 'required|in:pending,in_progress,completed,cancelled'
        ]);

        $order = ServiceOrder::findOrFail($id);
        $oldStatus = $order->status;
        $order->update(['status' => $request->status]);

        \Log::info("Service order status updated", [
            'order_id' => $id,
            'old_status' => $oldStatus,
            'new_status' => $request->status,
            'updated_by' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Service order status updated successfully',
            'data' => $order
        ]);

    } catch (\Exception $e) {
        \Log::error('Error in updateServiceOrderStatus: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to update service order status',
            'error' => $e->getMessage()
        ], 500);
    }
}

/**
 * Get service order statistics for dashboard
 */
public function getServiceOrderStats()
{
    try {
        $totalOrders = ServiceOrder::count();
        $pendingOrders = ServiceOrder::where('status', 'pending')->count();
        $inProgressOrders = ServiceOrder::where('status', 'in_progress')->count();
        $completedOrders = ServiceOrder::where('status', 'completed')->count();
        $cancelledOrders = ServiceOrder::where('status', 'cancelled')->count();

        // Today's orders
        $todayOrders = ServiceOrder::whereDate('created_at', today())->count();

        // Revenue from completed orders
        $revenue = ServiceOrder::where('status', 'completed')
            ->with('service')
            ->get()
            ->sum(function($order) {
                return $order->service ? $order->service->price : 0;
            });

        return response()->json([
            'success' => true,
            'data' => [
                'total_orders' => $totalOrders,
                'pending_orders' => $pendingOrders,
                'in_progress_orders' => $inProgressOrders,
                'completed_orders' => $completedOrders,
                'cancelled_orders' => $cancelledOrders,
                'today_orders' => $todayOrders,
                'revenue' => (float) $revenue
            ]
        ]);

    } catch (\Exception $e) {
        \Log::error('Error in getServiceOrderStats: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch service order statistics'
        ], 500);
    }
}

    public function confirmBooking(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $order = ServiceOrder::with(['user', 'service'])->findOrFail($id);
            
            if ($order->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending orders can be confirmed'
                ], 400);
            }

            // Generate booking slip first
            $slipData = $this->generateBookingSlip($order);

            // Update status + slip_number to database
            $order->update([
                'status' => 'confirmed',
                'slip_number' => $slipData['slip_number']
            ]);

            // Send confirmation email
            Mail::to($order->user->email)->send(new BookingConfirmedMail($order, $slipData));

            // Schedule auto status updates
            $this->scheduleStatusUpdates($order);

            DB::commit();

            Log::info("Booking confirmed", [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'confirmed_by' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Booking confirmed successfully',
                'data' => [
                    'order' => $order,
                    'slip' => $slipData
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Confirm booking error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm booking'
            ], 500);
        }
    }

    /**
     * Generate booking slip
     */
    
private function generateBookingSlip($order)
{
    $slipNumber = 'SLP-' . date('Ymd') . '-' . str_pad($order->id, 5, '0', STR_PAD_LEFT);
    
    return [
        'slip_number' => $slipNumber,
        'order_id' => $order->id,
        'customer_name' => $order->user->name,
        'customer_email' => $order->user->email,
        'service_name' => $order->service->name,
        'service_description' => $order->service->description,
        'booking_time' => $order->booking_time,
        'duration' => $order->service->duration,
        'price' => $order->service->price,
        'generated_at' => now()->toDateTimeString(),
        'instructions' => 'Please arrive 10 minutes before your booking time. Bring your vehicle and this slip.',
        'contact_info' => 'Contact: +880 XXXX-XXXXXX | Email: info@carwash.com'
    ];
}

    /**
     * Schedule automatic status updates
     */
    private function scheduleStatusUpdates($order)
    {
        $bookingTime = Carbon::parse($order->booking_time);
        $serviceDuration = $this->parseDuration($order->service->duration);
        
        // Schedule in_progress status at booking time
        $inProgressTime = $bookingTime;
        
        // Schedule completed status after service duration
        $completedTime = $bookingTime->addMinutes($serviceDuration);

        // In a real application, you would use Laravel Scheduler or Jobs
        // For now, we'll handle this in a separate method that can be called by cron
        Log::info("Status updates scheduled", [
            'order_id' => $order->id,
            'in_progress_at' => $inProgressTime,
            'completed_at' => $completedTime
        ]);

        // Store these timestamps for later use
        $order->update([
            'scheduled_in_progress_at' => $inProgressTime,
            'scheduled_completed_at' => $completedTime
        ]);
    }

    /**
     * Parse duration string to minutes
     */
    private function parseDuration($duration)
    {
        // Convert "30 min", "60 min", "120 min" to minutes
        preg_match('/(\d+)\s*min/', $duration, $matches);
        return isset($matches[1]) ? (int)$matches[1] : 30; // default 30 minutes
    }

    /**
     * Auto update statuses (to be called by cron job)
     */
    public function autoUpdateStatuses()
    {
        try {
            $now = now();
            
            // Update to in_progress
            $inProgressOrders = ServiceOrder::where('status', 'confirmed')
                ->where('scheduled_in_progress_at', '<=', $now)
                ->get();

            foreach ($inProgressOrders as $order) {
                $order->update(['status' => 'in_progress']);
                Log::info("Auto updated to in_progress", ['order_id' => $order->id]);
            }

            // Update to completed and generate invoices
            $completedOrders = ServiceOrder::where('status', 'in_progress')
                ->where('scheduled_completed_at', '<=', $now)
                ->get();

            foreach ($completedOrders as $order) {
                $order->update(['status' => 'completed']);
                $this->generateInvoice($order);
                
                // Send completion email
                Mail::to($order->user->email)->send(new ServiceCompletedMail($order));
                
                Log::info("Auto updated to completed", ['order_id' => $order->id]);
            }

            return response()->json([
                'success' => true,
                'updated_in_progress' => $inProgressOrders->count(),
                'updated_completed' => $completedOrders->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Auto update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Auto update failed'
            ], 500);
        }
    }

    /**
     * Generate invoice
     */
    private function generateInvoice($order)
    {
        $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad($order->id, 5, '0', STR_PAD_LEFT);
        
        $invoiceData = [
            'invoice_number' => $invoiceNumber,
            'order_id' => $order->id,
            'customer_name' => $order->user->name,
            'customer_email' => $order->user->email,
            'service_name' => $order->service->name,
            'service_price' => $order->service->price,
            'booking_time' => $order->booking_time,
            'completed_at' => now()->toDateTimeString(),
            'duration' => $order->service->duration
        ];

        // Store invoice in database or generate PDF
        $order->update([
            'invoice_number' => $invoiceNumber,
            'invoice_generated_at' => now(),
            'invoice_data' => json_encode($invoiceData)
        ]);

        return $invoiceData;
    }

    /**
     * Manual status update (for admin)
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:pending,confirmed,in_progress,completed,cancelled'
            ]);

            $order = ServiceOrder::findOrFail($id);
            $oldStatus = $order->status;
            $order->update(['status' => $request->status]);

            // If confirming, send email and generate slip
            if ($request->status === 'confirmed' && $oldStatus === 'pending') {
                $slipData = $this->generateBookingSlip($order);
                Mail::to($order->user->email)->send(new BookingConfirmedMail($order, $slipData));
                $this->scheduleStatusUpdates($order);
            }

            // If completing, generate invoice
            if ($request->status === 'completed' && $oldStatus === 'in_progress') {
                $this->generateInvoice($order);
                Mail::to($order->user->email)->send(new ServiceCompletedMail($order));
            }

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully',
                'data' => $order
            ]);

        } catch (\Exception $e) {
            Log::error('Update status error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order status'
            ], 500);
        }
    }

    public function downloadServiceSlip($id)
    {
        try {
            \Log::info('Download service slip called for order: ' . $id);
            
            $order = ServiceOrder::with(['user', 'service'])->find($id);
            
            if (!$order) {
                \Log::error('Order not found: ' . $id);
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            // Generate slip number if not exists
            if (!$order->slip_number) {
                $order->slip_number = 'SLP-' . date('Ymd') . '-' . str_pad($order->id, 5, '0', STR_PAD_LEFT);
                $order->save();
            }

            $slipData = [
                'slip_number' => $order->slip_number,
                'order_id' => $order->id,
                'customer_name' => $order->user->name ?? 'N/A',
                'customer_email' => $order->user->email ?? 'N/A', 
                'service_name' => $order->service->name ?? 'N/A',
                'service_description' => $order->service->description ?? '',
                'booking_time' => $order->booking_time,
                'duration' => $order->service->duration ?? '',
                'price' => $order->service->price ?? 0,
                'status' => $order->status,
                'generated_at' => now()->format('Y-m-d H:i:s'),
                'instructions' => 'Please arrive 10 minutes before your booking time.',
                'contact_info' => 'Contact: +8801900000000'
            ];

            \Log::info('Slip data prepared', $slipData);

            // Check if view exists
            if (!view()->exists('pdf.service-slip')) {
                \Log::error('PDF view not found: pdf.service-slip');
                return response()->json([
                    'success' => false,
                    'message' => 'PDF template not found'
                ], 500);
            }

            $pdf = PDF::loadView('pdf.service-slip', compact('slipData'));
            
            \Log::info('PDF generated successfully');
            
            return $pdf->download("booking-slip-{$order->slip_number}.pdf");

        } catch (\Exception $e) {
            \Log::error('Download service slip error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to download service slip: ' . $e->getMessage()
            ], 500);
        }
    }
 
    public function downloadServiceInvoice($id)
    {
        try {
            \Log::info('Download service invoice called for order: ' . $id);
            
            $order = ServiceOrder::with(['user', 'service'])->find($id);
            
            if (!$order) {
                \Log::error('Order not found: ' . $id);
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            \Log::info('Order found:', [
                'order_id' => $order->id,
                'status' => $order->status,
                'has_service' => !is_null($order->service)
            ]);

            // Generate invoice number if not exists
            if (!$order->invoice_number) {
                $order->invoice_number = 'INV-' . date('Ymd') . '-' . str_pad($order->id, 5, '0', STR_PAD_LEFT);
                $order->invoice_generated_at = now();
                $order->save();
                \Log::info('Generated new invoice number: ' . $order->invoice_number);
            }

            $invoiceData = [
                'invoice_number' => $order->invoice_number,
                'order_id' => $order->id,
                'customer_name' => $order->user->name ?? 'N/A',
                'customer_email' => $order->user->email ?? 'N/A',
                'service_name' => $order->service->name ?? 'N/A',
                'service_description' => $order->service->description ?? '',
                'service_price' => $order->service->price ?? 0,
                'booking_time' => $order->booking_time,
                'completed_at' => $order->updated_at->format('Y-m-d H:i:s'),
                'duration' => $order->service->duration ?? '',
                'generated_at' => now()->format('Y-m-d H:i:s')
            ];

            \Log::info('Invoice data prepared', $invoiceData);

            // Check if view exists
            if (!view()->exists('pdf.service-invoice')) {
                \Log::error('PDF view not found: pdf.service-invoice');
                return response()->json([
                    'success' => false,
                    'message' => 'PDF template not found'
                ], 500);
            }

            // Generate PDF
            $pdf = PDF::loadView('pdf.service-invoice', compact('invoiceData'));
            
            \Log::info('PDF generated successfully for invoice: ' . $order->invoice_number);
            
            return $pdf->download("invoice-{$order->invoice_number}.pdf");

        } catch (\Exception $e) {
            \Log::error('Download service invoice error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to download service invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    // Service Management Methods
public function getServices(Request $request)
{
    try {
        $query = Service::query();
        
        // Search functionality
        if ($request->has('q') && $request->q) {
            $search = $request->q;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        $perPage = $request->per_page ?? 10;
        $services = $query->latest()->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'data' => $services
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Error in getServices: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch services',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function addService(Request $request)
{
    try {
        $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|string|max:50',
            'image' => 'nullable|string',
            'status' => 'required|in:active,inactive'
        ]);

        $service = Service::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'duration' => $request->duration,
            'image' => $request->image,
            'status' => $request->status
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Service added successfully',
            'data' => $service
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Error in addService: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to add service',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function updateService(Request $request, Service $service)
{
    try {
        $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|string|max:50',
            'image' => 'nullable|string',
            'status' => 'required|in:active,inactive'
        ]);

        $service->update([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'duration' => $request->duration,
            'image' => $request->image,
            'status' => $request->status
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Service updated successfully',
            'data' => $service
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Error in updateService: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to update service',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function deleteService(Service $service)
{
    try {
        \Log::info('Attempting to delete service', ['service_id' => $service->id]);
        
        // FIX: Change orders() to serviceOrders()
        $activeOrders = $service->serviceOrders()->whereIn('status', ['pending', 'confirmed', 'in_progress'])->count();
        \Log::info('Active orders count', ['count' => $activeOrders]);

        if ($activeOrders > 0) {
            \Log::warning('Cannot delete service with active orders', ['service_id' => $service->id]);
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete service with active orders'
            ], 400);
        }

        $service->delete();
        \Log::info('Service deleted successfully', ['service_id' => $service->id]);

        return response()->json([
            'success' => true,
            'message' => 'Service deleted successfully'
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Error in deleteService: ' . $e->getMessage());
        \Log::error('Stack trace: ' . $e->getTraceAsString());
        return response()->json([
            'success' => false,
            'message' => 'Failed to delete service: ' . $e->getMessage(),
            'error' => $e->getMessage()
        ], 500);
    }
}
public function toggleServiceStatus(Service $service)
{
    try {
        $service->update([
            'status' => $service->status === 'active' ? 'inactive' : 'active'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Service status updated successfully',
            'data' => $service
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Error in toggleServiceStatus: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to update service status',
            'error' => $e->getMessage()
        ], 500);
    }
}
    public function uploadServiceImage(Request $request)
    {
        try {
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120'
            ]);

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                
                $path = $image->storeAs('images/services', $imageName, 'public');
                
                // পার্কিং এর মতোই URL return করুন
                /*$url = asset('storage/' . $path);*/

                return response()->json([
                    'success' => true,
                    'message' => 'Service image uploaded successfully',
                    'url' => 'storage/' . $path,
                    'path' => $path
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No image file found'
            ], 400);
            
        } catch (\Exception $e) {
            \Log::error('Upload service image error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload service image: ' . $e->getMessage()
            ], 500);
        }
    }
}
