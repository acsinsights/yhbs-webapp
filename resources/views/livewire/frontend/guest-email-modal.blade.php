<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h3 class="mb-2">Welcome!</h3>
                    <p class="text-muted mb-4">Please enter your email to continue with checkout</p>

                    <form wire:submit.prevent="checkEmailAndProceed">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email Address</label>
                            <input type="email" wire:model="email" required class="form-control"
                                placeholder="Enter your email">
                            @error('email')
                                <div class="text-danger mt-2 small">{{ $message }}</div>
                            @enderror
                        </div>

                        @if ($error)
                            <div class="alert alert-danger">
                                {{ $error }}
                            </div>
                        @endif

                        <button type="submit" class="btn btn-primary w-100" wire:loading.attr="disabled">
                            <span wire:loading.remove>Continue</span>
                            <span wire:loading>
                                <span class="spinner-border spinner-border-sm me-2" role="status"
                                    aria-hidden="true"></span>
                                Processing...
                            </span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('redirectToAuth', (event) => {
                window.location.href = event.url;
            });
        });
    </script>
</div>
