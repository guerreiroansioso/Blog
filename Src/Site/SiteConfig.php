<?php

declare(strict_types=1);

namespace App\Site;

final class SiteConfig
{
    public function __construct(
        private string $siteName,
        private string $displayName,
        private string $description
    ) {
    }

    public function siteName(): string
    {
        return $this->siteName;
    }

    public function displayName(): string
    {
        return $this->displayName;
    }

    public function description(): string
    {
        return $this->description;
    }
}
