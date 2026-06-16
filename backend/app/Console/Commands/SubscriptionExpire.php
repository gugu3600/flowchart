<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('subscription:expire')]
#[Description('Downgrade users with expired subscriptions to free tier')]
class SubscriptionExpire extends Command
{
    protected $signature = 'subscription:expire';

    public function handle()
    {
        $expired = User::whereNotNull('subscription_expires_at')
            ->where('subscription_expires_at', '<', now())
            ->get();

        $count = 0;
        foreach ($expired as $user) {
            $user->syncRoles(['free']);
            $user->subscription_expires_at = null;
            $user->save();
            $count++;
        }

        $this->info("Downgraded {$count} users to free tier.");
    }
}
