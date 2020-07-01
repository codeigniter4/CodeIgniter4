<?php

/**
 * Image language strings.
 *
 * @package    CodeIgniter
 * @author     CodeIgniter Dev Team
 * @copyright  2019-2020 CodeIgniter Foundation
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 *
 * @codeCoverageIgnore
 */

return [
   'sourceImageRequired'    => 'You must specify a source image in your preferences.',
   'gdRequired'             => 'The GD image library is required to use this feature.',
   'gdRequiredForProps'     => 'Your server must support the GD image library in order to determine the image properties.',
   'gifNotSupported'        => 'GIF images are often not supported due to licensing restrictions. You may have to use JPG or PNG images instead.',
   'jpgNotSupported'        => 'JPG images are not supported.',
   'pngNotSupported'        => 'PNG images are not supported.',
   'webpNotSupported'       => 'WEBP images are not supported.',   
   'fileNotSupported'       => 'The supplied file is not a supported image type.',
   'unsupportedImageCreate' => 'Your server does not support the GD function required to process this type of image.',
   'jpgOrPngRequired'       => 'The image resize protocol specified in your preferences only works with JPEG or PNG image types.',
   'rotateUnsupported'      => 'Image rotation does not appear to be supported by your server.',
   'libPathInvalid'         => 'The path to your image library is not correct. Please set the correct path in your image preferences. {0}',
   'imageProcessFailed'     => 'Image processing failed. Please verify that your server supports the chosen protocol and that the path to your image library is correct.',
   'rotationAngleRequired'  => 'An angle of rotation is required to rotate the image.',
   'invalidPath'            => 'The path to the image is not correct.',
   'copyFailed'             => 'The image copy routine failed.',
   'missingFont'            => 'Unable to find a font to use.',
   'saveFailed'             => 'Unable to save the image. Please make sure the image and file directory are writable.',
   'invalidDirection'       => 'Flip direction can be only `vertical` or `horizontal`. Given: {0}',
   'exifNotSupported'       => 'Reading EXIF data is not supported by this PHP installation.',
];
