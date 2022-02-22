<?php if (! empty($errors)): ?>
    <div class="alert alert-danger">
    <?php foreach ($errors as $field => $error): ?>
        <p><?= $error ?></p>
    <?php endforeach ?>
    </div>
<?php endif ?>
