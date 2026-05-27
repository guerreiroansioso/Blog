<?php

declare(strict_types=1);

function pageThemeVars(array $colors): string {
    if ($colors === []) { return ''; }

    $declarations = '';
    foreach ($colors as $cssVar => $hex) {
        $declarations .= $cssVar . ': ' . $hex . ';';
    }

    return ':root{' . $declarations . '}';
}

return [
    'buildViewData' => static function (
        string $pageTitle,
        object $siteConfig,
        array $menuItems,
        string $contentHtml,
        string $authorName,
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
        $logoFile = __DIR__ . '/../../Public/Logo.png';
        $hasLogoPng = is_file($logoFile);
        $logoHtml = '';
        if ($siteConfig->showLogo()) {
            $logoHtml = $hasLogoPng
                ? '<img class="siteLogo" src="/Logo.png" alt="Logo" />'
                : '<svg class="siteLogo siteLogoSvg" viewBox="0 0 100 100" '
                    . 'xmlns="http://www.w3.org/2000/svg" '
                    . 'role="img" aria-label="Logo">'
                    . '<text x="50" y="72" text-anchor="middle" '
                    . 'font-size="72">&#128214;</text>'
                    . '</svg>';
        }

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

        $authorHtml = '';
        if (trim($authorName) !== '') {
            $authorHtml = '<section class="authorBlock">'
                . '<span class="authorLabel">Autor</span>'
                . '<p class="authorName">' . $safeText($authorName) . '</p>'
                . '</section>';
        }

        return [
            'pageTitle' => $safeText($pageTitle),
            'displayName' => $safeText($siteConfig->displayName()),
            'description' => $safeText($siteConfig->description()),
            'themeCssVars' => pageThemeVars($siteConfig->blogColors()),
            'faviconDataUri' => $faviconDataUri,
            'faviconType' => 'image/svg+xml',
            'logoHtml' => $logoHtml,
            'menuHtml' => $menuHtml,
            'contentHtml' => $contentHtml,
            'authorHtml' => $authorHtml,
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
