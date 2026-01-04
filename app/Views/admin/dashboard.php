<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<h2 class="mb-4">داشبورد</h2>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5 class="card-title">کل سیم‌کارت‌ها</h5>
                <p class="card-text display-6"><?= number_format($totalSimcards) ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title">سیم‌کارت‌های آزاد</h5>
                <p class="card-text display-6"><?= number_format($freeSimcards) ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-secondary">
            <div class="card-body">
                <h5 class="card-title">سیم‌کارت‌های فروخته شده</h5>
                <p class="card-text display-6"><?= number_format($soldSimcards) ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h5 class="card-title">سفارشات امروز</h5>
                <p class="card-text display-6"><?= number_format($todayOrders) ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <h5 class="card-title">سفارشات این ماه</h5>
                <p class="card-text display-6"><?= number_format($monthOrders) ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-dark">
            <div class="card-body">
                <h5 class="card-title">مجموع فروش (تومان)</h5>
                <p class="card-text display-6"><?= number_format($totalSales / 10) ?></p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">10 سفارش آخر</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>کد رهگیری</th>
                        <th>خریدار</th>
                        <th>مبلغ (تومان)</th>
                        <th>وضعیت</th>
                        <th>تاریخ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($lastOrders as $order): ?>
                    <tr>
                        <td><?= esc($order['tracking_code']) ?></td>
                        <td><?= esc($order['buyer_name']) ?></td>
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
                        <td dir="ltr"><?= jdate('Y/m/d H:i', strtotime($order['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
