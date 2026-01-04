<?= $this->extend('catalog/templates/base') ?>

<?= $this->section('content') ?>
<div class="page-container">
    <?php if(!empty($content['main_image'])): ?>
        <img src="<?= $content['main_image'] ?>" class="bg-image">
    <?php else: ?>
        <div class="bg-image" style="background: #333;"></div>
    <?php endif; ?>

    <div style="position: absolute; bottom: 0; left: 0; width: 100%; background: linear-gradient(to top, rgba(0,0,0,0.9), transparent); padding: 100px 60px 60px;">
        <h2 style="font-size: 64px; color: white; margin-bottom: 20px;">
            <?= $content['headline'] ?? '' ?>
        </h2>
        <p style="font-size: 32px; color: rgba(255,255,255,0.9); max-width: 80%;">
            <?= $content['body_text'] ?? '' ?>
        </p>
    </div>

    <div style="position: absolute; bottom: 40px; left: 40px; font-size: 24px; color: rgba(255,255,255,0.5);">
        <?= $page_number ?? 1 ?>
    </div>
</div>
<?= $this->endSection() ?>
