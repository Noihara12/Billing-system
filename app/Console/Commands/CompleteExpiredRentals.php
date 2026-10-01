<?php

namespace App\Console\Commands;

use App\Services\RentalService;
use Illuminate\Console\Command;

class CompleteExpiredRentals extends Command
{
    protected $signature = 'rentals:complete-expired';

    protected $description = 'Complete rental sessions whose time has run out and free up their units.';

    public function handle(RentalService $rentalService): int
    {
        $count = $rentalService->completeExpired();

        $this->info("Completed {$count} expired rental session(s).");

        return self::SUCCESS;
    }
}
