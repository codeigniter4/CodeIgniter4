<?php

use CodeIgniter\HTTP\StreamResponse;

$stream = $this->response->stream(static function (StreamResponse $stream) {
    // Forward the upstream file chunk by chunk, without buffering it in memory
    $source = fopen('https://storage.internal/reports/annual.pdf', 'rb');

    try {
        while (! feof($source)) {
            $chunk = fread($source, 1_048_576);

            if ($chunk === false || ! $stream->write($chunk)) {
                break;
            }
        }
    } finally {
        fclose($source);
    }
});

$stream->setContentType('application/pdf');

return $stream;
