<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ServiceCenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AdminCenterController extends Controller
{
    /**
     * Display a listing of the service centers.
     */
    public function index(Request $request)
    {
        try {
            $query = ServiceCenter::query();
            
            // Search functionality
            if ($request->has('search') && $request->search != '') {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('address', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }
            
            // Filter by status
            if ($request->has('status') && $request->status != '') {
                $query->where('is_active', $request->status);
            }
            
            // Order by latest first
            $serviceCenters = $query->latest()->paginate(10);
            
            return response()->json([
                'success' => true,
                'data' => $serviceCenters,
                'message' => 'Service centers retrieved successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve service centers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created service center.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'address' => 'required|string',
                'phone' => 'required|string|max:50',
                'email' => 'nullable|email|max:100',
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'opening_hours' => 'nullable|string|max:100',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();
            
            // Handle image upload
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('service-centers', 'public');
                $data['image'] = 'storage/' . $imagePath;
            }

            $serviceCenter = ServiceCenter::create($data);

            return response()->json([
                'success' => true,
                'data' => $serviceCenter,
                'message' => 'Service center created successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create service center: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified service center.
     */
    public function show($id)
    {
        try {
            $serviceCenter = ServiceCenter::find($id);
            
            if (!$serviceCenter) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service center not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $serviceCenter,
                'message' => 'Service center retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve service center: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified service center.
     */
    public function update(Request $request, $id)
    {
        try {
            $serviceCenter = ServiceCenter::find($id);
            
            if (!$serviceCenter) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service center not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'address' => 'sometimes|required|string',
                'phone' => 'sometimes|required|string|max:50',
                'email' => 'nullable|email|max:100',
                'latitude' => 'sometimes|required|numeric|between:-90,90',
                'longitude' => 'sometimes|required|numeric|between:-180,180',
                'opening_hours' => 'nullable|string|max:100',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();
            
            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($serviceCenter->image && Storage::disk('public')->exists(str_replace('storage/', '', $serviceCenter->image))) {
                    Storage::disk('public')->delete(str_replace('storage/', '', $serviceCenter->image));
                }
                
                $imagePath = $request->file('image')->store('service-centers', 'public');
                $data['image'] = 'storage/' . $imagePath;
            }

            $serviceCenter->update($data);

            return response()->json([
                'success' => true,
                'data' => $serviceCenter,
                'message' => 'Service center updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update service center: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified service center.
     */
    public function destroy($id)
    {
        try {
            $serviceCenter = ServiceCenter::find($id);
            
            if (!$serviceCenter) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service center not found'
                ], 404);
            }

            // Delete associated image
            if ($serviceCenter->image && Storage::disk('public')->exists(str_replace('storage/', '', $serviceCenter->image))) {
                Storage::disk('public')->delete(str_replace('storage/', '', $serviceCenter->image));
            }

            $serviceCenter->delete();

            return response()->json([
                'success' => true,
                'message' => 'Service center deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete service center: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle service center status.
     */
    public function toggleStatus($id)
    {
        try {
            $serviceCenter = ServiceCenter::find($id);
            
            if (!$serviceCenter) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service center not found'
                ], 404);
            }

            $serviceCenter->update([
                'is_active' => !$serviceCenter->is_active
            ]);

            return response()->json([
                'success' => true,
                'data' => $serviceCenter,
                'message' => 'Service center status updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update service center status: ' . $e->getMessage()
            ], 500);
        }
    }
}