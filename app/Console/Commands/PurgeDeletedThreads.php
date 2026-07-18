<?php

namespace App\Console\Commands;

use App\Models\Thread;
use Illuminate\Console\Command;

class PurgeDeletedThreads extends Command
{
    protected $signature   = 'threads:purge-trashed';
    protected $description = 'Force-delete threads that have been in the trash for more than 3 days';

    public function handle(): void
    {
        // Hard-delete trashed threads older than 3 days
        $trashedIds = Thread::onlyTrashed()
            ->where('deleted_at', '<', now()->subDays(3))
            ->pluck('id');

        if ($trashedIds->isNotEmpty()) {
            \App\Models\Post::whereIn('thread_id', $trashedIds)->delete();
            Thread::onlyTrashed()->whereIn('id', $trashedIds)->forceDelete();
        }

        // Hard-delete stale draft threads (not submitted within 3 days of creation)
        $draftIds = Thread::where('status', 'draft')
            ->where('created_at', '<', now()->subDays(3))
            ->pluck('id');

        if ($draftIds->isNotEmpty()) {
            \App\Models\Post::whereIn('thread_id', $draftIds)->delete();
            Thread::whereIn('id', $draftIds)->forceDelete();
        }

        $total = $trashedIds->count() + $draftIds->count();
        $this->info("Purged {$trashedIds->count()} trashed + {$draftIds->count()} stale draft thread(s). Total: {$total}.");
    }
}
