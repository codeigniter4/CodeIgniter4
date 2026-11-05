# Upgrade Output Class

## Documentation

- [Output Class Documentation CodeIgniter 3.x](http://codeigniter.com/userguide3/libraries/output.html)
- [HTTP Responses Documentation CodeIgniter 4.x](../outgoing/response.md)

## What has been changed

- The Output class has been changed to the Response class.
- The methods have been renamed.

## Upgrade Guide

1.  The methods in the HTTP Response class are named slightly differently. The most important change in the naming is the switch from underscored method names to camelCase. The method `set_content_type()` from version 3 is now named `setContentType()` and so on.
2.  In most cases, you have to change `$this->output` to `$this->response` followed by the method. You can find all methods in [HTTP Responses](../outgoing/response.md).

## Code Example

### CodeIgniter Version 3.x

<!-- lint-ignore -->
```php
<?php

$this->output->set_status_header(404);

// ...

$this->output
    ->set_content_type('application/json')
    ->set_output(json_encode(array('foo' => 'bar')));
```

### CodeIgniter Version 4.x

```php
<?php

$this->response->setStatusCode(404);

// ...

return $this->response->setJSON(['foo' => 'bar']);
```
