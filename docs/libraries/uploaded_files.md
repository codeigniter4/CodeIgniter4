# Working with Uploaded Files

CodeIgniter makes working with files uploaded through a form much simpler and more secure than using PHP's `$_FILES` array directly. The UploadedFile class extends the [File class](files.md) and thus gains all of the features of that class.

!!! note "Note"
    This is not the same as the File Uploading class in CodeIgniter 3. This provides a raw interface to the uploaded files with a few small features.

## File Upload Form Tutorial

Uploading a file involves the following general process:

- An upload form is displayed, allowing a user to select a file and upload it.
- When the form is submitted, the file is uploaded to the destination you specify.
- Along the way, the file is validated to make sure it is allowed to be uploaded based on the preferences you set.
- Once uploaded, the user will be shown a success message.

To demonstrate this process, here is a brief tutorial. Afterward, you'll find reference information.

### Creating the Upload Form

Using a text editor, create a form called **upload_form.php**. In it, place this code and save it to your **app/Views** directory:

```php
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Upload Form</title>
</head>
<body>

<?php foreach ($errors as $error): ?>
    <li><?= esc($error) ?></li>
<?php endforeach ?>

<?= form_open_multipart('upload/upload') ?>
    <input type="file" name="userfile" size="20">
    <br><br>
    <input type="submit" value="upload">
</form>

</body>
</html>
```

You'll notice we are using a form helper to create the opening form tag. File uploads require a multipart form, so the helper creates the proper syntax for you.

You'll also notice we have an `$errors` variable. This is so we can show error messages in the event the user does something wrong.

### The Success Page

Using a text editor, create a form called **upload_success.php**. In it, place this code and save it to your **app/Views** directory:

```
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Upload Form</title>
</head>
<body>

<h3>Your file was successfully uploaded!</h3>

<ul>
    <li>name: <?= esc($uploaded_fileinfo->getBasename()) ?></li>
    <li>size: <?= esc($uploaded_fileinfo->getSizeByBinaryUnit(\CodeIgniter\Files\FileSizeUnit::KB)) ?> KB</li>
    <li>extension: <?= esc($uploaded_fileinfo->guessExtension()) ?></li>
</ul>

<p><?= anchor('upload', 'Upload Another File!') ?></p>

</body>
</html>
```

### The Controller

Using a text editor, create a controller called **Upload.php**. In it, place this code and save it to your **app/Controllers** directory:

```php
<?php

namespace App\Controllers;

use CodeIgniter\Files\File;

class Upload extends BaseController
{
    protected $helpers = ['form'];

    public function index()
    {
        return view('upload_form', ['errors' => []]);
    }

    public function upload()
    {
        $validationRule = [
            'userfile' => [
                'label' => 'Image File',
                'rules' => [
                    'uploaded[userfile]',
                    'is_image[userfile]',
                    'mime_in[userfile,image/jpg,image/jpeg,image/gif,image/png,image/webp]',
                    'max_size[userfile,100]',
                    'max_dims[userfile,1024,768]',
                ],
            ],
        ];
        if (! $this->validateData([], $validationRule)) {
            $data = ['errors' => $this->validator->getErrors()];

            return view('upload_form', $data);
        }

        $img = $this->request->getFile('userfile');

        if (! $img->hasMoved()) {
            $filepath = WRITEPATH . 'uploads/' . $img->store();

            $data = ['uploaded_fileinfo' => new File($filepath)];

            return view('upload_success', $data);
        }

        $data = ['errors' => 'The file has already been moved.'];

        return view('upload_form', $data);
    }
}
```

