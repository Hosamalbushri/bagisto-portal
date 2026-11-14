<?php

namespace Platform\PortalTokens;

class ComponentBindingRegistry
{
    /**
     * Component binding definitions map: id => definition.
     *
     * @var array<string, array<string, mixed>>
     */
    protected array $definitions = [];

    public function __construct()
    {
        $this->registerDefaults();
    }

    /**
     * Retrieve all registered component binding definitions in order.
     *
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        return $this->definitions;
    }

    /**
     * Retrieve a specific component definition by ID.
     *
     * @return array<string, mixed>|null
     */
    public function get(string $id): ?array
    {
        return $this->definitions[$id] ?? null;
    }

    /**
     * Check if a component definition exists.
     */
    public function has(string $id): bool
    {
        return isset($this->definitions[$id]);
    }

    /**
     * Get all registered component identifiers in order.
     *
     * @return array<string>
     */
    public function getIds(): array
    {
        return array_keys($this->definitions);
    }

    /**
     * Initialize trusted, code-owned component binding definitions.
     */
    protected function registerDefaults(): void
    {
        $this->definitions = [
            'container' => [
                'id' => 'container',
                'name' => 'Layout Container',
                'selector' => '.portal-container',
                'declarations' => [
                    'width' => '100%',
                    'max-width' => 'var(--container-max-width, 1280px)',
                    'margin-inline' => 'auto',
                    'padding-inline' => 'var(--page-padding-x, 1rem)',
                ],
                'tokens' => [
                    '--container-max-width',
                    '--page-padding-x',
                ],
            ],
            'card' => [
                'id' => 'card',
                'name' => 'Card Components',
                'selector' => '.rounded-card, .portal-card, [data-portal-card], .glass-card, [data-spotlight]',
                'declarations' => [
                    'border-radius' => 'var(--card-radius, 1rem) !important',
                ],
                'tokens' => [
                    '--card-radius',
                ],
            ],
            'button_generic' => [
                'id' => 'button_generic',
                'name' => 'Generic Rounded Button',
                'selector' => '.rounded-btn',
                'declarations' => [
                    'border-radius' => 'var(--btn-radius, 0.5rem) !important',
                ],
                'tokens' => [
                    '--btn-radius',
                ],
            ],
            'header' => [
                'id' => 'header',
                'name' => 'Portal Header',
                'selector' => '.portal-header, header[data-portal-header], header.portal-header',
                'declarations' => [
                    'background-color' => 'var(--header-bg, var(--bg-surface)) !important',
                    'color' => 'var(--header-text, var(--text-primary)) !important',
                    'min-height' => 'var(--header-height, 4rem)',
                ],
                'tokens' => [
                    '--header-bg',
                    '--bg-surface',
                    '--header-text',
                    '--text-primary',
                    '--header-height',
                ],
            ],
            'footer' => [
                'id' => 'footer',
                'name' => 'Portal Footer',
                'selector' => '.portal-footer, footer[data-portal-footer], footer.portal-footer',
                'declarations' => [
                    'background-color' => 'var(--footer-bg, var(--bg-surface-elevated)) !important',
                    'color' => 'var(--footer-text, var(--text-secondary)) !important',
                ],
                'tokens' => [
                    '--footer-bg',
                    '--bg-surface-elevated',
                    '--footer-text',
                    '--text-secondary',
                ],
            ],
            'button_primary' => [
                'id' => 'button_primary',
                'name' => 'Primary Button',
                'selector' => '.btn-primary, .primary-button, [data-portal-btn="primary"], button.primary-button, a.primary-button',
                'declarations' => [
                    'border-radius' => 'var(--btn-radius, 0.5rem) !important',
                    'background-color' => 'var(--brand-primary) !important',
                    'color' => 'var(--on-brand-primary, #ffffff) !important',
                ],
                'tokens' => [
                    '--btn-radius',
                    '--brand-primary',
                    '--on-brand-primary',
                ],
            ],
            'button_primary_hover' => [
                'id' => 'button_primary_hover',
                'name' => 'Primary Button (Hover)',
                'selector' => '.btn-primary:hover, .primary-button:hover, button.primary-button:hover, a.primary-button:hover',
                'declarations' => [
                    'background-color' => 'var(--brand-primary-hover) !important',
                ],
                'tokens' => [
                    '--brand-primary-hover',
                ],
            ],
            'button_accent' => [
                'id' => 'button_accent',
                'name' => 'Accent Button',
                'selector' => '.btn-accent, [data-portal-btn="accent"]',
                'declarations' => [
                    'border-radius' => 'var(--btn-radius, 0.5rem) !important',
                    'background-color' => 'var(--brand-accent) !important',
                    'color' => 'var(--on-brand-accent, #090d16) !important',
                ],
                'tokens' => [
                    '--btn-radius',
                    '--brand-accent',
                    '--on-brand-accent',
                ],
            ],
            'button_accent_hover' => [
                'id' => 'button_accent_hover',
                'name' => 'Accent Button (Hover)',
                'selector' => '.btn-accent:hover',
                'declarations' => [
                    'background-color' => 'var(--brand-accent-hover) !important',
                ],
                'tokens' => [
                    '--brand-accent-hover',
                ],
            ],
            'button_secondary' => [
                'id' => 'button_secondary',
                'name' => 'Secondary Button',
                'selector' => '.btn-secondary, .secondary-button, [data-portal-btn="secondary"], button.secondary-button, a.secondary-button, .btn-outline, .btn-ghost',
                'declarations' => [
                    'border-radius' => 'var(--btn-radius, 0.5rem) !important',
                ],
                'tokens' => [
                    '--btn-radius',
                ],
            ],
            'form_inputs' => [
                'id' => 'form_inputs',
                'name' => 'Form Inputs and Controls',
                'selector' => 'input[type="text"], input[type="email"], input[type="password"], input[type="number"], input[type="search"], input[type="tel"], input[type="url"], select, select.custom-select, textarea',
                'declarations' => [
                    'border-radius' => 'var(--btn-radius, 0.5rem) !important',
                ],
                'tokens' => [
                    '--btn-radius',
                ],
            ],
            'typography_rtl' => [
                'id' => 'typography_rtl',
                'name' => 'RTL Typography',
                'selector' => '[dir="rtl"], [dir="rtl"] *',
                'declarations' => [
                    'font-family' => "var(--font-arabic, 'Cairo', system-ui, sans-serif)",
                ],
                'tokens' => [
                    '--font-arabic',
                ],
            ],
            'typography_ltr' => [
                'id' => 'typography_ltr',
                'name' => 'LTR Typography',
                'selector' => '[dir="ltr"]',
                'declarations' => [
                    'font-family' => "var(--font-latin, 'Inter', system-ui, sans-serif)",
                ],
                'tokens' => [
                    '--font-latin',
                ],
            ],
        ];
    }
}
