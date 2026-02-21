<?php

$context = service('context');
$context->set('user_id', 123);
$context->set('transaction_id', 'txn_12345');

log_message('error', 'Payment processing failed');
