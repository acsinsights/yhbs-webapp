<div>
    <x-header title="Testimonials" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="New Testimonial" wire:click="$set('showCreateModal', true)" icon="o-plus"
                class="btn-primary" />
        </x-slot:actions>
    </x-header>

    <x-card>
        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Image</th>
                        <th wire:click="sortByField('customer_name')" class="cursor-pointer">Customer</th>
                        <th>Testimonial</th>
                        <th>Rating</th>
                        <th wire:click="sortByField('order')" class="cursor-pointer">Order</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($testimonials as $testimonial)
                        <tr>
                            <td>{{ $testimonial->id }}</td>
                            <td>
                                @if ($testimonial->customer_image)
                                    <img src="{{ Storage::url($testimonial->customer_image) }}"
                                        alt="{{ $testimonial->customer_name }}"
                                        class="w-10 h-10 object-cover rounded-full">
                                @else
                                    <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                                        <x-icon name="o-user" class="w-5 h-5 text-gray-400" />
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="font-semibold">{{ $testimonial->customer_name }}</div>
                                @if ($testimonial->customer_designation)
                                    <div class="text-sm text-gray-500">{{ $testimonial->customer_designation }}</div>
                                @endif
                            </td>
                            <td class="max-w-md truncate">{{ $testimonial->testimonial }}</td>
                            <td>
                                <div class="flex items-center gap-1">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <x-icon name="{{ $i <= $testimonial->rating ? 's-star' : 'o-star' }}"
                                            class="w-4 h-4 {{ $i <= $testimonial->rating ? 'text-yellow-400' : 'text-gray-300' }}" />
                                    @endfor
                                </div>
                            </td>
                            <td>{{ $testimonial->order }}</td>
                            <td>
                                <x-toggle wire:click="toggleStatus({{ $testimonial->id }})" :checked="$testimonial->is_active" />
                            </td>
                            <td>
                                <div class="flex gap-2">
                                    <x-button icon="o-pencil"
                                        link="{{ route('admin.testimonials.edit', $testimonial->id) }}"
                                        class="btn-sm btn-ghost" tooltip="Edit" />
                                    <x-button icon="o-trash" wire:click="delete({{ $testimonial->id }})"
                                        wire:confirm="Are you sure you want to delete this testimonial?"
                                        class="btn-sm btn-ghost text-error" tooltip="Delete" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8">
                                <div class="text-gray-500">
                                    <x-icon name="o-chat-bubble-left-right" class="w-12 h-12 mx-auto mb-2" />
                                    <p>No testimonials found</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $testimonials->links() }}
        </div>
    </x-card>

    <x-modal wire:model="showCreateModal" title="Create New Testimonial">
        <x-input label="Customer Name" wire:model="customer_name" placeholder="Enter customer name" />

        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.showCreateModal = false" />
            <x-button label="Create" wire:click="create" class="btn-primary" spinner="create" />
        </x-slot:actions>
    </x-modal>
</div>
