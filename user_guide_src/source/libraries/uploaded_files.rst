###########################
Working with Uploaded Files
###########################

CodeIgniter makes working with files uploaded through a form much simpler and more secure than using PHP's ``$_FILES``
array directly. This extends the :doc:`File class </libraries/files>` and thus gains all of the features of that class.

.. note:: This is not the same as the File Uploading class in previous versions of CodeIgniter. This provides a raw
    interface to the uploaded files with a few small features.

.. contents::
    :local:
    :depth: 2

***********
The Process
***********

Uploading a file involves the following general process:

-  An upload form is displayed, allowing a user to select a file and
   upload it.
-  When the form is submitted, the file is uploaded to the destination
   you specify.
-  Along the way, the file is validated to make sure it is allowed to be
   uploaded based on the preferences you set.
-  Once uploaded, the user will be shown a success message.

To demonstrate this process here is brief tutorial. Afterward you'll
find reference information.

Creating the Upload Form
========================

Using a text editor, create a form called upload_form.php. In it, place
this code and save it to your **app/Views/** directory::

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

    <input type="file" name="userfile" size="20" />

    <br /><br />

    <input type="submit" value="upload" />

    </form>

    </body>
    </html>

You'll notice we are using a form helper to create the opening form tag.
File uploads require a multipart form, so the helper creates the proper
syntax for you. You'll also notice we have an ``$errors`` variable. This is
so we can show error messages in the event the user does something
wrong.

The Success Page
================

Using a text editor, create a form called upload_success.php. In it,
place this code and save it to your **app/Views/** directory::

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <title>Upload Form</title>
    </head>
    <body>

    <h3>Your file was successfully uploaded!</h3>

    <ul>
        <li>name: <?= esc($uploaded_flleinfo->getBasename()) ?></li>
        <li>size: <?= esc($uploaded_flleinfo->getSizeByUnit('kb')) ?> KB</li>
        <li>extension: <?= esc($uploaded_flleinfo->guessExtension()) ?></li>
    </ul>

    <p><?= anchor('upload', 'Upload Another File!') ?></p>

    </body>
    </html>

The Controller
==============

Using a text editor, create a controller called Upload.php. In it, place
this code and save it to your **app/Controllers/** directory::

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
                    'rules' => 'uploaded[userfile]'
                        . '|is_image[userfile]'
                        . '|mime_in[userfile,image/jpg,image/jpeg,image/gif,image/png,image/webp]'
                        . '|max_size[userfile,100]'
                        . '|max_dims[userfile,1024,768]',
                ],
            ];
            if (! $this->validate($validationRule)) {
                $data = ['errors' => $this->validator->getErrors()];

                return view('upload_form', $data);
            }

            $img = $this->request->getFile('userfile');

            if (! $img->hasMoved()) {
                $filepath = WRITEPATH . 'uploads/' . $img->store();

                $data = ['uploaded_flleinfo' => new File($filepath)];

                return view('upload_success', $data);
            } else {
                $data = ['errors' => 'The file has already been moved.'];

                return view('upload_form', $data);
            }
        }
    }

.. note:: Since the value of a file upload HTML field doesn't exist, and is stored in the ``$_FILES`` global,
    only :ref:`rules-for-file-uploads` can be used to validate upload file with :doc:`validation`.
    The rule ``required`` also can't be used, so use ``uploaded`` instead.

The Upload Directory
====================

The uploaded files are stored in the **writable/uploads/** directory.

Try it!
=======

To try your form, visit your site using a URL similar to this one::

    example.com/index.php/upload/

You should see an upload form. Try uploading an image file (either a
**jpg**, **gif**, **png**, or **webp**). If the path in your controller is correct it should
work.

***************
Accessing Files
***************

All Files
=========

When you upload files they can be accessed natively in PHP through the ``$_FILES`` superglobal. This array has some
major shortcomings when working with multiple files uploaded at once, and has potential security flaws many developers
are not aware of. CodeIgniter helps with both of these situations by standardizing your usage of files behind a
common interface.

Files are accessed through the current ``IncomingRequest`` instance. To retrieve all files that were uploaded with this
request, use ``getFiles()``. This will return an array of files represented by instances of ``CodeIgniter\HTTP\Files\UploadedFile``::

    $files = $this->request->getFiles();

Of course, there are multiple ways to name the file input, and anything but the simplest can create strange results.
The array returns in a manner that you would expect. With the simplest usage, a single file might be submitted like::

    <input type="file" name="avatar" />

Which would return a simple array like::

    [
        'avatar' => // UploadedFile instance
    ]

If you used an array notation for the name, the input would look something like::

    <input type="file" name="my-form[details][avatar]" />

The array returned by ``getFiles()`` would look more like this::

    [
        'my-form' => [
            'details' => [
                'avatar' => // UploadedFile instance
            ]
        ]
    ]

In some cases, you may specify an array of files to upload::

    Upload an avatar: <input type="file" name="my-form[details][avatars][]" />
    Upload an avatar: <input type="file" name="my-form[details][avatars][]" />

In this case, the returned array of files would be more like::

    [
        'my-form' => [
            'details' => [
                'avatar' => [
                    0 => /* UploadedFile instance */,
                    1 => /* UploadedFile instance */
            ]
        ]
    ]

