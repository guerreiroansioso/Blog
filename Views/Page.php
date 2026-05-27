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
  <link rel="icon" href="<?= $view['faviconDataUri'] ?>">
  <link rel="stylesheet" href="/styles.css">
  <?php if (($view['themeCssVars'] ?? '') !== ''): ?>
  <style><?= $view['themeCssVars'] ?></style>
  <?php endif; ?>
</head>
<body>
  <div class="wrap">
    <section class="hero pageHero">
      <h1><?= $view['displayName'] ?></h1>
      <p><?= $view['description'] ?></p>
      <nav class="heroNav">
        <?= $view['menuHtml'] ?>
      </nav>
    </section>
    <?php if ($view['showBackLink']): ?>
      <div class="topbar">
        <a class="back" href="/">← Voltar para o início</a>
      </div>
    <?php endif; ?>

    <div class="<?= $view['pageLayoutClass'] ?>">
      <article class="card">
        <?= $view['authorHtml'] ?>
        <main><?= $view['contentHtml'] ?></main>
        <?= $view['paginationHtml'] ?>
      </article>
      <?= $view['sidebarsHtml'] ?>
    </div>
    <?= $view['footerHtml'] ?>
  </div>
</body>
</html>
