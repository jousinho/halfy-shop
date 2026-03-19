<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Feed;

final class DescriptionParser
{
    public function parse(string $description): array
    {
        $technique  = null;
        $dimensions = null;

        foreach (explode("\n", $description) as $line) {
            $line = trim($line);

            if (preg_match('/^[Tt]écnica\s*:\s*(.+)$/u', $line, $m)) {
                $technique = trim($m[1]);
            } elseif (preg_match('/^[Mm]edidas(?:\s+de\s+la\s+estampa)?\s*:\s*(.+)$/u', $line, $m)) {
                $dimensions = trim($m[1]);
            }
        }

        return [
            'technique'  => $technique,
            'dimensions' => $dimensions,
        ];
    }
}
