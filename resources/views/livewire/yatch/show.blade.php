<?php

use App\Models\Yatch;
use Livewire\Volt\Component;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;

    public Yatch $yatch;

    public function mount($id): void
    {
        $this->yatch = Yatch::findOrFail($id);
    }

    public function delete(): void
    {
        // Delete image if exists
        if ($this->yatch->image) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete(str_replace('/storage/', '', $this->yatch->image));
        }

        $this->yatch->delete();
        $this->success('Yacht deleted successfully!', redirectTo: route('admin.yatch.index'));
    }
}; ?>

<div>
    <!-- Header Section -->
    <div class="mb-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="p-3 rounded-xl bg-gradient-to-br from-primary/20 to-primary/10">
                    <x-icon name="o-sparkles" class="w-8 h-8 text-primary" />
                </div>
                <div>
                    <h1
                        class="text-3xl font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">
                        {{ $yatch->name }}
                    </h1>
                    <p class="text-sm text-base-content/60 mt-1">Yacht Details & Information</p>
                </div>
            </div>
            <div class="flex gap-2">
                <x-button label="Edit" link="{{ route('admin.yatch.edit', $yatch->id) }}" icon="o-pencil"
                    class="btn-warning shadow-lg hover:shadow-xl transition-all duration-300" />
                <x-button label="Back" link="{{ route('admin.yatch.index') }}" icon="o-arrow-left"
                    class="btn-ghost hover:btn-primary transition-all duration-300" />
            </div>
        </div>
        <div class="breadcrumbs text-sm">
            <ul>
                <li>
                    <a href="{{ route('admin.yatch.index') }}" wire:navigate
                        class="hover:text-primary transition-colors">
                        <x-icon name="o-sparkles" class="w-4 h-4 inline mr-1" />
                        Yachts
                    </a>
                </li>
                <li class="text-primary font-semibold">{{ $yatch->name }}</li>
            </ul>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Image Section -->
            <x-card shadow class="border-2 border-primary/20 overflow-hidden">
                <div
                    class="bg-gradient-to-r from-primary/10 via-primary/5 to-transparent p-4 border-b border-primary/20">
                    <h2 class="text-xl font-bold flex items-center gap-2">
                        <x-icon name="o-photo" class="w-6 h-6 text-primary" />
                        Main Image
                    </h2>
                </div>
                <div class="p-4">
                    @if ($yatch->image)
                        <div class="relative group overflow-hidden rounded-xl">
                            <img src="{{ asset($yatch->image) }}" alt="{{ $yatch->name }}"
                                class="w-full h-[500px] object-cover rounded-xl shadow-2xl border-2 border-primary/20 transition-transform duration-500 group-hover:scale-105">
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-base-content/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-xl">
                            </div>
                        </div>
                    @else
                        <div
                            class="w-full h-[500px] bg-gradient-to-br from-base-300 via-base-200 to-base-300 rounded-xl flex flex-col items-center justify-center border-2 border-dashed border-base-400 shadow-inner">
                            <x-icon name="o-photo" class="w-32 h-32 text-base-content/20 mb-4" />
                            <span class="text-xl text-base-content/40 font-medium">No image available</span>
                        </div>
                    @endif
                </div>
            </x-card>

            <!-- Description Section -->
            @if ($yatch->description)
                <x-card shadow class="border-2 border-base-300/50">
                    <div class="bg-gradient-to-r from-base-200/50 to-transparent p-4 border-b border-base-300">
                        <h2 class="text-xl font-bold flex items-center gap-2">
                            <x-icon name="o-document-text" class="w-6 h-6 text-primary" />
                            Description
                        </h2>
                    </div>
                    <div class="p-6">
                        <p class="text-base-content/80 whitespace-pre-wrap leading-relaxed text-lg">
                            {{ $yatch->description }}</p>
                    </div>
                </x-card>
            @endif

            <!-- Library Images -->
            @if ($yatch->library && is_array($yatch->library) && count($yatch->library) > 0)
                <x-card shadow class="border-2 border-base-300/50">
                    <div
                        class="bg-gradient-to-r from-primary/10 via-primary/5 to-transparent p-4 border-b border-primary/20">
                        <h2 class="text-xl font-bold flex items-center gap-2">
                            <x-icon name="o-photo" class="w-6 h-6 text-primary" />
                            Gallery
                        </h2>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            @foreach ($yatch->library as $libraryImage)
                                @if (is_string($libraryImage))
                                    <div class="relative group overflow-hidden rounded-xl">
                                        <img src="{{ asset($libraryImage) }}" alt="Gallery Image"
                                            class="w-full h-48 object-cover rounded-xl shadow-lg border-2 border-base-300 transition-transform duration-300 group-hover:scale-110">
                                        <div
                                            class="absolute inset-0 bg-primary/0 group-hover:bg-primary/20 rounded-xl transition-all duration-300">
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </x-card>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Actions Card -->
            <x-card shadow class="border-2 border-primary/20 overflow-hidden">
                <div
                    class="bg-gradient-to-r from-primary/10 via-primary/5 to-transparent p-4 border-b border-primary/20">
                    <h2 class="text-xl font-bold flex items-center gap-2">
                        <x-icon name="o-bolt" class="w-6 h-6 text-primary" />
                        Quick Actions
                    </h2>
                </div>
                <div class="p-4 space-y-3">
                    <x-button label="Edit Yacht" link="{{ route('admin.yatch.edit', $yatch->id) }}" icon="o-pencil"
                        class="btn-warning w-full shadow-lg hover:shadow-xl transition-all duration-300" />
                    <x-button label="Delete Yacht" wire:click="delete"
                        wire:confirm="Are you sure you want to delete this yacht? This action cannot be undone."
                        icon="o-trash" class="btn-error w-full shadow-lg hover:shadow-xl transition-all duration-300" />
                </div>
            </x-card>

            <!-- Details Card -->
            <x-card shadow class="border-2 border-success/20 overflow-hidden">
                <div
                    class="bg-gradient-to-r from-success/10 via-success/5 to-transparent p-4 border-b border-success/20">
                    <h2 class="text-xl font-bold flex items-center gap-2">
                        <x-icon name="o-information-circle" class="w-6 h-6 text-success" />
                        Details
                    </h2>
                </div>
                <div class="p-4 space-y-4">
                    <div class="p-3 rounded-lg bg-base-200/50 border border-base-300">
                        <div class="text-xs font-semibold text-base-content/60 mb-1 uppercase tracking-wide">SKU</div>
                        <div class="text-lg font-bold font-mono">{{ $yatch->sku ?? 'N/A' }}</div>
                    </div>

                    <div class="divider my-3"></div>

                    <div class="p-3 rounded-lg bg-base-200/50 border border-base-300">
                        <div class="text-xs font-semibold text-base-content/60 mb-1 uppercase tracking-wide">Slug</div>
                        <div class="text-sm font-mono break-all bg-base-100 p-2 rounded border border-base-300">
                            {{ $yatch->slug }}
                        </div>
                    </div>

                    <div class="divider my-3"></div>

                    <div
                        class="p-4 rounded-xl bg-gradient-to-br from-primary/10 to-primary/5 border-2 border-primary/20">
                        <div class="text-xs font-semibold text-base-content/60 mb-2 uppercase tracking-wide">Price</div>
                        @if ($yatch->price)
                            <div class="text-3xl font-bold text-primary mb-2">
                                ${{ number_format($yatch->price, 2) }}
                            </div>
                            <div class="badge badge-primary badge-lg">Regular Price</div>
                        @else
                            <div class="text-lg text-base-content/60">N/A</div>
                        @endif
                    </div>

                    @if ($yatch->discount_price)
                        <div
                            class="p-4 rounded-xl bg-gradient-to-br from-success/10 to-success/5 border-2 border-success/20">
                            <div class="text-xs font-semibold text-base-content/60 mb-2 uppercase tracking-wide">
                                Discount Price</div>
                            <div class="text-2xl font-bold text-success mb-2">
                                ${{ number_format($yatch->discount_price, 2) }}
                            </div>
                            @if ($yatch->price)
                                @php
                                    $discountPercent = round(
                                        (($yatch->price - $yatch->discount_price) / $yatch->price) * 100,
                                    );
                                @endphp
                                <div class="badge badge-success badge-lg">
                                    <x-icon name="o-tag" class="w-4 h-4 mr-1" />
                                    {{ $discountPercent }}% OFF
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </x-card>

            <!-- Metadata Card -->
            <x-card shadow class="border-2 border-base-300/50 overflow-hidden">
                <div class="bg-gradient-to-r from-base-200/50 to-transparent p-4 border-b border-base-300">
                    <h2 class="text-xl font-bold flex items-center gap-2">
                        <x-icon name="o-clock" class="w-6 h-6 text-base-content/60" />
                        Metadata
                    </h2>
                </div>
                <div class="p-4 space-y-3">
                    <div
                        class="flex justify-between items-center p-3 rounded-lg bg-base-200/50 border border-base-300">
                        <span class="text-sm font-semibold text-base-content/60 flex items-center gap-2">
                            <x-icon name="o-calendar" class="w-4 h-4" />
                            Created
                        </span>
                        <span class="text-sm font-bold">{{ $yatch->created_at->format('M d, Y') }}</span>
                    </div>
                    <div
                        class="flex justify-between items-center p-3 rounded-lg bg-base-200/50 border border-base-300">
                        <span class="text-sm font-semibold text-base-content/60 flex items-center gap-2">
                            <x-icon name="o-clock" class="w-4 h-4" />
                            Updated
                        </span>
                        <span class="text-sm font-bold">{{ $yatch->updated_at->format('M d, Y') }}</span>
                    </div>
                    <div
                        class="flex justify-between items-center p-3 rounded-lg bg-base-200/50 border border-base-300">
                        <span class="text-sm font-semibold text-base-content/60 flex items-center gap-2">
                            <x-icon name="o-hashtag" class="w-4 h-4" />
                            ID
                        </span>
                        <span class="text-sm font-mono font-bold">{{ $yatch->id }}</span>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
</div>
