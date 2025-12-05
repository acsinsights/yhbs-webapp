@extends('frontend.layouts.app')
@section('content')

<section style="padding: 60px 0; background:#f7f9fc;">
    <div class="container" style="max-width: 900px;">

        <h1 style="font-size:32px; font-weight:700;">Terms & Conditions</h1>
        <p style="color:#555; font-size:15px;">
            These Terms & Conditions govern your use of IKARUS United Marine Services (“IKARUS”, “we”, “us”, “our”).
            By booking any service, using our website, or visiting our locations, you agree to these terms.
        </p>

        <h3 style="font-size:20px; font-weight:600; margin-top:30px;">1. Booking & Reservations</h3>
        <ul style="color:#555; font-size:15px; padding-left:20px;">
            <li>All bookings for cruises, island trips, and marine services must be confirmed through our official channels.</li>
            <li>Customers must provide accurate personal details at the time of booking.</li>
            <li>IKARUS reserves the right to accept or reject any booking request.</li>
        </ul>

        <h3 style="font-size:20px; font-weight:600; margin-top:30px;">2. Payment Terms</h3>
        <ul style="color:#555; font-size:15px; padding-left:20px;">
            <li>Full or partial payment may be required at the time of reservation.</li>
            <li>All payments must be made in approved payment methods.</li>
            <li>Prices may vary depending on season, availability, and service type.</li>
        </ul>

        <h3 style="font-size:20px; font-weight:600; margin-top:30px;">3. Cancellations & Refund Policy</h3>
        <ul style="color:#555; font-size:15px; padding-left:20px;">
            <li>Cancellations must be made within the allowed time stated at the time of booking.</li>
            <li>Refund eligibility depends on service type and cancellation window.</li>
            <li>IKARUS may charge a cancellation fee for late cancellations or no-show customers.</li>
            <li>Weather-related cancellations may be rescheduled when possible.</li>
        </ul>

        <h3 style="font-size:20px; font-weight:600; margin-top:30px;">4. Safety & Marine Regulations</h3>
        <ul style="color:#555; font-size:15px; padding-left:20px;">
            <li>All passengers must follow safety instructions from the crew.</li>
            <li>Life jackets must be worn when instructed.</li>
            <li>Dangerous items or behavior that risk safety are strictly prohibited.</li>
            <li>IKARUS follows all Kuwait marine laws and operational standards.</li>
        </ul>

        <h3 style="font-size:20px; font-weight:600; margin-top:30px;">5. Customer Responsibilities</h3>
        <ul style="color:#555; font-size:15px; padding-left:20px;">
            <li>Arrive on time for marine trips, cruises, or island transfers.</li>
            <li>Provide valid identification when required.</li>
            <li>Respect the heritage village, island property, and other guests.</li>
            <li>Report any safety or medical issues to the crew immediately.</li>
        </ul>

        <h3 style="font-size:20px; font-weight:600; margin-top:30px;">6. Property & Equipment Damage</h3>
        <ul style="color:#555; font-size:15px; padding-left:20px;">
            <li>Damage caused by negligence or misconduct may result in additional charges.</li>
            <li>Guests are responsible for personal belongings during the trip.</li>
        </ul>

        <h3 style="font-size:20px; font-weight:600; margin-top:30px;">7. Website Usage Rules</h3>
        <ul style="color:#555; font-size:15px; padding-left:20px;">
            <li>You agree not to misuse the website or attempt unauthorized access.</li>
            <li>All content, photos, and materials belong to IKARUS and cannot be reused without permission.</li>
        </ul>

        <h3 style="font-size:20px; font-weight:600; margin-top:30px;">8. Changes to Services</h3>
        <p style="color:#555; font-size:15px;">
            IKARUS reserves the right to update service offerings, prices, schedules, or policies
            at any time for safety or operational requirements.
        </p>

        <h3 style="font-size:20px; font-weight:600; margin-top:30px;">9. Limitation of Liability</h3>
        <p style="color:#555; font-size:15px;">
            IKARUS is not responsible for delays, weather disruptions, or incidents beyond our control.
            Marine travel involves natural risks, and customers participate at their own responsibility.
        </p>

        <h3 style="font-size:20px; font-weight:600; margin-top:30px;">10. Contact Information</h3>
        <p style="color:#555; font-size:15px;">
            For questions regarding these Terms & Conditions:
            <br><strong>IKARUS United Marine Services</strong><br>
            Email: <a href="mailto:info@ikarusmarine.com">info@ikarusmarine.com</a><br>
            Phone: +965 180 8080
        </p>

        <p style="color:#888; font-size:13px; margin-top:25px;">
            Last Updated: {{ now()->format('F d, Y') }}
        </p>

    </div>
</section>

@endsection
