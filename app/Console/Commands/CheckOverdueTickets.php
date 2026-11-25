<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Ticket;

class CheckOverdueTickets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tickets:check-overdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for overdue tickets and notify requesters';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for overdue tickets...');

        // First, update is_overdue_once flag for all tickets that are currently overdue
        $flaggedCount = Ticket::whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->whereHas('status', function ($q) {
                $q->whereIn('name', ['Open', 'In Progress']);
            })
            ->where('is_overdue_once', 0)
            ->update(['is_overdue_once' => 1]);
        
        if ($flaggedCount > 0) {
            $this->info("Updated is_overdue_once flag for {$flaggedCount} ticket(s).");
        }

        // Report current overdue count (no comments/notifications to reduce noise and DB size)
        $overdueCount = Ticket::whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->whereHas('status', function ($q) {
                $q->whereNotIn('name', ['Completed', 'Cancelled', 'Closed']);
            })
            ->count();

        $this->info("Current overdue tickets: {$overdueCount} (no comments/notifications sent)");
        $this->info('Overdue ticket check completed.');
        return Command::SUCCESS;
    }
}
