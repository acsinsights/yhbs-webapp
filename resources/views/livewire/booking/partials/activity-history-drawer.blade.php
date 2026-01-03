{{-- Enhanced Activity History Drawer for Booking Management --}}
{{-- This partial can be included in any booking show page --}}
<x-drawer wire:model="showHistoryDrawer" title="Booking History & Activity Log" class="w-11/12 lg:w-2/5" right>
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

                                        // Get properties
                                        $properties = $activity->properties ?? [];
                                    @endphp
                                    <p class="text-sm font-medium text-base-content">
                                        {{ $mainDescription }}
                                    </p>
                                    @if ($reason)
                                        <p class="text-xs text-base-content/70 mt-2 italic">
                                            <strong>Reason:</strong> {{ $reason }}
                                        </p>
                                    @endif

                                    {{-- Display payment update details --}}
                                    @if (stripos($activity->description, 'payment') !== false && isset($properties['payment_status']))
                                        <div class="mt-3 p-2 bg-base-200 rounded-lg space-y-1 text-xs">
                                            <div class="font-semibold text-base-content/70 mb-2">Payment Details:</div>
                                            @if (isset($properties['payment_status']))
                                                <div class="flex justify-between">
                                                    <span class="text-base-content/60">Status:</span>
                                                    <span
                                                        class="font-medium">{{ ucfirst($properties['payment_status']) }}</span>
                                                </div>
                                            @endif
                                            @if (isset($properties['payment_method']))
                                                <div class="flex justify-between">
                                                    <span class="text-base-content/60">Method:</span>
                                                    <span
                                                        class="font-medium">{{ ucfirst($properties['payment_method']) }}</span>
                                                </div>
                                            @endif
                                            @if (isset($properties['extra_fee']) && $properties['extra_fee'] > 0)
                                                <div class="flex justify-between">
                                                    <span class="text-base-content/60">Extra Fee:</span>
                                                    <span
                                                        class="font-medium text-warning">{{ currency_format($properties['extra_fee']) }}</span>
                                                </div>
                                                @if (isset($properties['extra_fee_remark']))
                                                    <div class="col-span-2 text-xs italic text-base-content/50 mt-1">
                                                        {{ $properties['extra_fee_remark'] }}
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                    @endif

                                    {{-- Display reschedule details --}}
                                    @if (stripos($activity->description, 'reschedule') !== false || stripos($activity->description, 'rescheduled') !== false)
                                        <div
                                            class="mt-3 p-2 bg-warning/10 border border-warning/30 rounded-lg space-y-1 text-xs">
                                            <div class="font-semibold text-warning mb-2">Reschedule Details:</div>
                                            @if (isset($properties['attributes']['check_in']) || isset($properties['old']['check_in']))
                                                <div>
                                                    <div class="text-base-content/60 mb-1">Date Change:</div>
                                                    @if (isset($properties['old']['check_in']))
                                                        <div class="flex items-center gap-2">
                                                            <span class="text-base-content/50">From:</span>
                                                            <span>{{ \Carbon\Carbon::parse($properties['old']['check_in'])->format('M d, Y') }}</span>
                                                            <span class="text-base-content/50">→</span>
                                                            <span>{{ \Carbon\Carbon::parse($properties['old']['check_out'])->format('M d, Y') }}</span>
                                                        </div>
                                                    @endif
                                                    @if (isset($properties['attributes']['check_in']))
                                                        <div class="flex items-center gap-2">
                                                            <span class="text-base-content/50">To:</span>
                                                            <span
                                                                class="font-medium">{{ \Carbon\Carbon::parse($properties['attributes']['check_in'])->format('M d, Y') }}</span>
                                                            <span class="text-base-content/50">→</span>
                                                            <span
                                                                class="font-medium">{{ \Carbon\Carbon::parse($properties['attributes']['check_out'])->format('M d, Y') }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                            @if (isset($properties['reschedule_fee']) && $properties['reschedule_fee'] > 0)
                                                <div class="flex justify-between pt-2 border-t border-warning/20">
                                                    <span class="text-base-content/60">Reschedule Fee:</span>
                                                    <span
                                                        class="font-semibold text-warning">{{ currency_format($properties['reschedule_fee']) }}</span>
                                                </div>
                                            @endif
                                            @if (isset($properties['extra_fee']) && $properties['extra_fee'] > 0)
                                                <div class="flex justify-between">
                                                    <span class="text-base-content/60">Extra Fee:</span>
                                                    <span
                                                        class="font-semibold text-info">{{ currency_format($properties['extra_fee']) }}</span>
                                                </div>
                                                @if (isset($properties['extra_fee_remark']))
                                                    <div class="text-xs italic text-base-content/50">
                                                        {{ $properties['extra_fee_remark'] }}
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                    @endif

                                    {{-- Display cancellation details --}}
                                    @if (stripos($activity->description, 'cancel') !== false)
                                        <div
                                            class="mt-3 p-2 bg-error/10 border border-error/30 rounded-lg space-y-1 text-xs">
                                            <div class="font-semibold text-error mb-2">Cancellation Details:</div>
                                            @if (isset($properties['refund_amount']) && $properties['refund_amount'] > 0)
                                                <div class="flex justify-between">
                                                    <span class="text-base-content/60">Refund Amount:</span>
                                                    <span
                                                        class="font-semibold text-success">{{ currency_format($properties['refund_amount']) }}</span>
                                                </div>
                                            @endif
                                            @if (isset($properties['cancellation_reason']))
                                                <div class="pt-2 border-t border-error/20">
                                                    <div class="text-base-content/60 mb-1">Reason:</div>
                                                    <div class="italic">{{ $properties['cancellation_reason'] }}</div>
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    {{-- Display status change details --}}
                                    @if (stripos($activity->description, 'status') !== false &&
                                            (isset($properties['old_status']) || isset($properties['new_status'])))
                                        <div class="mt-2 flex items-center gap-2 text-xs">
                                            @if (isset($properties['old_status']))
                                                <x-badge value="{{ ucfirst($properties['old_status']) }}"
                                                    class="badge-sm" />
                                                <span>→</span>
                                            @endif
                                            @if (isset($properties['new_status']))
                                                <x-badge value="{{ ucfirst($properties['new_status']) }}"
                                                    class="badge-sm badge-primary" />
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div
                                class="flex items-center justify-between text-xs text-base-content/50 pt-2 border-t border-base-300">
                                <div class="flex items-center gap-2">
                                    <x-icon name="o-user" class="w-3 h-3" />
                                    <span>{{ $activity->causer?->name ?? 'System' }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <x-icon name="o-clock" class="w-3 h-3" />
                                    <span>{{ $activity->created_at->format('M d, Y H:i') }}</span>
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
