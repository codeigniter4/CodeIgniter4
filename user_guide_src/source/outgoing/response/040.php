<?php

// Any iterable of string chunks works, including generators.
$chunks = static function (): \Generator {
    foreach (model('LogModel')->findAll() as $log) {
        yield json_encode($log) . "\n";
    }
};

return $this->response->stream($chunks());
