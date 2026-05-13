<?php

declare(strict_types=1);

namespace App\Site\Parsing;

final class LineParserFactory {
    /**
     * @return list<LineParser>
     */
    public function createAllParsers(): array {
        return [
            new ImageLineParser(),
            new HeadingLineParser(),
            new ParagraphLineParser(),
        ];
    }
}
