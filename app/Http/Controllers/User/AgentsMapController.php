<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;

class AgentsMapController extends Controller
{
    /**
     * Display a map with all available agents
     */
    public function index()
    {
        // Get all active agents with location data
        $agents = User::role('Agent')
            ->where('status', 'active')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function($agent) {
                return [
                    'id' => $agent->id,
                    'name' => $agent->name,
                    'city' => $agent->city,
                    'phone' => $agent->phone,
                    'latitude' => (float) $agent->latitude,
                    'longitude' => (float) $agent->longitude,
                    'is_available' => $agent->is_available,
                    'is_currently_available' => $agent->isCurrentlyAvailable(),
                ];
            });

        // Get all agents for debugging (to show why they're not appearing)
        $allAgents = User::role('Agent')
            ->where('status', 'active')
            ->get()
            ->map(function($agent) {
                return [
                    'id' => $agent->id,
                    'name' => $agent->name,
                    'has_location' => !is_null($agent->latitude) && !is_null($agent->longitude),
                    'latitude' => $agent->latitude,
                    'longitude' => $agent->longitude,
                    'city' => $agent->city,
                ];
            });

        return view('user.agents-map', compact('agents', 'allAgents'));
    }
}

