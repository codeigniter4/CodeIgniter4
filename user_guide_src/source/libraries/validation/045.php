<?php

// In Controller.

if (! $this->validate([
    'username' => 'required',
    'password' => 'required|min_length[10]',
])) {
    // The validation failed.
    return view('login', [
        'errors' => $this->validator->getErrors(),
    ]);
}

// The validation was successful.

// Get the validated data.
$validData = $this->validator->getValidated();
