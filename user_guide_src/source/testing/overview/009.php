<?php

Events::on('foo', function ($arg) use(&$result) {
    $result = $arg;
});

Events::trigger('foo', 'bar');

$this->assertEventTriggered('foo');
