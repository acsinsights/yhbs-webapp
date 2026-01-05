<div>
    <x-header title="Hero Sliders" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="New Slider" wire:click="$set('showCreateModal', true)" icon="o-plus" class="btn-primary" />
        </x-slot:actions>
    </x-header>

    <x-card>
        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Image</th>
                        <th wire:click="sortByField('title')" class="cursor-pointer">Title</th>
                        <th>Description</th>
                        <th wire:click="sortByField('order')" class="cursor-pointer">Order</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sliders as $slider)
                        <tr>
                            <td>{{ $slider->id }}</td>
                            <td>
                                @if ($slider->image)
                                    <img src="{{ Storage::url($slider->image) }}" alt="{{ $slider->title }}"
                                        class="w-16 h-10 object-cover rounded">
                                @else
                                    <div class="w-16 h-10 bg-gray-200 rounded flex items-center justify-center">
                                        <x-icon name="o-photo" class="w-6 h-6 text-gray-400" />
                                    </div>
                                @endif
                            </td>
                            <td class="font-semibold">{{ $slider->title }}</td>
                            <td class="max-w-xs truncate">{{ $slider->description }}</td>
                            <td>{{ $slider->order }}</td>
                            <td>
                                <x-toggle wire:click="toggleStatus({{ $slider->id }})" :checked="$slider->is_active" />
                            </td>
                            <td>
                                <div class="flex gap-2">
                                    <x-button icon="o-pencil" link="{{ route('admin.sliders.edit', $slider->id) }}"
                                        class="btn-sm btn-ghost" tooltip="Edit" />
                                    <x-button icon="o-trash" wire:click="delete({{ $slider->id }})"
                                        wire:confirm="Are you sure you want to delete this slider?"
                                        class="btn-sm btn-ghost text-error" tooltip="Delete" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8">
                                <div class="text-gray-500">
                                    <x-icon name="o-photo" class="w-12 h-12 mx-auto mb-2" />
                                    <p>No sliders found</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $sliders->links() }}
        </div>
    </x-card>

    <x-modal wire:model="showCreateModal" title="Create New Slider">
        <x-input label="Title" wire:model="title" placeholder="Enter slider title" />

        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.showCreateModal = false" />
            <x-button label="Create" wire:click="create" class="btn-primary" spinner="create" />
        </x-slot:actions>
    </x-modal>
</div>
