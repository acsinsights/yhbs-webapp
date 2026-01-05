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
                            <td colspan="6" class="text-center py-8">
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
