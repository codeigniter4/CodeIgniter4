########################
Image Manipulation Class
########################

CodeIgniter's Image Manipulation class lets you perform the following
actions:

-  Image Resizing
-  Thumbnail Creation
-  Image Cropping
-  Image Rotating
-  Image Watermarking

The following image libraries are supported: GD/GD2, and ImageMagick.

.. contents::
    :local:
    :depth: 2

**********************
Initializing the Class
**********************

Like most other classes in CodeIgniter, the image class is initialized
in your controller by calling the Services class::

	$image = Config\Services::image();

You can pass the alias for the image library you wish to use into the
Service function::

    $image = Config\Services::image('imagick');

The available Handlers are as follows:

- gd        The GD/GD2 image library
- imagick   The ImageMagick library.

If using the ImageMagick library, you must set the path to the library on your
server in **app/Config/Images.php**.

.. note:: The ImageMagick handler does NOT require the imagick extension to be
        loaded on the server. As long as your script can access the library
        and can run ``exec()`` on the server, it should work.

Processing an Image
===================

Regardless of the type of processing you would like to perform
(resizing, cropping, rotation, or watermarking), the general process is
identical. You will set some preferences corresponding to the action you
intend to perform, then call one of the available processing functions.
For example, to create an image thumbnail you'll do this::

	$image = Config\Services::image()
		->withFile('/path/to/image/mypic.jpg')
		->fit(100, 100, 'center')
		->save('/path/to/image/mypic_thumb.jpg');

The above code tells the library to look for an image
called *mypic.jpg* located in the source_image folder, then create a
new image from it that is 100 x 100pixels using the GD2 image_library,
and save it to a new file (the thumb). Since it is using the fit() method,
it will attempt to find the best portion of the image to crop based on the
desired aspect ratio, and then crop and resize the result.

An image can be processed through as many of the available methods as
needed before saving. The original image is left untouched, and a new image
is used and passed through each method, applying the results on top of the
previous results::

	$image = Config\Services::image()
		->withFile('/path/to/image/mypic.jpg')
		->reorient()
		->rotate(90)
		->crop(100, 100, 0, 0)
		->save('/path/to/image/mypic_thumb.jpg');

This example would take the same image and first fix any mobile phone orientation issues,
rotate the image by 90 degress, and then crop the result into a 100x100 pixel image,
starting at the top left corner. The result would be saved as the thumbnail.

.. note:: In order for the image class to be allowed to do any
	processing, the folder containing the image files must have write
	permissions.

.. note:: Image processing can require a considerable amount of server
	memory for some operations. If you are experiencing out of memory errors
	while processing images you may need to limit their maximum size, and/or
	adjust PHP memory limits.

Processing Methods
==================

There are six available processing methods:

-  $image->crop()
-  $image->fit()
-  $image->flatten()
-  $image->flip()
-  $image->resize()
-  $image->rotate()
-  $image->text()

These methods return the class instance so they can be chained together, as shown above.
If they fail they will throw a ``CodeIgniter\Images\ImageException`` that contains
the error message. A good practice is to catch the exceptions, showing an
error upon failure, like this::

	try {
        $image = Config\Services::image()
            ->withFile('/path/to/image/mypic.jpg')
            ->fit(100, 100, 'center')
            ->save('/path/to/image/mypic_thumb.jpg');
	}
	catch (CodeIgniter\Images\ImageException $e)
	{
		echo $e->getMessage();
	}

.. note:: You can optionally specify the HTML formatting to be applied to
	the errors, by submitting the opening/closing tags in the function,
	like this::

	$this->image_lib->display_errors('<p>', '</p>');

Cropping Images
---------------

Images can be cropped so that only a portion of the original image remains. This is often used when creating
thumbnail images that should match a certain size/aspect ratio. This is handled with the ``crop()`` method::

    crop(int $width = null, int $height = null, int $x = null, int $y = null, bool $maintainRatio = false, string $masterDim = 'auto')

- **$width** is the desired width of the resulting image, in pixels.
- **$height** is the desired height of the resulting image, in pixels.
- **$x** is the number of pixels from the left side of the image to start cropping.
- **$y** is the number of pixels from the top of the image to start cropping.
- **$maintainRatio** will, if true, adjust the final dimensions as needed to maintain the image's original aspect ratio.
- **$masterDim** specifies which dimension should be left untouched when $maintainRatio is true. Values can be: 'width', 'height', or 'auto'.

To take a 50x50 pixel square out of the center of an image, you would need to first calculate the appropriate x and y
offset values::

    $info = Services::image('imagick')
		->withFile('/path/to/image/mypic.jpg')
		->getFile()
		->getProperties(true);

    $xOffset = ($info['width'] / 2) - 25;
    $yOffset = ($info['height'] / 2) - 25;

    Services::image('imagick')
		->withFile('/path/to/image/mypic.jpg')
		->crop(50, 50, $xOffset, $yOffset)
		->save('path/to/new/image.jpg');

