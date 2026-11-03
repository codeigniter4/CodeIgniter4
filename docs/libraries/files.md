# Working with Files

CodeIgniter provides a File class that wraps the [SplFileInfo](https://www.php.net/manual/en/class.splfileinfo.php) class and provides some additional convenience methods. This class is the base class for [uploaded files](uploaded_files.md) and [images](images.md).

## Getting a File instance

You create a new File instance by passing in the path to the file in the constructor.

```php
$file = new \CodeIgniter\Files\File($path);
```

By default, the file does not need to exist. However, you can pass an additional argument of `true` to check that the file exists and throw `FileNotFoundException()` if it does not.

## Taking Advantage of Spl

Once you have an instance, you have the full power of the SplFileInfo class at the ready, including:

```php
// Get the file's basename
echo $file->getBasename();
// Get last modified time
echo $file->getMTime();
// Get the true real path
echo $file->getRealPath();
// Get the file permissions
echo $file->getPerms();

// Write CSV rows to it.
if ($file->isWritable()) {
    $csv = $file->openFile('w');

    foreach ($rows as $row) {
        $csv->fputcsv($row);
    }
}
```

## New Features

In addition to all of the methods in the SplFileInfo class, you get some new tools.

### getRandomName()

You can generate a cryptographically secure random filename, with the current timestamp prepended, using the `getRandomName()` method. This is especially useful to rename files when moving them so that the filename is unguessable:

```php
// Generates something like: 1465965676_385e33f741.jpg
$newName = $file->getRandomName();
```

### getSize()

Returns the size of the file in bytes:

```php
$size = $file->getSize(); // 256901
```

A `RuntimeException` will be thrown if the file does not exist or an error occurs.

### getSizeByUnit()

!!! danger "Deprecated since version 4.6.0"

Returns the size of the file, in bytes by default. You can pass in either `'kb'` or `'mb'` as the first parameter to get the results in kibibytes or mebibytes, respectively:

```php
$bytes     = $file->getSizeByUnit(); // 256901
$kilobytes = $file->getSizeByUnit('kb'); // 250.880
$megabytes = $file->getSizeByUnit('mb'); // 0.245
```

A `RuntimeException` will be thrown if the file does not exist or an error occurs.

### getSizeByBinaryUnit()

!!! success "Available from version 4.6.0"

Returns the size of the file, in bytes by default. You can pass in different FileSizeUnit values as the first parameter to get the results in kibibytes, mebibytes, etc., respectively. You can pass in a precision value as the second parameter to define the number of decimal places.

```php
$bytes     = $file->getSizeByBinaryUnit(); // 256901
$kibibytes = $file->getSizeByBinaryUnit(FileSizeUnit::KB); // 250.880
$mebibytes = $file->getSizeByBinaryUnit(FileSizeUnit::MB); // 0.245
```

A `RuntimeException` will be thrown if the file does not exist or an error occurs.

### getSizeByMetricUnit()

!!! success "Available from version 4.6.0"

Returns the size of the file, in bytes by default. You can pass in different FileSizeUnit values as the first parameter to get the results in kilobytes, megabytes, etc., respectively. You can pass in a precision value as the second parameter to define the number of decimal places.

```php
$bytes     = $file->getSizeByMetricUnit(); // 256901
$kilobytes = $file->getSizeByMetricUnit(FileSizeUnit::KB); // 256.901
$megabytes = $file->getSizeByMetricUnit(FileSizeUnit::MB); // 0.256
```

A `RuntimeException` will be thrown if the file does not exist or an error occurs.

### getMimeType()

Retrieves the media type (mime type) of the file. Uses methods that are considered as secure as possible when determining the type of file:

```php
$type = $file->getMimeType();

echo $type; // image/png
```

### guessExtension()

Attempts to determine the file extension based on the trusted `getMimeType()` method. If the mime type is unknown, it will return null. This is often a more trusted source than simply using the extension provided by the filename. Uses the values in **app/Config/Mimes.php** to determine the extension:

```php
// Returns 'jpg' (WITHOUT the period)
$ext = $file->guessExtension();
```

### Moving Files

Each file can be moved to its new location with the aptly named `move()` method. This takes the directory to move the file to as the first parameter:

```php
$file->move(WRITEPATH . 'uploads');
```

By default, the original filename is used. You can specify a new filename by passing it as the second parameter:

```php
$newName = $file->getRandomName();
$file->move(WRITEPATH . 'uploads', $newName);
```

The `move()` method returns a new File instance for the relocated file, so you must capture the result if the resulting location is needed:

```php
$file = $file->move(WRITEPATH . 'uploads');
```
