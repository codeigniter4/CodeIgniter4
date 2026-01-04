<?= $this->extend('catalog/templates/base') ?>

<?= $this->section('content') ?>
<div class="page-container" style="padding: 100px;">
    <h2 style="font-size: 64px; margin-bottom: 60px; color: <?= $content['accent_color'] ?? '#3b82f6' ?>; border-bottom: 3px solid #eee; padding-bottom: 20px;">
        <?= $content['headline'] ?? 'عنوان بخش' ?>
    </h2>

    <div style="column-count: 2; column-gap: 80px;">
        <p style="font-size: 28px; line-height: 1.8; text-align: justify;">
            <?= nl2br($content['body_text'] ?? '') ?>
        </p>
    </div>

    <div style="position: absolute; bottom: 40px; left: 40px; font-size: 24px; color: #888;">
        <?= $page_number ?? 1 ?>
    </div>
</div>
<?= $this->endSection() ?>
