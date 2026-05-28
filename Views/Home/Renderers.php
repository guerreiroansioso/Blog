<?php

declare(strict_types=1);

function homeThemeVars(array $colors): string {
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
        array $items,
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

        ob_start();
        foreach ($items as $item) {
            $href = '/page?slug=' . urlencode(normalizeRequest($item->slug()));
            $title = $safeText($item->title());
            ?>
            <li>
              <a href="<?= $href ?>">
                <?= $title ?>
              </a>
            </li>
            <?php
        }
        $itemsHtml = ob_get_clean();

        $footerHtml = '';
        if ($footerSections !== []) {
            ob_start();
            ?>
            <footer class="footer">
            <?php
            foreach ($footerSections as $footerSection) {
                $titleHtml = '';
                if (($footerSection['title'] ?? '') !== '') {
                    $title = $safeText($footerSection['title']);
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

        $themeCssVars = homeThemeVars($siteConfig->blogColors());
        $themeStyleTag = '';
        if ($themeCssVars !== '') {
            ob_start();
            ?>
            <style><?= $themeCssVars ?></style>
            <?php
            $themeStyleTag = ob_get_clean();
        }

        return [
            'pageTitle' => $safeText($pageTitle),
            'displayName' => $safeText($siteConfig->displayName()),
            'description' => $safeText($siteConfig->description()),
            'themeCssVars' => $themeCssVars,
            'themeStyleTag' => $themeStyleTag,
            'faviconDataUri' => $faviconDataUri,
            'faviconType' => 'image/svg+xml',
            'logoHtml' => $logoHtml,
            'menuHtml' => $menuHtml,
            'itemsHtml' => $itemsHtml,
            'footerHtml' => $footerHtml,
        ];
    },
];
