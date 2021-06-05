#################
Filesystem Helper
#################

The Directory Helper file contains functions that assist in working with
directories.

.. contents::
  :local:

Loading this Helper
===================

This helper is loaded using the following code:

::

    helper('filesystem');

Available Functions
===================

The following functions are available:

.. php:function:: directory_map($source_dir[, $directory_depth = 0[, $hidden = FALSE]])

    :param	string  $source_dir: Path to the source directory
    :param	int	    $directory_depth: Depth of directories to traverse (0 = fully recursive, 1 = current dir, etc)
    :param	bool	$hidden: Whether to include hidden paths
    :returns:	An array of files
    :rtype:	array

    Examples::

        $map = directory_map('./mydirectory/');

    .. note:: Paths are almost always relative to your main index.php file.

    Sub-folders contained within the directory will be mapped as well. If
    you wish to control the recursion depth, you can do so using the second
    parameter (integer). A depth of 1 will only map the top level directory::

        $map = directory_map('./mydirectory/', 1);

    By default, hidden files will not be included in the returned array and
    hidden directories will be skipped. To override this behavior, you may
    set a third parameter to true (boolean)::

        $map = directory_map('./mydirectory/', FALSE, TRUE);

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

    :param	string	$original: Original source directory
    :param	string	$target: Target destination directory
    :param	bool	$overwrite: Whether individual files overwrite on collision

    Recursively copies the files and directories of the origin directory
    into the target directory, i.e. "mirror" its contents.

    Example::

        try
        {     
            directory_mirror($uploadedImages, FCPATH . 'images/');
        }
        catch (Throwable $e)
        {     
            echo 'Failed to export uploads!';
        }

    You can optionally change the overwrite behavior via the third parameter.

.. php:function:: write_file($path, $data[, $mode = 'wb'])

    :param	string	$path: File path
    :param	string	$data: Data to write to file
    :param	string	$mode: ``fopen()`` mode
    :returns:	TRUE if the write was successful, FALSE in case of an error
    :rtype:	bool

    Writes data to the file specified in the path. If the file does not exist then the
    function will create it.

    Example::

        $data = 'Some file data';
        if ( ! write_file('./path/to/file.php', $data))
        {     
            echo 'Unable to write the file';
        }
        else
        {     
            echo 'File written!';
        }

    You can optionally set the write mode via the third parameter::

        write_file('./path/to/file.php', $data, 'r+');

    The default mode is 'wb'. Please see the `PHP user guide <https://www.php.net/manual/en/function.fopen.php>`_
    for mode options.

    .. note:: In order for this function to write data to a file, its permissions must
        be set such that it is writable. If the file does not already exist,
        then the directory containing it must be writable.

    .. note:: The path is relative to your main site index.php file, NOT your
        controller or view files. CodeIgniter uses a front controller so paths
        are always relative to the main site index.

    .. note:: This function acquires an exclusive lock on the file while writing to it.

.. php:function:: delete_files($path[, $delDir = FALSE[, $htdocs = FALSE[, $hidden = FALSE]]])

    :param	string	$path: Directory path
    :param	bool	$delDir: Whether to also delete directories
    :param	bool	$htdocs: Whether to skip deleting .htaccess and index page files
    :param  bool    $hidden: Whether to also delete hidden files (files beginning with a period)
    :returns:	TRUE on success, FALSE in case of an error
    :rtype:	bool

    Deletes ALL files contained in the supplied path.

    Example::

        delete_files('./path/to/directory/');

    If the second parameter is set to TRUE, any directories contained within the supplied
    root path will be deleted as well.

    Example::

        delete_files('./path/to/directory/', TRUE);

    .. note:: The files must be writable or owned by the system in order to be deleted.

