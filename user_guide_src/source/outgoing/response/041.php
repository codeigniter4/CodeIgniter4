<?php

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\StreamResponse;

// Open and validate the upstream source before creating the response,
// so a failure can still produce a proper error page
$source = @fopen('https://storage.internal/reports/annual.pdf', 'rb');

if ($source === false) {
    throw PageNotFoundException::forPageNotFound();
}

$stream = $this->response->stream(static function (StreamResponse $stream) use ($source) {
    // Forward the upstream file chunk by chunk, without buffering it in memory
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
