<?php

declare(strict_types=1);

namespace App\Site;

final class NotFoundPage extends Page {
    public function isNotFound(): bool {
        return true;
    }
}
