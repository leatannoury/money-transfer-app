<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class FixAgentRoles extends Command
{
    protected $signature = 'agents:fix-roles';
    protected $description = 'Fix agents that have lowercase role name';

    public function handle()
    {
        // Find users with lowercase 'agent' role
        $agents = User::whereHas('roles', function($q) {
            $q->where('name', 'agent');
        })->get();
        
        $this->info("Found {$agents->count()} agent(s) with lowercase role");
        
        foreach ($agents as $agent) {
            $agent->removeRole('agent');
            $agent->assignRole('Agent');
            $this->info("Fixed: {$agent->name} (ID: {$agent->id})");
        }
        
        if ($agents->count() > 0) {
            $this->info("\nSuccessfully fixed {$agents->count()} agent(s)!");
        } else {
            $this->info("\nNo agents needed fixing.");
        }
        
        return Command::SUCCESS;
    }
}

