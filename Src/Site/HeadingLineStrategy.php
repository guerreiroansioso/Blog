<?php

declare(strict_types=1);

namespace App\Site;

final class HeadingLineStrategy implements LineParseStrategy {
    public function supports(string $line): bool {
        return $line !== '' && $line[0] === '#';
    }

    public function parse(string $line, Parser $parser): string {
        return $parser->buildHeading($line);
    }
}
