<?php

declare(strict_types=1);

return [
    'buildViewData' => static function (
        string $pageTitle,
        object $siteConfig,
        array $menuItems,
        string $contentHtml,
        string $sidebarHtml,
        array $sidebarItems,
        array $pagination,
        bool $showBackLink,
        array $footerSections
    ): array {
        $safeText = static fn(string $text): string => htmlspecialchars(
            $text,
            ENT_QUOTES,
            'UTF-8'
        );

        $faviconDataUri = "data:image/svg+xml,"
            . "%3Csvg xmlns='http://www.w3.org/2000/svg'"
            . " viewBox='0 0 100 100'%3E%3Ctext y='.9em' font-size='90'%3E"
            . "%26%23128214;%3C/text%3E%3C/svg%3E";

        ob_start();
        foreach ($menuItems as $menuItem) {
            $href = $safeText($menuItem->href());
            $label = $safeText($menuItem->label());
            ?>
            <a href="<?= $href ?>">
              <?= $label ?>
            </a>
            <?php
        }
        $menuHtml = ob_get_clean();

        ob_start();
        if (($pagination['links'] ?? []) !== []) {
            ?>
            <nav class="pagination" aria-label="Paginação">
            <?php foreach ($pagination['links'] as $paginationLink): ?>
                <?php if ($paginationLink['isCurrent']): ?>
                <span aria-current="page">
                  <?= $safeText((string) $paginationLink['label']) ?>
                </span>
                <?php else: ?>
                <a href="<?= $safeText((string) $paginationLink['href']) ?>">
                  <?= $safeText((string) $paginationLink['label']) ?>
                </a>
                <?php endif; ?>
            <?php endforeach; ?>
            </nav>
            <?php
        }
        $paginationHtml = ob_get_clean();

        ob_start();
        if ($sidebarHtml !== '') {
            ?>
            <div class="sidebarStack">
            <?php foreach ($sidebarItems as $sidebarItem): ?>
              <aside class="sidebar">
                <?= $sidebarItem ?>
              </aside>
            <?php endforeach; ?>
            </div>
            <?php
        }
        $sidebarsHtml = ob_get_clean();

        $footerHtml = '';
        if ($footerSections !== []) {
            ob_start();
            ?>
            <footer class="footer">
            <?php foreach ($footerSections as $footerSection): ?>
              <section class="footerSection">
                <?php if (($footerSection['title'] ?? '') !== ''): ?>
                  <h2><?= $safeText((string) $footerSection['title']) ?></h2>
                <?php endif; ?>
                <?= ($footerSection['content'] ?? '') . "\n" ?>
              </section>
            <?php endforeach; ?>
            </footer>
            <?php
            $footerHtml = ob_get_clean();
        }

        return [
            'pageTitle' => $safeText($pageTitle),
            'displayName' => $safeText($siteConfig->displayName()),
            'description' => $safeText($siteConfig->description()),
            'faviconDataUri' => $faviconDataUri,
            'menuHtml' => $menuHtml,
            'contentHtml' => $contentHtml,
            'pageLayoutClass' => $sidebarHtml !== ''
                ? 'pageLayout hasSidebar'
                : 'pageLayout',
            'paginationHtml' => $paginationHtml,
            'sidebarsHtml' => $sidebarsHtml,
            'footerHtml' => $footerHtml,
            'showBackLink' => $showBackLink,
        ];
    },
];
