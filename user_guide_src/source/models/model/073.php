<?php

$result = $accountModel->withLockedRow($id, static function (object $account, $model): bool {
    $account->balance -= 100;

    return $model->save($account);
});
