<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="stylesheet" href="/style.css">
</head>
<body>
  <div class="wrap">
    <section class="hero pageHero">
      <h1><?= htmlspecialchars($siteConfig->displayName(), ENT_QUOTES, 'UTF-8') ?></h1>
      <p><?= htmlspecialchars($siteConfig->description(), ENT_QUOTES, 'UTF-8') ?></p>
      <nav class="heroNav">
        <?php foreach ($menuItems as $menuItem): ?>
          <a href="<?= htmlspecialchars($menuItem->href(), ENT_QUOTES, 'UTF-8') ?>">
            <?= htmlspecialchars($menuItem->label(), ENT_QUOTES, 'UTF-8') ?>
          </a>
        <?php endforeach; ?>
      </nav>
    </section>
    <div class="topbar">
      <a class="back" href="/">← Voltar para lista</a>
    </div>

    <article class="card">
      <main><?= $contentHtml ?></main>
    </article>
  </div>
</body>
</html>
