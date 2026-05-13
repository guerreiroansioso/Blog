<?php

declare(strict_types=1);

return [
    'safeText' => static function (string $text): string {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    },
    'faviconDataUri' => static function (): string {
        return "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg'"
            . " viewBox='0 0 100 100'%3E%3Ctext y='.9em' font-size='90'%3E"
            . "%26%23128214;%3C/text%3E%3C/svg%3E";
    },
    'menu' => static function (array $menuItems): string {
        ob_start();
        foreach ($menuItems as $menuItem) {
            $href = htmlspecialchars($menuItem->href(), ENT_QUOTES, 'UTF-8');
            $label = htmlspecialchars($menuItem->label(), ENT_QUOTES, 'UTF-8');
            ?>
            <a href="<?= $href ?>">
              <?= $label ?>
            </a>
            <?php
        }

        $output = ob_get_clean();

        return $output;
    },
    'items' => static function (array $items): string {
        ob_start();
        foreach ($items as $item) {
            $href = '/page?slug=' . urlencode(normalizeRequest($item->slug()));
            $title = htmlspecialchars($item->title(), ENT_QUOTES, 'UTF-8');
            ?>
            <li>
              <a href="<?= $href ?>">
                <?= $title ?>
              </a>
            </li>
            <?php
        }

        $output = ob_get_clean();

        return $output;
    },
    'footer' => static function (array $footerSections): string {
        if ($footerSections === []) {
            return '';
        }

        ob_start();
        ?>
        <footer class="footer">
        <?php
        foreach ($footerSections as $footerSection) {
            $safeTitle = '';
            $titleHtml = '';
            if ($footerSection['title'] !== '') {
                $safeTitle = htmlspecialchars(
                    $footerSection['title'],
                    ENT_QUOTES,
                    'UTF-8'
                );
                $titleHtml = "<h2>{$safeTitle}</h2>\n";
            }
            ?>
          <section class="footerSection">
            <?= $titleHtml ?>
            <?= $footerSection['content'] . "\n" ?>
          </section>
            <?php
        }
        ?>
        </footer>
        <?php
        $output = ob_get_clean();

        return $output;
    },
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
                ?>
              <section class="footerSection">
                <?= $titleHtml ?>
                <?= $footerSection['content'] . "\n" ?>
              </section>
                <?php
            }
            ?>
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
            'itemsHtml' => $itemsHtml,
            'footerHtml' => $footerHtml,
        ];
    },
];
