<?php

use Livewire\Volt\Component;
use App\Models\CareerApplication;
use Livewire\WithPagination;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;
use Symfony\Component\HttpFoundation\StreamedResponse;

new class extends Component {
    use WithPagination, Toast;

    #[Title('Career Applications')]
    public $headers;
    #[Url]
    public string $search = '';

    public $sortBy = ['column' => 'created_at', 'direction' => 'desc'];

    public bool $filterDrawer = false;
    public $startDate = '';
    public $endDate = '';
    public $dateRange = '';

    public function boot(): void
    {
        $this->headers = [['key' => 'id', 'label' => '#', 'class' => 'w-1'], ['key' => 'name', 'label' => 'Name'], ['key' => 'email', 'label' => 'Email'], ['key' => 'phone', 'label' => 'Phone'], ['key' => 'position', 'label' => 'Position'], ['key' => 'created_at', 'label' => 'Applied On']];
    }

    public function rendering(View $view): void
    {
        $query = CareerApplication::orderBy(...array_values($this->sortBy));

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")
                    ->orWhere('phone', 'like', "%{$this->search}%")
                    ->orWhere('position', 'like', "%{$this->search}%");
            });
        }

        // Apply date filters
        if (!empty($this->dateRange)) {
            $dates = explode(' to ', $this->dateRange);
            if (count($dates) == 2) {
                $query->whereDate('created_at', '>=', $dates[0])->whereDate('created_at', '<=', $dates[1]);
            }
        } elseif (!empty($this->startDate) || !empty($this->endDate)) {
            if (!empty($this->startDate)) {
                $query->whereDate('created_at', '>=', $this->startDate);
            }
            if (!empty($this->endDate)) {
                $query->whereDate('created_at', '<=', $this->endDate);
            }
        }

        $view->applications = $query->paginate(20);
    }

    public function applyFilter()
    {
        $this->filterDrawer = false;
        $this->success('Filter applied successfully');
    }

    public function clearFilter()
    {
        $this->startDate = '';
        $this->endDate = '';
        $this->dateRange = '';
        $this->filterDrawer = false;
        $this->success('Filter cleared');
    }

    public function exportData(): StreamedResponse
    {
        $query = CareerApplication::orderBy(...array_values($this->sortBy));

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")
                    ->orWhere('phone', 'like', "%{$this->search}%")
                    ->orWhere('position', 'like', "%{$this->search}%");
            });
        }

        if (!empty($this->dateRange)) {
            $dates = explode(' to ', $this->dateRange);
            if (count($dates) == 2) {
                $query->whereDate('created_at', '>=', $dates[0])->whereDate('created_at', '<=', $dates[1]);
            }
        } elseif (!empty($this->startDate) || !empty($this->endDate)) {
            if (!empty($this->startDate)) {
                $query->whereDate('created_at', '>=', $this->startDate);
            }
            if (!empty($this->endDate)) {
                $query->whereDate('created_at', '<=', $this->endDate);
            }
        }

        $applications = $query->get();

        $filename = 'career_applications_' . date('Y-m-d_His') . '.csv';

        return response()->streamDownload(
            function () use ($applications) {
                $handle = fopen('php://output', 'w');

                // Add CSV headers
                fputcsv($handle, ['#', 'Name', 'Email', 'Phone', 'Position', 'Experience', 'Cover Letter', 'Applied Date']);

                // Add data rows
                foreach ($applications as $application) {
                    fputcsv($handle, [$application->id, $application->name, $application->email, $application->phone, $application->position, $application->experience ?? 'N/A', $application->cover_letter ?? 'N/A', $application->created_at->format('d M Y, h:i A')]);
                }

                fclose($handle);
            },
            $filename,
            [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ],
        );
    }

    public function delete($id)
    {
        $application = CareerApplication::find($id);

        // Delete resume file if exists
        if ($application->resume && \Storage::disk('public')->exists($application->resume)) {
            \Storage::disk('public')->delete($application->resume);
        }

        $application->delete();
        $this->success('Application deleted successfully');
    }
};
?>

<div>
    <div class="flex justify-between items-start lg:items-center flex-col lg:flex-row mt-3 mb-5 gap-2">
        <div>
            <h1 class="text-2xl font-bold mb-2">
                Career Applications
            </h1>
            <div class="breadcrumbs text-sm">
                <ul class="flex">
                    <li>
                        <a href="{{ route('admin.index') }}" wire:navigate>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        Career Applications
                    </li>
                </ul>
            </div>
        </div>
        <div class="flex gap-3">
            <x-input placeholder="Search ..." icon="o-magnifying-glass" wire:model.live.debounce="search" />
            <x-button label="Filter" icon="o-funnel" class="btn-primary" @click="$wire.filterDrawer = true" />
        </div>
    </div>
    <hr class="mb-5">
    <x-table :headers="$headers" :rows="$applications" with-pagination :sort-by="$sortBy">
        @scope('cell_name', $application)
            {{ $application->name }}
        @endscope
        @scope('cell_created_at', $application)
            {{ $application->created_at->format('d M Y, h:i A') }}
        @endscope
        @scope('actions', $application)
            <div class="flex gap-1">
                <x-button icon="o-eye" link="{{ route('admin.career-applications.show', $application->id) }}"
                    class="btn-xs btn-ghost" tooltip="View" />
                @if ($application->resume)
                    <x-button icon="o-arrow-down-tray" link="{{ Storage::url($application->resume) }}" external
                        class="btn-xs btn-ghost" tooltip="Download Resume" />
                @endif
                <x-button icon="o-trash" wire:click="delete({{ $application->id }})" wire:confirm="Are you sure?"
                    class="btn-xs btn-ghost text-error" tooltip="Delete" spinner />
            </div>
        @endscope
        <x-slot:empty>
            <x-empty icon="o-inbox" message="No career applications yet" description="Check back soon for updates!" />
        </x-slot>
    </x-table>

    <!-- Filter Drawer -->
    <x-drawer wire:model="filterDrawer" title="Export Career Applications" right class="w-11/12 lg:w-1/3">
        @php
            $rangeConfig = ['mode' => 'range'];
        @endphp

        <div class="space-y-4">
            <x-datepicker label="Select Date Range" wire:model="dateRange" icon="o-calendar" :config="$rangeConfig" inline />
        </div>

        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.filterDrawer = false" />
            <x-button label="Filter" icon="o-funnel" class="btn-primary" wire:click="applyFilter" spinner />
            <x-button label="Export" icon="o-arrow-down-tray" class="btn-primary" wire:click="exportData" spinner />
        </x-slot:actions>
    </x-drawer>
</div>
