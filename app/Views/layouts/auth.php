<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title') ?> - ورود/ثبت‌نام</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet" type="text/css" />

    <style>
        body {
            font-family: 'Vazirmatn', sans-serif;
            background-color: #f1f5f9;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h3 class="fw-bold text-primary">کاتالوگ‌ساز هوشمند</h3>
                        </div>
                        <?= $this->renderSection('content') ?>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <a href="/" class="text-decoration-none text-muted">بازگشت به صفحه اصلی</a>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
