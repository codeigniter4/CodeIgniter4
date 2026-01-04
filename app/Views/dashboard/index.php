<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>داشبورد<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-3">
        <?= $this->include('components/sidebar') ?>
    </div>
    <div class="col-md-9">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">داشبورد</h1>
            <div class="btn-toolbar mb-2 mb-md-0">
                <a href="/projects/create" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-lg"></i> پروژه جدید
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-header">پروژه‌های فعال</div>
                    <div class="card-body">
                        <h5 class="card-title display-4"><?= $active_projects ?></h5>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card text-white bg-success mb-3">
                    <div class="card-header">اعتبار کیف پول</div>
                    <div class="card-body">
                        <h5 class="card-title display-6"><?= number_format($wallet_balance) ?> <small class="fs-6">تومان</small></h5>
                    </div>
                </div>
            </div>
        </div>

        <h3>پروژه‌های اخیر</h3>
        <div class="table-responsive">
            <table class="table table-striped table-sm align-middle">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">عنوان</th>
                        <th scope="col">وضعیت</th>
                        <th scope="col">تاریخ ایجاد</th>
                        <th scope="col">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent_projects)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">هنوز پروژه‌ای ندارید.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recent_projects as $project): ?>
                            <tr>
                                <td><?= $project['id'] ?></td>
                                <td class="fw-bold"><?= esc($project['title']) ?></td>
                                <td>
                                    <?php
                                        $statusClass = match($project['status']) {
                                            'draft' => 'bg-secondary',
                                            'processing' => 'bg-warning text-dark',
                                            'ready' => 'bg-info text-dark',
                                            'paid' => 'bg-primary',
                                            'completed' => 'bg-success',
                                            default => 'bg-secondary'
                                        };
                                        $statusLabel = match($project['status']) {
                                            'draft' => 'پیش‌نویس',
                                            'processing' => 'در حال پردازش',
                                            'ready' => 'آماده پرداخت',
                                            'paid' => 'پرداخت شده',
                                            'completed' => 'تکمیل شده',
                                            default => $project['status']
                                        };
                                    ?>
                                    <span class="badge rounded-pill <?= $statusClass ?>"><?= $statusLabel ?></span>
                                </td>
                                <td><?= $project['created_at'] ?></td>
                                <td>
                                    <a href="/projects/<?= $project['id'] ?>" class="btn btn-sm btn-outline-primary">ویرایش</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
