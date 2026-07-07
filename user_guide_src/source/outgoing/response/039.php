<?php

use CodeIgniter\HTTP\StreamResponse;

return $this->response->stream(static function (StreamResponse $stream) {
    $stream->write("id,email\n");

    foreach (model('UserModel')->findAll() as $user) {
        // write() returns false when the client disconnects
        if (! $stream->write("{$user->id},{$user->email}\n")) {
            break;
        }
    }
});
