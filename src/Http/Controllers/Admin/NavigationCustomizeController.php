<?php

namespace Platform\Portal\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Platform\Portal\Repositories\PortalLayoutSettingRepository;
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
        protected PortalLayoutSettingRepository $portalLayoutSettingRepository,
        protected MenuSlotScanner $menuSlotScanner
    ) {}

    /**
     * Redirect to Header Customizer by default.
     */
    public function index(Request $request): RedirectResponse
    {
        return redirect()->route('admin.portal.navigation.header', $request->query());
    }

    /**
     * Display Header Navigation Customizer.
     */
    public function header(Request $request): View
    {
        abort_unless(
            bouncer()->hasPermission('portal.customize.header')
            || bouncer()->hasPermission('portal.customize.navigation')
            || bouncer()->hasPermission('portal_menus.navigation'),
            403
        );

        $scope = $this->resolveScope($request);

        $scannedSlots = $this->menuSlotScanner->scan();
        $headerSlots = $scannedSlots['header'] ?? [];

        $assignments = $this->portalMenuSlotAssignmentRepository->getAssignments(
            $scope['currentChannel']->id,
            $scope['themeCode'],
            $scope['currentLocale']->code
        );

        $headerSettings = $this->portalLayoutSettingRepository->getSettings(
            'header',
            $scope['currentChannel']->id,
            $scope['themeCode'],
            $scope['currentLocale']->code
        );

        $menus = $this->getAvailableMenus($scope['currentChannel']->id);

        return view('portal::admin.navigation.header', [
            'channels' => $scope['channels'],
            'currentChannel' => $scope['currentChannel'],
            'currentLocale' => $scope['currentLocale'],
            'themeCode' => $scope['themeCode'],
            'slots' => $headerSlots,
            'assignments' => $assignments,
            'menus' => $menus,
            'headerSettings' => $headerSettings,
        ]);
    }

    /**
     * Save Header Layout and Navigation Settings.
     */
    public function updateHeader(Request $request): RedirectResponse|JsonResponse
    {
        abort_unless(
            bouncer()->hasPermission('portal.customize.header')
            || bouncer()->hasPermission('portal.customize.navigation')
            || bouncer()->hasPermission('portal_menus.navigation'),
            403
        );

        $request->validate([
            'channel' => 'nullable|string',
            'locale' => 'nullable|string',
            'theme_code' => 'nullable|string',
            'slots' => 'nullable|array',
            'header_settings' => 'nullable|array',
        ]);

        $scope = $this->resolveScope($request);

        if ($request->has('slots')) {
            $this->portalMenuSlotAssignmentRepository->saveAssignments(
                $request->input('slots', []),
                $scope['currentChannel']->id,
                $scope['themeCode'],
                $scope['currentLocale']->code
            );
        }

        if ($request->has('header_settings')) {
            $this->portalLayoutSettingRepository->saveSettings(
                'header',
                $request->input('header_settings', []),
                $scope['currentChannel']->id,
                $scope['themeCode'],
                $scope['currentLocale']->code
            );
        }

        session()->flash('success', trans('portal::app.admin.navigation.header-saved-success'));

        $redirectParams = [
            'channel' => $scope['currentChannel']->code,
            'locale' => $scope['currentLocale']->code,
        ];

        if ($request->ajax()) {
            return response()->json([
                'message' => trans('portal::app.admin.navigation.header-saved-success'),
                'redirect' => route('admin.portal.navigation.header', $redirectParams),
            ]);
        }

        return redirect()->route('admin.portal.navigation.header', $redirectParams);
    }

    /**
     * Display Footer Navigation Customizer.
     */
    public function footer(Request $request): View
    {
        abort_unless(
            bouncer()->hasPermission('portal.customize.footer')
            || bouncer()->hasPermission('portal.customize.navigation')
            || bouncer()->hasPermission('portal_menus.navigation'),
            403
        );

        $scope = $this->resolveScope($request);

        $scannedSlots = $this->menuSlotScanner->scan();
        $footerSlots = $scannedSlots['footer'] ?? [];

        $assignments = $this->portalMenuSlotAssignmentRepository->getAssignments(
            $scope['currentChannel']->id,
            $scope['themeCode'],
            $scope['currentLocale']->code
        );

        $footerSettings = $this->portalLayoutSettingRepository->getSettings(
            'footer',
            $scope['currentChannel']->id,
            $scope['themeCode'],
            $scope['currentLocale']->code
        );

        $menus = $this->getAvailableMenus($scope['currentChannel']->id);

        return view('portal::admin.navigation.footer', [
            'channels' => $scope['channels'],
            'currentChannel' => $scope['currentChannel'],
            'currentLocale' => $scope['currentLocale'],
            'themeCode' => $scope['themeCode'],
            'slots' => $footerSlots,
            'assignments' => $assignments,
            'menus' => $menus,
            'footerSettings' => $footerSettings,
        ]);
    }

    /**
     * Save Footer Layout and Navigation Settings.
     */
    public function updateFooter(Request $request): RedirectResponse|JsonResponse
    {
        abort_unless(
            bouncer()->hasPermission('portal.customize.footer')
            || bouncer()->hasPermission('portal.customize.navigation')
            || bouncer()->hasPermission('portal_menus.navigation'),
            403
        );

        $request->validate([
            'channel' => 'nullable|string',
            'locale' => 'nullable|string',
            'theme_code' => 'nullable|string',
            'slots' => 'nullable|array',
            'footer_settings' => 'nullable|array',
        ]);

        $scope = $this->resolveScope($request);

        if ($request->has('slots')) {
            $this->portalMenuSlotAssignmentRepository->saveAssignments(
                $request->input('slots', []),
                $scope['currentChannel']->id,
                $scope['themeCode'],
                $scope['currentLocale']->code
            );
        }

        if ($request->has('footer_settings')) {
            $this->portalLayoutSettingRepository->saveSettings(
                'footer',
                $request->input('footer_settings', []),
                $scope['currentChannel']->id,
                $scope['themeCode'],
                $scope['currentLocale']->code
            );
        }

        session()->flash('success', trans('portal::app.admin.navigation.footer-saved-success'));

        $redirectParams = [
            'channel' => $scope['currentChannel']->code,
            'locale' => $scope['currentLocale']->code,
        ];

        if ($request->ajax()) {
            return response()->json([
                'message' => trans('portal::app.admin.navigation.footer-saved-success'),
                'redirect' => route('admin.portal.navigation.footer', $redirectParams),
            ]);
        }

        return redirect()->route('admin.portal.navigation.footer', $redirectParams);
    }

    /**
     * Fallback for general navigation update.
     */
    public function update(Request $request): RedirectResponse|JsonResponse
    {
        $activeTab = $request->input('active_tab', 'header');

        if ($activeTab === 'footer') {
            return $this->updateFooter($request);
        }

        return $this->updateHeader($request);
    }

    /**
     * Resolve channel, locale, and theme scope.
     */
    protected function resolveScope(Request $request): array
    {
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

        $themeCode = $request->input('theme_code', $currentChannel?->theme ?? 'default');

        return [
            'channels' => $channels,
            'currentChannel' => $currentChannel,
            'currentLocale' => $currentLocale,
            'themeCode' => $themeCode,
        ];
    }

    /**
     * Get available menus for channel + global.
     */
    protected function getAvailableMenus(int $channelId)
    {
        return $this->portalMenuRepository->findWhere(['channel_id' => $channelId])
            ->concat($this->portalMenuRepository->findWhere(['channel_id' => null]))
            ->unique('id');
    }
}
