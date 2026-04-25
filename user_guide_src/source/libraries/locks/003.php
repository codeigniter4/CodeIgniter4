<?php

$lock = service('locks')->create('imports.customer-feed', 300);

if ($lock->block(10)) {
    try {
        import_customer_feed();
    } finally {
        $lock->release();
    }
}
