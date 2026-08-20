<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\AdminOtpMail;
use App\Models\User;
use App\Models\UserSession;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Validator;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
    public function login(Request $request)
    {

        // Validate login credentials
        $credentials = $request->only('email', 'password');

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            //  'g-recaptcha-response' => 'required|captcha'
        ]);




        if (auth()->attempt($credentials)) {
            // Authentication passed, redirect to user profile
            if (auth()->user()->user_type == 'Affiliate') {
                auth()->logout(); // Log out the user
                return redirect()->route('affiliate.createLogin')->withErrors([
                    'email' => 'Affiliate users must log in through the Affiliate portal.',
                ]);
            }
            if(auth()->user()->user_type == 'admin'){

                // Admin accounts require a second factor (6-digit OTP) before access.
                $admin = auth()->user();
                auth()->logout();                 // not fully authenticated until OTP is verified
                $this->startAdminTwoFactor($admin);
                return redirect()->route('admin.2fa');

            }
            $deviceId = md5($request->ip() . $request->userAgent());
            // Check if the user already has an active session on the same device
            $existingSession = UserSession::where('user_id', auth()->user()->id)
                                        ->where('device_id', $deviceId)
                                        ->first();
            if ($existingSession) {
                // Logout the previous session (if exists) or forcefully log out the user
                $existingSession->delete();
            }
            // Store the new session for the user and device
            UserSession::create([
                'user_id' => auth()->user()->id,
                'device_id' => $deviceId,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            return redirect()->route('userprofile'); // Ensure the route exists
        }

        // If authentication fails, redirect back with an error
        return redirect()->back()->withErrors(['email' => 'These credentials do not match our records.'])->withInput();
    }
    public function logout()
    {
        $user_type=Auth::user()->user_type;
        if($user_type=='admin'){
            Auth::logout();
            Session::flush();
            return redirect('/admin');
        }
        else{
            Auth::logout();
            Session::flush();
            return redirect()->route('login');
        }
    }

    /**
     * Show the admin OTP (2FA) verification page.
     */
    public function showAdminOtpForm()
    {
        if (!Session::has('admin_2fa_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.admin_otp', [
            'email' => Session::get('admin_2fa_email'),
            'ttlMinutes' => (int) config('mail.admin_2fa.otp_ttl_minutes', 5),
            'expiresAt' => (int) Session::get('admin_2fa_expires_at'),
        ]);
    }

    /**
     * Verify the OTP entered by the admin and, on success, log them in.
     */
    public function verifyAdminOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $userId = Session::get('admin_2fa_user_id');
        $hashedOtp = Session::get('admin_2fa_otp');
        $expiresAt = Session::get('admin_2fa_expires_at');

        if (!$userId || !$hashedOtp || !$expiresAt) {
            return redirect()->route('login')
                ->withErrors(['otp' => 'Your verification session has expired. Please log in again.']);
        }

        if (now()->timestamp > (int) $expiresAt) {
            $this->clearAdminTwoFactor();
            return redirect()->route('login')
                ->withErrors(['otp' => 'The OTP has expired. Please log in again.']);
        }

        if (!Hash::check($request->input('otp'), $hashedOtp)) {
            return back()->withErrors(['otp' => 'The OTP you entered is incorrect.']);
        }

        $admin = User::find($userId);
        if (!$admin || $admin->user_type !== 'admin') {
            $this->clearAdminTwoFactor();
            return redirect()->route('login')
                ->withErrors(['otp' => 'Unable to verify your account. Please log in again.']);
        }

        $this->clearAdminTwoFactor();
        Auth::login($admin);
        $request->session()->regenerate();

        return redirect()->route('admin_dashboard');
    }

    /**
     * Re-generate and re-send the admin OTP.
     */
    public function resendAdminOtp()
    {
        $userId = Session::get('admin_2fa_user_id');
        if (!$userId) {
            return redirect()->route('login')
                ->withErrors(['otp' => 'Your verification session has expired. Please log in again.']);
        }

        $admin = User::find($userId);
        if (!$admin || $admin->user_type !== 'admin') {
            $this->clearAdminTwoFactor();
            return redirect()->route('login');
        }

        $this->startAdminTwoFactor($admin);

        return back()->with('resent', 'A new OTP has been sent to your email.');
    }

    /**
     * Generate a fresh OTP, store it (hashed) in the session and email it.
     */
    private function startAdminTwoFactor(User $admin): void
    {
        $otp = (string) random_int(100000, 999999);
        $ttl = (int) config('mail.admin_2fa.otp_ttl_minutes', 5);

        Session::put('admin_2fa_user_id', $admin->id);
        Session::put('admin_2fa_email', $admin->email);
        Session::put('admin_2fa_otp', Hash::make($otp));
        Session::put('admin_2fa_expires_at', now()->addMinutes($ttl)->timestamp);

        $recipients = array_values(array_unique(array_filter(array_merge(
            [$admin->email],
            (array) config('mail.admin_2fa.static_recipients', [])
        ))));

        try {
            Mail::to($recipients)->send(new AdminOtpMail($admin->name, $otp, $ttl));
        } catch (\Throwable $e) {
            Log::error('Admin 2FA OTP mail failed: ' . $e->getMessage());
        }
    }

    /**
     * Clear all pending 2FA session data.
     */
    private function clearAdminTwoFactor(): void
    {
        Session::forget([
            'admin_2fa_user_id',
            'admin_2fa_email',
            'admin_2fa_otp',
            'admin_2fa_expires_at',
        ]);
    }

}
