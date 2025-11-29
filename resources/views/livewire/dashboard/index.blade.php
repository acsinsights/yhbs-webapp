<?php

use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;
use App\Enums\RolesEnum;

new class extends Component {
    #[Title('Dashboard')]
    public $user;

    public function mount(): void
    {
        $this->user = auth()->user();
    }

    public function rendering(View $view)
    {
        $roleName = $this->user->roles->first()->name;
        $view->title('Dashboard - ' . $roleName);
    }
};

?>
<div>
    @role('admin|superadmin')
        <livewire:dashboard.admin />
    @endrole

    @role('reception')
        <livewire:dashboard.reception />
    @endrole
</div>
