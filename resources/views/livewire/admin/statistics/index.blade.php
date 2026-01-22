<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\{Url, Title};
use App\Models\Statistic;
use Mary\Traits\Toast;

new class extends Component {
    use WithPagination, Toast;

    #[Title('Statistics / Counters')]
    #[Url]
    public string $search = '';

    public array $sortBy = ['column' => 'order', 'direction' => 'asc'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortByField(string $field): void
    {
        $direction = $this->sortBy['column'] === $field && $this->sortBy['direction'] === 'asc' ? 'desc' : 'asc';
        $this->sortBy = ['column' => $field, 'direction' => $direction];
    }

    public function toggleStatus(int $id): void
    {
        $statistic = Statistic::findOrFail($id);
        $statistic->update(['is_active' => !$statistic->is_active]);
        $this->success('Status updated successfully.');
    }

    public function with(): array
    {
        $statistics = Statistic::query()->when($this->search, fn($query) => $query->where('title', 'like', "%{$this->search}%"))->orderBy(...array_values($this->sortBy))->paginate(10);

        return ['statistics' => $statistics];
    }
};

?>

<div>
    <x-header title="Statistics / Counters" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
    </x-header>

    <x-card>
        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Icon</th>
                        <th wire:click="sortByField('title')" class="cursor-pointer">Title</th>
                        <th>Count</th>
                        <th wire:click="sortByField('order')" class="cursor-pointer">Order</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($statistics as $statistic)
                        <tr>
                            <td>{{ $statistic->id }}</td>
                            <td>
                                @if ($statistic->icon)
                                    <img src="{{ asset('storage/' . $statistic->icon) }}" alt="Icon"
                                        class="h-10 w-10 object-contain rounded" />
                                @else
                                    <div class="h-10 w-10 bg-gray-200 rounded flex items-center justify-center">
                                        <x-icon name="o-chart-bar" class="w-6 h-6 text-gray-400" />
                                    </div>
                                @endif
                            </td>
                            <td class="font-semibold">{{ $statistic->title }}</td>
                            <td class="text-lg font-bold">{{ $statistic->count }}</td>
                            <td>{{ $statistic->order }}</td>
                            <td>
                                <x-toggle wire:click="toggleStatus({{ $statistic->id }})" :checked="$statistic->is_active" />
                            </td>
                            <td>
                                <div class="flex gap-2">
                                    <x-button icon="o-pencil"
                                        link="{{ route('admin.statistics.edit', $statistic->id) }}"
                                        class="btn-sm btn-ghost" tooltip="Edit" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8">
                                <div class="text-gray-500">
                                    <x-icon name="o-chart-bar" class="w-12 h-12 mx-auto mb-2" />
                                    <p>No statistics found</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $statistics->links() }}
        </div>
    </x-card>
</div>
