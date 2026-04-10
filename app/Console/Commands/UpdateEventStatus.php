<?php

namespace App\Console\Commands;

use App\Models\Event;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class UpdateEventStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update event status based on event_date and current time.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        // 1. Events that are starting now (within the last 15 mins) or ongoing
        // Note: For simplicity, assume events last 2 hours if end_date not specified, or use the event_date.
        // Usually, an event is 'past' if its date is in the past.
        
        $pastCount = Event::where('status', '=', 'upcoming')
            ->where('event_date', '<', $now->subHours(2)) // Default duration
            ->update(['status' => 'past']);

        if ($pastCount > 0) {
            $this->info("{$pastCount} events updated to 'past'.");
        } else {
            $this->info('No events to update at this time.');
        }
    }
}
