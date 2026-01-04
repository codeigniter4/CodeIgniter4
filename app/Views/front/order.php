<?= $this->extend('front/layout') ?>

<?= $this->section('content') ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-irancell text-center">
                    <h4 class="mb-0">تکمیل خرید سیم‌کارت</h4>
                </div>
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <h2 class="display-6" dir="ltr"><?= $simcard['number'] ?></h2>
                        <h5 class="text-muted mt-2">قیمت: <?= number_format($simcard['price'] / 10) ?> تومان</h5>
                        <div class="mt-3">
                            <span class="badge bg-secondary">دائمی</span>
                            <span class="badge bg-warning text-dark">ایرانسل</span>
                            <span class="badge bg-info text-dark">تحویل ۷-۱۰ روزه</span>
                        </div>
                    </div>

                    <?php if(session()->getFlashdata('errors')): ?>
                        <div class="alert alert-danger">
                            <ul>
                            <?php foreach(session()->getFlashdata('errors') as $error): ?>
                                <li><?= $error ?></li>
                            <?php endforeach ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if(session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
                    <?php endif; ?>

                    <form action="<?= base_url('payment/start') ?>" method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="simcard_id" value="<?= $simcard['id'] ?>">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">نام و نام خانوادگی</label>
                                <input type="text" class="form-control" name="buyer_name" value="<?= old('buyer_name') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">کد ملی</label>
                                <input type="text" class="form-control" name="buyer_national_code" maxlength="10" value="<?= old('buyer_national_code') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">شماره موبایل تماس</label>
                                <input type="text" class="form-control" name="buyer_phone" maxlength="11" placeholder="09xxxxxxxxx" value="<?= old('buyer_phone') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">نام پدر</label>
                                <input type="text" class="form-control" name="father_name" value="<?= old('father_name') ?>" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">تاریخ تولد</label>
                                <div class="row g-2">
                                    <div class="col">
                                        <select class="form-select" name="birth_day" required>
                                            <option value="">روز</option>
                                            <?php for($i=1; $i<=31; $i++) echo "<option value='$i'>$i</option>"; ?>
                                        </select>
                                    </div>
                                    <div class="col">
                                        <select class="form-select" name="birth_month" required>
                                            <option value="">ماه</option>
                                            <?php
                                            $months = ['فروردین','اردیبهشت','خرداد','تیر','مرداد','شهریور','مهر','آبان','آذر','دی','بهمن','اسفند'];
                                            foreach($months as $k => $m) echo "<option value='" . ($k+1) . "'>$m</option>";
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col">
                                        <select class="form-select" name="birth_year" required>
                                            <option value="">سال</option>
                                            <?php for($i=1300; $i<=1400; $i++) echo "<option value='$i'>$i</option>"; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 mt-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="rules" id="rulesCheck" required>
                                    <label class="form-check-label" for="rulesCheck">
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#rulesModal">قوانین و مقررات</a> را مطالعه کرده و می‌پذیرم.
                                    </label>
                                </div>
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-success w-100 py-3 fw-bold fs-5">تایید و پرداخت آنلاین</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
