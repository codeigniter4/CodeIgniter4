#################
Filesystem Helper
#################

The Directory Helper file contains functions that assist in working with
directories.

.. contents::
    :local:
    :depth: 2

Loading this Helper
===================

This helper is loaded using the following code:

.. literalinclude:: filesystem_helper/001.php

Available Functions
===================

The following functions are available:

.. php:function:: directory_map($sourceDir[, $directoryDepth = 0[, $hidden = false]])

    :param    string  $sourceDir: Path to the source directory
    :param    int   $directoryDepth: Depth of directories to traverse (0 = fully recursive, 1 = current dir, etc)
    :param    bool    $hidden: Whether to include hidden paths
    :returns:    An array of files
    :rtype:    array

    Examples:

    .. literalinclude:: filesystem_helper/002.php

    .. note:: Paths are almost always relative to your main index.php file.

    Sub-folders contained within the directory will be mapped as well. If
    you wish to control the recursion depth, you can do so using the second
    parameter (integer). A depth of 1 will only map the top level directory:

    .. literalinclude:: filesystem_helper/003.php

    By default, hidden files will not be included in the returned array and
    hidden directories will be skipped. To override this behavior, you may
    set a third parameter to true (boolean):

    .. literalinclude:: filesystem_helper/004.php

    Each folder name will be an array index, while its contained files will
    be numerically indexed. Here is an example of a typical array::

        Array (
            [libraries] => Array
                (
                    [0] => benchmark.html
                    [1] => config.html
                    ["database/"] => Array
                        (
                            [0] => query_builder.html
                            [1] => binds.html
                            [2] => configuration.html
                            [3] => connecting.html
                            [4] => examples.html
                            [5] => fields.html
                            [6] => index.html
                            [7] => queries.html
                        )
                    [2] => email.html
                    [3] => file_uploading.html
                    [4] => image_lib.html
                    [5] => input.html
                    [6] => language.html
                    [7] => loader.html
                    [8] => pagination.html
                    [9] => uri.html
                )
        )

    If no results are found, this will return an empty array.

.. php:function:: directory_mirror($original, $target[, $overwrite = true])

    :param    string    $original: Original source directory
    :param    string    $target: Target destination directory
    :param    bool    $overwrite: Whether individual files overwrite on collision

    Recursively copies the files and directories of the origin directory
    into the target directory, i.e. "mirror" its contents.

    Example:

    .. literalinclude:: filesystem_helper/005.php

    You can optionally change the overwrite behavior via the third parameter.

.. php:function:: write_file($path, $data[, $mode = 'wb'])

    :param    string    $path: File path
    :param    string    $data: Data to write to file
    :param    string    $mode: ``fopen()`` mode
    :returns:    true if the write was successful, false in case of an error
    :rtype:    bool

    Writes data to the file specified in the path. If the file does not exist then the
    function will create it.

    Example:

    .. literalinclude:: filesystem_helper/006.php

    You can optionally set the write mode via the third parameter:

    .. literalinclude:: filesystem_helper/007.php

    The default mode is 'wb'. Please see the `PHP user guide <https://www.php.net/manual/en/function.fopen.php>`_
    for mode options.

    .. note:: In order for this function to write data to a file, its permissions must
        be set such that it is writable. If the file does not already exist,
        then the directory containing it must be writable.

    .. note:: The path is relative to your main site index.php file, NOT your
        controller or view files. CodeIgniter uses a front controller so paths
        are always relative to the main site index.

    .. note:: This function acquires an exclusive lock on the file while writing to it.

.. php:function:: delete_files($path[, $delDir = false[, $htdocs = false[, $hidden = false]]])

    :param    string    $path: Directory path
    :param    bool    $delDir: Whether to also delete directories
    :param    bool    $htdocs: Whether to skip deleting .htaccess and index page files
    :param  bool    $hidden: Whether to also delete hidden files (files beginning with a period)
    :returns:    true on success, false in case of an error
    :rtype:    bool

    Deletes ALL files contained in the supplied path.

    Example:

    .. literalinclude:: filesystem_helper/008.php

    If the second parameter is set to true, any directories contained within the supplied
    root path will be deleted as well.

    Example:

    .. literalinclude:: filesystem_helper/009.php

    .. note:: The files must be writable or owned by the system in order to be deleted.

.. php:function:: get_filenames($sourceDir[, $includePath = false[, $hidden = false[, $includeDir = true]]])

    :param    string    $sourceDir: Directory path
    :param    bool|null    $includePath: Whether to include the path as part of the filename; false for no path, null for the path relative to ``$sourceDir``, true for the full path
    :param    bool    $hidden: Whether to include hidden files (files beginning with a period)
    :param    bool    $includeDir: Whether to include directories in the array output
    :returns:    An array of file names
    :rtype:    array

    Takes a server path as input and returns an array containing the names of all files
    contained within it. The file path can optionally be added to the file names by setting
    the second parameter to 'relative' for relative paths or any other non-empty value for
    a full file path.

    Example:

    .. literalinclude:: filesystem_helper/010.php

.. php:function:: get_dir_file_info($sourceDir[, $topLevelOnly = true])

    :param    string    $sourceDir: Directory path
    :param    bool    $topLevelOnly: Whether to look only at the specified directory (excluding sub-directories)
    :returns:    An array containing info on the supplied directory's contents
    :rtype:    array

    Reads the specified directory and builds an array containing the filenames, filesize,
    dates, and permissions. Sub-folders contained within the specified path are only read
    if forced by sending the second parameter to false, as this can be an intensive
    operation.

    Example:

    .. literalinclude:: filesystem_helper/011.php

.. php:function:: get_file_info($file[, $returnedValues = ['name', 'server_path', 'size', 'date']])

    :param    string        $file: File path
    :param    array|string  $returnedValues: What type of info to return to be passed as array or comma separated string
    :returns:    An array containing info on the specified file or false on failure
    :rtype:    array

    Given a file and path, returns (optionally) the *name*, *path*, *size* and *date modified*
    information attributes for a file. Second parameter allows you to explicitly declare what
    information you want returned.

    Valid ``$returnedValues`` options are: ``name``, ``size``, ``date``, ``readable``, ``writeable``,
    ``executable`` and ``fileperms``.

.. php:function:: symbolic_permissions($perms)

    :param    int    $perms: Permissions
    :returns:    Symbolic permissions string
    :rtype:    string

    Takes numeric permissions (such as is returned by ``fileperms()``) and returns
    standard symbolic notation of file permissions.

    .. literalinclude:: filesystem_helper/012.php

.. php:function:: octal_permissions($perms)

    :param    int    $perms: Permissions
    :returns:    Octal permissions string
    :rtype:    string

    Takes numeric permissions (such as is returned by ``fileperms()``) and returns
    a three character octal notation of file permissions.

    .. literalinclude:: filesystem_helper/013.php

.. php:function:: same_file($file1, $file2)

    :param    string    $file1: Path to the first file
    :param    string    $file2: Path to the second file
    :returns:    Whether both files exist with identical hashes
    :rtype:    boolean

    Compares two files to see if they are the same (based on their MD5 hash).

    .. literalinclude:: filesystem_helper/014.php

.. php:function:: set_realpath($path[, $checkExistence = false])

    :param    string    $path: Path
    :param    bool    $checkExistence: Whether to check if the path actually exists
    :returns:    An absolute path
    :rtype:    string

    This function will return a server path without symbolic links or
    relative directory structures. An optional second argument will
    cause an error to be triggered if the path cannot be resolved.

    Examples:

    .. literalinclude:: filesystem_helper/015.php
