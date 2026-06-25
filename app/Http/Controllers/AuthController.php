<?php

namespace App\Http\Controllers;

use App\Models\ReportNotification;
use App\Models\ReportReview;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'office_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'report_types' => ['required', 'array', 'min:1'],
            'report_types.*' => ['required', 'string', 'in:'.implode(',', array_keys(User::REPORT_TYPES))],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'office_name' => $data['office_name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'respondent',
            'approved_at' => null,
            'report_types' => $data['report_types'],
        ]);

        $this->notifyAdminsAboutRegistration($user);

        return redirect()
            ->route('login')
            ->with('status', 'Registration submitted. Please wait for an admin to approve your account before logging in.');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'The provided credentials do not match our records.'])
                ->onlyInput('email');
        }

        if (! $request->user()->isApproved()) {
            Auth::logout();

            $request->session()->regenerateToken();

            return back()
                ->withErrors(['email' => 'Your registration is still pending admin approval.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function notifyAdminsAboutRegistration(User $user): void
    {
        $review = ReportReview::create([
            'module_key' => 'responder-approvals',
            'module_label' => 'Responder Registration',
            'reportable_type' => User::class,
            'reportable_id' => $user->id,
            'respondent_id' => $user->id,
            'status' => 'pending',
        ]);

        User::query()
            ->where('role', 'admin')
            ->get()
            ->each(function (User $admin) use ($review, $user) {
                ReportNotification::create([
                    'user_id' => $admin->id,
                    'report_review_id' => $review->id,
                    'type' => 'responder_registration',
                    'message' => $user->name.' from '.$user->office_name.' registered as a responder and is awaiting approval.',
                ]);
            });
    }
}
