<?php

$model->protect(false)
    ->insert($data)
    ->protect(true);
