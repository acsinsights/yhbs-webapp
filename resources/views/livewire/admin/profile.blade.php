<?php

use Mary\Traits\Toast;
use Illuminate\View\View;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

new class extends Component {
    use WithFileUploads, Toast;

    #[Title('Edit Profile')]
    #[Url]
    public $name;
    public $email;
    public $phone;
    public $password;
    public $image;
    public $user;
    public $config = ['aspectRatio' => 1];

    public function mount()
    {
        $this->user = User::findOrFail(auth()->id());
        $this->name = $this->user->name;
        $this->email = $this->user->email;
        $this->phone = $this->user->phone_1;
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'nullable',
            'email' => 'nullable',
            'phone' => 'nullable',
            'password' => 'nullable|min:8',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:1024',
        ]);

        $this->user->name = $this->name;
        $this->user->email = $this->email;
        $this->user->phone_1 = $this->phone;

        if ($this->password) {
            $this->user->password = bcrypt($this->password);
        }

        if ($this->image) {
            if ($this->user->image) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $this->user->image));
            }

            $url = $this->image->store('users', 'public');
            $this->user->image = "/storage/$url";
        }

        $this->user->save();
        $this->success('Profile updated successfully.');
    }

    public function delete()
    {
        if ($this->user->image) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $this->user->image));
        }
        $this->user->delete();
        $this->success('Profile deleted.', redirectTo: route('admin.profile'));
    }
};
?>

@section('cdn')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" />
@endsection

<div>
    <div class="breadcrumbs text-sm">
        <h1 class="text-2xl font-bold mb-2">Edit Profile</h1>
        <ul>
            <li>
                <a href="{{ route('admin.index') }}" wire:navigate>
                    Dashboard
                </a>
            </li>
            <li>
                Edit Profile
            </li>
        </ul>
    </div>

    <div class="grid grid-cols-1 mt-6 gap-6 xl:grid-cols-2">
        <x-form wire:submit="save">
            <div class="flex gap-8 justify-between">
                <div class="w-full space-y-3">
                    <x-input label="Name" wire:model="name" />
                    <x-input label="Email" wire:model="email" />
                    <x-input label="Phone" wire:model="phone" />
                    <x-password label="Password" wire:model="password" right />
                </div>
            </div>

            <x-file label="Avatar" wire:model="image" accept="image" crop-after-change :crop-config="$config">
                <div class="mt-1">
                    <img id="imagePreview" src="{{ $user->image ?: 'https://placehold.co/300' }}"
                        class="h-40 rounded-lg" alt="Avatar Preview">
                </div>
            </x-file>

            <x-slot:actions>
                <div class="w-full flex justify-between">
                    <x-button label="Update" class="btn-primary" type="submit" spinner="save" />
                </div>
            </x-slot:actions>
        </x-form>
    </div>
</div>
