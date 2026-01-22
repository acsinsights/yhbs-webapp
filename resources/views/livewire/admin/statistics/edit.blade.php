<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\{Locked, Title};
use App\Models\Statistic;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    use WithFileUploads, Toast;

    #[Locked]
    public Statistic $statistic;

    public string $title = '';
    public string $count = '';
    public ?string $icon = null;
    public $new_icon;
    public int $order = 0;
    public bool $is_active = true;

    public function mount(Statistic $statistic): void
    {
        $this->statistic = $statistic;
        $this->title = $this->statistic->title;
        $this->count = $this->statistic->count;
        $this->icon = $this->statistic->icon;
        $this->order = $this->statistic->order;
        $this->is_active = $this->statistic->is_active;
    }

    public function save(): void
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'count' => 'required|string|max:50',
            'order' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'new_icon' => 'nullable|image|max:2048',
        ]);

        $data = [
            'title' => $this->title,
            'count' => $this->count,
            'order' => $this->order,
            'is_active' => $this->is_active,
        ];

        if ($this->new_icon) {
            if ($this->icon) {
                Storage::disk('public')->delete($this->icon);
            }
            $data['icon'] = $this->new_icon->store('statistics', 'public');
        }

        $this->statistic->update($data);
        $this->success('Statistic updated successfully.', redirectTo: route('admin.statistics.index'));
    }
};

?>

<div>
    <x-header title="Edit Statistic: {{ $statistic->title }}" separator progress-indicator>
        <x-slot:actions>
            <x-button label="Back" link="{{ route('admin.statistics.index') }}" icon="o-arrow-left" />
        </x-slot:actions>
    </x-header>

    <x-card>
        <x-form wire:submit="save">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="col-span-2">
                    <x-input label="Title" wire:model="title" placeholder="e.g., Tour Completed" />
                </div>

                <div>
                    <x-input label="Count" wire:model="count" placeholder="e.g., 26K+" />
                </div>

                <div>
                    <x-input label="Order" wire:model="order" type="number" min="0" />
                </div>

                <div class="col-span-2">
                    <x-file label="Icon Image" wire:model="new_icon" accept="image/*"
                        hint="Upload a new icon image (max 2MB)">
                        <img src="{{ $icon ? asset('storage/' . $icon) : 'https://placehold.co/100x100' }}"
                            alt="Statistic Icon" class="h-24 w-24 object-contain rounded p-2 border" />
                    </x-file>
                </div>

                <div>
                    <x-toggle label="Active" wire:model="is_active" />
                </div>
            </div>

            <x-slot:actions>
                <x-button label="Cancel" link="{{ route('admin.statistics.index') }}" />
                <x-button label="Save" type="submit" icon="o-check" spinner="save" class="btn-primary" />
            </x-slot:actions>
        </x-form>
    </x-card>
</div>
