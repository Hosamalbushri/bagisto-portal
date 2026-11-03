<?php

namespace Platform\Portal\Http\Controllers\Auth;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Platform\Portal\Helpers\PortalAuth;
use Platform\Portal\Http\Controllers\Controller;
use Platform\Portal\Http\Requests\Auth\LoginRequest;

class LoginController extends Controller
{
    /**
     * Display the portal login view.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        abort_unless(PortalAuth::isEnabled(), 404);

        if (PortalAuth::check()) {
            return redirect()->to(portal_home_url());
        }

        return view('portal::auth.login');
    }

    /**
     * Handle an incoming login request.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        abort_unless(PortalAuth::isEnabled(), 404);

        $throttleKey = Str::transliterate(Str::lower($request->input('email')).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            session()->flash('error', trans('portal::app.auth.login.throttle', ['seconds' => $seconds]));

            return redirect()->back()->withInput($request->only('email', 'remember'));
        }

        $guard = PortalAuth::guard();
        $credentials = $request->only('email', 'password');

        if ($guard === 'customer' && $channel = core()->getCurrentChannel()) {
            $credentials['channel_id'] = $channel->id;
        }

        if (auth()->guard($guard)->attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::clear($throttleKey);

            $request->session()->regenerate();

            session()->flash('success', trans('portal::app.auth.login.success'));

            return redirect()->intended(portal_home_url());
        }

        RateLimiter::hit($throttleKey);

        session()->flash('error', trans('portal::app.auth.login.invalid-credentials'));

        return redirect()->back()->withInput($request->only('email', 'remember'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function logout(Request $request): RedirectResponse
    {
        if (PortalAuth::isEnabled()) {
            auth()->guard(PortalAuth::guard())->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            session()->flash('success', trans('portal::app.auth.logout.success'));
        }

        return redirect()->to(portal_home_url());
    }
}
