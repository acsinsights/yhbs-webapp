<?php

namespace App\Console\Commands;

use App\Services\WhatsAppNotificationService;
use Illuminate\Console\Command;

class TestWhatsAppNotification extends Command
{
    protected $signature = 'whatsapp:test {phone} {--message=}';
    protected $description = 'Test WhatsApp notification';

    public function handle()
    {
        $phone = $this->argument('phone');
        $message = $this->option('message') ??
            "🎉 *Test Message from " . config('app.name') . "*\n\n" .
            "WhatsApp integration working! ✅\n\n" .
            "Date: " . now()->format('d M Y h:i A');

        $this->info('Sending WhatsApp test message to: ' . $phone);
        $this->newLine();

        $service = app(WhatsAppNotificationService::class);

        if (!$service->isEnabled()) {
            $this->error('❌ WhatsApp disabled or credentials missing!');
            return 1;
        }

        $result = $service->send($phone, $message);

        if ($result) {
            $this->newLine();
            $this->info('✅ Message sent successfully! SID: ' . $result);
            $this->line('📱 Check WhatsApp on: ' . $phone);
            return 0;
        } else {
            $this->newLine();
            $this->error('❌ Failed to send! Check logs: storage/logs/laravel.log');
            return 1;
        }
    }
}
