<?php

declare(strict_types=1);

namespace App\Site;

final class Page {
    public function __construct(
        private string $slug,
        private string $title,
        private string $body
    ) {}

    public function slug(): string { return $this->slug; }
    public function title(): string { return $this->title; }
    public function body(): string { return $this->body; }
}
