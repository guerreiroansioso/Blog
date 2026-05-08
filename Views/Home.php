<?php ?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="stylesheet" href="/Styles.css">
</head>
<body>
  <main class="container">
    <section class="hero">
      <h1><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></h1>
      <p>Escolha um conteúdo para abrir.</p>
    </section>
    <section class="content">
      <ul class="list">
        <?php foreach ($items as $item): ?>
          <li><a href="/page?slug=<?= urlencode($item->Slug()) ?>"><?= htmlspecialchars($item->Title(), ENT_QUOTES, 'UTF-8') ?></a></li>
        <?php endforeach; ?>
      </ul>
    </section>
    <section class="footer">Projeto PHP 8.5</section>
  </main>
</body>
</html>
