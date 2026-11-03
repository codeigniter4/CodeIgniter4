<?php

namespace App\Controllers;

class UserController extends BaseController
{
    public function updateUser(int $userID)
    {
        if (! $this->validate([
            'email' => "required|is_unique[users.email,id,{$userID}]",
            'name'  => 'required|alpha_numeric_spaces',
        ])) {
            // The validation failed.
            return view('users/update', [
                'errors' => $this->validator->getErrors(),
            ]);
        }

        // The validation was successful.

        // Get the validated data.
        $validData = $this->validator->getValidated();

        // ...
    }
}
