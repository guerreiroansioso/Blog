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
        $html = '';
        foreach ($menuItems as $menuItem) {
            $href = htmlspecialchars($menuItem->href(), ENT_QUOTES, 'UTF-8');
            $label = htmlspecialchars($menuItem->label(), ENT_QUOTES, 'UTF-8');
            $html .= "          <a href=\"{$href}\">\n";
            $html .= "            {$label}\n";
            $html .= "          </a>\n";
        }

        return $html;
    },
    'items' => static function (array $items): string {
        $html = '';
        foreach ($items as $item) {
            $href = '/page?slug=' . urlencode(normalizeRequest($item->slug()));
            $title = htmlspecialchars($item->title(), ENT_QUOTES, 'UTF-8');
            $html .= "          <li>\n";
            $html .= "            <a href=\"{$href}\">\n";
            $html .= "              {$title}\n";
            $html .= "            </a>\n";
            $html .= "          </li>\n";
        }

        return $html;
    },
    'footer' => static function (array $footerSections): string {
        if ($footerSections === []) {
            return '';
        }

        $html = "      <footer class=\"footer\">\n";
        foreach ($footerSections as $footerSection) {
            $html .= "        <section class=\"footerSection\">\n";

            if (($footerSection['title'] ?? '') !== '') {
                $safeTitle = htmlspecialchars(
                    $footerSection['title'],
                    ENT_QUOTES,
                    'UTF-8'
                );
                $html .= "          <h2>{$safeTitle}</h2>\n";
            }

            $html .= '          ' . $footerSection['content'] . "\n";
            $html .= "        </section>\n";
        }
        $html .= "      </footer>\n";

        return $html;
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

        $menuHtml = '';
        foreach ($menuItems as $menuItem) {
            $href = $safeText($menuItem->href());
            $label = $safeText($menuItem->label());
            $menuHtml .= "          <a href=\"{$href}\">\n";
            $menuHtml .= "            {$label}\n";
            $menuHtml .= "          </a>\n";
        }

        $itemsHtml = '';
        foreach ($items as $item) {
            $href = '/page?slug=' . urlencode(normalizeRequest($item->slug()));
            $title = $safeText($item->title());
            $itemsHtml .= "          <li>\n";
            $itemsHtml .= "            <a href=\"{$href}\">\n";
            $itemsHtml .= "              {$title}\n";
            $itemsHtml .= "            </a>\n";
            $itemsHtml .= "          </li>\n";
        }

        $footerHtml = '';
        if ($footerSections !== []) {
            $footerHtml = "      <footer class=\"footer\">\n";
            foreach ($footerSections as $footerSection) {
                $footerHtml .= "        <section class=\"footerSection\">\n";
                if (($footerSection['title'] ?? '') !== '') {
                    $title = $safeText($footerSection['title']);
                    $footerHtml .= "          <h2>{$title}</h2>\n";
                }
                $footerHtml .= '          ' . $footerSection['content'] . "\n";
                $footerHtml .= "        </section>\n";
            }
            $footerHtml .= "      </footer>\n";
        }

        return [
            'safePageTitle' => $safeText($pageTitle),
            'safeDisplayName' => $safeText($siteConfig->displayName()),
            'safeDescription' => $safeText($siteConfig->description()),
            'faviconDataUri' => $faviconDataUri,
            'menuHtml' => $menuHtml,
            'itemsHtml' => $itemsHtml,
            'footerHtml' => $footerHtml,
        ];
    },
];
