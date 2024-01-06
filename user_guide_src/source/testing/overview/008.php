<?php

use CodeIgniter\Events\Events;

Events::on('foo', static function ($arg) use (&$result) {
    $result = $arg;
});

Events::trigger('foo', 'bar');

$this->assertEventTriggered('foo');
