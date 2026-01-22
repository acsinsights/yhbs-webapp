<?php

use Livewire\Volt\Component;
use App\Models\CareerApplication;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;

    #[Title('View Career Application')]
    public CareerApplication $application;

    public function mount($id)
    {
        $this->application = CareerApplication::findOrFail($id);
    }

    public function delete()
    {
        // Delete resume file if exists
        if ($this->application->resume && \Storage::disk('public')->exists($this->application->resume)) {
            \Storage::disk('public')->delete($this->application->resume);
        }

        $this->application->delete();
        $this->success('Application deleted successfully', redirectTo: route('admin.career-applications.index'));
    }
};
?>

<div>
    <div class="breadcrumbs mb-4 text-sm">
        <h1 class="text-2xl font-bold mb-2">
            Career Application Details
        </h1>
        <ul>
            <li><a href="{{ route('admin.index') }}" wire:navigate>Dashboard</a></li>
            <li><a href="{{ route('admin.career-applications.index') }}" wire:navigate>Career Applications</a></li>
            <li>View</li>
        </ul>
    </div>
    <hr class="mb-5">

    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Name</span>
                    </label>
                    <p class="text-lg">{{ $application->name }}</p>
                </div>

                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Email</span>
                    </label>
                    <p class="text-lg">
                        <a href="mailto:{{ $application->email }}" class="link link-primary">
                            {{ $application->email }}
                        </a>
                    </p>
                </div>

                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Phone</span>
                    </label>
                    <p class="text-lg">
                        <a href="tel:{{ $application->phone }}" class="link link-primary">
                            {{ $application->phone }}
                        </a>
                    </p>
                </div>

                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Position</span>
                    </label>
                    <p class="text-lg">{{ $application->position }}</p>
                </div>

                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Experience</span>
                    </label>
                    <p class="text-lg">{{ $application->experience ?? 'N/A' }}</p>
                </div>

                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Applied At</span>
                    </label>
                    <p class="text-lg">{{ $application->created_at->format('d M Y, h:i A') }}</p>
                </div>
            </div>

            @if ($application->resume)
                <div class="mb-6">
                    <label class="label">
                        <span class="label-text font-semibold">Resume</span>
                    </label>
                    <div class="flex gap-2">
                        <x-button label="Download Resume" icon="o-arrow-down-tray"
                            link="{{ Storage::url($application->resume) }}" external class="btn-primary" />
                    </div>
                </div>
            @endif

            <div class="divider"></div>

            @if ($application->cover_letter)
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Cover Letter</span>
                    </label>
                    <div class="bg-base-200 p-4 rounded-lg">
                        <p class="whitespace-pre-wrap">{{ $application->cover_letter }}</p>
                    </div>
                </div>
            @endif

            <div class="card-actions justify-end mt-6">
                <x-button label="Back" link="{{ route('admin.career-applications.index') }}" />
                <x-button label="Delete" class="btn-error" wire:click="delete"
                    wire:confirm="Are you sure you want to delete this application?" spinner />
            </div>
        </div>
    </div>
</div>
