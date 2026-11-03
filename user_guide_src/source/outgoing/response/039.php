<?php

use CodeIgniter\HTTP\StreamResponse;

return $this->response
    ->stream(static function (StreamResponse $stream) {
        $userModel = model('UserModel');
        $offset    = 0;

        // Fetch rows in batches to keep memory usage low
        while ($users = $userModel->findAll(500, $offset)) {
            foreach ($users as $user) {
                // write() returns false once the client has disconnected
                if (! $stream->write(json_encode($user) . "\n", false)) {
                    return;
                }
            }

            // Push the whole batch to the client at once
            $stream->flush();

            $offset += 500;
        }
    })
    ->setContentType('application/x-ndjson');
