<?php

\CodeIgniter\Events\Events::trigger('some_events', $foo, $bar, $baz);

Events::on('some_event', static function ($foo, $bar, $baz) {
    // ...
});
