<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="stylesheet" href="/Styles.css">
</head>
<body>
  <div class="wrap">
    <div class="topbar">
      <a class="back" href="/">← Voltar para lista</a>
    </div>

    <article class="card">
      <main><?= $contentHtml ?></main>
    </article>
  </div>
</body>
</html>
