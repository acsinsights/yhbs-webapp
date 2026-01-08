<div>
    @if ($showModal)
        <style>
            @keyframes spin {
                from {
                    transform: rotate(0deg);
                }

                to {
                    transform: rotate(360deg);
                }
            }
        </style>
        <div id="unifiedAuthModal"
            style="display: flex; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
            <div
                style="background: white; border-radius: 15px; padding: 40px; max-width: 450px; width: 90%; position: relative; box-shadow: 0 10px 50px rgba(0,0,0,0.3);">

                <div id="emailStep">
                    <h3 style="margin: 0 0 10px 0; color: #1a1a1a; font-size: 24px;">Welcome!</h3>
                    <p style="margin: 0 0 25px 0; color: #666;">Please enter your email to continue with checkout</p>

                    <form wire:submit.prevent="checkEmailAndProceed">
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 8px; color: #333; font-weight: 500;">Email
                                Address</label>
                            <input type="email" wire:model="email" required
                                style="width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 15px; transition: border-color 0.3s;"
                                onfocus="this.style.borderColor='#136497'" onblur="this.style.borderColor='#e0e0e0'"
                                placeholder="Enter your email">
                            @error('email')
                                <div style="color: #dc3545; margin-top: 8px; font-size: 14px;">{{ $message }}</div>
                            @enderror
                        </div>

                        @if ($error)
                            <div style="color: #dc3545; margin-bottom: 15px; font-size: 14px;">
                                {{ $error }}
                            </div>
                        @endif

                        <button type="submit"
                            style="width: 100%; padding: 14px; background: #136497; color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: background 0.3s;"
                            onmouseover="this.style.background='#0d4d75'" onmouseout="this.style.background='#136497'">
                            <span wire:loading.remove>Continue</span>
                            <span wire:loading>
                                <i class="bi bi-arrow-repeat" style="animation: spin 1s linear infinite;"></i>
                                Processing...
                            </span>
                        </button>
                    </form>
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
    @endif
</div>
