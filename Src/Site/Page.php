<?php

declare(strict_types=1);

namespace App\Site;

final class Page
{
    public function __construct(
        private string $slug,
        private string $title,
        private string $body
    ) {
    }

    public function Slug(): string
    {
        return $this->slug;
    }

    public function Title(): string
    {
        return $this->title;
    }

    public function Body(): string
    {
        return $this->body;
    }
}
