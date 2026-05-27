<?php
$homeRender = require __DIR__ . '/Home/Renderers.php';
$view = $homeRender['buildViewData'](
    $pageTitle,
    $siteConfig,
    $menuItems,
    $items,
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
  <link rel="shortcut icon" type="<?= $view['faviconType'] ?>" href="<?= $view['faviconDataUri'] ?>">
  <link rel="stylesheet" href="/styles.css">
  <?php if (($view['themeCssVars'] ?? '') !== ''): ?>
  <style><?= $view['themeCssVars'] ?></style>
  <?php endif; ?>
</head>
<body>
  <main class="container">
    <section class="hero">
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
    <section class="content">
      <ul class="list">
        <?= $view['itemsHtml'] ?>
      </ul>
    </section>
    <?= $view['footerHtml'] ?>
  </main>
</body>
</html>
