<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= lang('Errors.404pageNotFound') ?></title>

    <style>
        <?= preg_replace('#[\r\n\t ]+#', ' ', file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'debug.css')) ?>
    </style>
</head>
<body>
    <div class="container text-center">

        <h1 class="headline"><?= lang('Errors.404pageNotFound') ?></h1>

        <p class="lead">
            <?php if (! empty($message) && $message !== '(null)') : ?>
                <?= nl2br(esc($message)) ?>
            <?php else : ?>
                <?= lang('Errors.404sorryCannotFind') ?>
            <?php endif ?>
        </p>
    </div>
</body>
</html>
