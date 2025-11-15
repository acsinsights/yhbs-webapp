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
