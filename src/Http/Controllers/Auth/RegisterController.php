<?php

namespace Platform\Portal\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Platform\Portal\Helpers\PortalAuth;
use Platform\Portal\Http\Controllers\Controller;
use Platform\Portal\Http\Requests\Auth\RegisterRequest;
use Webkul\Customer\Models\Customer;

class RegisterController extends Controller
{
    /**
     * Display the portal registration view.
     */
    public function showRegistrationForm(): View|RedirectResponse
    {
        abort_unless(PortalAuth::isEnabled(), 404);

        if (PortalAuth::check()) {
            return redirect()->route('shop.home.index');
        }

        return view('portal::auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function register(RegisterRequest $request): RedirectResponse
    {
        abort_unless(PortalAuth::isEnabled(), 404);

        $guard = PortalAuth::guard();
        $channel = core()->getCurrentChannel();

        // Check if user already exists
        if ($guard === 'customer' && class_exists('Webkul\Customer\Models\Customer')) {
            $exists = Customer::query()
                ->where('email', $request->input('email'))
                ->where('channel_id', $channel?->id)
                ->exists();

            if ($exists) {
                session()->flash('error', trans('portal::app.auth.register.email-taken'));

                return redirect()->back()->withInput($request->except('password', 'password_confirmation'));
            }

            $names = explode(' ', trim($request->input('name')), 2);
            $firstName = $names[0];
            $lastName = $names[1] ?? '.';

            $defaultGroupCode = core()->getConfigData('customer.settings.create_new_account_options.default_group') ?? 'general';
            $groupId = 1;

            if (class_exists('Webkul\Customer\Repositories\CustomerGroupRepository')) {
                $group = app('Webkul\Customer\Repositories\CustomerGroupRepository')->findOneWhere(['code' => $defaultGroupCode]);
                if ($group) {
                    $groupId = $group->id;
                }
            }

            $data = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $request->input('email'),
                'password' => bcrypt($request->input('password')),
                'is_verified' => 1,
                'status' => 1,
                'customer_group_id' => $groupId,
                'channel_id' => $channel?->id,
                'api_token' => Str::random(80),
                'token' => Str::random(64),
            ];

            if (class_exists('Webkul\Customer\Repositories\CustomerRepository')) {
                $user = app('Webkul\Customer\Repositories\CustomerRepository')->create($data);
            } else {
                $user = Customer::create($data);
            }
        } else {
            $provider = auth()->guard($guard)->getProvider();
            $modelClass = method_exists($provider, 'getModel') ? $provider->getModel() : User::class;

            $exists = $modelClass::where('email', $request->input('email'))->exists();

            if ($exists) {
                session()->flash('error', trans('portal::app.auth.register.email-taken'));

                return redirect()->back()->withInput($request->except('password', 'password_confirmation'));
            }

            $user = $modelClass::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => bcrypt($request->input('password')),
            ]);
        }

        auth()->guard($guard)->login($user);

        $request->session()->regenerate();

        session()->flash('success', trans('portal::app.auth.register.success'));

        return redirect()->route('shop.home.index');
    }
}