Single File
===========

If you just need to access a single file, you can use ``getFile()`` to retrieve the file instance directly. This will return an instance of ``CodeIgniter\HTTP\Files\UploadedFile``:

Simplest usage
^^^^^^^^^^^^^^

With the simplest usage, a single file might be submitted like::

    <input type="file" name="userfile" />

Which would return a simple file instance like::

    $file = $this->request->getFile('userfile');

Array notation
^^^^^^^^^^^^^^

If you used an array notation for the name, the input would look something like::

    <input type="file" name="my-form[details][avatar]" />

For get the file instance::

    $file = $this->request->getFile('my-form.details.avatar');

Multiple files
^^^^^^^^^^^^^^
::

    <input type="file" name="images[]" multiple />

In controller::

    if ($imagefile = $this->request->getFiles()) {
        foreach($imagefile['images'] as $img) {
            if ($img->isValid() && ! $img->hasMoved()) {
                $newName = $img->getRandomName();
                $img->move(WRITEPATH . 'uploads', $newName);
            }
        }
    }

where the **images** is a loop from the form field name

If there are multiple files with the same name you can use ``getFile()`` to retrieve every file individually::
In controller::

    $file1 = $this->request->getFile('images.0');
    $file2 = $this->request->getFile('images.1');

You might find it easier to use ``getFileMultiple()``, to get an array of uploaded files with the same name::

    $files = $this->request->getFileMultiple('images');


Another example::

    Upload an avatar: <input type="file" name="my-form[details][avatars][]" />
    Upload an avatar: <input type="file" name="my-form[details][avatars][]" />

In controller::

    $file1 = $this->request->getFile('my-form.details.avatars.0');
    $file2 = $this->request->getFile('my-form.details.avatars.1');

.. note:: Using ``getFiles()`` is more appropriate.

*********************
Working With the File
*********************

Once you've retrieved the UploadedFile instance, you can retrieve information about the file in safe ways, as well as
move the file to a new location.

Verify A File
=============

You can check that a file was actually uploaded via HTTP with no errors by calling the ``isValid()`` method::

    if (! $file->isValid()) {
        throw new \RuntimeException($file->getErrorString() . '(' . $file->getError() . ')');
    }

As seen in this example, if a file had an upload error, you can retrieve the error code (an integer) and the error
message with the ``getError()`` and ``getErrorString()`` methods. The following errors can be discovered through
this method:

* The file exceeds your ``upload_max_filesize`` ini directive.
* The file exceeds the upload limit defined in your form.
* The file was only partially uploaded.
* No file was uploaded.
* The file could not be written on disk.
* File could not be uploaded: missing temporary directory.
* File upload was stopped by a PHP extension.

File Names
==========

**getName()**

You can retrieve the original filename provided by the client with the ``getName()`` method. This will typically be the
filename sent by the client, and should not be trusted. If the file has been moved, this will return the final name of
the moved file::

    $name = $file->getName();

**getClientName()**

Always returns the original name of the uploaded file as sent by the client, even if the file has been moved::

  $originalName = $file->getClientName();

**getTempName()**

To get the full path of the temp file that was created during the upload, you can use the ``getTempName()`` method::

    $tempfile = $file->getTempName();

Other File Info
===============

**getClientExtension()**

Returns the original file extension, based on the file name that was uploaded::

    $ext = $file->getClientExtension();

.. warning:: This is NOT a trusted source. For a trusted version, use ``guessExtension()`` instead.

**getClientMimeType()**

Returns the mime type (mime type) of the file as provided by the client. This is NOT a trusted value. For a trusted
version, use ``getMimeType()`` instead::

    $type = $file->getClientMimeType();

    echo $type; // image/png

Moving Files
============

Each file can be moved to its new location with the aptly named ``move()`` method. This takes the directory to move
the file to as the first parameter::

    $file->move(WRITEPATH . 'uploads');

By default, the original filename was used. You can specify a new filename by passing it as the second parameter::

    $newName = $file->getRandomName();
    $file->move(WRITEPATH . 'uploads', $newName);

Once the file has been removed the temporary file is deleted. You can check if a file has been moved already with
the ``hasMoved()`` method, which returns a boolean::

    if ($file->isValid() && ! $file->hasMoved()) {
        $file->move($path);
    }

Moving an uploaded file can fail, with an HTTPException, under several circumstances:

- the file has already been moved
- the file did not upload successfully
- the file move operation fails (e.g., improper permissions)

Store Files
===========

Each file can be moved to its new location with the aptly named ``store()`` method.

With the simplest usage, a single file might be submitted like::

    <input type="file" name="userfile" />

By default, upload files are saved in **writable/uploads** directory. The **YYYYMMDD** folder
and random file name will be created. Returns a file path::

    $path = $this->request->getFile('userfile')->store();

You can specify a directory to move the file to as the first parameter. A new filename by
passing it as the second parameter::

    $path = $this->request->getFile('userfile')->store('head_img/', 'user_name.jpg');

Moving an uploaded file can fail, with an ``HTTPException``, under several circumstances:

- the file has already been moved
- the file did not upload successfully
- the file move operation fails (e.g., improper permissions)
