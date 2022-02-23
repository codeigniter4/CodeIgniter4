<?php

foreach ($userAccounts as $user) {
    $validation->reset();
    $validation->setRules($userAccountRules);

    if (! $validation->run($user)) {
        // handle validation errors
    }
}
