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
            ob_start();
            if ($hasLogoPng) {
                ?>
                <img class="siteLogo" src="/Logo.png" alt="Logo" />
                <?php
            } else {
                ?>
                <svg class="siteLogo siteLogoSvg" viewBox="0 0 100 100"
                     xmlns="http://www.w3.org/2000/svg"
                     role="img" aria-label="Logo">
                  <text x="50" y="72" text-anchor="middle"
                        font-size="72">&#128214;</text>
                </svg>
                <?php
            }
            $logoHtml = ob_get_clean();
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

        $paginationHtml = '';
        if (($pagination['links'] ?? []) !== []) {
            ob_start();
            ?>
            <nav class="pagination" aria-label="Paginação">
            <?php
            foreach ($pagination['links'] as $paginationLink) {
                $labelValue = $paginationLink['label'] ?? '';
                $label = is_string($labelValue) ? $safeText($labelValue) : '';
                if ($paginationLink['isCurrent']) {
                    ?>
                    <span aria-current="page">
                      <?= $label ?>
                    </span>
                    <?php
                } else {
                    $hrefValue = $paginationLink['href'] ?? '';
                    $href = is_string($hrefValue) ? $safeText($hrefValue) : '';
                    ?>
                    <a href="<?= $href ?>">
                      <?= $label ?>
                    </a>
                    <?php
                }
            }
            ?>
            </nav>
            <?php
            $paginationHtml = ob_get_clean();
        }

        $sidebarsHtml = '';
        if ($sidebarHtml !== '') {
            ob_start();
            ?>
            <div class="sidebarStack">
            <?php
            foreach ($sidebarItems as $sidebarItem) {
                ?>
                <aside class="sidebar">
                  <?= $sidebarItem ?>
                </aside>
                <?php
            }
            ?>
            </div>
            <?php
            $sidebarsHtml = ob_get_clean();
        }

        $footerHtml = '';
        if ($footerSections !== []) {
            ob_start();
            ?>
            <footer class="footer">
            <?php
            foreach ($footerSections as $footerSection) {
                $titleHtml = '';
                if (($footerSection['title'] ?? '') !== '') {
                    $titleValue = $footerSection['title'] ?? '';
                    $title = is_string($titleValue) ? $safeText($titleValue) : '';
                    $titleHtml = "<h2>{$title}</h2>\n";
                }
                $footerContent = ($footerSection['content'] ?? '') . "\n";
                ?>
                <section class="footerSection">
                  <?= $titleHtml ?>
                  <?= $footerContent ?>
                </section>
                <?php
            }
            ?>
            </footer>
            <?php
            $footerHtml = ob_get_clean();
        }

        $authorHtml = '';
        if (trim($authorName) !== '') {
            ob_start();
            $safeAuthorName = $safeText($authorName);
            ?>
            <section class="authorBlock">
              <span class="authorLabel">Autor</span>
              <p class="authorName"><?= $safeAuthorName ?></p>
            </section>
            <?php
            $authorHtml = ob_get_clean();
        }

        $themeCssVars = pageThemeVars($siteConfig->blogColors());
        $backLinkHtml = '';
        if ($showBackLink) {
            ob_start();
            ?>
            <div class="topbar">
              <a class="back" href="/">← Voltar para o início</a>
            </div>
            <?php
            $backLinkHtml = ob_get_clean();
        }

        return [
            'pageTitle' => $safeText($pageTitle),
            'displayName' => $safeText($siteConfig->displayName()),
            'description' => $safeText($siteConfig->description()),
            'themeCssVars' => $themeCssVars,
            'themeStyleTag' => $themeCssVars !== ''
                ? '<style>' . $themeCssVars . '</style>'
                : '',
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
            'backLinkHtml' => $backLinkHtml,
        ];
    },
];
