<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login', $this->brandingData());
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('username', $credentials['username'])->first();

        if (! $user || ! $this->passwordMatches($credentials['password'], (string) $user->password)) {
            return back()
                ->withErrors(['username' => 'Username or password is incorrect.'])
                ->onlyInput('username');
        }

        if (hash_equals((string) $user->password, $credentials['password'])) {
            $user->password = Hash::make($credentials['password']);
            $user->save();
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function forgotPasswordForm(): View
    {
        return view('auth.forgot_password', $this->brandingData());
    }

    public function forgotPasswordSubmit(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'identity' => ['required', 'string', 'max:150'],
        ]);

        $identity = $validated['identity'];
        $query = User::query()->where('username', $identity);

        if (Schema::hasColumn('users', 'email')) {
            $query->orWhere('email', $identity);
        }

        $user = $query->first();

        if (! $user) {
            return back()
                ->withErrors(['identity' => 'We cannot find your email.'])
                ->onlyInput('identity');
        }

        return back()->with('status', 'Request received. Please contact admin to reset your password.');
    }

    public function logoutPage(): View
    {
        return view('auth.logout', array_merge($this->brandingData(), [
            'username' => (string) Auth::user()->username,
        ]));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function passwordMatches(string $plainPassword, string $storedPassword): bool
    {
        try {
            if (Hash::check($plainPassword, $storedPassword)) {
                return true;
            }
        } catch (\RuntimeException) {
            // Some legacy rows are not bcrypt hashes. Fall back to plain comparison.
        }

        return hash_equals($storedPassword, $plainPassword);
    }

    private function brandingData(): array
    {
        $clinicName = Clinic::query()->value('clinic_name') ?: 'Clinic System';
        $logoValue = Clinic::query()
            ->whereNotNull('clinic_logo')
            ->where('clinic_logo', '!=', '')
            ->value('clinic_logo');

        return [
            'clinicName' => $clinicName,
            'clinicLogoUrl' => $this->resolveLogoUrl($logoValue),
        ];
    }

    private function resolveLogoUrl(?string $logo): ?string
    {
        if (! $logo) {
            return null;
        }

        if (filter_var($logo, FILTER_VALIDATE_URL)) {
            return $logo;
        }

        $normalized = ltrim($logo, '/');

        if (file_exists(public_path($normalized))) {
            return asset($normalized);
        }

        if (file_exists(storage_path('app/public/'.$normalized))) {
            return asset('storage/'.$normalized);
        }

        return asset($normalized);
    }
}