Fitting Images
--------------

The ``fit()`` method aims to help simplify cropping a portion of an image in a "smart" way, by doing the following steps:

- Determine the correct portion of the original image to crop in order to maintain the desired aspect ratio.
- Crop the original image.
- Resize to the final dimensions.

::

    fit(int $width, int $height = null, string $position = 'center')

- **$width** is the desired final width of the image.
- **$height** is the desired final height of the image.
- **$position** determines the portion of the image to crop out. Allowed positions: 'top-left', 'top', 'top-right', 'left', 'center', 'right', 'bottom-left', 'bottom', 'bottom-right'.

This provides a much simpler way to crop that will always maintain the aspect ratio::

	Services::image('imagick')
		->withFile('/path/to/image/mypic.jpg')
		->fit(100, 150, 'left')
		->save('path/to/new/image.jpg');

Flattening Images
-----------------

The ``flatten()`` method aims to add a background color behind transparent images (PNG) and convert RGBA pixels to RGB pixels

- Specify a background color when converting from transparent images to jpgs.

::

    flatten(int $red = 255, int $green = 255, int $blue = 255)

- **$red** is the red value of the background.
- **$green** is the green value of the background.
- **$blue** is the blue value of the background.

::

	Services::image('imagick')
		->withFile('/path/to/image/mypic.png')
		->flatten()
		->save('path/to/new/image.jpg');

	Services::image('imagick')
		->withFile('/path/to/image/mypic.png')
		->flatten(25,25,112)
		->save('path/to/new/image.jpg');

Flipping Images
---------------

Images can be flipped along either their horizontal or vertical axis::

    flip(string $dir)

- **$dir** specifies the axis to flip along. Can be either 'vertical' or 'horizontal'.

::

	Services::image('imagick')
		->withFile('/path/to/image/mypic.jpg')
		->flip('horizontal')
		->save('path/to/new/image.jpg');

Resizing Images
---------------

Images can be resized to fit any dimension you require with the resize() method::

	resize(int $width, int $height, bool $maintainRatio = false, string $masterDim = 'auto')

- **$width** is the desired width of the new image in pixels
- **$height** is the desired height of the new image in pixels
- **$maintainRatio** determines whether the image is stretched to fit the new dimensions, or the original aspect ratio is maintained.
- **$masterDim** specifies which axis should have its dimension honored when maintaining ratio. Either 'width', 'height'.

When resizing images you can choose whether to maintain the ratio of the original image, or stretch/squash the new
image to fit the desired dimensions. If $maintainRatio is true, the dimension specified by $masterDim will stay the same,
while the other dimension will be altered to match the original image's aspect ratio.

::

	Services::image('imagick')
		->withFile('/path/to/image/mypic.jpg')
		->resize(200, 100, true, 'height')
		->save('path/to/new/image.jpg');

Rotating Images
---------------

The rotate() method allows you to rotate an image in 90 degree increments::

	rotate(float $angle)

- **$angle** is the number of degrees to rotate. One of '90', '180', '270'.

.. note:: While the $angle parameter accepts a float, it will convert it to an integer during the process.
		If the value is any other than the three values listed above, it will throw a CodeIgniter\Images\ImageException.

Adding a Text Watermark
-----------------------

You can overlay a text watermark onto the image very simply with the text() method. This is useful for placing copyright
notices, photographer names, or simply marking the images as a preview so they won't be used in other people's final
products.

::

	text(string $text, array $options = [])

The first parameter is the string of text that you wish to display. The second parameter is an array of options
that allow you to specify how the text should be displayed::

	Services::image('imagick')
		->withFile('/path/to/image/mypic.jpg')
		->text('Copyright 2017 My Photo Co', [
		    'color'      => '#fff',
		    'opacity'    => 0.5,
		    'withShadow' => true,
		    'hAlign'     => 'center',
		    'vAlign'     => 'bottom',
		    'fontSize'   => 20
		])
		->save('path/to/new/image.jpg');

The possible options that are recognized are as follows:

- color         Text Color (hex number), i.e. #ff0000
- opacity		A number between 0 and 1 that represents the opacity of the text.
- withShadow	Boolean value whether to display a shadow or not.
- shadowColor   Color of the shadow (hex number)
- shadowOffset	How many pixels to offset the shadow. Applies to both the vertical and horizontal values.
- hAlign        Horizontal alignment: left, center, right
- vAlign        Vertical alignment: top, middle, bottom
- hOffset		Additional offset on the x axis, in pixels
- vOffset		Additional offset on the y axis, in pixels
- fontPath		The full server path to the TTF font you wish to use. System font will be used if none is given.
- fontSize		The font size to use. When using the GD handler with the system font, valid values are between 1-5.

.. note:: The ImageMagick driver does not recognize full server path for fontPath. Instead, simply provide the
		name of one of the installed system fonts that you wish to use, i.e. Calibri.

