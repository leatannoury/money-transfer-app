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
            'commission' => 'nullable|numeric|min:0',
        ]);

        /** @var \App\Models\User $agent */
        $agent = Auth::user();

        // Update profile fields
        $agent->phone = $request->phone;
        $agent->city = $request->city;
        $agent->commission = $request->commission;
        $agent->status = $agent->status ?? 'active';

        // --- Auto Fetch Latitude & Longitude based on City ---
        if (!empty($request->city)) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'LaravelApp/1.0 (contact@example.com)',
                ])->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $request->city . ', Lebanon',
                    'format' => 'json',
                    'limit' => 1,
                ]);

                $data = $response->json();

                if (!empty($data[0])) {
                    $agent->latitude = $data[0]['lat'];
                    $agent->longitude = $data[0]['lon'];
                } else {
                    Log::warning('City not found in geocoding: ' . $request->city);
                }
            } catch (\Exception $e) {
                Log::error('Geocoding failed: ' . $e->getMessage());
            }
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
