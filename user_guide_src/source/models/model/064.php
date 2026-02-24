<?php

$userModel->chunkRows(100, static function ($rows) {
    // do something.
    // $rows is an array of rows representing chunk of 100 items.
});
