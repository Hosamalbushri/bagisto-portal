<?php

namespace Platform\Portal\Services\Tokens;

class DtcgExporter
{
    /**
     * Export tokens in W3C Design Tokens Community Group (DTCG) specification format.
     *
     * @param  array<string, mixed>  $tokens
     * @return array<string, mixed>
     */
    public function export(array $tokens): array
    {
        $dtcg = [
            '$name' => 'Bagisto Portal Design Tokens',
            '$version' => '2.0.0',
            '$description' => 'W3C DTCG & Figma Tokens Studio compliant design tokens',
            'color' => [],
            'dimension' => [],
            'typography' => [],
        ];

        foreach ($tokens as $name => $value) {
            if ($name === 'custom_css') {
                $dtcg['custom_css'] = $value;

                continue;
            }

            $cleanName = ltrim($name, '-');
            if (str_contains($cleanName, 'font')) {
                $dtcg['typography'][$cleanName] = [
                    '$value' => $value,
                    '$type' => 'fontFamily',
                ];
            } elseif (
                str_contains($cleanName, 'radius')
                || str_contains($cleanName, 'spacing')
                || str_contains($cleanName, 'width')
                || str_contains($cleanName, 'height')
                || str_contains($cleanName, 'padding')
            ) {
                $dtcg['dimension'][$cleanName] = [
                    '$value' => $value,
                    '$type' => 'dimension',
                ];
            } else {
                $dtcg['color'][$cleanName] = [
                    '$value' => $value,
                    '$type' => 'color',
                ];
            }
        }

        return $dtcg;
    }

    /**
     * Alias for export() to maintain interface flexibility.
     *
     * @param  array<string, mixed>  $tokens
     * @return array<string, mixed>
     */
    public function exportDtcg(array $tokens): array
    {
        return $this->export($tokens);
    }
}
