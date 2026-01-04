<?= $this->extend('catalog/templates/base') ?>

<?= $this->section('content') ?>
<div class="page-container" style="padding: 60px;">
    <div style="text-align: center; margin-bottom: 50px;">
        <h2 style="font-size: 56px; color: <?= $content['accent_color'] ?? '#3b82f6' ?>;">
            <?= $content['headline'] ?? 'گالری تصاویر' ?>
        </h2>
    </div>

    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; height: 800px;">
        <?php
        $images = $content['gallery_images'] ?? [];
        // Fill up to 6 slots
        for($i=0; $i<6; $i++):
            $img = $images[$i] ?? null;
        ?>
            <div style="background: #f0f0f0; border-radius: 15px; overflow: hidden; position: relative;">
                <?php if($img): ?>
                    <img src="<?= $img ?>" style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                    <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #ccc;">
                        Image <?= $i+1 ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endfor; ?>
    </div>

    <div style="position: absolute; bottom: 40px; left: 40px; font-size: 24px; color: #888;">
        <?= $page_number ?? 1 ?>
    </div>
</div>
<?= $this->endSection() ?>
