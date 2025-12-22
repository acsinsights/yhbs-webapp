<?php

use Livewire\Volt\Component;
use App\Models\Contact;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;
    
    #[Title('View Contact')]
    public Contact $contact;
    
    public function mount($id)
    {
        $this->contact = Contact::findOrFail($id);
        
        if (!$this->contact->is_read) {
            $this->contact->is_read = true;
            $this->contact->save();
        }
    }

    public function delete()
    {
        $this->contact->delete();
        $this->success('Contact deleted successfully', redirectTo: route('admin.contacts.index'));
    }
};
?>

<div>
    <div class="breadcrumbs mb-4 text-sm">
        <h1 class="text-2xl font-bold mb-2">
            Contact Details
        </h1>
        <ul>
            <li><a href="{{ route('admin.index') }}" wire:navigate>Dashboard</a></li>
            <li><a href="{{ route('admin.contacts.index') }}" wire:navigate>Contact Submissions</a></li>
            <li>View</li>
        </ul>
    </div>
    <hr class="mb-5">
    
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Full Name</span>
                    </label>
                    <p class="text-lg">{{ $contact->full_name }}</p>
                </div>
                
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Email</span>
                    </label>
                    <p class="text-lg">
                        <a href="mailto:{{ $contact->email }}" class="link link-primary">
                            {{ $contact->email }}
                        </a>
                    </p>
                </div>
                
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Phone</span>
                    </label>
                    <p class="text-lg">
                        <a href="tel:{{ $contact->phone }}" class="link link-primary">
                            {{ $contact->phone }}
                        </a>
                    </p>
                </div>
                
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Submitted At</span>
                    </label>
                    <p class="text-lg">{{ $contact->created_at->format('d M Y, h:i A') }}</p>
                </div>
            </div>
            
            <div class="divider"></div>
            
            <div>
                <label class="label">
                    <span class="label-text font-semibold">Message</span>
                </label>
                <div class="bg-base-200 p-4 rounded-lg">
                    <p class="whitespace-pre-wrap">{{ $contact->message }}</p>
                </div>
            </div>
            
            <div class="card-actions justify-end mt-6">
                <x-button label="Back" link="{{ route('admin.contacts.index') }}" />
                <x-button label="Delete" class="btn-error" wire:click="delete" wire:confirm="Are you sure you want to delete this contact?" spinner />
            </div>
        </div>
    </div>
</div>
