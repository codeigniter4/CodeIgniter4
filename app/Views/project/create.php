<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>ایجاد پروژه جدید<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0">ایجاد پروژه جدید</h5>
            </div>
            <div class="card-body">
                <form action="/projects/create" method="post">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="title" class="form-label">عنوان پروژه <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?= (isset($validation) && $validation->hasError('title')) ? 'is-invalid' : '' ?>" id="title" name="title" value="<?= old('title') ?>" placeholder="مثلاً: کاتالوگ محصولات بهاره">
                        <?php if(isset($validation) && $validation->hasError('title')): ?>
                            <div class="invalid-feedback"><?= $validation->getError('title') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">توضیحات (اختیاری)</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="توضیح مختصری درباره این کاتالوگ..."><?= old('description') ?></textarea>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <a href="/dashboard" class="btn btn-outline-secondary">انصراف</a>
                        <button type="submit" class="btn btn-primary px-4">ایجاد پروژه</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
