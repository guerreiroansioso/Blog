<?php
$pageRender = require __DIR__ . '/Page/Renderers.php';
$view = $pageRender['buildViewData'](
    $pageTitle,
    $siteConfig,
    $menuItems,
    $contentHtml,
    $authorName,
    $sidebarHtml,
    $sidebarItems,
    $pagination,
    $showBackLink,
    $footerSections
);
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $view['pageTitle'] ?></title>
  <link rel="icon" type="<?= $view['faviconType'] ?>" href="<?= $view['faviconDataUri'] ?>" sizes="any">
  <link rel="stylesheet" href="/styles.css">
  <?= $view['themeStyleTag'] ?>
</head>
<body>
  <div class="wrap">
    <section class="hero pageHero">
      <div class="heroHead">
        <?= $view['logoHtml'] ?>
        <div class="heroText">
          <h1><?= $view['displayName'] ?></h1>
          <p><?= $view['description'] ?></p>
        </div>
      </div>
      <nav class="heroNav">
        <?= $view['menuHtml'] ?>
      </nav>
    </section>
    <?= $view['backLinkHtml'] ?>

    <div class="<?= $view['pageLayoutClass'] ?>">
      <article class="card">
        <main>
          <?= $view['authorHtml'] ?>
          <?= $view['contentHtml'] ?>
        </main>
        <?= $view['paginationHtml'] ?>
      </article>
      <?= $view['sidebarsHtml'] ?>
    </div>
    <?= $view['footerHtml'] ?>
  </div>
</body>
</html>
