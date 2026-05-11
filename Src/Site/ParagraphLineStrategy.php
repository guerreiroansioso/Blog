<?php

declare(strict_types=1);

namespace App\Site;

final class ParagraphLineStrategy implements LineParseStrategy {
    public function supports(string $line): bool {
        return true;
    }

    public function parse(string $line, Parser $parser): string {
        return '<p>' . $parser->inline($line) . '</p>';
    }
}
