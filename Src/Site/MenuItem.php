<?php

declare(strict_types=1);

namespace App\Site;

final class MenuItem {
    public function __construct(
        private string $label,
        private string $href
    ) {}

    public function label(): string { return $this->label; }
    public function href(): string { return $this->href; }
}
