<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\{Url, Title};
use App\Models\Testimonial;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    use WithPagination, Toast;

    #[Title('Testimonials')]
    #[Url]
    public string $search = '';

    public array $sortBy = ['column' => 'order', 'direction' => 'asc'];
    public bool $showCreateModal = false;
    public string $customer_name = '';
    public bool $showDeleteModal = false;
    public ?int $deletingId = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function sortByField(string $field): void
    {
        $direction = $this->sortBy['column'] === $field && $this->sortBy['direction'] === 'asc' ? 'desc' : 'asc';
        $this->sortBy = ['column' => $field, 'direction' => $direction];
    }

    public function toggleStatus(int $id): void
    {
        $testimonial = Testimonial::findOrFail($id);
        $testimonial->update(['is_active' => !$testimonial->is_active]);
        $this->success('Status updated successfully.');
    }

    public function delete(): void
    {
        if (!$this->deletingId) {
            return;
        }

        $testimonial = Testimonial::findOrFail($this->deletingId);

        if ($testimonial->customer_image) {
            Storage::disk('public')->delete($testimonial->customer_image);
        }

        $testimonial->delete();
        $this->showDeleteModal = false;
        $this->deletingId = null;
        $this->success('Testimonial deleted successfully.');
    }

    public function create(): void
    {
        $this->validate([
            'customer_name' => 'required|string|max:255',
        ]);

        $testimonial = Testimonial::create([
            'customer_name' => $this->customer_name,
            'testimonial' => '',
            'rating' => 5,
            'is_active' => false,
            'order' => Testimonial::max('order') + 1,
        ]);

        $this->redirect(route('admin.testimonials.edit', $testimonial->id), navigate: true);
    }

    public function with(): array
    {
        $testimonials = Testimonial::query()
            ->when(
                $this->search,
                fn($query) => $query
                    ->where('customer_name', 'like', "%{$this->search}%")
                    ->orWhere('testimonial', 'like', "%{$this->search}%")
                    ->orWhere('customer_designation', 'like', "%{$this->search}%"),
            )
            ->orderBy(...array_values($this->sortBy))
            ->paginate(10);

        return ['testimonials' => $testimonials];
    }
};

?>

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
                                    <img src="{{ asset('storage/' . $testimonial->customer_image) }}"
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
                                    <x-button icon="o-trash" wire:click="confirmDelete({{ $testimonial->id }})"
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

    <x-modal wire:model="showDeleteModal" title="Confirm Delete">
        <div class="text-center py-4">
            <x-icon name="o-exclamation-triangle" class="w-16 h-16 mx-auto text-error mb-4" />
            <p class="text-lg font-semibold mb-2">Are you sure?</p>
            <p class="text-gray-500">Do you really want to delete this testimonial? This action cannot be undone.</p>
        </div>

        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.showDeleteModal = false" />
            <x-button label="Delete" wire:click="delete" class="btn-error" spinner="delete" />
        </x-slot:actions>
    </x-modal>
</div>
