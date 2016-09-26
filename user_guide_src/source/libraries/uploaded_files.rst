***************************
Working with Uploaded Files
***************************

CodeIgniter makes working with files uploaded through a form much simpler and more secure than using PHP's ``$_FILES``
array directly.

.. note:: This is not the same as the File Uploading class in previous versions of CodeIgniter. This provides a raw
	interface to the uploaded files with a few small features. An uploader class will be present in the final release.

.. contents:: Page Contents
  :local:

===============
Accessing Files
===============

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

If you just need to access a single file, you can use ``getFile()`` to retrieve the file instance directly::

	$file = $this->request->getFile('avatar');

.. note:: This currently only works with simple file names and not with array syntax naming.

=====================
Working With the File
=====================

Once you've gotten the UploadedFile instance, you can retrieve information about the file in safe ways, as well as
move the file to a new location.

Verify A File
-------------

You can check that a file was actually uploaded via HTTP with no errors by calling the ``isValid()`` method::

	if (! $file->isValid())
	{
		throw new RuntimeException($file->getErrorString().'('.$file->getError().')');
	}

As seen in this example, if a file had an upload error, you can retrieve the error code (an integer) and the error
message with the ``getError()`` and ``getErrorString()`` methods. The following errors can be discovered through
this method:

* The file exceeds your upload_max_filesize ini directive.
* The file exceeds the upload limit defined in your form.
* The file was only partially uploaded.
* No file was uploaded.
* The file could not be written on disk.
* File could not be uploaded: missing temporary directory.
* File upload was stopped by a PHP extension.

File Names
----------

**getName()**

You can retrieve the original filename provided by the client with the ``getName()`` method. This will typically be the
filename sent by the client, and should not be trusted. If the file has been moved, this will return the final name of
the moved file::

	$name = $file->getName();

**getTempName()**

To get the name of the temp file that was created during the upload, you can use the ``getTempName()`` method::

	$tempfile = $file->getTempName();

**getRandomName()**

You can generate a cryptographically secure random filename, with the current timestamp prepended, with the ``getRandomName()``
method. This is especially useful to rename files when moving it so that the filename is unguessable::

	// Generates something like: 1465965676_385e33f741.jpg
	$newName = $file->getRandomName();


Other File Info
---------------

**getExtension()**

Attempts to determine the file extension based on the trusted ``getType()`` method instead of the value listed
in ``$_FILES``. If the mime type is unknown, will return null. Uses the values in **application/Config/Mimes.php**
to determine extension::

	// Returns 'jpg' (WITHOUT the period)
	$ext = $file->getExtension();

**getClientExtension()**

Returns the original file extension, based on the file name that was uploaded. This is NOT a trusted source. For a
trusted version, use ``getExtension()`` instead::

	$ext = $file->getClientExtension();

**getType()**

Retrieve the media type (mime type) of the file. Does not use information from the $_FILES array, but uses other methods to more
accurately determine the type of file, like ``finfo``, or ``mime_content_type``::

	$type = $file->getType();

	echo $type; // image/png

**getClientType()**

Returns the mime type (mime type) of the file as provided by the client. This is NOT a trusted value. For a trusted
version, use ``getType()`` instead::

	$type = $file->getClientType();

	echo $type; // image/png

**getSize()**

Returns the size of the uploaded file in bytes. You can pass in either 'kb' or 'mb' as the first parameter to get
the results in kilobytes or megabytes, respectively::

	$bytes     = $file->getSize();      // 256901
	$kilobytes = $file->getSize('kb');  // 250.880
	$megabytes = $file->getSize('mb');  // 0.245

Moving Files
------------

Each file can be moved to its new location with the aptly named ``move()`` method. This takes the directory to move
the file to as the first parameter::

	$file->move(WRITEPATH.'uploads');

By default, the original filename was used. You can specify a new filename by passing it as the second parameter::

	$newName = $file->getRandomName();
	$file->move(WRITEPATH.'uploads', $newName);

Once the file has been removed the temporary file is deleted. You can check if a file has been moved already with
the ``hasMoved()`` method, which returns a boolean::

    if ($file->isValid() && ! $file->hasMoved())
    {
        $file->move($path);
    }
