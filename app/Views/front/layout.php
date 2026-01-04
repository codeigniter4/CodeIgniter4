<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'نمایندگی فروش سیم‌کارت ایرانسل' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Vazirmatn', sans-serif; background-color: #fcfcfc; }
        .bg-irancell { background-color: #FFD700; color: #333; }
        .btn-irancell { background-color: #FFC107; color: #000; border: none; }
        .btn-irancell:hover { background-color: #FFB300; }
        .hero { background-color: #FFD700; padding: 60px 0; text-align: center; margin-bottom: 40px; }
        footer { background-color: #333; color: #FFD700; padding: 20px 0; margin-top: 50px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-irancell shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?= base_url() ?>">
                <img src="<?= base_url('img/logo.png') ?>" alt="Logo" height="40" class="d-inline-block align-text-top me-2">
                نمایندگی ایرانسل - خسروبیگی
            </a>
            <div class="ms-auto">
                <button class="btn btn-outline-dark btn-sm" data-bs-toggle="modal" data-bs-target="#contactModal">تماس با ما</button>
            </div>
        </div>
    </nav>

    <?= $this->renderSection('content') ?>

    <footer>
        <div class="container text-center">
            <p>&copy; <?= date('Y') ?> تمامی حقوق محفوظ است.</p>
            <div class="mt-3">
                <!-- Enamad Placeholder -->
                <a href="#" class="text-decoration-none text-warning border border-warning p-2 rounded">نماد اعتماد الکترونیکی</a>
            </div>
        </div>
    </footer>

    <!-- Contact Modal -->
    <div class="modal fade" id="contactModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">اطلاعات تماس</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><strong>تلفن:</strong> 02636604705</p>
                    <p><strong>آدرس:</strong> کرج، جاده ملارد، خیابان اهری، نبش نسترن اول</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Rules Modal (Global placeholder) -->
    <div class="modal fade" id="rulesModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">قوانین و مقررات</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>1. مسئولیت صحت اطلاعات وارد شده بر عهده خریدار است.</p>
                    <p>2. سیم‌کارت خریداری شده تنها به نام خریدار (صاحب کد ملی) فعال می‌شود.</p>
                    <p>3. هزینه ارسال یا فعال‌سازی (در صورت وجود) جداگانه محاسبه نمی‌شود مگر ذکر شده باشد.</p>
                    <p>4. زمان تحویل بین 7 تا 10 روز کاری می‌باشد.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
