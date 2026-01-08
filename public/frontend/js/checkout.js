// Checkout Page JavaScript - Guest Management Only
// Email modal is now handled by Livewire component (GuestEmailModal)

// Initialize guest management functionality
function initGuestManagement(oldGuests, maxGuests = 10) {
    let guestCount = 1;
    const container = document.getElementById('guestDetailsContainer');
    const addGuestBtn = document.getElementById('addGuestBtn');

    if (!container || !addGuestBtn) {
        console.error('Guest management elements not found');
        return;
    }

    // Function to update button state
    function updateAddButtonState() {
        const currentGuestCount = document.querySelectorAll('.guest-detail-item').length;
        if (currentGuestCount >= maxGuests) {
            addGuestBtn.disabled = true;
            addGuestBtn.innerHTML = '<i class="bi bi-info-circle me-2"></i>Maximum ' + maxGuests + ' guests allowed';
            addGuestBtn.classList.add('disabled');
        } else {
            addGuestBtn.disabled = false;
            addGuestBtn.innerHTML = '<i class="bi bi-plus-circle me-2"></i>Add Another Guest';
            addGuestBtn.classList.remove('disabled');
        }
    }

    // Restore old guest data if validation failed
    if (oldGuests && oldGuests.length > 0) {
        // Add additional guests that were previously entered
        for (let i = 1; i < oldGuests.length; i++) {
            const guest = oldGuests[i];
            const guestHtml = `
                <div class="guest-detail-item mb-4 pb-3 border-bottom" data-guest-index="${i}">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Guest ${i + 1}</h6>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-guest-btn">
                            <i class="bi bi-trash"></i> Remove
                        </button>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Full Name *</label>
                            <input type="text" class="form-control ${oldGuests[i].name ? '' : 'is-invalid'}"
                                name="guests[${i}][name]" required
                                value="${guest.name || ''}"
                                placeholder="Enter guest full name">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email <small class="text-muted">(Optional)</small></label>
                            <input type="email" class="form-control"
                                name="guests[${i}][email]"
                                value="${guest.email || ''}"
                                placeholder="Enter guest email">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone Number <small class="text-muted">(Optional)</small></label>
                            <input type="tel" class="form-control"
                                name="guests[${i}][phone]"
                                value="${guest.phone || ''}"
                                placeholder="Enter guest phone">
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', guestHtml);
            guestCount++;
        }
        updateAddButtonState();
    }

    // Add guest button handler
    addGuestBtn.addEventListener('click', function () {
        const currentGuestCount = document.querySelectorAll('.guest-detail-item').length;

        // Check if max limit reached
        if (currentGuestCount >= maxGuests) {
            alert('You can only add up to ' + maxGuests + ' guests for this booking.');
            return;
        }

        const guestHtml = `
            <div class="guest-detail-item mb-4 pb-3 border-bottom" data-guest-index="${guestCount}">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Guest ${guestCount + 1}</h6>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-guest-btn">
                        <i class="bi bi-trash"></i> Remove
                    </button>
                </div>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Full Name *</label>
                        <input type="text" class="form-control" name="guests[${guestCount}][name]" required placeholder="Enter guest full name">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email <small class="text-muted">(Optional)</small></label>
                        <input type="email" class="form-control" name="guests[${guestCount}][email]" placeholder="Enter guest email">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Phone Number <small class="text-muted">(Optional)</small></label>
                        <input type="tel" class="form-control" name="guests[${guestCount}][phone]" placeholder="Enter guest phone">
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', guestHtml);
        guestCount++;
        updateAddButtonState();
    });

    // Remove guest (event delegation)
    container.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-guest-btn') || e.target.closest('.remove-guest-btn')) {
            const btn = e.target.classList.contains('remove-guest-btn') ? e.target : e.target.closest('.remove-guest-btn');
            const guestItem = btn.closest('.guest-detail-item');
            if (guestItem) {
                guestItem.remove();
                // Renumber remaining guests
                const guests = document.querySelectorAll('.guest-detail-item');
                guests.forEach((guest, index) => {
                    guest.querySelector('h6').textContent = index === 0 ? 'Guest 1 *' : `Guest ${index + 1}`;
                });
                updateAddButtonState();
            }
        }
    });

    // Initial button state check
    updateAddButtonState();
}
