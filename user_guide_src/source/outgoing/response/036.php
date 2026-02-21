<?php

use CodeIgniter\HTTP\SSEResponse;

return new SSEResponse(static function (SSEResponse $sse) {
    foreach (['Hello', 'World'] as $text) {
        if (! $sse->event(['text' => $text])) {
            break;
        }

        sleep(1);
    }

    $sse->event('[DONE]');
});
