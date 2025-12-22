<?php

use Livewire\Volt\Component;
use App\Models\Contact;
use Livewire\WithPagination;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;

new class extends Component {
    use WithPagination, Toast;
    
    #[Title('Contact Submissions')]
    public $headers;
    #[Url]
    public string $search = '';

    public $sortBy = ['column' => 'created_at', 'direction' => 'desc'];
    
    public function boot(): void
    {
        $this->headers = [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'], 
            ['key' => 'full_name', 'label' => 'Name'], 
            ['key' => 'email', 'label' => 'Email'],
            ['key' => 'phone', 'label' => 'Phone'],
            ['key' => 'created_at', 'label' => 'Submitted']
        ];
    }

    public function rendering(View $view): void
    {
        $query = Contact::orderBy(...array_values($this->sortBy));
        
        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('full_name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%")
                  ->orWhere('phone', 'like', "%{$this->search}%");
            });
        }
        
        $view->contacts = $query->paginate(20);
    }

    public function markAsRead($id)
    {
        $contact = Contact::find($id);
        $contact->is_read = true;
        $contact->save();
        $this->success('Marked as read');
    }

    public function delete($id)
    {
        Contact::find($id)->delete();
        $this->success('Contact deleted successfully');
    }
};
?>

<div>
    <div class="flex justify-between items-start lg:items-center flex-col lg:flex-row mt-3 mb-5 gap-2">
        <div>
            <h1 class="text-2xl font-bold mb-2">
                Contact Submissions
            </h1>
            <div class="breadcrumbs text-sm">
                <ul class="flex">
                    <li>
                        <a href="{{ route('admin.index') }}" wire:navigate>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        Contact Submissions
                    </li>
                </ul>
            </div>
        </div>
        <div class="flex gap-3">
            <x-input placeholder="Search ..." icon="o-magnifying-glass" wire:model.live.debounce="search" />
        </div>
    </div>
    <hr class="mb-5">
    <x-table :headers="$headers" :rows="$contacts" with-pagination :sort-by="$sortBy">
        @scope('cell_full_name', $contact)
            <div class="flex items-center gap-2">
                @if(!$contact->is_read)
                    <span class="badge badge-primary badge-xs"></span>
                @endif
                {{ $contact->full_name }}
            </div>
        @endscope
        @scope('cell_created_at', $contact)
            {{ $contact->created_at->format('d M Y, h:i A') }}
        @endscope
        @scope('actions', $contact)
            <div class="flex gap-1">
                <x-button icon="o-eye" link="{{ route('admin.contacts.show', $contact->id) }}" class="btn-xs btn-ghost" tooltip="View" />
                <x-button icon="o-trash" wire:click="delete({{ $contact->id }})" wire:confirm="Are you sure?" class="btn-xs btn-ghost text-error" tooltip="Delete" spinner />
            </div>
        @endscope
        <x-slot:empty>
            <x-empty icon="o-inbox" message="No contact submissions found" />
        </x-slot>
    </x-table>
</div>
