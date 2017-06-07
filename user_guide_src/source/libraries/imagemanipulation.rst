=====================
Image Manipulation Class
=====================

The Image Manipulation class provides an easier and expressive way to create, edit, and compose images and supports
currently the two most common image processing libraries GD Library and Imagick.

.. contents:: Page Contents

Loading the Library
=====================

The library can be loaded manually::

	$manager = new \Intervention\Image\ImageManager();


Resize
========================

Resizes current image based on given width and/or height. To constraint the resize command, pass an optional Closure callback as third parameter::

        $img = $manager->make(WRITEPATH . 'uploads/example.jpg');
        $img->resize(300, 300);

or::

        $img = $manager->make(WRITEPATH . 'uploads/example.jpg');
        $img->resize(500, 500, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

Crop
========================

Cut out a rectangular part of the current image with given width and height. Define optional x,y coordinates to move the top-left corner of the cutout to a certain position.::

        $img = $manager->make(WRITEPATH . 'uploads/example.jpg');
        $img->crop(100, 100, 25, 25);


Fit
========================

Combine cropping and resizing to format image in a smart way. The method will find the best fitting aspect ratio of your given width and height on the current image automatically, cut it out and resize it to the given dimension. You may pass an optional Closure callback as third parameter, to prevent possible upsizing and a custom position of the cutout as fourth parameter.::

        $img = $manager->make(WRITEPATH . 'uploads/example.jpg');
        $img->fit(800, 600);


Rotate
========================
Rotate the current image counter-clockwise by a given angle. Optionally define a background color for the uncovered zone after the rotation.::

        $img = $manager->make(WRITEPATH . 'uploads/example.jpg');
        $img->rotate(-45);


Exif
========================

Read Exif meta data from current image. Image object must be instantiated from file path to read the EXIF data correctly.::

        $img = $manager->make(WRITEPATH . 'uploads/example.jpg');
        $data = $img->exif();


Read model of the camera::

        $img = $manager->make(WRITEPATH . 'uploads/example.jpg');
        $model = $img->exif('Model');



Watermark
========================

Paste a given image source over the current image with an optional position and a offset coordinate. This method can be used to apply another image as watermark because the transparency values are maintained.::

        $img = $manager->make(WRITEPATH . 'uploads/example.jpg');
        $img->text('The quick brown fox jumps over the lazy dog.', 780, 580, function($font) {
            $font->file('foo/bar.ttf');
            $font->size(80);
            $font->color('#ffffff');
            $font->align('center');
            $font->valign('top');
            $font->angle(45);
        });

Insert Image::

        $img = $manager->make(WRITEPATH . 'uploads/example.jpg');
        $img->insert(WRITEPATH . 'uploads/ci-logo-white.png', 'bottom-right', 10, 10);


Encode
========================
Encodes the current image in given format and given image quality.::

        $img = $manager->make(WRITEPATH . 'uploads/example.jpg');
        $img->encode('png',75);

Or save method::

        $img = $manager->make(WRITEPATH . 'uploads/example.jpg');
        $img->save(WRITEPATH . 'uploads/example-encode.png');
        
    


Orientation
========================

This method reads the EXIF image profile setting 'Orientation' and performs a rotation on the image to display the image correctly. Image object must be instantiated from file path to read the EXIF data correctly.::

        $img = $manager->make(WRITEPATH . 'uploads/example.jpg');
        $img->orientate();


Save image in filesystem
========================
Save the current state of the image object in filesystem. Define optionally a certain path where the image should be saved. You can also optionally set the quality of the image file as second parameter.::
        
        $img = $manager->make(WRITEPATH . 'uploads/example.jpg');
        $img->resize(300, 300);
        $img->save(WRITEPATH . 'uploads/example-resized.jpg');