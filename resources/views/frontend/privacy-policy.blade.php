@extends('frontend.layouts.app')
@section('content')
    <section style="padding: 60px 0; background:#f7f9fc;">
        <div class="container">

            <p style="color:#555; font-size:15px; margin-bottom:25px;">
                This Privacy Policy explains how IKARUS United Marine Services (“IKARUS”, “we”, “us”, or “our”)
                collects, uses, and protects your personal information when you visit our website, use our
                services, or contact us.
            </p>

            <h3 style="font-size:20px; font-weight:600; margin-top:30px;">1. Information We Collect</h3>
            <p style="color:#555; font-size:15px;">
                We may collect the following types of information:
            </p>
            <ul style="color:#555; font-size:15px; padding-left:20px;">
                <li>Contact details such as your name, email address, and phone number.</li>
                <li>Booking and reservation details for trips, cruises, and heritage village stays.</li>
                <li>Job application information such as CV, work history, and qualifications.</li>
                <li>Technical data such as IP address, browser type, and pages viewed on our website.</li>
            </ul>

            <h3 style="font-size:20px; font-weight:600; margin-top:30px;">2. How We Use Your Information</h3>
            <p style="color:#555; font-size:15px;">
                We use your information for purposes such as:
            </p>
            <ul style="color:#555; font-size:15px; padding-left:20px;">
                <li>Processing bookings, reservations, and service requests.</li>
                <li>Responding to your inquiries and customer support requests.</li>
                <li>Reviewing job applications and contacting suitable candidates.</li>
                <li>Improving our services, website experience, and safety standards.</li>
                <li>Complying with legal, regulatory, or security requirements.</li>
            </ul>

            <h3 style="font-size:20px; font-weight:600; margin-top:30px;">3. Sharing of Information</h3>
            <p style="color:#555; font-size:15px;">
                We do not sell your personal information. We may share your information:
            </p>
            <ul style="color:#555; font-size:15px; padding-left:20px;">
                <li>With our internal departments and authorized staff for operational purposes.</li>
                <li>With trusted service providers (for example: IT, hosting, or payment processors).</li>
                <li>When required by law, government authorities, or to protect our legal rights.</li>
            </ul>

            <h3 style="font-size:20px; font-weight:600; margin-top:30px;">4. Data Security</h3>
            <p style="color:#555; font-size:15px;">
                We use reasonable technical and organizational measures to protect your personal data from
                unauthorized access, loss, misuse, or disclosure. However, no method of transmission over the
                internet is completely secure, and we cannot guarantee absolute security.
            </p>

            <h3 style="font-size:20px; font-weight:600; margin-top:30px;">5. Cookies & Website Tracking</h3>
            <p style="color:#555; font-size:15px;">
                Our website may use cookies and similar technologies to improve your browsing experience,
                analyze website traffic, and remember your preferences. You can manage cookie settings from
                your browser, but disabling cookies may affect some website features.
            </p>

            <h3 style="font-size:20px; font-weight:600; margin-top:30px;">6. Data Retention</h3>
            <p style="color:#555; font-size:15px;">
                We retain your personal information only for as long as necessary to fulfill the purposes
                described in this policy, or as required by law, safety, and operational needs.
            </p>

            <h3 style="font-size:20px; font-weight:600; margin-top:30px;">7. Your Rights</h3>
            <p style="color:#555; font-size:15px;">
                Depending on applicable laws, you may have the right to:
            </p>
            <ul style="color:#555; font-size:15px; padding-left:20px;">
                <li>Request access to the personal data we hold about you.</li>
                <li>Request correction or update of inaccurate information.</li>
                <li>Request deletion of your personal data, where legally possible.</li>
                <li>Object to certain types of data processing.</li>
            </ul>

            <h3 style="font-size:20px; font-weight:600; margin-top:30px;">8. Third-Party Links</h3>
            <p style="color:#555; font-size:15px;">
                Our website may contain links to third-party websites (for example: social media, partners,
                or booking platforms). We are not responsible for the content or privacy practices of those
                external websites.
            </p>

            <h3 style="font-size:20px; font-weight:600; margin-top:30px;">9. Updates to This Policy</h3>
            <p style="color:#555; font-size:15px;">
                We may update this Privacy Policy from time to time. Any changes will be posted on this page
                with an updated “Last Updated” date.
            </p>

            <h3 style="font-size:20px; font-weight:600; margin-top:30px;">10. Contact Us</h3>
            <p style="color:#555; font-size:15px;">
                If you have any questions about this Privacy Policy or how we handle your data, you can contact us at:
            </p>
            <p style="color:#555; font-size:15px;">
                <strong>IKARUS United Marine Services</strong><br>
                Email: <a href="mailto:info@ikarusmarine.com">info@ikarusmarine.com</a><br>
                Phone: +965 180 8080
            </p>

            <p style="color:#888; font-size:13px; margin-top:25px;">
                Last Updated: {{ now()->format('F d, Y') }}
            </p>

        </div>
    </section>
@endsection
