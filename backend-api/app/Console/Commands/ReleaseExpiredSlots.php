<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;

class ReleaseExpiredSlots extends Command
{
    protected $signature = 'slots:release-expired';
    protected $description = 'Release expired slots and mark bookings as completed';

    public function handle()
    {
        $bookings = Booking::where('status', 'confirmed')
            ->where('end_time', '<', now())
            ->get();

        foreach ($bookings as $booking) {
            if ($booking->slot) {
                $booking->slot->available = true;
                $booking->slot->save();
            }

            $booking->status = 'completed';
            $booking->save();
        }

        $this->info('✅ Expired slots released successfully.');
    }
}
