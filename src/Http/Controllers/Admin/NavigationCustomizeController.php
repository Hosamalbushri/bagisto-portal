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
        abort_unless(bouncer()->hasPermission('portal.navigation'), 403);

        $channels = core()->getAllChannels();

        $currentChannel = $request->filled('channel_id')
            ? $channels->firstWhere('id', (int) $request->input('channel_id'))
            : core()->getCurrentChannel();

        if (! $currentChannel) {
            $currentChannel = $channels->first();
        }

        $themeCode = $currentChannel?->theme ?? config('themes.shop-default', 'default');

        // Scan layout files for slots
        $scannedSlots = $this->menuSlotScanner->scan();

        // Get saved assignments from DB
        $assignments = $this->portalMenuSlotAssignmentRepository->getAssignments($currentChannel->id, $themeCode);

        // Get available menus for channel + global
        $menus = $this->portalMenuRepository->findWhere(['channel_id' => $currentChannel->id])
            ->concat($this->portalMenuRepository->findWhere(['channel_id' => null]))
            ->unique('id');

        return view('portal::admin.navigation.index', [
            'channels' => $channels,
            'currentChannel' => $currentChannel,
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
        abort_unless(bouncer()->hasPermission('portal.navigation'), 403);

        $request->validate([
            'channel_id' => 'required|integer|exists:channels,id',
            'theme_code' => 'nullable|string',
            'slots' => 'nullable|array',
        ]);

        $channelId = (int) $request->input('channel_id');
        $themeCode = $request->input('theme_code', 'default');
        $slots = $request->input('slots', []);

        $this->portalMenuSlotAssignmentRepository->saveAssignments($slots, $channelId, $themeCode);

        session()->flash('success', trans('portal::app.admin.navigation.update-success'));

        if ($request->ajax()) {
            return response()->json([
                'message' => trans('portal::app.admin.navigation.update-success'),
                'redirect' => route('admin.portal.navigation.index', [
                    'channel_id' => $channelId,
                ]),
            ]);
        }

        return redirect()->route('admin.portal.navigation.index', [
            'channel_id' => $channelId,
        ]);
    }
}
