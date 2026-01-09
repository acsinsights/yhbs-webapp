<?php

namespace App\Http\Controllers\Customer;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\{Password, Auth, Hash, Mail, DB};
use App\Mail\PasswordResetOtpMail;
use App\Mail\RegistrationOtpMail;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        return view('frontend.auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            if (hasAuthAnyRole('admin|reception|superadmin')) {
                return redirect()->intended(route('admin.index'))
                    ->with('success', 'Welcome back, ' . Auth::user()->first_name . '!');
            }

            // Check if there's a return_url from POST data (hidden field from form)
            $returnUrl = $request->input('return_url');
            if (!$returnUrl) {
                // If not in POST, check query string
                $returnUrl = $request->query('return_url');
            }

            if ($returnUrl) {
                // Decode URL if it was encoded
                $returnUrl = urldecode($returnUrl);
                return redirect($returnUrl)
                    ->with('success', 'Welcome back, ' . Auth::user()->first_name . '!');
            }

            // Check if there's an intended URL (like checkout page)
            $intendedUrl = session('url.intended');
            if ($intendedUrl && str_contains($intendedUrl, '/checkout')) {
                return redirect()->intended($intendedUrl)
                    ->with('success', 'Welcome back, ' . Auth::user()->first_name . '!');
            }

            return redirect()->intended(route('customer.dashboard'))
                ->with('success', 'Welcome back, ' . Auth::user()->first_name . '!');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Show registration form
     */
    public function showRegister()
    {
        return view('frontend.auth.register');
    }

    /**
     * Handle registration request
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Store user data in session temporarily
        session([
            'registration_data' => [
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
            ]
        ]);

        // Store return_url if provided (from checkout page)
        if ($request->has('return_url')) {
            session(['registration_return_url' => $request->input('return_url')]);
        }

        // Generate 6-digit OTP
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        // Delete old OTPs for this email
        DB::table('password_reset_otps')
            ->where('email', $validated['email'])
            ->where('type', 'registration')
            ->delete();

        // Store new OTP
        DB::table('password_reset_otps')->insert([
            'email' => $validated['email'],
            'otp' => $otp,
            'type' => 'registration',
            'expires_at' => Carbon::now()->addMinutes(10),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Send OTP email
        Mail::to($validated['email'])->send(new RegistrationOtpMail($otp, $validated['first_name'] . ' ' . $validated['last_name']));

        return redirect()->route('customer.verify-registration-otp')
            ->with('email', $validated['email'])
            ->with('success', 'OTP sent to your email! Please verify to complete registration.');
    }

    /**
     * Show forgot password form
     */
    public function showForgotPassword()
    {
        return view('frontend.auth.forgot-password');
    }

    /**
     * Handle forgot password request
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', 'Password reset link sent to your email!')
            : back()->withErrors(['email' => __($status)]);
    }

    /**
     * Show reset password form
     */
    public function showResetPassword(Request $request, $token = null)
    {
        return view('frontend.auth.reset-password')->with(['token' => $token, 'email' => $request->email]);
    }

    /**
     * Handle reset password request
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status == Password::PASSWORD_RESET
            ? redirect()->route('customer.login')->with('success', 'Password has been reset successfully!')
            : back()->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
    }

    /**
     * Show registration OTP verification form
     */
    public function showVerifyRegistrationOtp()
    {
        if (!session('registration_data')) {
            return redirect()->route('customer.register')
                ->with('error', 'Registration session expired. Please register again.');
        }

        return view('frontend.auth.verify-registration-otp');
    }

    /**
     * Verify registration OTP
     */
    public function verifyRegistrationOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'string', 'size:6'],
        ]);

        if (!session('registration_data')) {
            return redirect()->route('customer.register')
                ->with('error', 'Registration session expired. Please register again.');
        }

        $otpRecord = DB::table('password_reset_otps')
            ->where('email', $request->email)
            ->where('otp', $request->otp)
            ->where('type', 'registration')
            ->first();

        if (!$otpRecord) {
            return back()->withErrors(['otp' => 'Invalid OTP code.'])->withInput();
        }

        if (Carbon::parse($otpRecord->expires_at)->isPast()) {
            return back()->withErrors(['otp' => 'OTP has expired. Please request a new one.'])->withInput();
        }

        // OTP verified, create user
        $registrationData = session('registration_data');

        $user = User::create($registrationData);

        // Assign customer role
        if (method_exists($user, 'assignRole')) {
            $user->assignRole('customer');
        }

        // Mark email as verified
        $user->email_verified_at = Carbon::now();
        $user->save();

        // Delete used OTP
        DB::table('password_reset_otps')
            ->where('email', $request->email)
            ->where('type', 'registration')
            ->delete();

        // Clear registration data from session
        session()->forget('registration_data');

        // Login user
        Auth::login($user);

        // Check if there's a return_url from registration (like checkout page)
        $returnUrl = session('registration_return_url');
        if ($returnUrl) {
            session()->forget('registration_return_url');
            return redirect($returnUrl)
                ->with('success', 'Email verified successfully! Welcome to YHBS.');
        }

        // Check if there's an intended URL (like checkout page)
        $intendedUrl = session('url.intended');
        if ($intendedUrl && str_contains($intendedUrl, '/checkout')) {
            return redirect($intendedUrl)
                ->with('success', 'Email verified successfully! Welcome to YHBS.');
        }

        return redirect()->route('customer.dashboard')
            ->with('success', 'Email verified successfully! Welcome to YHBS.');
    }

    /**
     * Resend registration OTP
     */
    public function resendRegistrationOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        if (!session('registration_data')) {
            return response()->json([
                'success' => false,
                'message' => 'Registration session expired. Please register again.'
            ], 400);
        }

        $email = $request->email;

        // Get user name from registration data
        $registrationData = session('registration_data');
        $userName = $registrationData['first_name'] ?? 'User';

        // Delete old OTPs for this email
        DB::table('password_reset_otps')
            ->where('email', $email)
            ->where('type', 'registration')
            ->delete();

        // Generate new OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store OTP in database
        DB::table('password_reset_otps')->insert([
            'email' => $email,
            'otp' => $otp,
            'type' => 'registration',
            'expires_at' => Carbon::now()->addMinutes(10),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Send OTP via email
        try {
            Mail::to($email)->send(new RegistrationOtpMail($otp, $userName));

            return response()->json([
                'success' => true,
                'message' => 'OTP has been resent successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.'
            ], 500);
        }
    }

    /**
     * Check if email exists
     */
    public function checkEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email']
        ]);

        $userExists = User::where('email', $request->email)->exists();

        return response()->json([
            'exists' => $userExists,
            'email' => $request->email
        ]);
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('customer.login')
            ->with('success', 'You have been logged out successfully.');
    }
}
