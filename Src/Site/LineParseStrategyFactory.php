<?php

declare(strict_types=1);

namespace App\Site;

final class LineParseStrategyFactory {
    /**
     * @return list<LineParseStrategy>
     */
    public function createAll(): array {
        return [
            new ImageLineStrategy(),
            new HeadingLineStrategy(),
            new ParagraphLineStrategy(),
        ];
    }
}
