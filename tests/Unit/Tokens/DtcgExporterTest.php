<?php

namespace Platform\Portal\Tests\Unit\Tokens;

use Platform\Portal\Services\TokenCompilerService;
use Platform\Portal\Services\Tokens\DtcgExporter;
use Tests\TestCase;

class DtcgExporterTest extends TestCase
{
    protected DtcgExporter $exporter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->exporter = new DtcgExporter;
    }

    /**
     * Test exporting empty token array produces standard DTCG metadata headers and empty group maps.
     */
    public function test_exports_empty_token_array_with_valid_metadata_headers(): void
    {
        $dtcg = $this->exporter->export([]);

        $this->assertEquals('Bagisto Portal Design Tokens', $dtcg['$name']);
        $this->assertEquals('2.0.0', $dtcg['$version']);
        $this->assertEquals('W3C DTCG & Figma Tokens Studio compliant design tokens', $dtcg['$description']);
        $this->assertSame([], $dtcg['color']);
        $this->assertSame([], $dtcg['dimension']);
        $this->assertSame([], $dtcg['typography']);
        $this->assertArrayNotHasKey('custom_css', $dtcg);
    }

    /**
     * Test normal supported tokens are classified into color, dimension, and typography.
     */
    public function test_exports_normal_supported_tokens(): void
    {
        $tokens = [
            '--brand-primary' => '#1c66f6',
            '--radius-base' => '16px',
            '--font-arabic' => "'Cairo', sans-serif",
        ];

        $dtcg = $this->exporter->export($tokens);

        $this->assertEquals([
            '$value' => '#1c66f6',
            '$type' => 'color',
        ], $dtcg['color']['brand-primary']);

        $this->assertEquals([
            '$value' => '16px',
            '$type' => 'dimension',
        ], $dtcg['dimension']['radius-base']);

        $this->assertEquals([
            '$value' => "'Cairo', sans-serif",
            '$type' => 'fontFamily',
        ], $dtcg['typography']['font-arabic']);
    }

    /**
     * Test custom_css is preserved at the root level of the exported array.
     */
    public function test_preserves_custom_css_at_root(): void
    {
        $tokens = [
            '--brand-primary' => '#1c66f6',
            'custom_css' => '.custom-card { backdrop-filter: blur(8px); }',
        ];

        $dtcg = $this->exporter->export($tokens);

        $this->assertEquals('.custom-card { backdrop-filter: blur(8px); }', $dtcg['custom_css']);
        $this->assertArrayHasKey('brand-primary', $dtcg['color']);
    }

    /**
     * Test dimension token classification keywords (radius, spacing, width, height, padding).
     */
    public function test_classifies_dimension_keywords_properly(): void
    {
        $tokens = [
            '--card-radius' => '12px',
            '--grid-spacing' => '1.5rem',
            '--container-max-width' => '1280px',
            '--header-height' => '64px',
            '--page-padding' => '2rem',
        ];

        $dtcg = $this->exporter->export($tokens);

        $this->assertArrayHasKey('card-radius', $dtcg['dimension']);
        $this->assertEquals('dimension', $dtcg['dimension']['card-radius']['$type']);

        $this->assertArrayHasKey('grid-spacing', $dtcg['dimension']);
        $this->assertEquals('dimension', $dtcg['dimension']['grid-spacing']['$type']);

        $this->assertArrayHasKey('container-max-width', $dtcg['dimension']);
        $this->assertEquals('dimension', $dtcg['dimension']['container-max-width']['$type']);

        $this->assertArrayHasKey('header-height', $dtcg['dimension']);
        $this->assertEquals('dimension', $dtcg['dimension']['header-height']['$type']);

        $this->assertArrayHasKey('page-padding', $dtcg['dimension']);
        $this->assertEquals('dimension', $dtcg['dimension']['page-padding']['$type']);
    }

    /**
     * Test type preservation in export values.
     */
    public function test_type_preservation_in_export(): void
    {
        $tokens = [
            '--opacity-level' => '0.85',
            '--shadow-ambient' => '0 4px 12px rgba(0,0,0,0.1)',
        ];

        $dtcg = $this->exporter->export($tokens);

        $this->assertEquals('0.85', $dtcg['color']['opacity-level']['$value']);
        $this->assertEquals('0 4px 12px rgba(0,0,0,0.1)', $dtcg['color']['shadow-ambient']['$value']);
    }

    /**
     * Test exportDtcg alias on exporter.
     */
    public function test_export_dtcg_alias(): void
    {
        $tokens = ['--brand-primary' => '#059669'];
        $dtcg = $this->exporter->exportDtcg($tokens);

        $this->assertEquals('#059669', $dtcg['color']['brand-primary']['$value']);
    }

    /**
     * Test backward compatibility delegation through TokenCompilerService.
     */
    public function test_backward_compatibility_through_token_compiler_service(): void
    {
        $service = app(TokenCompilerService::class);
        $tokens = [
            '--brand-primary' => '#7c3aed',
            '--btn-radius' => '8px',
        ];

        $dtcg = $service->exportDtcg($tokens);

        $this->assertEquals('Bagisto Portal Design Tokens', $dtcg['$name']);
        $this->assertEquals('#7c3aed', $dtcg['color']['brand-primary']['$value']);
        $this->assertEquals('8px', $dtcg['dimension']['btn-radius']['$value']);
    }

    /**
     * Test direct construction works without container or external dependencies.
     */
    public function test_direct_construction_works_independently(): void
    {
        $exporter = new DtcgExporter;
        $dtcg = $exporter->export(['--brand-primary' => '#10b981']);

        $this->assertIsArray($dtcg);
        $this->assertEquals('#10b981', $dtcg['color']['brand-primary']['$value']);
    }
}
