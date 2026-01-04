<?= $this->extend('layouts/auth') ?>

<?= $this->section('title') ?>ورود<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <h4 class="mb-4 text-center">ورود به حساب کاربری</h4>

    <?php if(session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <form action="/login" method="post">
        <?= csrf_field() ?>
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
        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary">ورود</button>
        </div>
        <div class="mt-3 text-center">
            <a href="/register">حساب کاربری ندارید؟ ثبت‌نام کنید</a>
        </div>
    </form>
<?= $this->endSection() ?>
