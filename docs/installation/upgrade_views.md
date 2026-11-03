# Upgrade Views

## Documentation

- [View Documentation CodeIgniter 3.x](http://codeigniter.com/userguide3/general/views.html)
- [View Documentation CodeIgniter 4.x](../outgoing/views.md)

## What has been changed

- Your views look much like before, but they are invoked differently: instead of CI3's `$this->load->view('x');`, you can use `return view('x');`.
- CI4 supports [View Cells](../outgoing/view_cells.md) to build your response in pieces, and [View Layouts](../outgoing/view_layouts.md) for page layout.
- The [Template Parser](../outgoing/view_parser.md) is still there, and substantially enhanced.

## Upgrade Guide

1.  First, move all views to the folder **app/Views**.
2.  Change the loading syntax of views in every script where you load views:
    - from `$this->load->view('directory_name/file_name')` to `return view('directory_name/file_name');`
    - from `$content = $this->load->view('file', $data, TRUE);` to `$content = view('file', $data);`
3.  (optional) You can change the echo syntax in views from `<?php echo $title; ?>` to `<?= $title ?>`
4.  Remove the line `defined('BASEPATH') OR exit('No direct script access allowed');` if it exists.

## Code Example

### CodeIgniter Version 3.x

Path: **application/views**:

```php
<html>
<head>
    <title><?php echo html_escape($title); ?></title>
</head>
<body>
    <h1><?php echo html_escape($heading); ?></h1>

    <h3>My Todo List</h3>

    <ul>
    <?php foreach ($todo_list as $item): ?>
        <li><?php echo html_escape($item); ?></li>
    <?php endforeach; ?>
    </ul>

</body>
</html>
```

### CodeIgniter Version 4.x

Path: **app/Views**:

```php
<html>
<head>
    <title><?= esc($title) ?></title>
</head>
<body>
    <h1><?= esc($heading) ?></h1>

    <h3>My Todo List</h3>

    <ul>
    <?php foreach ($todo_list as $item): ?>
        <li><?= esc($item) ?></li>
    <?php endforeach ?>
    </ul>

</body>
</html>
```
