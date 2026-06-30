<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExpireGroupPayments extends Command
{
    protected $signature = 'group-payments:expire';

    protected $description = 'Cancel expired group payments that have not been fully paid within the 1-hour deadline';

    public function handle(): int
    {
        $expired = DB::table('group_payments')
            ->where('status', 'pending')
            ->where('deadline_at', '<=', now())
            ->get();

        if ($expired->isEmpty()) {
            $this->info('No expired group payments found.');
            return self::SUCCESS;
        }

        foreach ($expired as $groupPayment) {
            DB::table('group_payments')
                ->where('id', $groupPayment->id)
                ->update([
                    'status' => 'expired',
                    'updated_at' => now(),
                ]);

            $members = DB::table('group_payment_members')
                ->where('group_payment_id', $groupPayment->id)
                ->whereNotNull('user_id')
                ->get();

            foreach ($members as $member) {
                DB::table('notifications')->insert([
                    'user_id' => $member->user_id,
                    'title' => 'Group payment expired',
                    'message' => sprintf(
                        'The group payment for %s has expired because not all members paid within 1 hour.',
                        $groupPayment->homestay_name
                    ),
                    'is_read' => 0,
                    'created_at' => now(),
                ]);
            }

            $this->info("Expired group payment #{$groupPayment->id} ({$groupPayment->homestay_name})");
        }

        $this->info("Expired {$expired->count()} group payment(s).");

        return self::SUCCESS;
    }
}
