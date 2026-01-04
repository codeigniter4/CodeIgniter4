<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>صفحه اصلی<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="px-4 py-5 my-5 text-center">
    <h1 class="display-5 fw-bold text-primary">کاتالوگ‌ساز هوشمند</h1>
    <div class="col-lg-6 mx-auto">
        <p class="lead mb-4">با استفاده از هوش مصنوعی، کاتالوگ‌ها و بروشورهای حرفه‌ای بسازید. فایل Word خود را آپلود کنید و تصاویر خود را اضافه کنید تا در چند دقیقه کاتالوگ شما آماده شود.</p>
        <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
            <?php if(session()->get('isLoggedIn')): ?>
                <a href="/dashboard" class="btn btn-primary btn-lg px-4 gap-3">برو به داشبورد</a>
            <?php else: ?>
                <a href="/register" class="btn btn-primary btn-lg px-4 gap-3">شروع کنید</a>
                <a href="/login" class="btn btn-outline-secondary btn-lg px-4">ورود</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="container px-4 py-5" id="features">
    <h2 class="pb-2 border-bottom">ویژگی‌ها</h2>
    <div class="row g-4 py-5 row-cols-1 row-cols-lg-3">
        <div class="feature col">
            <h3 class="fs-2">هوش مصنوعی</h3>
            <p>تحلیل محتوا و پیشنهاد ساختار کاتالوگ با استفاده از GPT-4 و Claude.</p>
        </div>
        <div class="feature col">
            <h3 class="fs-2">طراحی خودکار</h3>
            <p>صفحه‌آرایی خودکار با قالب‌های زیبا و حرفه‌ای.</p>
        </div>
        <div class="feature col">
            <h3 class="fs-2">خروجی باکیفیت</h3>
            <p>دریافت فایل نهایی با کیفیت بالا مناسب برای چاپ و وب.</p>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
