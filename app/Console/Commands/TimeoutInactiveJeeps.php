<?php

namespace App\Console\Commands;

use App\Models\Jeep;
use App\Models\Trip;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TimeoutInactiveJeeps extends Command
{
    protected $signature   = 'jeeps:timeout-inactive';
    protected $description = 'Mark jeeps inactive and close their open trips when no location update arrives within 2 minutes.';

    public function handle(): void
    {
        $cutoff = now()->subMinutes(2);

        // Jeeps that are marked active but whose last location is older than the cutoff
        $staleJeeps = Jeep::where('status', 'active')
            ->whereHas('latestLocation', fn ($q) => $q->where('recorded_at', '<', $cutoff))
            ->get();

        if ($staleJeeps->isEmpty()) {
            return;
        }

        $staleIds = $staleJeeps->pluck('id');

        // Close any open trips for these jeeps
        Trip::whereIn('jeep_id', $staleIds)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->update([
                'status'   => 'completed',
                'ended_at' => now(),
            ]);

        // Mark the jeeps themselves inactive
        Jeep::whereIn('id', $staleIds)->update(['status' => 'inactive']);

        $this->info("Timed out {$staleJeeps->count()} stale jeep(s): " . $staleIds->implode(', '));
    }
}
