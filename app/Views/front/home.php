<?= $this->extend('front/layout') ?>

<?= $this->section('content') ?>
<div class="hero">
    <div class="container">
        <h1 class="display-5 fw-bold mb-4">نمایندگی رسمی فروش عمده سیم‌کارت ایرانسل</h1>
        <p class="lead mb-5">استعلام آنلاین و خرید آسان سیم‌کارت</p>

        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-4">
                        <label class="form-label fw-bold">شماره مورد نظر خود را وارد کنید</label>
                        <div class="input-group mb-3" dir="ltr">
                            <input type="text" id="mobileInput" class="form-control text-center fs-5" placeholder="09xxxxxxxxx" maxlength="11">
                            <span class="input-group-text bg-light">IR-MCI</span>
                        </div>
                        <button id="checkBtn" class="btn btn-dark w-100 py-2 fs-5">بررسی شماره</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="row text-center">
        <div class="col-md-4 mb-4">
            <div class="p-4 border rounded shadow-sm bg-white h-100">
                <i class="bi bi-truck fs-1 text-warning mb-3"></i>
                <h4>ارسال سریع</h4>
                <p class="text-muted">تحویل در کمترین زمان ممکن</p>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="p-4 border rounded shadow-sm bg-white h-100">
                <i class="bi bi-shield-check fs-1 text-warning mb-3"></i>
                <h4>خرید امن</h4>
                <p class="text-muted">درگاه پرداخت معتبر و نماد اعتماد</p>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="p-4 border rounded shadow-sm bg-white h-100">
                <i class="bi bi-headset fs-1 text-warning mb-3"></i>
                <h4>پشتیبانی</h4>
                <p class="text-muted">پاسخگویی سریع به سوالات شما</p>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        $('#checkBtn').click(function() {
            var mobile = $('#mobileInput').val().trim();

            // Basic validation
            if (!/^09\d{9}$/.test(mobile)) {
                Swal.fire({
                    icon: 'error',
                    title: 'خطا',
                    text: 'لطفا یک شماره معتبر ۱۱ رقمی وارد کنید که با ۰۹ شروع شود.',
                    confirmButtonText: 'باشه'
                });
                return;
            }

            // AJAX Check
            var btn = $(this);
            btn.prop('disabled', true).text('در حال بررسی...');

            $.ajax({
                url: '<?= base_url("AjaxSearchNumber") ?>',
                type: 'GET',
                data: { mobile: mobile },
                success: function(response) {
                    if (response.found) {
                        window.location.href = '<?= base_url("i/") ?>' + mobile;
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'موجود نیست',
                            text: 'متاسفانه این شماره موجود نیست یا قبلا فروخته شده است.',
                            confirmButtonText: 'باشه'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'خطا',
                        text: 'خطا در برقراری ارتباط با سرور.',
                        confirmButtonText: 'باشه'
                    });
                },
                complete: function() {
                    btn.prop('disabled', false).text('بررسی شماره');
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>
