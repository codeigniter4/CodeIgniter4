# Upgrade HTML Tables

## Documentation

- [HTML Table Documentation CodeIgniter 3.x](http://codeigniter.com/userguide3/libraries/table.html)
- [HTML Table Documentation CodeIgniter 4.x](../outgoing/table.md)

## What has been changed

- Only small things like the method names and the loading of the library have changed.

## Upgrade Guide

1.  Within your class, change `$this->load->library('table');` to `$table = new \CodeIgniter\View\Table();`.
2.  From then on, you have to change `$this->table` to `$table` in every line starting with it. For example: `echo $this->table->generate($query);` will become `echo $table->generate($query);`
3.  The methods in the HTML Table class could be named slightly differently. The most important change in the naming is the switch from underscored method names to camelCase. The method `set_heading()` from version 3 is now named `setHeading()` and so on.

## Code Example

### CodeIgniter Version 3.x

```php
<?php

$this->load->library('table');

$this->table->set_heading('Name', 'Color', 'Size');

$this->table->add_row('Fred', 'Blue', 'Small');
$this->table->add_row('Mary', 'Red', 'Large');
$this->table->add_row('John', 'Green', 'Medium');

echo $this->table->generate();
```

### CodeIgniter Version 4.x

```php
<?php

$table = new \CodeIgniter\View\Table();

$table->setHeading('Name', 'Color', 'Size');

$table->addRow('Fred', 'Blue', 'Small');
$table->addRow('Mary', 'Red', 'Large');
$table->addRow('John', 'Green', 'Medium');

echo $table->generate();
```
