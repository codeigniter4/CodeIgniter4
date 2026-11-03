<?php

$csrf = array(
    'name' => $this->security->get_csrf_token_name(),
    'hash' => $this->security->get_csrf_hash()
);

?>

<form>
    <input name="name" type="text">
    <input name="email" type="text">
    <input name="password" type="password">

    <input type="hidden" name="<?= $csrf['name'] ?>" value="<?= $csrf['hash'] ?>">
    <input type="submit" value="Save">
</form>
