<?php

$userModel->chunk(100, static function ($data) {
    // do something.
    // $data is a single row of data.
});
