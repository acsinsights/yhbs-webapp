<?php

use Carbon\Carbon;
use Mary\Traits\Toast;
use Livewire\Volt\Component;
use App\Models\{Booking, Boat};
use App\Services\WalletService;

new class extends Component {
    use Toast;

    public Booking $booking;
    public bool $showPaymentModal = false;
    public string $payment_status = '';
    public string $payment_method = '';
    public bool $showCancelModal = false;
    public string $cancellation_reason = '';
    public ?float $refund_amount = null;
    public bool $showRescheduleModal = false;
    public ?string $new_check_in = null;
    public ?string $new_time_slot = null;
    public ?string $reschedule_notes = null;
    public bool $showHistoryDrawer = false;
    public $activities = [];

    public function mount(Booking $booking): void
    {
        $this->booking = $booking->load(['bookingable', 'user']);

        if ($booking->bookingable_type !== Boat::class) {
            $this->error('Invalid booking type.', redirectTo: route('admin.bookings.boat.index'));
            return;
        }

        $this->payment_status = $booking->payment_status->value;
        $this->payment_method = $booking->payment_method->value;

        // Explicitly set modal states to false
        $this->showRescheduleModal = false;
        $this->showPaymentModal = false;
        $this->showCancelModal = false;
    }

    public function updated($property): void
    {
        if ($property === 'showRescheduleModal' && $this->showRescheduleModal) {
            $this->new_check_in = null;
            $this->new_time_slot = null;
            $this->reschedule_notes = null;
        }

        if ($property === 'showHistoryDrawer' && $this->showHistoryDrawer) {
            $this->loadActivities();
        }
    }

    public function loadActivities(): void
    {
        $this->activities = \Spatie\Activitylog\Models\Activity::where('subject_type', get_class($this->booking))
            ->where('subject_id', $this->booking->id)
            ->with('causer')
            ->latest()
            ->get();
    }

    public function updatePayment(): void
    {
        $this->validate([
            'payment_status' => 'required|in:pending,paid',
            'payment_method' => 'required|in:cash,card,online,other',
        ]);

        $this->booking->update([
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
        ]);

        $this->showPaymentModal = false;
        $this->success('Payment details updated successfully.');
        $this->booking->refresh();
    }

    public function cancelBooking(): void
    {
        $this->validate([
            'cancellation_reason' => 'required|min:10',
            'refund_amount' => 'nullable|numeric|min:0|max:' . $this->booking->price,
        ]);

        $this->booking->update([
            'status' => 'cancelled',
            'notes' => ($this->booking->notes ? $this->booking->notes . "\n\n" : '') . 'Cancellation Reason: ' . $this->cancellation_reason,
        ]);

        // Add refund to customer's wallet if amount is specified
        if ($this->refund_amount && $this->refund_amount > 0) {
            $walletService = app(WalletService::class);
            $walletService->addCredit($this->booking->user, $this->refund_amount, $this->booking, "Refund for cancelled booking #{$this->booking->id}", 'booking_cancellation');
        }

        $this->showCancelModal = false;
        $this->success('Booking cancelled successfully.' . ($this->refund_amount ? " Refund of {$this->refund_amount} added to customer's wallet." : ''), redirectTo: route('admin.bookings.boat.index'));
    }

    public function rescheduleBooking(): void
    {
        $this->validate([
            'new_check_in' => 'required|date|after_or_equal:today',
            'new_time_slot' => 'required|string',
            'reschedule_notes' => 'nullable|string|min:3',
        ]);

        $newCheckIn = Carbon::parse($this->new_check_in . ' ' . $this->new_time_slot);

        // Update booking with new date/time
        $this->booking->update([
            'check_in' => $newCheckIn,
            'check_out' => $newCheckIn->copy()->addHours(1), // Adjust based on service type
            'notes' => ($this->booking->notes ? $this->booking->notes . "\n\n" : '') . 'Rescheduled: ' . ($this->reschedule_notes ?? 'No notes provided'),
        ]);

        $this->showRescheduleModal = false;
        $this->success('Booking rescheduled successfully.');
        $this->booking->refresh();
    }

    public function with(): array
    {
        return [
            'breadcrumbs' => [['label' => 'Dashboard', 'url' => route('admin.index')], ['label' => 'Boat Bookings', 'link' => route('admin.bookings.boat.index')], ['label' => 'Booking #' . $this->booking->id]],
        ];
    }
}; ?>

