<?php

namespace Platform\Portal\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Platform\Portal\Http\Controllers\Controller;
use Platform\Portal\Repositories\PortalLayoutSettingRepository;
use Platform\Portal\Repositories\PortalPageRepository;
use Platform\Portal\Services\ThemeManager;
use Platform\Portal\Services\TokenCompilerService;
use Platform\Portal\Themes\ThemeManifest;

class ThemeController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected ThemeManager $themeManager,
        protected PortalLayoutSettingRepository $layoutSettingRepository,
        protected PortalPageRepository $portalPageRepository,
        protected TokenCompilerService $tokenCompiler
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
     * Dedicated full-page customizer for design tokens.
     */
    public function customizeTokensPage(string $code): View
    {
        abort_unless(
            bouncer()->hasPermission('appearance.themes')
            || bouncer()->hasPermission('portal.pages.customize')
            || bouncer()->hasPermission('appearance.sections'),
            403
        );

        $channels = core()->getAllChannels();

        $channelId = request('channel_id')
            ? (int) request('channel_id')
            : ($channels->first(fn ($ch) => portal_active_theme($ch->id) === $code)?->id ?? core()->getCurrentChannel()?->id ?? 1);

        $currentChannel = $channels->firstWhere('id', $channelId) ?? $channels->first();

        $tokens = $this->layoutSettingRepository->getSettings('theme_tokens', $currentChannel->id, $code) ?: [];

        $presets = $this->tokenCompiler->getPresets();

        $previewUrl = URL::temporarySignedRoute('portal.appearance.preview', now()->addHours(4), [
            'channel' => $currentChannel->code,
            'theme' => $code,
        ]);

        return view('portal::admin.themes.customize-tokens', [
            'themeCode' => $code,
            'currentChannel' => $currentChannel,
            'channels' => $channels,
            'tokens' => $tokens,
            'presets' => $presets,
            'previewUrl' => $previewUrl,
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
        abort_unless(
            bouncer()->hasPermission('appearance.themes')
            || bouncer()->hasPermission('portal.customize.themes'),
            403
        );

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
        abort_unless(
            bouncer()->hasPermission('appearance.themes')
            || bouncer()->hasPermission('portal.customize.themes')
            || bouncer()->hasPermission('portal.pages.customize'),
            403
        );

        $channelId = (int) $request->input('channel_id', core()->getCurrentChannel()?->id ?? 1);
        $themeCode = (string) $request->input('theme_code', 'portal');

        $tokens = $this->layoutSettingRepository->getSettings('theme_tokens', $channelId, $themeCode);

        return new JsonResponse([
            'tokens' => $tokens,
        ]);
    }

    /**
     * Retrieve available curated design presets.
     */
    public function getPresets(): JsonResponse
    {
        abort_unless(
            bouncer()->hasPermission('appearance.themes')
            || bouncer()->hasPermission('portal.customize.themes')
            || bouncer()->hasPermission('portal.pages.customize'),
            403
        );

        $presets = $this->tokenCompiler->getPresets();

        $formatted = [];
        foreach ($presets as $key => $preset) {
            $formatted[$key] = [
                'name' => trans($preset['name'] ?? ''),
                'description' => trans($preset['description'] ?? ''),
                'preview' => $preset['preview'] ?? [],
                'tokens' => $preset['tokens'] ?? [],
            ];
        }

        return new JsonResponse([
            'presets' => $formatted,
        ]);
    }

    /**
     * Apply a preset directly to a channel/theme.
     */
    public function applyPreset(Request $request): JsonResponse|RedirectResponse
    {
        abort_unless(
            bouncer()->hasPermission('appearance.themes')
            || bouncer()->hasPermission('portal.customize.themes')
            || bouncer()->hasPermission('portal.pages.customize'),
            403
        );

        $validated = $request->validate([
            'channel_id' => 'required|integer',
            'theme_code' => 'required|string',
            'preset' => 'required|string',
        ]);

        $preset = $this->tokenCompiler->getPreset($validated['preset']);

        if (! $preset) {
            if ($request->wantsJson() || $request->ajax() || ! $request->hasSession()) {
                return new JsonResponse([
                    'message' => trans('portal::app.admin.themes.preset-not-found'),
                ], 404);
            }

            session()->flash('error', trans('portal::app.admin.themes.preset-not-found'));

            return redirect()->back();
        }

        $rawTokens = $preset['tokens'] ?? [];
        if (isset($preset['dark']) && is_array($preset['dark'])) {
            foreach ($preset['dark'] as $k => $v) {
                $rawTokens['--dark-'.ltrim($k, '-')] = $v;
            }
        }

        $tokens = $this->tokenCompiler->sanitizeTokens($rawTokens);

        $this->layoutSettingRepository->saveSettings(
            'theme_tokens',
            $tokens,
            (int) $validated['channel_id'],
            $validated['theme_code']
        );

        if ($request->wantsJson() || $request->ajax() || ! $request->hasSession()) {
            return new JsonResponse([
                'message' => trans('portal::app.admin.tokens.apply-preset-success'),
                'tokens' => $tokens,
            ]);
        }

        session()->flash('success', trans('portal::app.admin.tokens.apply-preset-success'));

        return redirect()->back();
    }

    /**
     * Import tokens from uploaded W3C DTCG JSON file or raw payload.
     */
    public function importTokens(Request $request): JsonResponse
    {
        abort_unless(
            bouncer()->hasPermission('appearance.themes')
            || bouncer()->hasPermission('portal.customize.themes')
            || bouncer()->hasPermission('portal.pages.customize'),
            403
        );

        $validated = $request->validate([
            'channel_id' => 'required|integer',
            'theme_code' => 'required|string',
            'file' => 'nullable|file|mimes:json,txt|max:2048',
            'json' => 'nullable|string',
        ]);

        $payload = null;

        if ($request->hasFile('file')) {
            $content = file_get_contents($request->file('file')->getRealPath());
            $payload = json_decode($content, true);
        } elseif ($request->filled('json')) {
            $payload = json_decode($request->input('json'), true);
        }

        if (! is_array($payload)) {
            return new JsonResponse([
                'message' => trans('portal::app.admin.tokens.invalid-json'),
            ], 422);
        }

        $tokens = $this->tokenCompiler->importDtcg($payload);

        if (empty($tokens)) {
            return new JsonResponse([
                'message' => trans('portal::app.admin.tokens.no-valid-tokens'),
            ], 422);
        }

        $this->layoutSettingRepository->saveSettings(
            'theme_tokens',
            $tokens,
            (int) $validated['channel_id'],
            $validated['theme_code']
        );

        return new JsonResponse([
            'message' => trans('portal::app.admin.tokens.import-success'),
            'tokens' => $tokens,
        ]);
    }

    /**
     * Export tokens in W3C DTCG JSON format.
     */
    public function exportTokens(Request $request): JsonResponse
    {
        abort_unless(
            bouncer()->hasPermission('appearance.themes')
            || bouncer()->hasPermission('portal.customize.themes')
            || bouncer()->hasPermission('portal.pages.customize'),
            403
        );

        $channelId = (int) $request->input('channel_id', core()->getCurrentChannel()?->id ?? 1);
        $themeCode = (string) $request->input('theme_code', 'portal');

        $tokens = $this->layoutSettingRepository->getSettings('theme_tokens', $channelId, $themeCode);
        $dtcg = $this->tokenCompiler->exportDtcg($tokens);

        return new JsonResponse($dtcg);
    }

    /**
     * Save live design tokens (colors and styling) for zero-build theme customization.
     */
    public function customizeTokens(Request $request): JsonResponse|RedirectResponse
    {
        abort_unless(
            bouncer()->hasPermission('appearance.themes')
            || bouncer()->hasPermission('portal.customize.themes')
            || bouncer()->hasPermission('portal.pages.customize'),
            403
        );

        $validated = $request->validate([
            'channel_id' => 'required|integer',
            'theme_code' => 'required|string',
            'tokens' => 'nullable|array',
        ]);

        $sanitizedTokens = $this->tokenCompiler->sanitizeTokens($validated['tokens'] ?? []);

        $this->layoutSettingRepository->saveSettings(
            'theme_tokens',
            $sanitizedTokens,
            (int) $validated['channel_id'],
            $validated['theme_code']
        );

        if ($request->wantsJson() || $request->ajax()) {
            return new JsonResponse([
                'message' => trans('portal::app.admin.themes.tokens-saved-success'),
            ]);
        }

        session()->flash('success', trans('portal::app.admin.themes.tokens-saved-success'));

        return redirect()->back();
    }

    /**
     * Reset customized tokens back to theme defaults.
     */
    public function resetTokens(Request $request): JsonResponse|RedirectResponse
    {
        abort_unless(
            bouncer()->hasPermission('appearance.themes')
            || bouncer()->hasPermission('portal.customize.themes')
            || bouncer()->hasPermission('portal.pages.customize'),
            403
        );

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

        if ($request->wantsJson() || $request->ajax()) {
            return new JsonResponse([
                'message' => trans('portal::app.admin.themes.tokens-saved-success'),
            ]);
        }

        session()->flash('success', trans('portal::app.admin.themes.tokens-saved-success'));

        return redirect()->back();
    }
}
