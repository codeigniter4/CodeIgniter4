<?php

// With $logContext = true in Config\Logger.
// The exception is not used as a placeholder, so it is normalized and passed to handlers.
try {
    // Something throws an error here
} catch (\Exception $e) {
    log_message('error', 'Payment processing failed', ['exception' => $e]);
}

// Handlers receive context:
// [
//     'exception' => [
//         'class'   => 'RuntimeException',
//         'message' => 'Something went wrong',
//         'code'    => 0,
//         'file'    => 'app/Controllers/Payment.php',
//         'line'    => 42,
//     ]
// ]
