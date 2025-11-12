<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AgentController extends Controller
{
    /**
     * Display the agent's dashboard (own profile only)
     */
    public function dashboard()
    {
        /** @var \App\Models\User $agent */
        $agent = Auth::user();

        return view('agent.dashboard', compact('agent'));
    }

    /**
     * Update the agent's profile (phone, city, commission)
     * + Automatically detect latitude and longitude from city name
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'phone' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:255',
            'commission' => 'nullable|numeric|min:0|max:100',
            'work_start_time' => 'nullable|date_format:H:i',
            'work_end_time' => 'nullable|date_format:H:i',
            'is_available' => 'nullable|boolean',
        ], [
            'commission.max' => 'Commission cannot exceed 100%.',
            'work_start_time.date_format' => 'Work start time must be in HH:MM format.',
            'work_end_time.date_format' => 'Work end time must be in HH:MM format.',
        ]);

        /** @var \App\Models\User $agent */
        $agent = Auth::user();

        // Update profile fields only if they are provided
        if ($request->filled('phone')) {
        $agent->phone = $request->phone;
        }
        
        if ($request->filled('city')) {
        $agent->city = $request->city;
        } elseif ($request->has('city') && $request->city === '') {
            // Allow clearing the city field
            $agent->city = null;
        }
        
        if ($request->filled('commission')) {
        $agent->commission = $request->commission;
        }
        
        if ($request->filled('work_start_time')) {
        $agent->work_start_time = $request->work_start_time;
        }
        
        if ($request->filled('work_end_time')) {
        $agent->work_end_time = $request->work_end_time;
        }
        
        // Ensure status is set
        if (!$agent->status) {
            $agent->status = 'active';
        }
        
        // Handle availability toggle if provided
        if ($request->has('is_available')) {
            $agent->is_available = $request->boolean('is_available');
        }

        // --- Auto Fetch Latitude & Longitude based on City ---
        if ($request->filled('city')) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'LaravelApp/1.0 (contact@example.com)',
                ])->timeout(5)->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $request->city . ', Lebanon',
                    'format' => 'json',
                    'limit' => 1,
                ]);

                $data = $response->json();

                if (!empty($data) && !empty($data[0])) {
                    $agent->latitude = $data[0]['lat'];
                    $agent->longitude = $data[0]['lon'];
                } else {
                    Log::warning('City not found in geocoding: ' . $request->city);
                    // Don't clear existing coordinates if geocoding fails
                }
            } catch (\Exception $e) {
                Log::error('Geocoding failed: ' . $e->getMessage());
                // Don't clear existing coordinates if geocoding fails
            }
        } elseif ($request->has('city') && $request->city === '') {
            // If city is cleared, optionally clear coordinates too
            // $agent->latitude = null;
            // $agent->longitude = null;
        }

        $agent->save();

        return back()->with('success', 'Profile updated successfully!');
    }

    /**
     * Update the agent's location manually (latitude & longitude)
     */
    public function saveLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        /** @var \App\Models\User $agent */
        $agent = Auth::user();

        $agent->latitude = $request->latitude;
        $agent->longitude = $request->longitude;
        $agent->save();

        return back()->with('success', 'Location updated successfully!');
    }
}
