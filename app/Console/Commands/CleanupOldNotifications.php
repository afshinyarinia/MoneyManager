<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CleanupOldNotifications extends Command
{
    protected $signature = 'notifications:cleanup {--days=30}';
    protected $description = 'Clean up old read notifications';

    public function handle()
    {
        $days = $this->option('days');
        $date = Carbon::now()->subDays($days);

        $deleted = DB::table('notifications')
            ->whereNotNull('read_at')
            ->where('created_at', '<', $date)
            ->delete();

        $this->info("Deleted {$deleted} old notifications.");
    }
} 