<?php

namespace Platform\Portal\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Platform\Portal\Repositories\PortalMenuRepository;
use Platform\Portal\Repositories\PortalMenuSlotAssignmentRepository;
use Platform\Portal\Services\MenuSlotScanner;
use Webkul\Admin\Http\Controllers\Controller;

class NavigationCustomizeController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected PortalMenuRepository $portalMenuRepository,
        protected PortalMenuSlotAssignmentRepository $portalMenuSlotAssignmentRepository,
        protected MenuSlotScanner $menuSlotScanner
    ) {}

    /**
     * Display Header and Footer Navigation Customizer.
     */
    public function index(Request $request): View
    {
        abort_unless(bouncer()->hasPermission('portal_menus.navigation'), 403);

        $channels = core()->getAllChannels();

        $channelCode = $request->query('channel') ?: $request->input('channel');
        $localeCode = $request->query('locale') ?: $request->input('locale');

        $currentChannel = $channelCode
            ? $channels->firstWhere('code', $channelCode)
            : core()->getRequestedChannel();

        if (! $currentChannel) {
            $currentChannel = core()->getCurrentChannel();
        }

        $currentLocale = $localeCode
            ? core()->getAllLocales()->firstWhere('code', $localeCode)
            : core()->getRequestedLocale();

        if (! $currentLocale) {
            $currentLocale = core()->getCurrentLocale();
        }

        $themeCode = $currentChannel?->theme ?? config('themes.shop-default', 'default');

        // Scan layout files for slots
        $scannedSlots = $this->menuSlotScanner->scan();

        // Get saved assignments from DB for channel, theme, and locale
        $assignments = $this->portalMenuSlotAssignmentRepository->getAssignments(
            $currentChannel->id,
            $themeCode,
            $currentLocale->code
        );

        // Get available menus for channel + global
        $menus = $this->portalMenuRepository->findWhere(['channel_id' => $currentChannel->id])
            ->concat($this->portalMenuRepository->findWhere(['channel_id' => null]))
            ->unique('id');

        return view('portal::admin.navigation.index', [
            'channels' => $channels,
            'currentChannel' => $currentChannel,
            'currentLocale' => $currentLocale,
            'themeCode' => $themeCode,
            'slots' => $scannedSlots,
            'assignments' => $assignments,
            'menus' => $menus,
        ]);
    }

    /**
     * Save slot assignments.
     */
    public function update(Request $request): RedirectResponse|JsonResponse
    {
        abort_unless(bouncer()->hasPermission('portal_menus.navigation'), 403);

        $request->validate([
            'channel' => 'nullable|string',
            'locale' => 'nullable|string',
            'theme_code' => 'nullable|string',
            'slots' => 'nullable|array',
        ]);

        $channelCode = $request->input('channel') ?: $request->query('channel');
        $localeCode = $request->input('locale') ?: $request->query('locale');

        $currentChannel = $channelCode
            ? core()->getAllChannels()->firstWhere('code', $channelCode)
            : core()->getRequestedChannel();

        if (! $currentChannel) {
            $currentChannel = core()->getCurrentChannel();
        }

        $currentLocale = $localeCode
            ? core()->getAllLocales()->firstWhere('code', $localeCode)
            : core()->getRequestedLocale();

        if (! $currentLocale) {
            $currentLocale = core()->getCurrentLocale();
        }

        $themeCode = $request->input('theme_code', $currentChannel?->theme ?? 'default');
        $slots = $request->input('slots', []);

        $this->portalMenuSlotAssignmentRepository->saveAssignments(
            $slots,
            $currentChannel->id,
            $themeCode,
            $currentLocale->code
        );

        session()->flash('success', trans('portal::app.admin.navigation.update-success'));

        $redirectParams = [
            'channel' => $currentChannel->code,
            'locale' => $currentLocale->code,
        ];

        if ($request->ajax()) {
            return response()->json([
                'message' => trans('portal::app.admin.navigation.update-success'),
                'redirect' => route('admin.portal.navigation.index', $redirectParams),
            ]);
        }

        return redirect()->route('admin.portal.navigation.index', $redirectParams);
    }
}
