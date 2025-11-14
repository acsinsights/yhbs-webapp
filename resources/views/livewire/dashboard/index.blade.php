<?php

use App\Enums\RolesEnum;
use Illuminate\View\View;
use Livewire\Volt\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    #[Title('Dashboard')]
    public $user;

    public function mount(): void
    {
        $this->user = auth()->user();
    }

    public function rendering(View $view)
    {
        $roleName = $this->user->role?->label() ?? 'User';
        $view->title('Dashboard - ' . $roleName);
    }
};

?>
<div>
    @if ($user->role === RolesEnum::ADMIN)
        <livewire:dashboard.admin />
    @endif

    @if ($user->role === RolesEnum::RECEPTION)
        <livewire:dashboard.reception />
    @endif
</div>
