<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class FormatPhoneNumbers extends Command
{
    protected $signature = 'phone:format {--country=91} {--dry-run}';
    protected $description = 'Format existing phone numbers to include country code';

    public function handle()
    {
        $countryCode = $this->option('country');
        $dryRun = $this->option('dry-run');

        $this->info('Scanning users with phone numbers...');
        $this->newLine();

        $users = User::whereNotNull('phone')
            ->where('phone', '!=', '')
            ->get();

        $toUpdate = [];
        $alreadyFormatted = 0;
        $empty = 0;

        foreach ($users as $user) {
            $phone = trim($user->phone);

            // Skip if already has + (already formatted)
            if (str_starts_with($phone, '+')) {
                $alreadyFormatted++;
                continue;
            }

            // Skip if empty
            if (empty($phone)) {
                $empty++;
                continue;
            }

            // Remove spaces, dashes, and other characters
            $cleaned = preg_replace('/[^0-9]/', '', $phone);

            // If it's a valid 10-digit number, add country code
            if (strlen($cleaned) >= 10) {
                $formatted = '+' . $countryCode . $cleaned;
                $toUpdate[] = [
                    'user' => $user,
                    'old' => $phone,
                    'new' => $formatted
                ];
            }
        }

        $this->table(
            ['Status', 'Count'],
            [
                ['Already formatted (with +)', $alreadyFormatted],
                ['Need formatting', count($toUpdate)],
                ['Empty/Invalid', $empty],
                ['Total', $users->count()]
            ]
        );

        if (count($toUpdate) === 0) {
            $this->info('✅ All phone numbers are already properly formatted!');
            return 0;
        }

        $this->newLine();
        $this->warn('Phone numbers to be updated:');
        $this->table(
            ['User ID', 'Name', 'Old Phone', 'New Phone'],
            collect($toUpdate)->map(fn($item) => [
                $item['user']->id,
                $item['user']->name,
                $item['old'],
                $item['new']
            ])->toArray()
        );

        if ($dryRun) {
            $this->newLine();
            $this->info('🔍 Dry run mode - no changes made.');
            $this->line('Run without --dry-run to apply changes.');
            return 0;
        }

        $this->newLine();
        if (!$this->confirm('Update ' . count($toUpdate) . ' phone numbers?')) {
            $this->warn('Cancelled.');
            return 0;
        }

        $updated = 0;
        foreach ($toUpdate as $item) {
            try {
                $item['user']->update(['phone' => $item['new']]);
                $updated++;
            } catch (\Exception $e) {
                $this->error('Failed to update user ' . $item['user']->id . ': ' . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info("✅ Successfully updated $updated phone numbers!");

        return 0;
    }
}
