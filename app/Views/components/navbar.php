<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="/">کاتالوگ‌ساز هوشمند</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active" href="/">خانه</a>
                </li>
                <?php if(session()->get('isLoggedIn')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/dashboard">داشبورد</a>
                    </li>
                <?php endif; ?>
            </ul>
            <div class="d-flex">
                <?php if(session()->get('isLoggedIn')): ?>
                    <div class="dropdown">
                        <a class="btn btn-outline-secondary dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                            <?= session()->get('name') ?>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                            <li><a class="dropdown-item" href="/dashboard">پنل کاربری</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="/logout">خروج</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="/login" class="btn btn-outline-primary me-2">ورود</a>
                    <a href="/register" class="btn btn-primary">ثبت‌نام</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
