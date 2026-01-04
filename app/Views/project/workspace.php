<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>محیط کاربری<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-0"><?= esc($project['title']) ?></h1>
            <span class="text-muted small">وضعیت: <?= $project['status'] ?></span>
        </div>
        <div>
            <button class="btn btn-outline-primary me-2">پیش‌نمایش</button>
            <button class="btn btn-primary">ذخیره نهایی</button>
        </div>
    </div>

    <!-- Workspace Layout -->
    <div class="row" style="height: calc(100vh - 150px);">
        <!-- Assets Panel -->
        <div class="col-md-2 border-end bg-white overflow-auto h-100 p-0">
            <div class="p-3 border-bottom">
                <h6 class="fw-bold mb-0">فایل‌ها</h6>
            </div>
            <div class="p-3">
                <div class="d-grid gap-2 mb-3">
                    <button class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('fileUpload').click()">
                        <i class="bi bi-upload"></i> آپلود فایل
                    </button>
                    <input type="file" id="fileUpload" hidden multiple>
                </div>
                <!-- Assets List -->
                <?php if (empty($assets)): ?>
                    <div class="text-center text-muted small mt-5">
                        فایلی آپلود نشده است
                    </div>
                <?php else: ?>
                    <div class="row g-2">
                        <?php foreach ($assets as $asset): ?>
                            <div class="col-6">
                                <div class="card h-100">
                                    <?php if ($asset['type'] == 'image'): ?>
                                        <img src="/<?= $asset['file_path'] ?>" class="card-img-top" alt="<?= esc($asset['original_name']) ?>" style="height: 60px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 60px;">
                                            <i class="bi bi-file-earmark-word fs-3 text-primary"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="card-body p-1 text-center">
                                        <small class="d-block text-truncate" title="<?= esc($asset['original_name']) ?>"><?= esc($asset['original_name']) ?></small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Chat / AI Interface -->
        <div class="col-md-4 border-end bg-light d-flex flex-column h-100 p-0">
            <div class="p-3 border-bottom bg-white">
                <h6 class="fw-bold mb-0">دستیار هوشمند</h6>
            </div>

            <div class="flex-grow-1 overflow-auto p-3" id="chat-messages">
                <!-- Chat Messages Placeholder -->
                <div class="d-flex mb-3">
                    <div class="flex-shrink-0">
                        <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">AI</div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="bg-white p-3 rounded shadow-sm">
                            سلام! من آماده‌ام تا در طراحی کاتالوگ به شما کمک کنم. فایل‌های خود را آپلود کنید تا شروع کنیم.
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-3 bg-white border-top">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="پیام خود را بنویسید...">
                    <button class="btn btn-primary" type="button">ارسال</button>
                </div>
            </div>
        </div>

        <!-- Preview Panel -->
        <div class="col-md-6 bg-secondary bg-opacity-10 overflow-auto h-100 p-4">
            <div class="d-flex justify-content-center">
                <div class="bg-white shadow-sm" style="width: 100%; max-width: 800px; aspect-ratio: 16/9; display: flex; align-items: center; justify-content: center;">
                    <span class="text-muted">پیش‌نمایش صفحه</span>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="/assets/js/upload.js"></script>
<script src="/assets/js/chat.js"></script>
<script src="/assets/js/workspace.js"></script>
<?= $this->endSection() ?>
