<?php

// With $logContext = true in Config\Logger.
// 'user_id' is not used as a placeholder, so it is passed to handlers as structured data.
log_message('error', 'Payment failed for order {order_id}', [
    'order_id' => 'ord_999', // interpolated into the message, stripped from context by default
    'user_id'  => 42,        // not in message, kept and passed to handlers
]);

// Handlers receive context: ['user_id' => 42]
