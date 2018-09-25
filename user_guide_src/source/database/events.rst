###############
Database Events
###############

The Database classes contain a few :doc:`Events </extending/events>` that you can tap into in
order to learn more about what is happening during the database execution. These events can
be used to collect data for analysis and reporting. The :doc:`Debug Toolbar </testing/debugging>`
uses this to collect the queries to display in the Toolbar.

==========
The Events
==========

**DBQuery**

This event is triggered whenever a new query has been run, whether successful or not. The only parameter is
a :doc:`Query </database/queries>` instance of the current query. You could use this to display all queries
in STDOUT, or logging to a file, or even creating tools to do automatic query analysis to help you spot
potentially missing indexes, slow queries, etc. An example usage might be::

    // In Config\Events.php
    Events::on('DBQuery', 'CodeIgniter\Debug\Toolbar\Collectors\Database::collect');

    // Collect the queries so something can be done with them later.
    public static function collect(CodeIgniter\Database\Query $query)
    {
        static::$queries[] = $query;
    }
