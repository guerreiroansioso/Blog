<?php

declare(strict_types=1);

namespace App\Site\Parsing;

final class HeadingLineParser implements LineParser {
    public function supports(string $line): bool {
        return $line !== '' && $line[0] === '#';
    }

    public function parse(string $line, Parser $parser): string {
        return $parser->buildHeading($line);
    }
}