<div>
    @php
        $breadcrumbs = [
            [
                'link' => route('admin.index'),
                'icon' => 's-home',
            ],
            [
                'link' => route('admin.bookings.boat.index'),
                'label' => 'Boat Bookings',
            ],
            [
                'label' => 'Booking Details',
            ],
        ];
    @endphp

    <x-header title="Booking Details" separator>
        <x-slot:subtitle>
            <p class="text-sm text-base-content/50 mb-2">View booking information</p>
            <x-breadcrumbs :items="$breadcrumbs" separator="o-slash" class="mb-3" />
        </x-slot:subtitle>
        <x-slot:actions>
            <x-button icon="o-arrow-left" label="Back" link="{{ route('admin.bookings.boat.index') }}"
                class="btn-ghost btn-outline" />

            <x-dropdown icon="o-ellipsis-vertical" class="btn-ghost btn-circle">
                <x-menu-item title="History" icon="o-clock" wire:click.stop="$set('showHistoryDrawer', true)" />
                @if ($booking->status->value === 'booked' || $booking->status->value === 'pending')
                    <x-menu-item title="Edit" icon="o-pencil"
                        link="{{ route('admin.bookings.boat.edit', $booking->id) }}" />
                    <x-menu-item title="Reschedule" icon="o-calendar"
                        wire:click.stop="$set('showRescheduleModal', true)" />
                    <x-menu-separator />
                    <x-menu-item title="Cancel Booking" icon="o-x-circle"
                        wire:click.stop="$set('showCancelModal', true)" class="text-error" />
                @endif
            </x-dropdown>
        </x-slot:actions>
    </x-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Details --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Booking Information --}}
            <x-card shadow>
                <x-slot:title>
                    <div class="flex items-center gap-2">
                        <x-icon name="o-document-text" class="w-5 h-5" />
                        <span>Booking Information</span>
                    </div>
                </x-slot:title>
                <x-slot:menu>
                    <x-badge :value="$booking->status->label()" class="{{ $booking->status->badgeColor() }}" />
                </x-slot:menu>

                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="text-sm text-base-content/50 mb-1">Booking ID</div>
                            <div class="font-semibold">#{{ $booking->id }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-base-content/50 mb-1">Booking Date</div>
                            <div class="font-semibold">{{ $booking->created_at->format('M d, Y h:i A') }}</div>
                        </div>
                    </div>

                    @if ($booking->check_in)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <div class="text-sm text-base-content/50 mb-1">Check In</div>
                                <div class="font-semibold">
                                    {{ $booking->check_in->format('M d, Y h:i A') }}
                                </div>
                            </div>
                            @if ($booking->check_out)
                                <div>
                                    <div class="text-sm text-base-content/50 mb-1">Check Out</div>
                                    <div class="font-semibold">
                                        {{ $booking->check_out->format('M d, Y h:i A') }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <div class="text-sm text-base-content/50 mb-1">Adults</div>
                            <x-badge :value="$booking->adults ?? 0" class="badge-soft badge-primary" />
                        </div>

                        <div>
                            <div class="text-sm text-base-content/50 mb-1">Children</div>
                            <x-badge :value="$booking->children ?? 0" class="badge-soft badge-secondary" />
                        </div>
                    </div>

                    @if ($booking->guest_details)
                        <div>
                            <div class="text-sm text-base-content/50 mb-2">Guest Names</div>
                            <div class="grid gap-2 md:grid-cols-2">
                                @php
                                    $guestDetails = is_string($booking->guest_details)
                                        ? json_decode($booking->guest_details, true)
                                        : $booking->guest_details;
                                @endphp
                                @if (isset($guestDetails['adultNames']))
                                    @foreach ($guestDetails['adultNames'] as $index => $name)
                                        @if ($name)
                                            <div class="text-sm">
                                                <x-icon name="o-user" class="w-4 h-4 inline" /> Adult
                                                {{ $index + 1 }}:
                                                {{ $name }}
                                            </div>
                                        @endif
                                    @endforeach
                                @endif
                                @if (isset($guestDetails['childrenNames']))
                                    @foreach ($guestDetails['childrenNames'] as $index => $name)
                                        @if ($name)
                                            <div class="text-sm">
                                                <x-icon name="o-user" class="w-4 h-4 inline" /> Child
                                                {{ $index + 1 }}:
                                                {{ $name }}
                                            </div>
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    @endif

                    @if ($booking->notes)
                        <div>
                            <div class="text-sm text-base-content/50 mb-1">Notes</div>
                            <div class="text-sm">{{ strip_tags($booking->notes) }}</div>
                        </div>
                    @endif
                </div>
            </x-card>

            {{-- Boat Information --}}
            @if ($booking->bookingable)
                <x-card shadow>
                    <x-slot:title>
                        <div class="flex items-center gap-2">
                            <x-icon name="o-archive-box" class="w-5 h-5" />
                            <span>Boat Information</span>
                        </div>
                    </x-slot:title>

                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <div class="text-sm text-base-content/50 mb-1">Boat Name</div>
                                <div class="font-semibold text-lg">{{ $booking->bookingable->name }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-base-content/50 mb-1">Service Type</div>
                                <x-badge :value="$booking->bookingable->service_type_label" class="badge-primary" />
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <div class="text-sm text-base-content/50 mb-1">Capacity</div>
                                <div class="text-sm">
                                    {{ $booking->bookingable->min_passengers }}-{{ $booking->bookingable->max_passengers }}
                                    passengers</div>
                            </div>
                            <div>
                                <div class="text-sm text-base-content/50 mb-1">Location</div>
                                <div class="text-sm">{{ $booking->bookingable->location ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                </x-card>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Customer Information --}}
            <x-card shadow>
                <x-slot:title>
                    <div class="flex items-center gap-2">
                        <x-icon name="o-user" class="w-5 h-5" />
                        <span>Customer</span>
                    </div>
                </x-slot:title>

                <div class="space-y-4">
                    <div>
                        <div class="text-sm text-base-content/50 mb-1">Name</div>
                        <div class="font-semibold">{{ $booking->user->name ?? 'N/A' }}</div>
                    </div>

                    @if ($booking->user)
                        <div>
                            <div class="text-sm text-base-content/50 mb-1">Email</div>
                            <div class="text-sm">{{ $booking->user->email }}</div>
                        </div>

                        @if ($booking->user->phone)
                            <div>
                                <div class="text-sm text-base-content/50 mb-1">Phone</div>
                                <div class="text-sm">{{ $booking->user->phone }}</div>
                            </div>
                        @endif
                    @endif
                </div>
            </x-card>

            {{-- Payment Information --}}
            <x-card shadow>
                <x-slot:title>
                    <div class="flex items-center gap-2">
                        <x-icon name="o-currency-dollar" class="w-5 h-5" />
                        <span>Payment</span>
                    </div>
                </x-slot:title>
                <x-slot:menu>
                    @if ($booking->status->value !== 'cancelled')
                        <x-button icon="o-pencil" label="Update" wire:click="$set('showPaymentModal', true)"
                            class="btn-ghost btn-sm" />
                    @endif
                </x-slot:menu>

                <div class="space-y-4">
                    <div>
                        <div class="text-sm text-base-content/50 mb-1">Amount</div>
                        <div class="font-semibold text-2xl">KD {{ number_format($booking->price ?? 0, 2) }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-base-content/50 mb-1">Payment Method</div>
                        <x-badge :value="$booking->payment_method->label()" class="{{ $booking->payment_method->badgeColor() }}" />
                    </div>
                    <div>
                        <div class="text-sm text-base-content/50 mb-1">Payment Status</div>
                        <x-badge :value="$booking->payment_status->label()" class="{{ $booking->payment_status->badgeColor() }}" />
                    </div>
                </div>
            </x-card>
        </div>
    </div>

    {{-- Payment Update Modal --}}
    <x-modal wire:model="showPaymentModal" title="Update Payment Details" class="backdrop-blur">
        <div class="space-y-4">
            <x-select label="Payment Status" wire:model="payment_status" :options="[['id' => 'pending', 'name' => 'Pending'], ['id' => 'paid', 'name' => 'Paid']]" icon="o-credit-card" />

            <x-select label="Payment Method" wire:model="payment_method" :options="[
                ['id' => 'cash', 'name' => 'Cash'],
                ['id' => 'card', 'name' => 'Card'],
                ['id' => 'online', 'name' => 'Online'],
                ['id' => 'other', 'name' => 'Other'],
            ]" icon="o-banknotes" />
        </div>

        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.showPaymentModal = false" />
            <x-button label="Update" wire:click="updatePayment" class="btn-primary" spinner="updatePayment" />
        </x-slot:actions>
    </x-modal>

    {{-- Cancel Booking Modal --}}
    <x-modal wire:model="showCancelModal" title="Cancel Booking" class="backdrop-blur">
        <div class="space-y-4">
            <x-alert title="Warning!"
                description="This action cannot be undone. Please provide a reason for cancellation."
                icon="o-exclamation-triangle" class="alert-warning" />

            <x-textarea label="Cancellation Reason" wire:model="cancellation_reason"
                placeholder="Please provide a detailed reason for cancellation..." rows="4"
                hint="Minimum 10 characters required" />

            <x-input label="Refund Amount (Optional)" wire:model="refund_amount" type="number" step="0.01"
                min="0" max="{{ $booking->price }}" icon="o-currency-dollar"
                hint="Enter amount to refund to customer's wallet. Max: {{ $booking->price }}" />
        </div>

        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.showCancelModal = false" />
            <x-button label="Confirm Cancellation" wire:click="cancelBooking" class="btn-error"
                spinner="cancelBooking" />
        </x-slot:actions>
    </x-modal>

    {{-- Reschedule Booking Modal --}}
    <x-modal wire:model="showRescheduleModal" title="Reschedule Booking" class="backdrop-blur">
        <div class="space-y-4">
            <x-alert title="Current Booking" description="Check-in: {{ $booking->check_in->format('M d, Y h:i A') }}"
                icon="o-information-circle" class="alert-info" />

            <x-input label="New Date *" type="date" icon="o-calendar" wire:model="new_check_in"
                min="{{ now()->format('Y-m-d') }}" />

            <x-input label="New Time Slot *" type="time" icon="o-clock" wire:model="new_time_slot" />

            <x-textarea label="Reason for Rescheduling (Optional)" wire:model="reschedule_notes"
                placeholder="Enter reason for rescheduling..." rows="3"
                hint="Provide context for the date change" />
        </div>

        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.showRescheduleModal = false" />
            <x-button label="Confirm Reschedule" wire:click="rescheduleBooking" class="btn-primary"
                spinner="rescheduleBooking" />
        </x-slot:actions>
    </x-modal>

    {{-- Activity History Drawer --}}
    <x-drawer wire:model="showHistoryDrawer" title="Booking History" class="w-11/12 lg:w-2/5" right>
        <div class="space-y-4">
            @if (count($activities) > 0)
                <div class="space-y-3">
                    @foreach ($activities as $activity)
                        <x-card shadow>
                            <div class="space-y-2">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        @php
                                            // Parse description to extract reason
                                            $mainDescription = $activity->description;
                                            $reason = null;
                                            if (strpos($activity->description, '. Reason: ') !== false) {
                                                [$mainDescription, $reason] = explode(
                                                    '. Reason: ',
                                                    $activity->description,
                                                    2,
                                                );
                                            }
                                        @endphp
                                        <p class="text-sm font-medium text-base-content">
                                            {{ $mainDescription }}
                                        </p>
                                        @if ($reason)
                                            <p class="text-xs text-base-content/70 mt-2 italic">
                                                <strong>Reason:</strong> {{ $reason }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center justify-between text-xs text-base-content/50">
                                    <div class="flex items-center gap-2">
                                        <x-icon name="o-user" class="w-3 h-3" />
                                        <span>{{ $activity->causer?->name ?? 'System' }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <x-icon name="o-clock" class="w-3 h-3" />
                                        <span>{{ $activity->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>
                        </x-card>
                    @endforeach
                </div>
            @else
                <x-alert title="No History" description="No activity history available for this booking yet."
                    icon="o-information-circle" class="alert-info" />
            @endif
        </div>

        <x-slot:actions>
            <x-button label="Close" @click="$wire.showHistoryDrawer = false" />
        </x-slot:actions>
    </x-drawer>
</div>
