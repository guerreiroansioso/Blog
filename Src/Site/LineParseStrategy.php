<?php

declare(strict_types=1);

namespace App\Site;

interface LineParseStrategy {
    public function supports(string $line): bool;
    public function parse(string $line, Parser $parser): string;
}
