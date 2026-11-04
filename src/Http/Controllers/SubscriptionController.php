<?php

namespace Platform\Portal\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Webkul\Core\Repositories\SubscribersListRepository;

class SubscriptionController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected SubscribersListRepository $subscriptionRepository
    ) {}

    /**
     * Subscribes an email to the portal channel subscription list.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'website' => ['nullable', 'max:0'],
        ]);

        $email = $validated['email'];

        Event::dispatch('portal.customer.subscription.before', $email);

        if ($this->isAlreadySubscribed($email)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => trans('portal::app.subscription.already'),
                ], 422);
            }

            session()->flash('warning', trans('portal::app.subscription.already'));

            return redirect()->back();
        }

        $subscription = $this->upsertSubscription($email);

        $this->syncCustomerNewsletterFlag();

        Event::dispatch('portal.customer.subscription.after', $subscription);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => trans('portal::app.subscription.subscribe-success'),
            ]);
        }

        session()->flash('success', trans('portal::app.subscription.subscribe-success'));

        return redirect()->back();
    }

    /**
     * Unsubscribe from the subscription list.
     */
    public function destroy(Request $request, string $token): RedirectResponse
    {
        // Ignore automated email preview and prefetch bot scanners
        if (
            $request->header('X-Purpose') === 'preview'
            || $request->header('Purpose') === 'prefetch'
            || $request->header('X-Moz') === 'prefetch'
        ) {
            return redirect()->route('portal.home');
        }

        $this->subscriptionRepository->deleteWhere(['token' => $token]);

        session()->flash('success', trans('portal::app.subscription.unsubscribe-success'));

        return redirect()->route('portal.home');
    }

    /**
     * Check if the email is already subscribed in the current channel.
     */
    protected function isAlreadySubscribed(string $email): bool
    {
        $channel = core()->getCurrentChannel();

        return (bool) $this->subscriptionRepository->findOneWhere([
            'email' => $email,
            'channel_id' => $channel->id,
            'is_subscribed' => 1,
        ]);
    }

    /**
     * Upsert the subscription record for the given email.
     */
    protected function upsertSubscription(string $email): mixed
    {
        $channel = core()->getCurrentChannel();
        $customer = auth()->user();

        $payload = [
            'is_subscribed' => 1,
            'token' => Str::random(64),
            'customer_id' => $customer?->id,
        ];

        $existing = $this->subscriptionRepository->findOneWhere([
            'email' => $email,
            'channel_id' => $channel->id,
        ]);

        if ($existing) {
            $existing->update($payload);

            return $existing;
        }

        return $this->subscriptionRepository->create(array_merge($payload, [
            'email' => $email,
            'channel_id' => $channel->id,
        ]));
    }

    /**
     * Sync customer newsletter flag if user is logged in.
     */
    protected function syncCustomerNewsletterFlag(): void
    {
        $customer = auth()->user();

        if (! $customer) {
            return;
        }

        if (method_exists($customer, 'update')) {
            $customer->update(['subscribed_to_news_letter' => 1]);
        }
    }
}
