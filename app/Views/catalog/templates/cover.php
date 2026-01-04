<?= $this->extend('catalog/templates/base') ?>

<?= $this->section('content') ?>
<div class="page-container" style="justify-content: center; align-items: flex-start; padding: 120px;">
    <?php if(!empty($content['background_image'])): ?>
        <img src="<?= $content['background_image'] ?>" class="bg-image" alt="Cover">
        <div style="position: absolute; top:0; left:0; width:100%; height:100%; background: linear-gradient(to right, rgba(0,0,0,0.7), transparent);"></div>
    <?php endif; ?>

    <div style="z-index: 1; color: white; max-width: 50%;">
        <h1 style="font-size: 120px; margin-bottom: 20px; color: <?= $content['accent_color'] ?? '#3b82f6' ?>;">
            <?= $content['headline'] ?? 'عنوان کاتالوگ' ?>
        </h1>
        <h2 style="font-size: 48px; font-weight: 300; opacity: 0.9;">
            <?= $content['subheadline'] ?? 'زیرعنوان کاتالوگ' ?>
        </h2>
        <div style="margin-top: 60px; font-size: 32px; border-top: 2px solid white; padding-top: 20px; display: inline-block;">
            <?= $content['footer_text'] ?? date('Y') ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
