<?php

namespace Platform\Portal\Services;

use Illuminate\Support\Facades\Event;
use Platform\Portal\Repositories\PortalLayoutSettingRepository;

class ThemeTokenService
{
    public function __construct(
        protected TokenCompilerService $tokenCompiler,
        protected PortalLayoutSettingRepository $layoutSettingRepository
    ) {}

    /**
     * Get customized tokens for a channel and theme.
     *
     * @return array<string, mixed>
     */
    public function getTokens(int $channelId, string $themeCode): array
    {
        return $this->layoutSettingRepository->getSettings('theme_tokens', $channelId, $themeCode);
    }

    /**
     * Apply a curated preset to a channel and theme.
     *
     * @return array<string, string>|null Returns sanitized tokens array on success, or null if preset not found.
     */
    public function applyPreset(int $channelId, string $themeCode, string $presetCode): ?array
    {
        $preset = $this->tokenCompiler->getPreset($presetCode);

        if (! $preset) {
            return null;
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
            $channelId,
            $themeCode
        );

        $this->dispatchTokensUpdatedEvent($channelId, $themeCode, 'apply_preset');

        return $tokens;
    }

    /**
     * Import design tokens from a decoded W3C DTCG payload.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, string>
     */
    public function importDtcg(int $channelId, string $themeCode, array $payload): array
    {
        $tokens = $this->tokenCompiler->importDtcg($payload);

        if (empty($tokens)) {
            return [];
        }

        $this->layoutSettingRepository->saveSettings(
            'theme_tokens',
            $tokens,
            $channelId,
            $themeCode
        );

        $this->dispatchTokensUpdatedEvent($channelId, $themeCode, 'import_dtcg');

        return $tokens;
    }

    /**
     * Customize design tokens and optional custom CSS for a channel and theme.
     *
     * @param  array<string, mixed>  $tokens
     * @return array<string, mixed>
     */
    public function customizeTokens(
        int $channelId,
        string $themeCode,
        array $tokens = [],
        ?string $customCss = null
    ): array {
        $sanitizedTokens = $this->tokenCompiler->sanitizeTokens($tokens);

        $rawCustomCss = $customCss ?? ($tokens['custom_css'] ?? null);

        if ($rawCustomCss !== null && is_string($rawCustomCss)) {
            $cleanCustomCss = $this->tokenCompiler->sanitizeCustomCss($rawCustomCss);
            if (filled($cleanCustomCss)) {
                $sanitizedTokens['custom_css'] = $cleanCustomCss;
            }
        }

        $this->layoutSettingRepository->saveSettings(
            'theme_tokens',
            $sanitizedTokens,
            $channelId,
            $themeCode
        );

        $this->dispatchTokensUpdatedEvent($channelId, $themeCode, 'customize_tokens');

        return $sanitizedTokens;
    }

    /**
     * Reset customized tokens back to theme defaults for a channel and theme.
     *
     * @return array<string, mixed>
     */
    public function resetTokens(int $channelId, string $themeCode): array
    {
        $this->layoutSettingRepository->saveSettings(
            'theme_tokens',
            [],
            $channelId,
            $themeCode
        );

        $this->dispatchTokensUpdatedEvent($channelId, $themeCode, 'reset_tokens');

        return [];
    }

    /**
     * Dispatch domain event after successful persistence.
     * Exceptions thrown by event listeners are reported and isolated so they do not
     * fail the completed database write.
     */
    protected function dispatchTokensUpdatedEvent(int $channelId, string $themeCode, string $operation): void
    {
        try {
            Event::dispatch('portal.tokens.updated', [[
                'channel_id' => $channelId,
                'theme_code' => $themeCode,
                'operation' => $operation,
            ]]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
