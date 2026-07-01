<?php

$userModel->where('country', 'US')
    ->chunkRowsById(100, static function ($rows) {
        // do something.
        // $rows is an array of rows representing a chunk of 100 items.
    });
