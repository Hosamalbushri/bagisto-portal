<?php

namespace Platform\Portal\Tests\Unit\Tokens;

use Platform\Portal\Services\TokenCompilerService;
use Platform\Portal\Services\Tokens\DtcgExporter;
use Platform\Portal\Services\Tokens\DtcgImporter;
use Platform\Portal\Services\Tokens\TokenSanitizer;
use Tests\TestCase;

class DtcgImporterTest extends TestCase
{
    protected DtcgImporter $importer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->importer = new DtcgImporter(new TokenSanitizer);
    }

    /**
     * Test 1: Empty token input produces empty array.
     */
    public function test_imports_empty_dtcg_input(): void
    {
        $imported = $this->importer->import([]);
        $this->assertSame([], $imported);

        $importedEmptyGroups = $this->importer->import([
            'color' => [],
            'dimension' => [],
            'typography' => [],
        ]);
        $this->assertSame([], $importedEmptyGroups);
    }

    /**
     * Test 2: Normal supported tokens in standard DTCG object format.
     */
    public function test_imports_normal_supported_tokens(): void
    {
        $dtcg = [
            'color' => [
                'brand-primary' => [
                    '$value' => '#1c66f6',
                    '$type' => 'color',
                ],
            ],
            'dimension' => [
                'radius-base' => [
                    '$value' => '16px',
                    '$type' => 'dimension',
                ],
            ],
            'typography' => [
                'font-arabic' => [
                    '$value' => "'Cairo', sans-serif",
                    '$type' => 'fontFamily',
                ],
            ],
            'custom_css' => '.header { backdrop-filter: blur(4px); }',
        ];

        $imported = $this->importer->import($dtcg);

        $this->assertEquals('#1c66f6', $imported['--brand-primary']);
        $this->assertEquals('16px', $imported['--radius-base']);
        $this->assertEquals("'Cairo', sans-serif", $imported['--font-arabic']);
        $this->assertEquals('.header { backdrop-filter: blur(4px); }', $imported['custom_css']);
    }

    /**
     * Test 3: Supports alternative 'value' key alongside '$value' and scalar direct values.
     */
    public function test_supports_alternative_value_key_and_direct_scalars(): void
    {
        $dtcg = [
            'color' => [
                'primary' => ['value' => '#ff0000'],
                'secondary' => '#00ff00',
            ],
            'radius' => [
                'card' => ['value' => '12px'],
            ],
            'shadow' => [
                'glow' => '0 0 10px rgba(0,0,0,0.5)',
            ],
        ];

        $imported = $this->importer->import($dtcg);

        $this->assertEquals('#ff0000', $imported['--primary']);
        $this->assertEquals('#00ff00', $imported['--secondary']);
        $this->assertEquals('12px', $imported['--card']);
        $this->assertEquals('0 0 10px rgba(0,0,0,0.5)', $imported['--glow']);
    }

    /**
     * Test 4: Missing $value is safely ignored.
     */
    public function test_handles_missing_value_gracefully(): void
    {
        $dtcg = [
            'color' => [
                'valid' => ['$value' => '#1c66f6'],
                'missing-value' => ['$type' => 'color', '$description' => 'No value provided'],
                'null-value' => ['$value' => null],
                'empty-value' => ['$value' => ''],
            ],
        ];

        $imported = $this->importer->import($dtcg);

        $this->assertArrayHasKey('--valid', $imported);
        $this->assertArrayNotHasKey('--missing-value', $imported);
        $this->assertArrayNotHasKey('--null-value', $imported);
        $this->assertArrayNotHasKey('--empty-value', $imported);
    }

    /**
     * Test 5: Nested token groups without top-level value are handled safely without errors.
     */
    public function test_handles_nested_token_groups_safely(): void
    {
        $dtcg = [
            'color' => [
                'brand' => [
                    'primary' => ['$value' => '#1c66f6'],
                ],
                'direct' => ['$value' => '#333333'],
            ],
        ];

        $imported = $this->importer->import($dtcg);

        // 'direct' has top-level $value and is imported
        $this->assertEquals('#333333', $imported['--direct']);
        // 'brand' lacks direct $value and is excluded safely without PHP warnings/notices
        $this->assertArrayNotHasKey('--brand', $imported);
    }

    /**
     * Test 6: Unsupported top-level token groups are safely ignored.
     */
    public function test_ignores_unsupported_groups(): void
    {
        $dtcg = [
            'color' => [
                'primary' => ['$value' => '#1c66f6'],
            ],
            'transition' => [
                'ease' => ['$value' => 'cubic-bezier(0.4, 0, 0.2, 1)'],
            ],
            'elevation' => [
                'high' => ['$value' => '16dp'],
            ],
        ];

        $imported = $this->importer->import($dtcg);

        $this->assertArrayHasKey('--primary', $imported);
        $this->assertArrayNotHasKey('--ease', $imported);
        $this->assertArrayNotHasKey('--high', $imported);
    }

    /**
     * Test 7: Malformed input (non-array group values or invalid types) does not trigger exceptions.
     */
    public function test_handles_malformed_input(): void
    {
        $dtcg = [
            'color' => 'not an array',
            'dimension' => 12345,
            'typography' => false,
            'custom_css' => ['not', 'a', 'string'],
        ];

        $imported = $this->importer->import($dtcg);
        $this->assertSame([], $imported);
    }

    /**
     * Test 8: Sanitizes imported tokens to prevent CSS injection.
     */
    public function test_sanitizes_imported_tokens_strictly(): void
    {
        $dtcg = [
            'color' => [
                'malicious-key;injection' => ['$value' => '#1c66f6'],
                'bad-value' => ['$value' => 'red; background: url(https://attacker.com/leak)'],
                'valid' => ['$value' => '#059669'],
            ],
            'custom_css' => '<script>alert("xss")</script>.safe { color: blue; }',
        ];

        $imported = $this->importer->import($dtcg);

        $this->assertArrayNotHasKey('--malicious-key;injection', $imported);
        $this->assertArrayNotHasKey('--bad-value', $imported);
        $this->assertEquals('#059669', $imported['--valid']);
        $this->assertStringNotContainsString('<script>', $imported['custom_css']);
        $this->assertStringContainsString('.safe { color: blue; }', $imported['custom_css']);
    }

    /**
     * Test 9: Bidirectional round-trip export and import.
     */
    public function test_round_trip_export_and_import(): void
    {
        $exporter = new DtcgExporter;

        $original = [
            '--brand-primary' => '#1c66f6',
            '--radius-base' => '16px',
            '--font-arabic' => "'Cairo', sans-serif",
            'custom_css' => '.box { border: 1px solid black; }',
        ];

        $dtcg = $exporter->export($original);
        $imported = $this->importer->import($dtcg);

        $this->assertEquals($original['--brand-primary'], $imported['--brand-primary']);
        $this->assertEquals($original['--radius-base'], $imported['--radius-base']);
        $this->assertEquals($original['--font-arabic'], $imported['--font-arabic']);
        $this->assertEquals($original['custom_css'], $imported['custom_css']);
    }

    /**
     * Test 10: Backward compatibility delegation through TokenCompilerService.
     */
    public function test_backward_compatibility_through_token_compiler_service(): void
    {
        $service = app(TokenCompilerService::class);

        $dtcg = [
            'color' => [
                'brand-primary' => ['$value' => '#4338ca'],
            ],
            'dimension' => [
                'btn-radius' => ['$value' => '6px'],
            ],
        ];

        $imported = $service->importDtcg($dtcg);

        $this->assertEquals('#4338ca', $imported['--brand-primary']);
        $this->assertEquals('6px', $imported['--btn-radius']);
    }

    /**
     * Test 11: Direct construction works without container.
     */
    public function test_direct_construction_works_without_container(): void
    {
        $importer = new DtcgImporter;
        $imported = $importer->import([
            'color' => [
                'brand-primary' => ['$value' => '#10b981'],
            ],
        ]);

        $this->assertEquals('#10b981', $imported['--brand-primary']);
    }
}
