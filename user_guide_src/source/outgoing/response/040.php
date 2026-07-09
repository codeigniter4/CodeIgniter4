<?php

// Any iterable of string chunks works, including generators.
$chunks = static function (): \Generator {
    yield json_encode(['event' => 'started']) . "\n";
    yield json_encode(['event' => 'finished']) . "\n";
};

return $this->response->stream($chunks());
