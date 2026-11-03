<?php

namespace Platform\Portal\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Platform\Portal\Http\Controllers\Controller;
use Platform\Portal\Repositories\PortalLayoutSettingRepository;
use Platform\Portal\Repositories\PortalPageRepository;
use Platform\Portal\Services\ThemeManager;
use Platform\Portal\Themes\ThemeManifest;

class ThemeController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected ThemeManager $themeManager,
        protected PortalLayoutSettingRepository $layoutSettingRepository,
        protected PortalPageRepository $portalPageRepository
    ) {}

    /**
     * Display the portal themes list matching Bagisto core theme catalog structure.
     */
    public function index(): View
    {
        $channels = core()->getAllChannels();

        $allThemes = [];
        $themesList = $this->themeManager->all(true);

        foreach ($themesList as $code => $theme) {
            $name = $theme instanceof ThemeManifest ? $theme->name : ($theme['name'] ?? ucfirst($code));
            $author = $theme instanceof ThemeManifest ? $theme->author : ($theme['author'] ?? null);
            $version = $theme instanceof ThemeManifest ? $theme->version : ($theme['version'] ?? null);
            $description = $theme instanceof ThemeManifest ? $theme->description : ($theme['description'] ?? null);
            $screenshot = $theme instanceof ThemeManifest ? $theme->screenshotUrl() : ($theme['screenshot'] ?? null);

            $activeOnChannels = $channels->filter(function ($ch) use ($code) {
                return portal_active_theme($ch->id) === $code;
            })->map(fn ($ch) => [
                'id' => $ch->id,
                'name' => $ch->name,
            ])->values()->toArray();

            $allThemes[] = [
                'code' => $code,
                'name' => $name,
                'author' => $author,
                'version' => $version,
                'description' => $description,
                'screenshot' => $screenshot,
                'is_installed' => true,
                'status' => count($activeOnChannels) > 0 ? 'active' : 'installed',
                'active_on' => $activeOnChannels,
            ];
        }

        // Sort: active themes first, then alphabetically
        usort($allThemes, function ($a, $b) {
            $statusA = $a['status'] === 'active' ? 0 : 1;
            $statusB = $b['status'] === 'active' ? 0 : 1;

            if ($statusA !== $statusB) {
                return $statusA <=> $statusB;
            }

            return strcmp($a['name'], $b['name']);
        });

        return view('portal::admin.themes.index', [
            'themes' => $allThemes,
            'channels' => $channels,
        ]);
    }

    /**
     * Launch the visual section customizer for the home page of the specified theme,
     * matching the core Bagisto Appearance customizer workflow.
     */
    public function customize(string $code): RedirectResponse
    {
        abort_unless(
            bouncer()->hasPermission('portal.pages.customize')
            || bouncer()->hasPermission('appearance.sections'),
            403
        );

        $channels = core()->getAllChannels();

        // Find the channel running this theme, or the requested one, or default
        $targetChannel = request('channel')
            ? $channels->firstWhere('code', request('channel'))
            : $channels->first(fn ($ch) => portal_active_theme($ch->id) === $code);

        if (! $targetChannel) {
            $targetChannel = core()->getCurrentChannel() ?? $channels->first();
        }

        // Find or create the HomePage for this channel
        $homePage = $this->portalPageRepository->findHomePage($targetChannel->id);

        if (! $homePage) {
            $locale = app()->getLocale() ?: 'ar';

            $homePage = $this->portalPageRepository->create([
                'channel_id' => $targetChannel->id,
                'slug' => 'home',
                'is_home_page' => 1,
                'status' => 1,
                'sort_order' => 0,
                $locale => [
                    'title' => 'الصفحة الرئيسية',
                    'meta_title' => 'الصفحة الرئيسية',
                ],
            ]);
        }

        return redirect()->route('admin.portal.pages.customize', [
            'id' => $homePage->id,
            'channel' => $targetChannel->code,
        ]);
    }

    /**
     * Activate a theme across specified channels matching core Bagisto workflow.
     */
    public function activate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'theme_code' => 'required|string',
            'channel_ids' => 'required|array',
            'channel_ids.*' => 'integer',
        ]);

        foreach ($validated['channel_ids'] as $channelId) {
            $this->themeManager->activateTheme((int) $channelId, $validated['theme_code']);
        }

        session()->flash('success', trans('portal::app.admin.themes.activated-success'));

        return new JsonResponse([
            'message' => trans('portal::app.admin.themes.activated-success'),
            'redirect_url' => route('admin.portal.themes.index'),
        ]);
    }

    /**
     * Fetch existing tokens for a theme and channel to populate modal editor.
     */
    public function getTokens(Request $request): JsonResponse
    {
        $channelId = (int) $request->input('channel_id', core()->getCurrentChannel()?->id ?? 1);
        $themeCode = (string) $request->input('theme_code', 'portal');

        $tokens = $this->layoutSettingRepository->getSettings('theme_tokens', $channelId, $themeCode);

        return new JsonResponse([
            'tokens' => $tokens,
        ]);
    }

    /**
     * Save live design tokens (colors and styling) for zero-build theme customization.
     */
    public function customizeTokens(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'channel_id' => 'required|integer',
            'theme_code' => 'required|string',
            'tokens' => 'nullable|array',
        ]);

        $tokens = $validated['tokens'] ?? [];

        // Sanitize tokens
        $sanitizedTokens = [];
        foreach ($tokens as $token => $value) {
            if (is_string($token) && str_starts_with($token, '--') && is_string($value) && filled($value)) {
                $sanitizedTokens[$token] = strip_tags(trim($value));
            }
        }

        $this->layoutSettingRepository->saveSettings(
            'theme_tokens',
            $sanitizedTokens,
            (int) $validated['channel_id'],
            $validated['theme_code']
        );

        return new JsonResponse([
            'message' => trans('portal::app.admin.themes.tokens-saved-success'),
        ]);
    }

    /**
     * Reset customized tokens back to theme defaults.
     */
    public function resetTokens(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'channel_id' => 'required|integer',
            'theme_code' => 'required|string',
        ]);

        $this->layoutSettingRepository->saveSettings(
            'theme_tokens',
            [],
            (int) $validated['channel_id'],
            $validated['theme_code']
        );

        return new JsonResponse([
            'message' => trans('portal::app.admin.themes.tokens-saved-success'),
        ]);
    }
}
