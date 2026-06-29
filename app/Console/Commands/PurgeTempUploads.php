<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PurgeTempUploads extends Command
{
    protected $signature   = 'uploads:purge-temp';
    protected $description = 'Delete temp upload folders older than 24 hours (abandoned Lara sessions)';

    public function handle(): void
    {
        $cutoff = now()->subHours(24)->timestamp;
        $purged = 0;

        foreach (Storage::directories('temp') as $dir) {
            // Get the most recent file timestamp in the folder
            $files = Storage::files($dir);
            if (empty($files)) {
                Storage::deleteDirectory($dir);
                $purged++;
                continue;
            }

            $newest = max(array_map(fn($f) => Storage::lastModified($f), $files));
            if ($newest < $cutoff) {
                Storage::deleteDirectory($dir);
                $purged++;
            }
        }

        $this->info("Purged {$purged} temp folder(s).");
    }
}
