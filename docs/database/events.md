# Database Events

The Database classes contain a few [Events](../extending/events.md) that you can tap into in order to learn more about what is happening during the database execution. These events can be used to collect data for analysis and reporting. The [Debug Toolbar](../testing/debugging.md) uses this to collect the queries to display in the Toolbar.

## The Events

### DBQuery

This event is triggered whenever a new query has been run, whether successful or not. The only parameter is a [Query](queries.md) instance of the current query.

You could use this to display all queries in STDOUT, to log them to a file, or even to create tools for automatic query analysis to help you spot potentially missing indexes, slow queries, etc.

An example to log all queries:

```php
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
    },
);
```
