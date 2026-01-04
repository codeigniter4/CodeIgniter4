<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'پنل مدیریت' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet" type="text/css" />
    <style>
        body { font-family: 'Vazirmatn', sans-serif; background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background-color: #343a40; color: white; }
        .sidebar a { color: #ced4da; text-decoration: none; padding: 10px 15px; display: block; }
        .sidebar a:hover, .sidebar a.active { background-color: #495057; color: white; }
        .main-content { padding: 20px; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 sidebar p-0">
                <div class="p-3 text-center border-bottom border-secondary">
                    <h5>پنل مدیریت</h5>
                    <small>خوش آمدید، <?= session('admin_name') ?></small>
                </div>
                <nav class="mt-3">
                    <a href="<?= base_url('admin/dashboard') ?>" class="<?= uri_string() == 'admin/dashboard' ? 'active' : '' ?>"><i class="bi bi-speedometer2"></i> داشبورد</a>
                    <a href="<?= base_url('admin/admins') ?>" class="<?= strpos(uri_string(), 'admin/admins') !== false ? 'active' : '' ?>"><i class="bi bi-people"></i> مدیران</a>
                    <a href="<?= base_url('admin/simcards') ?>" class="<?= strpos(uri_string(), 'admin/simcards') !== false ? 'active' : '' ?>"><i class="bi bi-sim"></i> سیم‌کارت‌ها</a>
                    <a href="<?= base_url('admin/orders') ?>" class="<?= strpos(uri_string(), 'admin/orders') !== false ? 'active' : '' ?>"><i class="bi bi-cart"></i> سفارشات</a>
                    <a href="<?= base_url('admin/logout') ?>" class="text-danger mt-5"><i class="bi bi-box-arrow-right"></i> خروج</a>
                </nav>
            </div>
            <div class="col-md-10 main-content">
                <?= $this->renderSection('content') ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php if(session()->getFlashdata('success')): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'موفق',
            text: '<?= session()->getFlashdata('success') ?>',
        });
    </script>
    <?php endif; ?>
    <?php if(session()->getFlashdata('error')): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'خطا',
            text: '<?= session()->getFlashdata('error') ?>',
        });
    </script>
    <?php endif; ?>
</body>
</html>
