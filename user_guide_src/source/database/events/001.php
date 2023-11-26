<?php

// In app/Config/Events.php

namespace Config;

use CodeIgniter\Events\Events;
use CodeIgniter\Exceptions\FrameworkException;
use CodeIgniter\HotReloader\HotReloader;

// ...

Events::on(
    'DBQuery',
    static function (\CodeIgniter\Database\Query $query) {
        log_message('info', (string) $query);
    }
);
