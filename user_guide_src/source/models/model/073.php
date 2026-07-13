<?php

$result = $accountModel->withLockedRow($id, static function (object $account, $model): bool {
    $account->balance -= 100;

    if (! $model->save($account)) {
        throw new RuntimeException('Unable to save the account.');
    }

    return true;
});
