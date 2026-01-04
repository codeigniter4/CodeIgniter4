<?= $this->extend('front/layout') ?>

<?= $this->section('content') ?>
<div class="container py-5 text-center">
    <div class="card shadow-sm mx-auto" style="max-width: 500px;">
        <div class="card-body p-5">
            <i class="bi bi-x-circle-fill text-danger" style="font-size: 5rem;"></i>
            <h2 class="mt-4 text-danger">پرداخت ناموفق</h2>
            <p class="lead mt-3"><?= $message ?? 'عملیات پرداخت با خطا مواجه شد.' ?></p>

            <p class="text-muted mt-3">چنانچه مبلغی از حساب شما کسر شده است، طی ۲۴ ساعت آینده به حساب شما بازخواهد گشت.</p>

            <div class="d-grid gap-2 mt-4">
                <a href="javascript:history.back()" class="btn btn-warning">تلاش مجدد</a>
                <a href="<?= base_url() ?>" class="btn btn-outline-secondary">بازگشت به صفحه اصلی</a>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
