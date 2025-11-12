<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class SetAgentLocation extends Command
{
    protected $signature = 'agent:set-location {agent_id} {city?}';
    protected $description = 'Set location for an agent by agent ID. If city is provided, geocodes it. Otherwise uses Beirut as default.';

    public function handle()
    {
        $agentId = $this->argument('agent_id');
        $city = $this->argument('city') ?? 'Beirut';
        
        $agent = User::role('Agent')->find($agentId);
        
        if (!$agent) {
            $this->error("Agent with ID {$agentId} not found.");
            return Command::FAILURE;
        }
        
        $this->info("Setting location for agent: {$agent->name}");
        $this->info("City: {$city}");
        
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'LaravelApp/1.0 (contact@example.com)',
            ])->timeout(5)->get('https://nominatim.openstreetmap.org/search', [
                'q' => $city . ', Lebanon',
                'format' => 'json',
                'limit' => 1,
            ]);

            $data = $response->json();

            if (!empty($data) && !empty($data[0])) {
                $agent->city = $city;
                $agent->latitude = $data[0]['lat'];
                $agent->longitude = $data[0]['lon'];
                $agent->save();
                
                $this->info("✓ Location set successfully!");
                $this->info("  Latitude: {$agent->latitude}");
                $this->info("  Longitude: {$agent->longitude}");
                
                return Command::SUCCESS;
            } else {
                $this->error("City '{$city}' not found in geocoding service.");
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error("Geocoding failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

