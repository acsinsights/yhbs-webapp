<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\{Url, Title};
use App\Models\Slider;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    use WithPagination, Toast;

    #[Title('Hero Sliders')]
    #[Url]
    public string $search = '';

    public array $sortBy = ['column' => 'order', 'direction' => 'asc'];
    public bool $showCreateModal = false;
    public string $title = '';
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
        $slider = Slider::findOrFail($id);
        $slider->update(['is_active' => !$slider->is_active]);
        $this->success('Status updated successfully.');
    }

    public function delete(): void
    {
        if (!$this->deletingId) {
            return;
        }

        $slider = Slider::findOrFail($this->deletingId);

        if ($slider->image) {
            Storage::disk('public')->delete($slider->image);
        }

        $slider->delete();
        $this->showDeleteModal = false;
        $this->deletingId = null;
        $this->success('Slider deleted successfully.');
    }

    public function create(): void
    {
        $this->validate([
            'title' => 'required|string|max:255',
        ]);

        $slider = Slider::create([
            'title' => $this->title,
            'description' => '',
            'image' => null,
            'is_active' => false,
            'order' => Slider::max('order') + 1,
        ]);

        $this->redirect(route('admin.sliders.edit', $slider->id), navigate: true);
    }

    public function with(): array
    {
        $sliders = Slider::query()->when($this->search, fn($query) => $query->where('title', 'like', "%{$this->search}%")->orWhere('description', 'like', "%{$this->search}%"))->orderBy(...array_values($this->sortBy))->paginate(10);

        return ['sliders' => $sliders];
    }
};

?>

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
                                    <img src="{{ asset('storage/' . $slider->image) }}" alt="{{ $slider->title }}"
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
                                    <x-button icon="o-trash" wire:click="confirmDelete({{ $slider->id }})"
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

    <x-modal wire:model="showDeleteModal" title="Confirm Delete">
        <div class="text-center py-4">
            <x-icon name="o-exclamation-triangle" class="w-16 h-16 mx-auto text-error mb-4" />
            <p class="text-lg font-semibold mb-2">Are you sure?</p>
            <p class="text-gray-500">Do you really want to delete this slider? This action cannot be undone.</p>
        </div>

        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.showDeleteModal = false" />
            <x-button label="Delete" wire:click="delete" class="btn-error" spinner="delete" />
        </x-slot:actions>
    </x-modal>
</div>
