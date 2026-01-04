<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>مدیریت سیم‌کارت‌ها</h2>
    <div>
        <button type="button" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="bi bi-file-earmark-excel"></i> ایمپورت اکسل
        </button>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSimcardModal">
            <i class="bi bi-plus-lg"></i> افزودن تکی
        </button>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form action="" method="get" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="جستجو شماره..." value="<?= $search ?>">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">همه وضعیت‌ها</option>
                    <option value="free" <?= $status == 'free' ? 'selected' : '' ?>>آزاد</option>
                    <option value="sold" <?= $status == 'sold' ? 'selected' : '' ?>>فروخته شده</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary w-100">فیلتر</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>شماره</th>
                        <th>قیمت (ریال)</th>
                        <th>قیمت (تومان)</th>
                        <th>وضعیت</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($simcards as $sim): ?>
                    <tr>
                        <td><?= $sim['number'] ?></td>
                        <td><?= number_format($sim['price']) ?></td>
                        <td><?= number_format($sim['price'] / 10) ?></td>
                        <td>
                            <?php if($sim['status'] == 'free'): ?>
                                <span class="badge bg-success">آزاد</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">فروخته شده</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editSimModal<?= $sim['id'] ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <?php if($sim['status'] == 'free'): ?>
                            <a href="<?= base_url('admin/simcards/delete/' . $sim['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('آیا از حذف این سیم‌کارت اطمینان دارید؟')">
                                <i class="bi bi-trash"></i>
                            </a>
                            <?php endif; ?>

                            <!-- Edit Modal -->
                            <div class="modal fade" id="editSimModal<?= $sim['id'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">ویرایش قیمت سیم‌کارت <?= $sim['number'] ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="<?= base_url('admin/simcards/update/' . $sim['id']) ?>" method="post">
                                            <?= csrf_field() ?>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">قیمت (ریال)</label>
                                                    <input type="number" class="form-control" name="price" value="<?= $sim['price'] ?>" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                                                <button type="submit" class="btn btn-primary">ذخیره</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            <?= $pager->links() ?>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addSimcardModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">افزودن سیم‌کارت جدید</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('admin/simcards/create') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">شماره (۱۱ رقم با ۰۹)</label>
                        <input type="text" class="form-control" name="number" placeholder="09xxxxxxxxx" required pattern="09\d{9}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">قیمت (ریال)</label>
                        <input type="number" class="form-control" name="price" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-primary">افزودن</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ایمپورت سیم‌کارت از اکسل</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('admin/simcards/import') ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="alert alert-info">
                        فایل اکسل باید دارای دو ستون باشد:<br>
                        ستون اول: شماره (مثلا 9123456789)<br>
                        ستون دوم: قیمت به ریال<br>
                        شماره‌های تکراری نادیده گرفته می‌شوند.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">فایل اکسل (xlsx, xls)</label>
                        <input type="file" class="form-control" name="excel_file" accept=".xlsx, .xls" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-primary">آپلود و پردازش</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Import Results Modal (triggered via session flashdata if needed, but for simplicity we show alert) -->
<?php if(session()->getFlashdata('import_report')): ?>
<div class="modal fade show" id="importResultModal" tabindex="-1" style="display: block; background: rgba(0,0,0,0.5);">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">گزارش ایمپورت</h5>
                <button type="button" class="btn-close" onclick="document.getElementById('importResultModal').remove()"></button>
            </div>
            <div class="modal-body">
                <?= session()->getFlashdata('import_report') ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('importResultModal').remove()">بستن</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