.. php:function:: get_filenames($source_dir[, $include_path = FALSE])

    :param	string	$source_dir: Directory path
    :param	bool|null	$include_path: Whether to include the path as part of the filename; false for no path, null for the path relative to $source_dir, true for the full path
    :param	bool	$hidden: Whether to include hidden files (files beginning with a period)
    :returns:	An array of file names
    :rtype:	array

    Takes a server path as input and returns an array containing the names of all files
    contained within it. The file path can optionally be added to the file names by setting
    the second parameter to 'relative' for relative paths or any other non-empty value for
    a full file path.

    Example::

        $controllers = get_filenames(APPPATH.'controllers/');

.. php:function:: get_dir_file_info($source_dir, $top_level_only)

    :param	string	$source_dir: Directory path
    :param	bool	$top_level_only: Whether to look only at the specified directory (excluding sub-directories)
    :returns:	An array containing info on the supplied directory's contents
    :rtype:	array

    Reads the specified directory and builds an array containing the filenames, filesize,
    dates, and permissions. Sub-folders contained within the specified path are only read
    if forced by sending the second parameter to FALSE, as this can be an intensive
    operation.

    Example::

        $models_info = get_dir_file_info(APPPATH.'models/');

.. php:function:: get_file_info($file[, $returned_values = ['name', 'server_path', 'size', 'date']])

    :param	string	        $file: File path
    :param	array|string    $returned_values: What type of info to return to be passed as array or comma separated string
    :returns:	An array containing info on the specified file or FALSE on failure
    :rtype:	array

    Given a file and path, returns (optionally) the *name*, *path*, *size* and *date modified*
    information attributes for a file. Second parameter allows you to explicitly declare what
    information you want returned.

    Valid ``$returned_values`` options are: `name`, `size`, `date`, `readable`, `writeable`,
    `executable` and `fileperms`.

.. php:function:: symbolic_permissions($perms)

    :param	int	$perms: Permissions
    :returns:	Symbolic permissions string
    :rtype:	string

    Takes numeric permissions (such as is returned by ``fileperms()``) and returns
    standard symbolic notation of file permissions.

    ::

        echo symbolic_permissions(fileperms('./index.php'));  // -rw-r--r--

.. php:function:: octal_permissions($perms)

    :param	int	$perms: Permissions
    :returns:	Octal permissions string
    :rtype:	string

    Takes numeric permissions (such as is returned by ``fileperms()``) and returns
    a three character octal notation of file permissions.

    ::

        echo octal_permissions(fileperms('./index.php')); // 644

.. php:function:: same_file($file1, $file2)

    :param	string	$file1: Path to the first file
    :param	string	$file2: Path to the second file
    :returns:	Whether both files exist with identical hashes
    :rtype:	boolean

    Compares two files to see if they are the same (based on their MD5 hash).

    ::

        echo same_file($newFile, $oldFile) ? 'Same!' : 'Different!';

.. php:function:: set_realpath($path[, $check_existence = FALSE])

    :param	string	$path: Path
    :param	bool	$check_existence: Whether to check if the path actually exists
    :returns:	An absolute path
    :rtype:	string

    This function will return a server path without symbolic links or
    relative directory structures. An optional second argument will
    cause an error to be triggered if the path cannot be resolved.

    Examples::

        $file = '/etc/php5/apache2/php.ini';
        echo set_realpath($file); // Prints '/etc/php5/apache2/php.ini'

        $non_existent_file = '/path/to/non-exist-file.txt';
        echo set_realpath($non_existent_file, TRUE);	// Shows an error, as the path cannot be resolved
        echo set_realpath($non_existent_file, FALSE);	// Prints '/path/to/non-exist-file.txt'

        $directory = '/etc/php5';
        echo set_realpath($directory);	// Prints '/etc/php5/'

        $non_existent_directory = '/path/to/nowhere';
        echo set_realpath($non_existent_directory, TRUE);	// Shows an error, as the path cannot be resolved
        echo set_realpath($non_existent_directory, FALSE);	// Prints '/path/to/nowhere'
