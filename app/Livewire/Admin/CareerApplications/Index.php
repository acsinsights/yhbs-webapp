<?php

namespace App\Livewire\Admin\CareerApplications;

use Livewire\Component;
use App\Models\CareerApplication;
use Livewire\WithPagination;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Storage;

class Index extends Component
{
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
        $this->headers = [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'email', 'label' => 'Email'],
            ['key' => 'phone', 'label' => 'Phone'],
            ['key' => 'position', 'label' => 'Position'],
            ['key' => 'created_at', 'label' => 'Applied On']
        ];
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
                $query->whereDate('created_at', '>=', $dates[0])
                    ->whereDate('created_at', '<=', $dates[1]);
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
                $query->whereDate('created_at', '>=', $dates[0])
                    ->whereDate('created_at', '<=', $dates[1]);
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
                    fputcsv($handle, [
                        $application->id,
                        $application->name,
                        $application->email,
                        $application->phone,
                        $application->position,
                        $application->experience ?? 'N/A',
                        $application->cover_letter ?? 'N/A',
                        $application->created_at->format('d M Y, h:i A')
                    ]);
                }

                fclose($handle);
            },
            $filename,
            [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }

    public function delete($id)
    {
        $application = CareerApplication::find($id);

        // Delete resume file if exists
        if ($application->resume && Storage::disk('public')->exists($application->resume)) {
            Storage::disk('public')->delete($application->resume);
        }

        $application->delete();
        $this->success('Application deleted successfully');
    }

    public function render()
    {
        return view('livewire.admin.career-applications.index');
    }
}
