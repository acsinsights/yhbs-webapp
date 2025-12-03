/**
 * Room Booking Date Picker
 */
document.addEventListener('DOMContentLoaded', function () {
    const dateRangeInput = document.getElementById('dateRangePicker');
    const checkInInput = document.getElementById('checkInDate');
    const checkOutInput = document.getElementById('checkOutDate');
    const adultsInput = document.getElementById('adultsInput');
    const childrenInput = document.getElementById('childrenInput');
    const capacityAlert = document.getElementById('capacityAlert');
    const availabilityMessage = document.getElementById('availabilityMessage');
    const availabilityText = document.getElementById('availabilityText');
    const form = document.getElementById('roomBookingForm');

    if (!dateRangeInput || !checkInInput || !checkOutInput) {
        return; // Exit if elements don't exist
    }

    const maxAdults = parseInt(adultsInput?.getAttribute('max')) || 1;
    const maxChildren = parseInt(childrenInput?.getAttribute('max')) || 0;

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
                    const checkIn = selectedDates[0];
                    const checkOut = selectedDates[1];

                    // Format dates
                    const checkInFormatted = flatpickr.formatDate(checkIn, 'Y-m-d');
                    const checkOutFormatted = flatpickr.formatDate(checkOut, 'Y-m-d');

                    // Set hidden inputs
                    checkInInput.value = checkInFormatted;
                    checkOutInput.value = checkOutFormatted;

                    // Check if any booked date falls within the selected range
                    const hasConflict = checkDateRangeConflict(checkIn, checkOut);

                    if (hasConflict) {
                        availabilityMessage.classList.remove('d-none', 'alert-success');
                        availabilityMessage.classList.add('alert-danger');
                        availabilityText.textContent = 'Selected date range includes booked dates. Please select different dates.';
                        // Clear the selection
                        dateRangePicker.clear();
                        checkInInput.value = '';
                        checkOutInput.value = '';
                    } else {
                        availabilityMessage.classList.remove('d-none', 'alert-danger');
                        availabilityMessage.classList.add('alert-success');
                        availabilityText.textContent = 'Room is available for selected dates!';
                    }
                } else if (selectedDates.length === 0) {
                    // Clear hidden inputs
                    checkInInput.value = '';
                    checkOutInput.value = '';
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
            // Check if booked date falls within the selected range (excluding the checkout day)
            if (bookedDate >= startDate && bookedDate < endDate) {
                return true;
            }
        }
        return false;
    }

    // Validate guest capacity
    function validateCapacity() {
        if (!adultsInput || !childrenInput) return true;

        const adults = parseInt(adultsInput.value) || 0;
        const children = parseInt(childrenInput.value) || 0;

        if (adults > maxAdults || children > maxChildren) {
            capacityAlert?.classList.remove('d-none');
            return false;
        } else {
            capacityAlert?.classList.add('d-none');
            return true;
        }
    }

    if (adultsInput) adultsInput.addEventListener('input', validateCapacity);
    if (childrenInput) childrenInput.addEventListener('input', validateCapacity);

    if (form) {
        form.addEventListener('submit', function (e) {
            if (!validateCapacity()) {
                e.preventDefault();
                alert('Please ensure guest numbers are within the allowed capacity.');
                return;
            }

            // Additional validation for availability
            if (availabilityMessage && availabilityMessage.classList.contains('alert-danger')) {
                e.preventDefault();
                alert('This room is not available for the selected dates.');
            }
        });
    }

    // Initialize on load
    initializeDatePicker();
});
