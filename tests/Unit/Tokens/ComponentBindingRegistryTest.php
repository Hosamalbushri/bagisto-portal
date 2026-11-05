<?php

namespace Platform\Portal\Tests\Unit\Tokens;

use Platform\Portal\Services\Tokens\ComponentBindingRegistry;
use Platform\Portal\Services\Tokens\TokenCompiler;
use Tests\TestCase;

class ComponentBindingRegistryTest extends TestCase
{
    protected ComponentBindingRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();
        $this->registry = new ComponentBindingRegistry;
    }

    /**
     * Test all 13 expected component IDs are present in exact order.
     */
    public function test_registry_contains_all_13_expected_components_in_order(): void
    {
        $expectedIds = [
            'container',
            'card',
            'button_generic',
            'header',
            'footer',
            'button_primary',
            'button_primary_hover',
            'button_accent',
            'button_accent_hover',
            'button_secondary',
            'form_inputs',
            'typography_rtl',
            'typography_ltr',
        ];

        $this->assertSame($expectedIds, $this->registry->getIds());
        $this->assertCount(13, $this->registry->all());

        foreach ($expectedIds as $id) {
            $this->assertTrue($this->registry->has($id), "Registry missing expected component [{$id}].");
            $this->assertNotNull($this->registry->get($id));
        }
    }

    /**
     * Test structure of each component definition.
     */
    public function test_each_component_definition_has_valid_structure(): void
    {
        foreach ($this->registry->all() as $id => $definition) {
            $this->assertArrayHasKey('id', $definition);
            $this->assertArrayHasKey('name', $definition);
            $this->assertArrayHasKey('selector', $definition);
            $this->assertArrayHasKey('declarations', $definition);
            $this->assertArrayHasKey('tokens', $definition);

            $this->assertSame($id, $definition['id']);
            $this->assertNotEmpty($definition['name']);
            $this->assertNotEmpty($definition['selector']);
            $this->assertIsArray($definition['declarations']);
            $this->assertNotEmpty($definition['declarations']);
            $this->assertIsArray($definition['tokens']);
        }
    }

    /**
     * Test exact selector and declaration match to the original baseline rules.
     */
    public function test_each_component_matches_original_contract(): void
    {
        // 1. container
        $container = $this->registry->get('container');
        $this->assertSame('.portal-container', $container['selector']);
        $this->assertSame('100%', $container['declarations']['width']);
        $this->assertSame('var(--container-max-width, 1280px)', $container['declarations']['max-width']);
        $this->assertSame('auto', $container['declarations']['margin-inline']);
        $this->assertSame('var(--page-padding-x, 1rem)', $container['declarations']['padding-inline']);

        // 2. card
        $card = $this->registry->get('card');
        $this->assertSame('.rounded-card, .portal-card, [data-portal-card], .glass-card, [data-spotlight]', $card['selector']);
        $this->assertSame('var(--card-radius, 1rem) !important', $card['declarations']['border-radius']);

        // 3. button_generic
        $btnGeneric = $this->registry->get('button_generic');
        $this->assertSame('.rounded-btn', $btnGeneric['selector']);
        $this->assertSame('var(--btn-radius, 0.5rem) !important', $btnGeneric['declarations']['border-radius']);

        // 4. header
        $header = $this->registry->get('header');
        $this->assertSame('.portal-header, header[data-portal-header], header.portal-header', $header['selector']);
        $this->assertSame('var(--header-bg, var(--bg-surface)) !important', $header['declarations']['background-color']);
        $this->assertSame('var(--header-text, var(--text-primary)) !important', $header['declarations']['color']);
        $this->assertSame('var(--header-height, 4rem)', $header['declarations']['min-height']);

        // 5. footer
        $footer = $this->registry->get('footer');
        $this->assertSame('.portal-footer, footer[data-portal-footer], footer.portal-footer', $footer['selector']);
        $this->assertSame('var(--footer-bg, var(--bg-surface-elevated)) !important', $footer['declarations']['background-color']);
        $this->assertSame('var(--footer-text, var(--text-secondary)) !important', $footer['declarations']['color']);

        // 6. button_primary
        $btnPrimary = $this->registry->get('button_primary');
        $this->assertSame('.btn-primary, .primary-button, [data-portal-btn="primary"], button.primary-button, a.primary-button', $btnPrimary['selector']);
        $this->assertSame('var(--btn-radius, 0.5rem) !important', $btnPrimary['declarations']['border-radius']);
        $this->assertSame('var(--brand-primary) !important', $btnPrimary['declarations']['background-color']);
        $this->assertSame('var(--on-brand-primary, #ffffff) !important', $btnPrimary['declarations']['color']);

        // 7. button_primary_hover
        $btnPrimaryHover = $this->registry->get('button_primary_hover');
        $this->assertSame('.btn-primary:hover, .primary-button:hover, button.primary-button:hover, a.primary-button:hover', $btnPrimaryHover['selector']);
        $this->assertSame('var(--brand-primary-hover) !important', $btnPrimaryHover['declarations']['background-color']);

        // 8. button_accent
        $btnAccent = $this->registry->get('button_accent');
        $this->assertSame('.btn-accent, [data-portal-btn="accent"]', $btnAccent['selector']);
        $this->assertSame('var(--btn-radius, 0.5rem) !important', $btnAccent['declarations']['border-radius']);
        $this->assertSame('var(--brand-accent) !important', $btnAccent['declarations']['background-color']);
        $this->assertSame('var(--on-brand-accent, #090d16) !important', $btnAccent['declarations']['color']);

        // 9. button_accent_hover
        $btnAccentHover = $this->registry->get('button_accent_hover');
        $this->assertSame('.btn-accent:hover', $btnAccentHover['selector']);
        $this->assertSame('var(--brand-accent-hover) !important', $btnAccentHover['declarations']['background-color']);

        // 10. button_secondary
        $btnSecondary = $this->registry->get('button_secondary');
        $this->assertSame('.btn-secondary, .secondary-button, [data-portal-btn="secondary"], button.secondary-button, a.secondary-button, .btn-outline, .btn-ghost', $btnSecondary['selector']);
        $this->assertSame('var(--btn-radius, 0.5rem) !important', $btnSecondary['declarations']['border-radius']);

        // 11. form_inputs
        $formInputs = $this->registry->get('form_inputs');
        $this->assertSame('input[type="text"], input[type="email"], input[type="password"], input[type="number"], input[type="search"], input[type="tel"], input[type="url"], select, select.custom-select, textarea', $formInputs['selector']);
        $this->assertSame('var(--btn-radius, 0.5rem) !important', $formInputs['declarations']['border-radius']);

        // 12. typography_rtl
        $typoRtl = $this->registry->get('typography_rtl');
        $this->assertSame('[dir="rtl"], [dir="rtl"] *', $typoRtl['selector']);
        $this->assertSame("var(--font-arabic, 'Cairo', system-ui, sans-serif)", $typoRtl['declarations']['font-family']);

        // 13. typography_ltr
        $typoLtr = $this->registry->get('typography_ltr');
        $this->assertSame('[dir="ltr"]', $typoLtr['selector']);
        $this->assertSame("var(--font-latin, 'Inter', system-ui, sans-serif)", $typoLtr['declarations']['font-family']);
    }

    /**
     * Test tokens metadata matches CSS variables consumed in declarations.
     */
    public function test_tokens_metadata_matches_declarations(): void
    {
        foreach ($this->registry->all() as $component) {
            $declarationValues = implode(' ', array_values($component['declarations']));

            foreach ($component['tokens'] as $token) {
                $this->assertStringContainsString(
                    $token,
                    $declarationValues,
                    "Component [{$component['id']}] lists token [{$token}] which is not found in its declarations."
                );
            }
        }
    }

    /**
     * Test deterministic property ordering within declarations.
     */
    public function test_property_ordering_is_deterministic(): void
    {
        $container = $this->registry->get('container');
        $this->assertSame(['width', 'max-width', 'margin-inline', 'padding-inline'], array_keys($container['declarations']));

        $header = $this->registry->get('header');
        $this->assertSame(['background-color', 'color', 'min-height'], array_keys($header['declarations']));

        $footer = $this->registry->get('footer');
        $this->assertSame(['background-color', 'color'], array_keys($footer['declarations']));

        $btnPrimary = $this->registry->get('button_primary');
        $this->assertSame(['border-radius', 'background-color', 'color'], array_keys($btnPrimary['declarations']));
    }

    /**
     * Test unknown component IDs return null and false.
     */
    public function test_unknown_component_id_handling(): void
    {
        $this->assertFalse($this->registry->has('non_existent_component'));
        $this->assertNull($this->registry->get('non_existent_component'));
        $this->assertFalse($this->registry->has('sidebar'));
        $this->assertNull($this->registry->get('sidebar'));
    }

    /**
     * Test direct construction of ComponentBindingRegistry works independently.
     */
    public function test_direct_construction_works(): void
    {
        $direct = new ComponentBindingRegistry;
        $this->assertInstanceOf(ComponentBindingRegistry::class, $direct);
        $this->assertCount(13, $direct->all());
    }

    /**
     * Test direct construction of TokenCompiler works without container and uses default registry.
     */
    public function test_token_compiler_direct_construction_works(): void
    {
        $compiler = new TokenCompiler;
        $css = $compiler->compile(['--brand-primary' => '#1c66f6']);

        $this->assertStringContainsString('.portal-container {', $css);
        $this->assertStringContainsString('.portal-header, header[data-portal-header]', $css);
        $this->assertStringContainsString('.portal-card, [data-portal-card]', $css);
    }

    /**
     * Test manual injection of custom ComponentBindingRegistry into TokenCompiler.
     */
    public function test_manual_registry_injection_into_compiler(): void
    {
        $customRegistry = new class extends ComponentBindingRegistry
        {
            protected function registerDefaults(): void
            {
                $this->definitions = [
                    'custom_widget' => [
                        'id' => 'custom_widget',
                        'name' => 'Custom Widget',
                        'selector' => '.custom-widget-card',
                        'declarations' => [
                            'padding' => '2rem',
                            'border-radius' => 'var(--card-radius, 1rem) !important',
                        ],
                        'tokens' => ['--card-radius'],
                    ],
                ];
            }
        };

        $compiler = new TokenCompiler($customRegistry);
        $css = $compiler->compile(['--card-radius' => '16px']);

        $this->assertStringContainsString('.custom-widget-card {', $css);
        $this->assertStringContainsString('padding: 2rem;', $css);
        $this->assertStringNotContainsString('.portal-container {', $css);
    }

    /**
     * Test compiler integration with default registry produces expected component rules.
     */
    public function test_compiler_integration_with_registry(): void
    {
        $compiler = app(TokenCompiler::class);
        $css = $compiler->compile(['--brand-primary' => '#10b981']);

        foreach ($this->registry->all() as $component) {
            $this->assertStringContainsString($component['selector'], $css);
        }
    }
}