Only the [Rules for File Uploads](validation.md#rules-for-file-uploads) can be used to validate uploaded files.

Therefore, the rule `required` cannot be used either, so if the file is required, use the rule `uploaded` instead.

Note that an empty array (`[]`) is passed as the first argument to `$this->validateData()`. This is because the file validation rules get the data for the uploaded file directly from the Request object.

If the form has fields other than the file upload, pass the field data as the first argument.

### The Routes

Using a text editor, open **app/Config/Routes.php**. In it, add the following two routes:

```php
<?php

// ...

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/', 'Home::index');

$routes->get('upload', 'Upload::index');          // Add this line.
$routes->post('upload/upload', 'Upload::upload'); // Add this line.

// ...
```

### The Upload Directory

The uploaded files are stored in the **writable/uploads/** directory.

### Try it!

To try your form, visit your site using a URL similar to this one:

```
example.com/index.php/upload/
```

You should see an upload form. Try uploading an image file (either a **jpg**, **gif**, **png**, or **webp**). If the path in your controller is correct, it should work.

## Accessing Files

### All Files

When you upload files, they can be accessed natively in PHP through the `$_FILES` superglobal. This array has some major shortcomings when working with multiple files uploaded at once, and has potential security flaws many developers are not aware of. CodeIgniter helps with both of these situations by standardizing your usage of files behind a common interface.

Files are accessed through the current `IncomingRequest` instance. To retrieve all files that were uploaded with this request, use `getFiles()`. This will return an array of files represented by instances of `CodeIgniter\HTTP\Files\UploadedFile`:

```php
<?php

$files = $this->request->getFiles();
```

Of course, there are multiple ways to name the file input, and anything but the simplest can create strange results. The array is returned in a manner that you would expect. With the simplest usage, a single file might be submitted like:

```
<input type="file" name="avatar">
```

This would return a simple array like:

```
[
    'avatar' => // UploadedFile instance,
];
```

!!! note "Note"
    The UploadedFile instance corresponds to `$_FILES`. Even if a user just clicks the submit button and does not upload any file, the instance will still exist. You can check that the file was actually uploaded with the `isValid()` method in UploadedFile. See [Verify a File](#verify-a-file).

If you used array notation for the name, the input would look something like:

```
<input type="file" name="my-form[details][avatar]">
```

The array returned by `getFiles()` would look more like this:

```
[
     'my-form' => [
        'details' => [
            'avatar' => // UploadedFile instance
        ],
    ],
]
```

In some cases, you may specify an array of files to upload:

```
Upload an avatar: <input type="file" name="my-form[details][avatars][]">
Upload an avatar: <input type="file" name="my-form[details][avatars][]">
```

In this case, the returned array of files would be more like:

```
[
    'my-form' => [
        'details' => [
            'avatars' => [
                0 => // UploadedFile instance,
                1 => // UploadedFile instance,
            ],
        ],
    ],
]
```

### Single File

If you just need to access a single file, you can use `getFile()` to retrieve the file instance directly. This will return an instance of `CodeIgniter\HTTP\Files\UploadedFile`.

#### Simplest usage

With the simplest usage, a single file might be submitted like:

```
<input type="file" name="userfile">
```

This would return a simple file instance like:

```php
<?php

$file = $this->request->getFile('userfile');
```

#### Array notation

If you used array notation for the name, the input would look something like:

```
<input type="file" name="my-form[details][avatar]">
```

To get the file instance:

```php
<?php

$file = $this->request->getFile('my-form.details.avatar');
```

### Multiple files

```
<input type="file" name="images[]" multiple>
```

In the controller:

```php
<?php

if ($imagefile = $this->request->getFiles()) {
    foreach ($imagefile['images'] as $img) {
        if ($img->isValid() && ! $img->hasMoved()) {
            $newName = $img->getRandomName();
            $img->move(WRITEPATH . 'uploads', $newName);
        }
    }
}
```

Here, `images` is a loop from the form field name.

If there are multiple files with the same name, you can use `getFile()` to retrieve every file individually.

In the controller:

```php
<?php

$file1 = $this->request->getFile('images.0');
$file2 = $this->request->getFile('images.1');
```

You might find it easier to use `getFileMultiple()` to get an array of uploaded files with the same name:

```php
<?php

$files = $this->request->getFileMultiple('images');
```

Another example:

```
Upload an avatar: <input type="file" name="my-form[details][avatars][]">
Upload an avatar: <input type="file" name="my-form[details][avatars][]">
```

In the controller:

```php
<?php

$file1 = $this->request->getFile('my-form.details.avatars.0');
$file2 = $this->request->getFile('my-form.details.avatars.1');
```

!!! note "Note"
    Using `getFiles()` is more appropriate.

## Working with the File

Once you've retrieved the UploadedFile instance, you can get information about the file in safe ways, as well as move the file to a new location.

### Verify a File

You can check that a file was actually uploaded via HTTP with no errors by calling the `isValid()` method:

```php
<?php

if (! $file->isValid()) {
    throw new \RuntimeException($file->getErrorString() . '(' . $file->getError() . ')');
}
```

As seen in this example, if a file had an upload error, you can retrieve the error code (an integer) and the error message with the `getError()` and `getErrorString()` methods. The following errors can be discovered through this method:

- The file exceeds your `upload_max_filesize` ini directive.
- The file exceeds the upload limit defined in your form.
- The file was only partially uploaded.
- No file was uploaded.
- The file could not be written on disk.
- File could not be uploaded: missing temporary directory.
- File upload was stopped by a PHP extension.

### File Names

#### getName()

You can retrieve the original filename provided by the client with the `getName()` method. This will typically be the filename sent by the client, and should not be trusted. If the file has been moved, this will return the final name of the moved file:

```php
<?php

$name = $file->getName();
```

#### getClientName()

Always returns the original name of the uploaded file as sent by the client, even if the file has been moved:

```php
<?php

$originalName = $file->getClientName();
```

#### getTempName()

To get the full path of the temp file that was created during the upload, you can use the `getTempName()` method:

```php
<?php

$tempfile = $file->getTempName();
```

### Other File Info

#### getClientExtension()

Returns the original file extension, based on the file name that was uploaded:

```php
<?php

$ext = $file->getClientExtension();
```

!!! warning "Warning"
    This is NOT a trusted source. For a trusted version, use `guessExtension()` instead.

#### getClientMimeType()

Returns the mime type of the file as provided by the client. This is NOT a trusted value. For a trusted version, use `getMimeType()` instead:

```php
<?php

$type = $file->getClientMimeType();

echo $type; // image/png
```

#### getClientPath()

!!! success "Available from version 4.4.0"

Returns the [webkit relative path](https://developer.mozilla.org/en-US/docs/Web/API/File/webkitRelativePath) of the uploaded file when the client has uploaded files via directory upload. In PHP versions below 8.1, this returns `null`.

```php
<?php

$clientPath = $file->getClientPath();
echo $clientPath; // dir/file.txt, or dir/sub_dir/file.txt
```

### Moving Files

!!! warning "Warning"
    Do not let user input directly decide the destination directory. The `$targetPath` argument of `move()` and the `$folderName` argument of `store()` are never sanitized, so an attacker-controlled value containing `../` can move the uploaded file outside the intended directory. Use directories controlled by your application.

    Filenames are only sanitized by default: `move()` sanitizes the client-provided name when the `$name` argument is omitted, and `store()` generates a random name when `$fileName` is omitted. A caller-supplied `$name` or `$fileName` is NOT sanitized, so if you pass one, generate it yourself or sanitize it with `sanitize_filename`.
    On Windows, `move()` and `store()` reject names ending in a period or space or containing a colon, whether the name was supplied by the caller or chosen by default. Sanitization alone does not remove these forms. Use a generated name when possible.

    Sanitizing a client-provided filename does not make it safe to keep in a web-accessible directory where scripts can execute. Some web servers select a handler from any extension in a filename, including an extension before the final one. Keep uploads outside the public directory with `store()` when possible. If they must be public, disable script execution in the upload directory and pass `$file->getRandomName()` as the second argument to `move()`.

#### with Original Filename

Each file can be moved to its new location with the aptly named `move()` method. This takes the directory to move the file to as the first parameter:

```php
<?php

$file->move(WRITEPATH . 'uploads');
```

By default, the client-provided filename is sanitized and used. On Windows, an `HTTPException` is thrown if the name to be stored ends with a period or a space, or contains a colon. This also applies when you pass a name explicitly. Such names may be stored under a different filename or in an NTFS alternate data stream. Use a generated filename if the client-provided name is rejected.

#### with New Filename

You can specify a new filename by passing it as the second parameter:

```php
<?php

$newName = $file->getRandomName();
$file->move(WRITEPATH . 'uploads', $newName);
```

#### Overwriting Existing File

By default, if the destination file already exists, a new filename will be used. For example, if **image_name.jpg** already exists in the directory, then the filename will be **image_name_1.jpg** automatically.

You can overwrite the existing file by passing `true` as the third parameter:

```php
<?php

$file->move(WRITEPATH . 'uploads', null, true);
```

#### Check if the File Moved

Once the file has been moved, the temporary file is deleted. You can check if a file has been moved already with the `hasMoved()` method, which returns a boolean:

```php
<?php

if ($file->isValid() && ! $file->hasMoved()) {
    $file->move($path);
}
```

#### When Moving Fails

Moving an uploaded file can fail, with an `HTTPException`, under several circumstances:

- the file has already been moved
- the file did not upload successfully
- on Windows, the filename ends with a period or a space, or contains a colon
- the file move operation fails (e.g., improper permissions)

### Store Files

Each file can be moved to its new location with the aptly named `store()` method.

With the simplest usage, a single file might be submitted like:

```
<input type="file" name="userfile">
```

By default, uploaded files are saved in the **writable/uploads** directory. A **YYYYMMDD** folder and a random file name will be created. The method returns a file path:

```php
<?php

$path = $this->request->getFile('userfile')->store();
```

You can specify a directory to move the file to as the first parameter, and a new filename by passing it as the second parameter:

```php
<?php

$path = $this->request->getFile('userfile')->store('head_img/', 'user_name.jpg');
```

Moving an uploaded file can fail, with an `HTTPException`, under several circumstances:

- the file has already been moved
- the file did not upload successfully
- on Windows, the supplied filename ends with a period or a space, or contains a colon
- the file move operation fails (e.g., improper permissions)
