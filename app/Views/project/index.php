<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>پروژه‌های من<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">پروژه‌های من</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/projects/create" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-lg"></i> پروژه جدید
        </a>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>

<div class="table-responsive">
    <table class="table table-striped table-hover align-middle">
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
            <?php if (empty($projects)): ?>
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">هنوز پروژه‌ای ندارید.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($projects as $project): ?>
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
<?= $this->endSection() ?>
