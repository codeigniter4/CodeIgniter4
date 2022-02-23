<?php

if ($agent->isBrowser('Safari')) {
    echo 'You are using Safari.';
} elseif ($agent->isBrowser()) {
    echo 'You are using a browser.';
}
