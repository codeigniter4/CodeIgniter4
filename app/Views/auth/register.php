<?= $this->extend('layouts/auth') ?>

<?= $this->section('title') ?>ثبت‌نام<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <h4 class="mb-4 text-center">ایجاد حساب کاربری</h4>

    <form action="/register" method="post">
        <?= csrf_field() ?>
        <div class="mb-3">
            <label for="name" class="form-label">نام و نام خانوادگی</label>
            <input type="text" class="form-control <?= (isset($validation) && $validation->hasError('name')) ? 'is-invalid' : '' ?>" id="name" name="name" value="<?= old('name') ?>">
            <?php if(isset($validation) && $validation->hasError('name')): ?>
                <div class="invalid-feedback"><?= $validation->getError('name') ?></div>
            <?php endif; ?>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">ایمیل</label>
            <input type="email" class="form-control <?= (isset($validation) && $validation->hasError('email')) ? 'is-invalid' : '' ?>" id="email" name="email" value="<?= old('email') ?>">
            <?php if(isset($validation) && $validation->hasError('email')): ?>
                <div class="invalid-feedback"><?= $validation->getError('email') ?></div>
            <?php endif; ?>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">رمز عبور</label>
            <input type="password" class="form-control <?= (isset($validation) && $validation->hasError('password')) ? 'is-invalid' : '' ?>" id="password" name="password">
            <?php if(isset($validation) && $validation->hasError('password')): ?>
                <div class="invalid-feedback"><?= $validation->getError('password') ?></div>
            <?php endif; ?>
        </div>
        <div class="mb-3">
            <label for="confirm_password" class="form-label">تکرار رمز عبور</label>
            <input type="password" class="form-control <?= (isset($validation) && $validation->hasError('confirm_password')) ? 'is-invalid' : '' ?>" id="confirm_password" name="confirm_password">
            <?php if(isset($validation) && $validation->hasError('confirm_password')): ?>
                <div class="invalid-feedback"><?= $validation->getError('confirm_password') ?></div>
            <?php endif; ?>
        </div>
        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-success">ثبت‌نام</button>
        </div>
        <div class="mt-3 text-center">
            <a href="/login">قبلاً ثبت‌نام کرده‌اید؟ وارد شوید</a>
        </div>
    </form>
<?= $this->endSection() ?>
