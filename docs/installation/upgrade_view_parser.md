# Upgrade View Parser

## Documentation

- [Template Parser Documentation CodeIgniter 3.x](http://codeigniter.com/userguide3/libraries/parser.html)
- [View Parser Documentation CodeIgniter 4.x](../outgoing/view_parser.md)

## What has been changed

- You have to change the implementation and loading of the Parser Library.
- The Views can be copied from CI3. Usually, no changes are required there.

## Upgrade Guide

1.  Wherever you use the View Parser Library, replace `$this->load->library('parser');` with `$parser = service('parser');`.
2.  You have to change the render part in your controller from `$this->parser->parse('blog_template', $data);` to `return $parser->setData($data)->render('blog_template');`.

## Code Example

### CodeIgniter Version 3.x

<!-- lint-ignore -->
```php
<?php

$this->load->library('parser');

$data = array(
    'blog_title'   => 'My Blog Title',
    'blog_heading' => 'My Blog Heading'
);

$this->parser
    ->parse('blog_template', $data);
```

### CodeIgniter Version 4.x

```php
<?php

$parser = service('parser');

$data = [
    'blog_title'   => 'My Blog Title',
    'blog_heading' => 'My Blog Heading',
];

return $parser->setData($data)->render('blog_template');
```
