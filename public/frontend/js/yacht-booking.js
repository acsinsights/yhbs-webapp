/**
 * Yacht Booking Date Picker
 */
document.addEventListener('DOMContentLoaded', function () {
    const dateRangeInput = document.getElementById('yachtDateRangePicker');
    const startInput = document.getElementById('startDate');
    const endInput = document.getElementById('endDate');
    const guestsInput = document.getElementById('guestsInput');
    const crewInput = document.getElementById('crewInput');
    const capacityAlert = document.getElementById('yachtCapacityAlert');
    const availabilityMessage = document.getElementById('yachtAvailabilityMessage');
    const availabilityText = document.getElementById('yachtAvailabilityText');
    const form = document.getElementById('yachtBookingForm');

    if (!dateRangeInput || !startInput || !endInput) {
        return; // Exit if elements don't exist
    }

    const maxGuests = parseInt(guestsInput?.getAttribute('max')) || 1;
    const maxCrew = parseInt(crewInput?.getAttribute('max')) || 0;
    const maxCapacity = parseInt(form?.getAttribute('data-max-capacity')) || 999;

    // Get booked dates from data attribute
    const bookedDates = JSON.parse(dateRangeInput.getAttribute('data-booked-dates') || '[]');
    let dateRangePicker;

    // Initialize Flatpickr range date picker
    function initializeDatePicker() {
        dateRangePicker = flatpickr(dateRangeInput, {
            mode: 'range',
            minDate: 'today',
            dateFormat: 'd-m-Y',
            altInput: true,
            altFormat: 'd-m-Y',
            locale: {
                rangeSeparator: ' to '
            },
            onDayCreate: function (dObj, dStr, fp, dayElem) {
                // Highlight booked dates in red
                const dateStr = flatpickr.formatDate(dayElem.dateObj, 'Y-m-d');
                if (bookedDates.includes(dateStr)) {
                    dayElem.classList.add('booked-date');
                    dayElem.setAttribute('title', 'Already Booked - Not Available');
                }
            },
            disable: [
                function (date) {
                    const dateStr = flatpickr.formatDate(date, 'Y-m-d');
                    return bookedDates.includes(dateStr);
                }
            ],
            onChange: function (selectedDates, dateStr) {
                if (selectedDates.length === 2) {
                    const startDate = selectedDates[0];
                    const endDate = selectedDates[1];

                    // Format dates
                    const startFormatted = flatpickr.formatDate(startDate, 'Y-m-d');
                    const endFormatted = flatpickr.formatDate(endDate, 'Y-m-d');

                    // Set hidden inputs
                    startInput.value = startFormatted;
                    endInput.value = endFormatted;

                    // Check if any booked date falls within the selected range
                    const hasConflict = checkDateRangeConflict(startDate, endDate);

                    if (hasConflict) {
                        availabilityMessage.classList.remove('d-none', 'alert-success');
                        availabilityMessage.classList.add('alert-danger');
                        availabilityText.textContent = 'Selected date range includes booked dates. Please select different dates.';
                        // Clear the selection
                        dateRangePicker.clear();
                        startInput.value = '';
                        endInput.value = '';
                    } else {
                        availabilityMessage.classList.remove('d-none', 'alert-danger');
                        availabilityMessage.classList.add('alert-success');
                        availabilityText.textContent = 'Yacht is available for selected dates!';
                    }
                } else if (selectedDates.length === 0) {
                    // Clear hidden inputs
                    startInput.value = '';
                    endInput.value = '';
                    availabilityMessage.classList.add('d-none');
                }
            },
            onReady: function (dateObj, dateStr, instance) {
                instance.calendarContainer.style.zIndex = 9999;
            }
        });
    }

    // Check if selected date range has any conflict with booked dates
    function checkDateRangeConflict(startDate, endDate) {
        for (let bookedDateStr of bookedDates) {
            const bookedDate = new Date(bookedDateStr);
            // Check if booked date falls within the selected range (excluding the end day)
            if (bookedDate >= startDate && bookedDate < endDate) {
                return true;
            }
        }
        return false;
    }

    // Validate capacity
    function validateCapacity() {
        if (!guestsInput) return true;

        const guests = parseInt(guestsInput.value) || 0;
        const crew = crewInput ? (parseInt(crewInput.value) || 0) : 0;
        const total = guests + crew;

        if (guests > maxGuests || crew > maxCrew || total > maxCapacity) {
            capacityAlert?.classList.remove('d-none');
            return false;
        } else {
            capacityAlert?.classList.add('d-none');
            return true;
        }
    }

    if (guestsInput) guestsInput.addEventListener('input', validateCapacity);
    if (crewInput) crewInput.addEventListener('input', validateCapacity);

    if (form) {
        form.addEventListener('submit', function (e) {
            if (!validateCapacity()) {
                e.preventDefault();
                alert('Please ensure guest and crew numbers are within the allowed capacity.');
                return;
            }

            if (availabilityMessage && availabilityMessage.classList.contains('alert-danger')) {
                e.preventDefault();
                alert('This yacht is not available for the selected dates.');
            }
        });
    }

    // Initialize on load
    initializeDatePicker();
});
