<?php

$builder->where('status', 'pending');

if ($builder->exists()) {
    // At least one pending row exists.
}
