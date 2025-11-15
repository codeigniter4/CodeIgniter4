<?php

if (! $request->isValidIP($ip)) {
    echo 'Not Valid';
} else {
    echo 'Valid';
}
