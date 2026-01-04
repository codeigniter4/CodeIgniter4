<?= $this->extend('catalog/templates/base') ?>

<?= $this->section('content') ?>
<div class="page-container" style="background-color: <?= $content['accent_color'] ?? '#1e293b' ?>; color: white; display: flex; align-items: center; justify-content: center;">
    <div style="text-align: center;">
        <h1 style="font-size: 80px; margin-bottom: 40px;">
            <?= $content['headline'] ?? 'پایان' ?>
        </h1>

        <div style="font-size: 32px; opacity: 0.9; margin-top: 60px;">
            <p><?= $content['contact_info'] ?? 'info@example.com' ?></p>
            <p><?= $content['website'] ?? 'www.example.com' ?></p>
        </div>

        <div style="margin-top: 100px;">
            <img src="/assets/images/logo-placeholder.png" style="height: 100px; opacity: 0.8;">
        </div>
    </div>
</div>
<?= $this->endSection() ?>
