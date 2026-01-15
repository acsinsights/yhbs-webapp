<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\User;
use App\Services\WhatsAppNotificationService;
use Illuminate\Console\Command;

class TestWhatsAppNotifications extends Command
{
    protected $signature = 'whatsapp:test-all {phone} {--booking_id=}';
    protected $description = 'Test all WhatsApp notification types';

    public function handle()
    {
        $phone = $this->argument('phone');
        $bookingId = $this->option('booking_id');

        // Create dummy user
        $user = new User([
            'first_name' => 'Test',
            'name' => 'Test User',
            'phone' => $phone,
            'email' => 'test@example.com'
        ]);

        // Create or use existing booking
        if ($bookingId) {
            $booking = Booking::find($bookingId);
            if (!$booking) {
                $this->error('Booking not found!');
                return 1;
            }
        } else {
            $booking = new Booking([
                'booking_id' => 'TEST-' . rand(1000, 9999),
                'check_in' => now()->addDays(7),
                'arrival_time' => '10:00 AM',
                'total_amount' => 5000,
                'refund_amount' => 4500,
                'bookingable_type' => 'boat',
            ]);
            $booking->bookingable = (object) ['name' => 'Luxury Yacht'];
        }

        $service = app(WhatsAppNotificationService::class);

        $this->info('Testing all WhatsApp notifications...');
        $this->newLine();

        // Test 1: Booking Confirmation
        $this->line('1️⃣  Testing: Booking Confirmation');
        $result1 = $service->sendBookingConfirmation($booking, $user);
        $this->displayResult($result1);
        sleep(2);

        // Test 2: Cancellation Approved
        $this->line('2️⃣  Testing: Cancellation Approved');
        $result2 = $service->sendCancellationApproved($booking, $user);
        $this->displayResult($result2);
        sleep(2);

        // Test 3: Cancellation Rejected
        $this->line('3️⃣  Testing: Cancellation Rejected');
        $result3 = $service->sendCancellationRejected($booking, $user, 'Cancellation not allowed within 24 hours');
        $this->displayResult($result3);
        sleep(2);

        // Test 4: Reschedule Approved
        $this->line('4️⃣  Testing: Reschedule Approved');
        $result4 = $service->sendRescheduleApproved($booking, $user);
        $this->displayResult($result4);
        sleep(2);

        // Test 5: Reschedule Rejected
        $this->line('5️⃣  Testing: Reschedule Rejected');
        $result5 = $service->sendRescheduleRejected($booking, $user, 'Dates not available');
        $this->displayResult($result5);
        sleep(2);

        // Test 6: Payment Received
        $this->line('6️⃣  Testing: Payment Received');
        $result6 = $service->sendPaymentReceived($booking, $user, 'TXN' . rand(100000, 999999));
        $this->displayResult($result6);

        $this->newLine();
        $this->info('✅ All tests completed! Check your WhatsApp: ' . $phone);

        return 0;
    }

    private function displayResult($result)
    {
        if ($result) {
            $this->info('   ✅ Sent successfully! SID: ' . substr($result, 0, 20) . '...');
        } else {
            $this->error('   ❌ Failed to send!');
        }
    }
}
