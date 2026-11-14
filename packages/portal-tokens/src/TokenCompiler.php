<?php

namespace Platform\PortalTokens;

class TokenCompiler
{
    protected ComponentBindingRegistry $registry;

    public function __construct(?ComponentBindingRegistry $registry = null)
    {
        $this->registry = $registry ?? new ComponentBindingRegistry;
    }

    /**
     * Compile prepared token declarations into CSS string.
     *
     * @param  array<string, string>  $rootTokens  Prepared and sanitized root tokens.
     * @param  array<string, string>  $darkTokens  Prepared and sanitized dark tokens / overrides.
     * @param  string|null  $customCss  Sanitized custom CSS to append.
     */
    public function compile(array $rootTokens = [], array $darkTokens = [], ?string $customCss = null): string
    {
        if (empty($rootTokens) && empty($darkTokens) && empty($customCss)) {
            return '';
        }

        $rootCss = ":root {\n";
        foreach ($rootTokens as $name => $value) {
            $rootCss .= "  {$name}: {$value};\n";
        }
        $rootCss .= "}\n";

        $darkCss = ".dark,\n[data-theme=\"dark\"] {\n";
        foreach ($darkTokens as $darkName => $darkVal) {
            $darkCss .= "  {$darkName}: {$darkVal};\n";
        }
        $darkCss .= "}\n";

        $fontsUrl = $this->getGoogleFontsUrl($rootTokens);
        $fontImport = $fontsUrl ? "@import url('{$fontsUrl}');\n\n" : '';

        // Global container utility class and universal token bindings from registry
        $layoutUtilities = '';
        foreach ($this->registry->all() as $component) {
            $layoutUtilities .= ($layoutUtilities === '' ? "\n" : '')."{$component['selector']} {\n";
            foreach ($component['declarations'] as $property => $value) {
                $layoutUtilities .= "  {$property}: {$value};\n";
            }
            $layoutUtilities .= "}\n";
        }

        $compiled = $fontImport.$rootCss."\n".$darkCss.$layoutUtilities;

        if (filled($customCss)) {
            $compiled .= "\n/* Additional Custom CSS */\n".$customCss."\n";
        }

        return $compiled;
    }

    /**
     * Compile scoped sparse token declarations into CSS string for a specific scope selector.
     *
     * @param  array<string, string>  $lightTokens  Sanitized explicit and locally derived light tokens.
     * @param  array<string, string>  $darkTokens  Sanitized explicit and locally derived dark tokens.
     * @param  string  $lightSelector  The CSS selector for the light scope.
     * @param  string  $darkSelector  The CSS selector for the dark scope.
     */
    public function compileScoped(
        array $lightTokens,
        array $darkTokens,
        string $lightSelector,
        string $darkSelector
    ): string {
        if (empty($lightTokens) && empty($darkTokens)) {
            return '';
        }

        $css = '';

        if (! empty($lightTokens)) {
            $css .= "{$lightSelector} {\n";
            foreach ($lightTokens as $name => $value) {
                $css .= "  {$name}: {$value};\n";
            }
            $css .= "}\n";
        }

        if (! empty($darkTokens)) {
            if ($css !== '') {
                $css .= "\n";
            }

            $css .= "{$darkSelector} {\n";
            foreach ($darkTokens as $darkName => $darkVal) {
                $css .= "  {$darkName}: {$darkVal};\n";
            }
            $css .= "}\n";
        }

        return $css;
    }

    /**
     * Generate Google Fonts embed URL for any Google fonts specified in tokens.
     */
    public function getGoogleFontsUrl(array $tokens): ?string
    {
        $supportedFonts = [
            'Cairo' => 'Cairo:wght@400;500;600;700;800;900',
            'Tajawal' => 'Tajawal:wght@400;500;700;800',
            'Almarai' => 'Almarai:wght@400;700;800',
            'Readex Pro' => 'Readex+Pro:wght@400;500;600;700',
            'Alexandria' => 'Alexandria:wght@400;500;600;700',
            'IBM Plex Sans Arabic' => 'IBM+Plex+Sans+Arabic:wght@400;500;600;700',
            'Inter' => 'Inter:wght@400;500;600;700;800',
            'Plus Jakarta Sans' => 'Plus+Jakarta+Sans:wght@400;500;600;700;800',
            'Outfit' => 'Outfit:wght@400;500;600;700;800',
            'Poppins' => 'Poppins:wght@400;500;600;700;800',
            'Roboto' => 'Roboto:wght@400;500;700;900',
        ];

        $matched = [];

        foreach ($tokens as $key => $value) {
            if (! is_string($value)) {
                continue;
            }

            foreach ($supportedFonts as $fontName => $fontParam) {
                if (stripos($value, $fontName) !== false) {
                    $matched[$fontName] = $fontParam;
                }
            }
        }

        if (empty($matched)) {
            return null;
        }

        $query = implode('&family=', array_values($matched));

        return "https://fonts.googleapis.com/css2?family={$query}&display=swap";
    }
}
