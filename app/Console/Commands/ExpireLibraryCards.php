<?php

namespace App\Console\Commands;

use App\Models\LibraryCard;
use Illuminate\Console\Command;

class ExpireLibraryCards extends Command
{
    protected $signature   = 'library-cards:expire';
    protected $description = 'Set kartu yang sudah lewat expired_at menjadi expired';

    public function handle(): void
    {
        $count = LibraryCard::where('status', 'active')
            ->where('expired_at', '<', now())
            ->update(['status' => 'expired']);

        $this->info("$count kartu berhasil diset expired.");
    }
}