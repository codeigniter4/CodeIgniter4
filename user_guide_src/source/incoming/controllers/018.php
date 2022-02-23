<?php

public function updateUser(int $userID)
{
    if (! $this->validate('userRules')) {
        return view('users/update', [
            'errors' => $this->validator->getErrors()
        ]);
    }

    // do something here if successful...
}
