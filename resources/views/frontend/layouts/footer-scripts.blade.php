<!--  Main jQuery  -->
<script src="{{ asset('frontend/js/jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('frontend/js/jquery-ui.js') }}"></script>
<script src="{{ asset('frontend/js/moment.min.js') }}"></script>
<script src="{{ asset('frontend/js/daterangepicker.min.js') }}"></script>
<!-- Popper and Bootstrap JS -->
<script src="{{ asset('frontend/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('frontend/js/popper.min.js') }}"></script>
<!-- Swiper slider JS -->
<script src="{{ asset('frontend/js/swiper-bundle.min.js') }}"></script>
<script src="{{ asset('frontend/js/slick.js') }}"></script>
<!-- Waypoints JS -->
<script src="{{ asset('frontend/js/waypoints.min.js') }}"></script>
<!-- Counterup JS -->
<script src="{{ asset('frontend/js/jquery.counterup.min.js') }}"></script>
<!-- Wow JS -->
<script src="{{ asset('frontend/js/wow.min.js') }}"></script>
<!-- Gsap  JS -->
<script src="{{ asset('frontend/js/gsap.min.js') }}"></script>
<script src="{{ asset('frontend/js/ScrollTrigger.min.js') }}"></script>
<script src="{{ asset('frontend/js/jquery.fancybox.min.js') }}"></script>
<!-- Custom JS -->
<script src="{{ asset('frontend/js/select-dropdown.js') }}"></script>
<script src="{{ asset('frontend/js/custom.js') }}"></script>
<!-- Customer Panel JS -->
<script src="{{ asset('frontend/js/customer-panel.js') }}"></script>

<!-- International Phone Input -->
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.4/build/js/intlTelInput.min.js"></script>
<script>
    // Function to initialize phone input
    function initializePhoneInput(input) {
        // Skip if already initialized
        if (input.itiInstance) {
            return;
        }

        const iti = window.intlTelInput(input, {
            initialCountry: "kw", // Kuwait as default
            preferredCountries: ["kw", "ae", "sa", "in", "us"],
            separateDialCode: true,
            utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.4/build/js/utils.js",
            autoPlaceholder: "aggressive",
            formatOnDisplay: true,
            nationalMode: false,
            useFullscreenPopup: false, // Disable fullscreen popup on mobile
            customPlaceholder: function(selectedCountryPlaceholder, selectedCountryData) {
                return selectedCountryPlaceholder.replace(/[0-9]/g, "X");
            }
        });

        // Store the instance on the input element
        input.itiInstance = iti;

        // On form submit, set the full international number
        const form = input.closest('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (iti.isValidNumber()) {
                    input.value = iti.getNumber();
                } else {
                    e.preventDefault();
                    const errorCode = iti.getValidationError();
                    let errorMsg = "Invalid phone number";

                    switch (errorCode) {
                        case 1:
                            errorMsg = "Invalid country code";
                            break;
                        case 2:
                            errorMsg = "Phone number too short";
                            break;
                        case 3:
                            errorMsg = "Phone number too long";
                            break;
                        case 4:
                            errorMsg = "Invalid phone number";
                            break;
                    }

                    // Show error near input
                    let errorDiv = input.nextElementSibling;
                    if (!errorDiv || !errorDiv.classList.contains('iti-error')) {
                        errorDiv = document.createElement('div');
                        errorDiv.className = 'iti-error invalid-feedback d-block';
                        input.parentNode.insertBefore(errorDiv, input.nextSibling);
                    }
                    errorDiv.textContent = errorMsg;
                    input.classList.add('is-invalid');

                    return false;
                }
            });
        }

        // Clear error on input
        input.addEventListener('input', function() {
            input.classList.remove('is-invalid');
            const errorDiv = input.nextElementSibling;
            if (errorDiv && errorDiv.classList.contains('iti-error')) {
                errorDiv.remove();
            }
        });

        // Validate on blur
        input.addEventListener('blur', function() {
            if (input.value.trim() !== '' && !iti.isValidNumber()) {
                input.classList.add('is-invalid');
            }
        });
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize all phone fields (account phone + guest phones)
        const phoneInputs = document.querySelectorAll(
            'input[type="tel"][name="phone"], input[type="tel"].guest-phone');

        phoneInputs.forEach(function(input) {
            initializePhoneInput(input);
        });

        // Watch for dynamically added guest fields
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) { // Element node
                        // Check if the added node contains phone inputs
                        const phoneInputs = node.querySelectorAll(
                            'input[type="tel"].guest-phone');
                        phoneInputs.forEach(function(input) {
                            initializePhoneInput(input);
                        });
                    }
                });
            });
        });

        // Observe the guest container for new guests
        const guestContainer = document.getElementById('guestContainer') || document.querySelector(
            '.guest-information');
        if (guestContainer) {
            observer.observe(guestContainer, {
                childList: true,
                subtree: true
            });
        }
    });
</script>
