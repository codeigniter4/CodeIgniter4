<?= $this->extend('catalog/templates/base') ?>

<?= $this->section('content') ?>
<div class="page-container" style="padding: 80px;">
    <div class="grid-2" style="height: 100%; align-items: center;">
        <div>
            <h2 style="font-size: 64px; margin-bottom: 40px; color: <?= $content['accent_color'] ?? '#3b82f6' ?>;">
                <?= $content['headline'] ?? 'عنوان صفحه' ?>
            </h2>
            <div class="content-box">
                <p style="white-space: pre-line;">
                    <?= $content['body_text'] ?? 'متن نمونه برای این صفحه...' ?>
                </p>
                <?php if(!empty($content['bullet_points'])): ?>
                    <ul style="font-size: 24px; margin-top: 30px; line-height: 1.8;">
                        <?php foreach($content['bullet_points'] as $point): ?>
                            <li><?= $point ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
        <div style="height: 100%; display: flex; align-items: center; justify-content: center;">
            <?php if(!empty($content['main_image'])): ?>
                <img src="<?= $content['main_image'] ?>" class="img-fluid" style="max-height: 800px; box-shadow: 0 20px 40px rgba(0,0,0,0.2);">
            <?php else: ?>
                <div style="width: 100%; height: 600px; background: #eee; display: flex; align-items: center; justify-content: center; border-radius: 20px;">
                    <span style="font-size: 40px; color: #aaa;">تصویر</span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Page Number -->
    <div style="position: absolute; bottom: 40px; left: 40px; font-size: 24px; color: #888;">
        <?= $page_number ?? 1 ?>
    </div>
</div>
<?= $this->endSection() ?>
