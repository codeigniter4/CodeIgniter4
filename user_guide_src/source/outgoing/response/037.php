<?php

use CodeIgniter\HTTP\SSEResponse;

$sse = new SSEResponse(static function (SSEResponse $sse) {
    $sse->comment('keep-alive');

    foreach (['one', 'two', 'three', 'four'] as $text) {
        if (! $sse->event(['text' => $text])) {
            break;
        }
    }

    sleep(1);

    $sse->retry(5000);
});

$sse->setHeader('X-Stream-Name', 'demo');

return $sse;
