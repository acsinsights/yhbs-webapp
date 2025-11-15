<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    use Toast;
    #[Layout('components.layouts.empty')]
    #[Title('Login')]
    #[Rule('required|email')]
    public string $email = '';

    #[Rule('required')]
    public string $password = '';

    public function mount()
    {
        // It is logged in
        if (Auth::check()) {
            $this->success('You are already logged in.', redirectTo: route('admin.index'));
        }
    }
    public function login()
    {
        $credentials = $this->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            request()->session()->regenerate();
            $intended = session('url.intended', route('admin.index'));
            $this->success('You are logged in.', redirectTo: $intended);
            return;
        }

        $this->addError('email', 'The provided credentials do not match our records.');
    }
};
?>
<div>
    <div class="grid md:grid-cols-2">
        <!-- Left Side - Image -->
        <div class="hidden md:flex bg-base-300 items-center justify-center p-8">
            <div class="w-full max-w-lg">
                <img src="{{ asset('auth/login-cover.svg') }}" class="w-full h-auto object-contain animate-fade-in"
                    alt="Login Illustration">
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="flex flex-col items-center justify-center p-6 md:p-12 h-full bg-base-200">
            <div class="w-full max-w-md">
                <!-- Logo Text -->
                <div class="text-center mb-8">
                    <h1 class="text-4xl md:text-5xl font-bold bg-clip-text text-transparent mb-2">
                        {{ config('app.name', 'YHBS') }}
                    </h1>
                    <div class="w-20 h-1 mx-auto rounded-full"></div>
                </div>

                <!-- Welcome Text -->
                <div class="text-center mb-8">
                    <h2 class="text-2xl md:text-3xl font-semibold text-base-content mb-3">
                        Welcome Back
                    </h2>
                    <p class="text-base-content/70 text-sm md:text-base">
                        Seamless Access, Secure Connection.
                        <br>
                        Your Way to a Personalized Experience.
                    </p>
                </div>

                <!-- Login Form -->
                <div class="bg-base-100 rounded-2xl p-6 md:p-8 shadow-lg">
                    @if (session('error'))
                        <x-alert title="Error!" description="{!! session('error') !!}" dismissible
                            class="mb-6 alert-error" />
                    @endif

                    <x-form wire:submit="login">
                        <div class="space-y-5">
                            <x-input label="E-mail" type="email" wire:model="email" icon="o-envelope" class="input-lg"
                                placeholder="Enter your email address" />

                            <x-password label="Password" wire:model="password" icon="o-lock-closed" right
                                class="input-lg" placeholder="Enter your password" />
                        </div>

                        <x-slot:actions>
                            <x-button label="Sign In" type="submit" icon="o-paper-airplane"
                                class="btn-primary btn-lg w-full md:w-auto px-8" spinner="login" />
                        </x-slot:actions>
                    </x-form>
                </div>

                <!-- Footer Text -->
                <div class="text-center mt-6">
                    <p class="text-xs text-base-content/60">
                        © {{ date('Y') }} {{ config('app.name', 'YHBS') }}. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
