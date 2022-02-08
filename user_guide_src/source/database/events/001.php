<?php

// In Config\Events.php
Events::on('DBQuery', 'CodeIgniter\Debug\Toolbar\Collectors\Database::collect');

// Collect the queries so something can be done with them later.
public static function collect(CodeIgniter\Database\Query $query)
{
    static::$queries[] = $query;
}
