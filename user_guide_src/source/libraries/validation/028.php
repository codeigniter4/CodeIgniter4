<?php

if ($validation->hasError('username')) {
    echo $validation->getError('username');
}
