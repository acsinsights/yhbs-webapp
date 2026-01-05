<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WalletService;

class ExpireWalletCredits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wallet:expire-credits';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire wallet credits older than 90 days';

    /**
     * Execute the console command.
     */
    public function handle(WalletService $walletService): int
    {
        $this->info('Expiring old wallet credits...');

        $expiredCount = $walletService->expireOldCredits();

        $this->info("Successfully expired {$expiredCount} wallet credit(s).");

        return Command::SUCCESS;
    }
}
