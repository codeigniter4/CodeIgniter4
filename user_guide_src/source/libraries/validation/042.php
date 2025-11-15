<?php

// In Controller.
if (! $this->validateData($data, $rules)) {
    return redirect()->back()->withInput();
}
