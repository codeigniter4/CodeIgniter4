<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<h2 class="mb-4">مدیریت سفارشات</h2>

<div class="card mb-4">
    <div class="card-body">
        <form action="" method="get" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="جستجو (کد رهگیری، نام، موبایل...)" value="<?= $search ?>">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">همه وضعیت‌های پرداخت</option>
                    <option value="success" <?= $status == 'success' ? 'selected' : '' ?>>موفق</option>
                    <option value="pending" <?= $status == 'pending' ? 'selected' : '' ?>>در انتظار</option>
                    <option value="failed" <?= $status == 'failed' ? 'selected' : '' ?>>ناموفق</option>
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
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>کد رهگیری</th>
                        <th>شماره سیم‌کارت</th>
                        <th>خریدار</th>
                        <th>کد ملی</th>
                        <th>موبایل</th>
                        <th>مبلغ (تومان)</th>
                        <th>وضعیت</th>
                        <th>Ref ID</th>
                        <th>تاریخ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($orders as $order): ?>
                    <tr>
                        <td><?= esc($order['tracking_code']) ?></td>
                        <td dir="ltr"><?= esc($order['simcard_number']) ?></td>
                        <td><?= esc($order['buyer_name']) ?></td>
                        <td><?= esc($order['buyer_national_code']) ?></td>
                        <td><?= esc($order['buyer_phone']) ?></td>
                        <td><?= number_format($order['amount'] / 10) ?></td>
                        <td>
                            <?php if($order['payment_status'] == 'success'): ?>
                                <span class="badge bg-success">موفق</span>
                            <?php elseif($order['payment_status'] == 'failed'): ?>
                                <span class="badge bg-danger">ناموفق</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">در انتظار</span>
                            <?php endif; ?>
                        </td>
                        <td><?= esc($order['ref_id']) ?></td>
                        <td dir="ltr"><?= jdate('Y/m/d H:i', strtotime($order['created_at'])) ?></td>
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
<?= $this->endSection() ?>
