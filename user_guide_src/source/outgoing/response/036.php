<?php

use CodeIgniter\HTTP\SSEResponse;

return $this->response->eventStream(static function (SSEResponse $sse) {
    foreach (['Hello', 'World'] as $text) {
        if (! $sse->event(['text' => $text])) {
            break;
        }

        sleep(1);
    }

    $sse->event('[DONE]');
});
