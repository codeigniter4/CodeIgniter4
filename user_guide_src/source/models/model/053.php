<?php

$model->allowCallbacks(false)->find(1); // No callbacks triggered
$model->find(1); // Callbacks subject to original property value
