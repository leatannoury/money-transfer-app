<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AgentController extends Controller
{
    public function markNotificationsRead()
    {
        /** @var \App\Models\User $agent */
        $agent = Auth::user();

        $agent->agentNotifications()
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['message' => 'Notifications marked as read.']);
    }

    public function clearNotifications()
    {
        /** @var \App\Models\User $agent */
        $agent = Auth::user();

        $agent->agentNotifications()->delete();

        return response()->json([
            'message' => 'Notifications cleared.',
            'remaining' => 0,
        ]);
    }

    /**
     * Display the agent's dashboard (own profile only)
     */
    public function dashboard()
    {
        /** @var \App\Models\User $agent */
        $agent = Auth::user();

        // ✅ Fetch latest notifications for this agent
        $notifications = $agent->agentNotifications()
        ->where('is_read', false)
        ->latest()
        ->take(5)
        ->get();

        // ✅ Count unread notifications
        $unreadCount = $agent->agentNotifications()
            ->where('is_read', false)
            ->count();

        return view('agent.dashboard', compact('agent', 'notifications', 'unreadCount'));
    }

    /**
     * Update the agent's profile (phone, city, commission)
     * + Automatically detect latitude and longitude from city name
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'phone'           => 'nullable|string|max:20',
            'city'            => 'nullable|string|max:255',
            'commission'      => 'nullable|numeric|min:0|max:100',
            'work_start_time' => 'nullable|date_format:H:i',
            'work_end_time'   => 'nullable|date_format:H:i',
            'is_available'    => 'nullable|boolean',
        ], [
            'commission.max'           => 'Commission cannot exceed 100%.',
            'work_start_time.date_format' => 'Work start time must be in HH:MM format.',
            'work_end_time.date_format'   => 'Work end time must be in HH:MM format.',
        ]);

        /** @var \App\Models\User $agent */
        $agent = Auth::user();

        // ---- Basic profile fields ----
        if ($request->filled('phone')) {
            $agent->phone = $request->phone;
        }

        if ($request->filled('city')) {
            $agent->city = $request->city;
        } elseif ($request->has('city') && $request->city === '') {
            // allow clearing city
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

        // Availability toggle
        if ($request->has('is_available')) {
            $agent->is_available = $request->boolean('is_available');
        }

        // ---- Auto Fetch Latitude & Longitude (ANY Lebanon city/village) ----
        if ($request->filled('city')) {
            $cityQuery = trim($request->city);

            try {
                $response = Http::withHeaders([
                        // Nominatim requires a real user-agent
                        'User-Agent' => 'Transferly/1.0 (contact@yourapp.com)',
                        'Accept-Language' => 'en',
                    ])
                    ->timeout(10)
                    ->get('https://nominatim.openstreetmap.org/search', [
                        'q'              => $cityQuery . ', Lebanon',
                        'format'         => 'json',
                        'limit'          => 1,
                        'addressdetails' => 0,
                    ]);

                if ($response->successful()) {
                    $data = $response->json();

                    if (!empty($data) && isset($data[0]['lat'], $data[0]['lon'])) {
                        $agent->latitude  = $data[0]['lat'];
                        $agent->longitude = $data[0]['lon'];
                    } else {
                        Log::warning("Geocoding: no results for '{$cityQuery}'", [
                            'response' => $data,
                        ]);
                        // keep existing coordinates if any
                    }
                } else {
                    Log::error('Geocoding HTTP error', [
                        'status' => $response->status(),
                        'body'   => $response->body(),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('Geocoding failed: ' . $e->getMessage());
                // keep existing coordinates on failure
            }

        } elseif ($request->has('city') && $request->city === '') {
            // City cleared: also clear coordinates if you want
            // comment these lines if you prefer to keep old coords
            $agent->latitude  = null;
            $agent->longitude = null;
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
            'latitude'  => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        /** @var \App\Models\User $agent */
        $agent = Auth::user();

        $agent->latitude  = $request->latitude;
        $agent->longitude = $request->longitude;
        $agent->save();

        return back()->with('success', 'Location updated successfully!');
    }
    public function editProfilePage()
{
    $agent = Auth::user();
    return view('agent.edit-profile', compact('agent'));
}

}
