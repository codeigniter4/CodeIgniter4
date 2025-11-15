<?php

// in controller, checking form submittal
if (! $model->save($user)) {
    // 'withInput()' is what specifies "old data" should be saved.
    return redirect()->back()->withInput();
}

?>

<!-- In your view file: -->
<input type="email" name="email" value="<?= old('email') ?>">

<!-- Or with arrays: -->
<input type="email" name="user[email]" value="<?= old('user.email') ?>">
