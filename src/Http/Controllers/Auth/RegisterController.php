<?php

namespace Platform\Portal\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Platform\Portal\Helpers\PortalAuth;
use Platform\Portal\Http\Controllers\Controller;
use Platform\Portal\Http\Requests\Auth\RegisterRequest;

class RegisterController extends Controller
{
    /**
     * Display the portal registration view.
     */
    public function showRegistrationForm(): View|RedirectResponse
    {
        abort_unless(PortalAuth::isEnabled(), 404);

        if (PortalAuth::check()) {
            return redirect()->to(portal_home_url());
        }

        return view('portal::auth.register');
    }

    /**
     * Handle an incoming registration request using the configured portal guard.
     */
    public function register(RegisterRequest $request): RedirectResponse
    {
        abort_unless(PortalAuth::isEnabled(), 404);

        $guard = PortalAuth::guard();
        $provider = auth()->guard($guard)->getProvider();
        $modelClass = method_exists($provider, 'getModel') ? $provider->getModel() : User::class;

        $exists = $modelClass::where('email', $request->input('email'))->exists();

        if ($exists) {
            session()->flash('error', trans('portal::app.auth.register.email-taken'));

            return redirect()->back()->withInput($request->except('password', 'password_confirmation'));
        }

        $userData = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password')),
        ];

        // Transparently support models expecting first_name / last_name
        $instance = new $modelClass;
        if ($instance->isFillable('first_name') && ! $instance->isFillable('name')) {
            $names = explode(' ', trim($request->input('name')), 2);
            $userData['first_name'] = $names[0];
            $userData['last_name'] = $names[1] ?? '.';
            unset($userData['name']);
        }

        try {
            $user = $modelClass::create($userData);
        } catch (QueryException $e) {
            session()->flash('error', trans('portal::app.auth.register.email-taken'));

            return redirect()->back()->withInput($request->except('password', 'password_confirmation'));
        }

        auth()->guard($guard)->login($user);

        $request->session()->regenerate();

        session()->flash('success', trans('portal::app.auth.register.success'));

        return redirect()->to(portal_home_url());
    }
}
