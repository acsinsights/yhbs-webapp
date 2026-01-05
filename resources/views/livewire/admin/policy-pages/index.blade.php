<div>
    <x-header title="Policy Pages" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
    </x-header>

    <x-card>
        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th wire:click="sortByField('id')" class="cursor-pointer">#</th>
                        <th wire:click="sortByField('title')" class="cursor-pointer">Title</th>
                        <th>Slug</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pages as $page)
                        <tr>
                            <td>{{ $page->id }}</td>
                            <td class="font-semibold">{{ $page->title }}</td>
                            <td><code class="text-sm">{{ $page->slug }}</code></td>
                            <td>{{ $page->order }}</td>
                            <td>
                                <x-toggle wire:click="toggleStatus({{ $page->id }})" :checked="$page->is_active" />
                            </td>
                            <td>
                                <x-button icon="o-pencil" link="{{ route('admin.policy-pages.edit', $page->id) }}"
                                    class="btn-sm btn-ghost" tooltip="Edit" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-8">
                                <div class="text-gray-500">
                                    <x-icon name="o-document-text" class="w-12 h-12 mx-auto mb-2" />
                                    <p>No policy pages found</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $pages->links() }}
        </div>
    </x-card>
</div>
