<?php

declare(strict_types=1);

namespace App\Site\Model;

final class SiteConfig {
    public function __construct(
        private string $siteName,
        private string $displayName,
        private string $description,
        private bool $hidePageList,
        private bool $showLogo,
        private bool $useLogoAsFavicon,
        private array $blogColors = []
    ) {}

    public function siteName(): string { return $this->siteName; }
    public function displayName(): string { return $this->displayName; }
    public function description(): string { return $this->description; }
    public function hidePageList(): bool { return $this->hidePageList; }
    public function showLogo(): bool { return $this->showLogo; }
    public function useLogoAsFavicon(): bool { return $this->useLogoAsFavicon; }
    public function blogColors(): array { return $this->blogColors; }
}
