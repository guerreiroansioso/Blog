<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ctext y='.9em' font-size='90'%3E%26%23128214;%3C/text%3E%3C/svg%3E">
  <link rel="stylesheet" href="/Styles.css">
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
    <?php if ($showBackLink): ?>
      <div class="topbar">
        <a class="back" href="/">← Voltar para o início</a>
      </div>
    <?php endif; ?>

    <div class="<?= $sidebarHtml !== '' ? 'pageLayout hasSidebar' : 'pageLayout' ?>">
      <article class="card">
        <main><?= $contentHtml ?></main>
        <?php if ($pagination['links'] !== []): ?>
          <nav class="pagination" aria-label="Paginação">
            <?php foreach ($pagination['links'] as $paginationLink): ?>
              <?php if ($paginationLink['isCurrent']): ?>
                <span aria-current="page"><?= htmlspecialchars($paginationLink['label'], ENT_QUOTES, 'UTF-8') ?></span>
              <?php else: ?>
                <a href="<?= htmlspecialchars($paginationLink['href'], ENT_QUOTES, 'UTF-8') ?>">
                  <?= htmlspecialchars($paginationLink['label'], ENT_QUOTES, 'UTF-8') ?>
                </a>
              <?php endif; ?>
            <?php endforeach; ?>
          </nav>
        <?php endif; ?>
      </article>
      <?php if ($sidebarHtml !== ''): ?>
        <div class="sidebarStack">
          <?php foreach ($sidebarItems as $sidebarItem): ?>
            <aside class="sidebar">
              <?= $sidebarItem ?>
            </aside>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
    <?php if ($footerSections !== []): ?>
      <footer class="footer">
        <?php foreach ($footerSections as $footerSection): ?>
          <section class="footerSection">
            <?php if ($footerSection['title'] !== ''): ?>
              <h2><?= htmlspecialchars($footerSection['title'], ENT_QUOTES, 'UTF-8') ?></h2>
            <?php endif; ?>
            <?= $footerSection['content'] ?>
          </section>
        <?php endforeach; ?>
      </footer>
    <?php endif; ?>
  </div>
</body>
</html>
