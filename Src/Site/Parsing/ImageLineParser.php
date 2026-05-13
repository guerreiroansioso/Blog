<?php

declare(strict_types=1);

namespace App\Site\Parsing;

final class ImageLineParser implements LineParser {
    public function supports(string $line): bool {
        return preg_match('/^!\[(.*?)\]\((.+)\)$/', $line) === 1;
    }

    public function parse(string $line, Parser $parser): string {
        return $parser->buildImage($line);
    }
}
