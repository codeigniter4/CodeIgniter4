******************
Working with Files
******************

CodeIgniter provides a File class that wraps the `SplFileInfo <http://php.net/manual/en/class.splfileinfo.php>`_ class
and provides some additional convenience methods. This class is the base class for :doc:`uploaded files </libraries/uploaded_files>`
and :doc:`images </libraries/images>`.

.. contents::
    :local:
    :depth: 2

Getting a File instance
=======================

You create a new File instance by passing in the path to the file in the constructor.
By default, the file does not need to exist. However, you can pass an additional argument of "true"
to check that the file exists and throw ``FileNotFoundException()`` if it does not.

::

    $file = new \CodeIgniter\Files\File($path);

Taking Advantage of Spl
=======================

Once you have an instance, you have the full power of the SplFileInfo class at the ready, including::

    // Get the file's basename
    echo $file->getBasename();
    // Get last modified time
    echo $file->getMTime();
    // Get the true real path
    echo $file->getRealPath();
    // Get the file permissions
    echo $file->getPerms();

    // Write CSV rows to it.
    if ($file->isWritable())
    {
        $csv = $file->openFile('w');

        foreach ($rows as $row)
        {
            $csv->fputcsv($row);
        }
    }

New Features
============

In addition to all of the methods in the SplFileInfo class, you get some new tools.

**getRandomName()**

You can generate a cryptographically secure random filename, with the current timestamp prepended, with the ``getRandomName()``
method. This is especially useful to rename files when moving it so that the filename is unguessable::

	// Generates something like: 1465965676_385e33f741.jpg
	$newName = $file->getRandomName();

**getSize()**

Returns the size of the uploaded file in bytes. You can pass in either 'kb' or 'mb' as the first parameter to get
the results in kilobytes or megabytes, respectively::

	$bytes     = $file->getSize();      // 256901
	$kilobytes = $file->getSize('kb');  // 250.880
	$megabytes = $file->getSize('mb');  // 0.245

**getSizeByUnit()**

Returns the size of the uploaded file default in bytes. You can pass in either 'kb' or 'mb' as the first parameter to get
the results in kilobytes or megabytes, respectively::

	$bytes     = $file->getSizeByUnit();      // 256901
	$kilobytes = $file->getSizeByUnit('kb');  // 250.880
	$megabytes = $file->getSizeByUnit('mb');  // 0.245

**getMimeType()**

Retrieve the media type (mime type) of the file. Uses methods that are considered as secure as possible when determining
the type of file::

	$type = $file->getMimeType();

	echo $type; // image/png

**guessExtension()**

Attempts to determine the file extension based on the trusted ``getMimeType()`` method. If the mime type is unknown,
will return null. This is often a more trusted source than simply using the extension provided by the filename. Uses
the values in **app/Config/Mimes.php** to determine extension::

	// Returns 'jpg' (WITHOUT the period)
	$ext = $file->guessExtension();

Moving Files
------------

Each file can be moved to its new location with the aptly named ``move()`` method. This takes the directory to move
the file to as the first parameter::

	$file->move(WRITEPATH.'uploads');

By default, the original filename was used. You can specify a new filename by passing it as the second parameter::

	$newName = $file->getRandomName();
	$file->move(WRITEPATH.'uploads', $newName);

The move() method returns a new File instance that for the relocated file, so you must capture the result if the
resulting location is needed::

    $file = $file->move(WRITEPATH.'uploads');
