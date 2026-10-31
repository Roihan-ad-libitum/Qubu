<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? APP_NAME) ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= url('public/css/variables.css') ?>?v=<?= (int) @filemtime(__DIR__ . '/../../public/css/variables.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/components.css') ?>?v=<?= (int) @filemtime(__DIR__ . '/../../public/css/components.css') ?>">
    <?php if (isset($extraCss)): ?>
        <?php foreach ((array)$extraCss as $css):
            $cleanCssPath = explode('?', (string) $css)[0];
            $cssVersion   = strpos((string) $css, '?') === false
                ? ('?v=' . (int) @filemtime(__DIR__ . '/../../' . ltrim($cleanCssPath, '/')))
                : '';
        ?>
            <link rel="stylesheet" href="<?= url($css) . $cssVersion ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    <link rel="stylesheet" href="<?= url('public/css/design-system.css') ?>?v=<?= (int) filemtime(__DIR__ . '/../../public/css/design-system.css') ?>">
</head>
<body>
