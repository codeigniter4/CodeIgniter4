# Upgrade Image Manipulation Class

## Documentation

- [Image Manipulation Class Documentation CodeIgniter 3.x](https://www.codeigniter.com/userguide3/libraries/image_lib.html)
- [Image Manipulation Class Documentation CodeIgniter 4.x](../libraries/images.md)

## What has been changed

- The preferences passed to the constructor or `initialize()` method in CI3 have been changed to be specified in the new methods in CI4.
- Some preferences like `create_thumb` are removed.
- In CI4, the `save()` method must be called to save the manipulated image.
- The `display_errors()` method has been removed, and an exception will be thrown if an error occurs.

## Upgrade Guide

1.  Within your class, change `$this->load->library('image_lib');` to `$image = \Config\Services::image();`.
2.  Change the preferences passed to the constructor or `initialize()` method to be specified in the corresponding methods.
3.  Call the `save()` method to save the file.

## Code Example

### CodeIgniter Version 3.x

<!-- lint-ignore -->
```php
<?php

$config['image_library']  = 'gd2';
$config['source_image']   = '/path/to/image/mypic.jpg';
$config['create_thumb']   = TRUE;
$config['maintain_ratio'] = TRUE;
$config['width']          = 75;
$config['height']         = 50;

$this->load->library('image_lib', $config);

$this->image_lib->resize();
```

### CodeIgniter Version 4.x

```php
<?php

$image = \Config\Services::image();

$image
    ->withFile('/path/to/image/mypic.jpg')
    ->resize(75, 50, true)
    ->save('/path/to/image/mypic_thumb.jpg');
```
