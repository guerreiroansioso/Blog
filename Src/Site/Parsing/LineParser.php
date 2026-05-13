<?php

declare(strict_types=1);

namespace App\Site\Parsing;

interface LineParser {
    public function supports(string $line): bool;
    public function parse(string $line, Parser $parser): string;
}
