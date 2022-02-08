<?php

if ($query->hasError()) {
    echo 'Code: ' . $query->getErrorCode();
    echo 'Error: ' . $query->getErrorMessage();
}
