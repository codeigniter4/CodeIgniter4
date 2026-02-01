<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Images\Handlers;

use CodeIgniter\Images\Exceptions\ImageException;
use Config\Images;
use Imagick;
use ImagickDraw;
use ImagickDrawException;
use ImagickException;
use ImagickPixel;
use ImagickPixelException;

/**
 * Image handler for Imagick extension.
 */
class ImageMagickHandler extends BaseHandler
{
    /**
     * Stores Imagick instance.
     *
     * @var Imagick|null
     */
    protected $resource;

    /**
     * Constructor.
     *
     * @param Images $config
     *
     * @throws ImageException
     */
    public function __construct($config = null)
    {
        parent::__construct($config);

        if (! extension_loaded('imagick')) {
            throw ImageException::forMissingExtension('IMAGICK');  // @codeCoverageIgnore
        }
    }

    /**
     * Loads the image for manipulation.
     *
     * @return void
     *
     * @throws ImageException
     */
    protected function ensureResource()
    {
        if (! $this->resource instanceof Imagick) {
            // Verify that we have a valid image
            $this->image();

            try {
                $this->resource = new Imagick();
                $this->resource->readImage($this->image()->getPathname());

                // Check for valid image
                if ($this->resource->getImageWidth() === 0 || $this->resource->getImageHeight() === 0) {
                    throw ImageException::forInvalidImageCreate($this->image()->getPathname());
                }

                $this->supportedFormatCheck();
            } catch (ImagickException $e) {
                throw ImageException::forInvalidImageCreate($e->getMessage());
            }
        }
    }

    /**
     * Handles all the grunt work of resizing, etc.
     *
     * @param string $action  Type of action to perform
     * @param int    $quality Quality setting for Imagick operations
     *
     * @return $this
     *
     * @throws ImageException
     */
    protected function process(string $action, int $quality = 100)
    {
        $this->image();

        $this->ensureResource();

        try {
            switch ($action) {
                case 'resize':
                    $this->resource->resizeImage(
                        $this->width,
                        $this->height,
                        Imagick::FILTER_LANCZOS,
                        0,
                    );
                    break;

                case 'crop':
                    $width  = $this->width;
                    $height = $this->height;
                    $xAxis  = $this->xAxis ?? 0;
                    $yAxis  = $this->yAxis ?? 0;

                    $this->resource->cropImage(
                        $width,
                        $height,
                        $xAxis,
                        $yAxis,
                    );

                    // Reset canvas to cropped size
                    $this->resource->setImagePage(0, 0, 0, 0);
                    break;
            }

            // Handle transparency for supported image types
            if (in_array($this->image()->imageType, $this->supportTransparency, true)
                && $this->resource->getImageAlphaChannel() === Imagick::ALPHACHANNEL_UNDEFINED) {
                $this->resource->setImageAlphaChannel(Imagick::ALPHACHANNEL_OPAQUE);
            }
        } catch (ImagickException) {
            throw ImageException::forImageProcessFailed();
        }

        return $this;
    }

    /**
     * Handles the actual resizing of the image.
     *
     * @return ImageMagickHandler
     *
     * @throws ImagickException
     */
    public function _resize(bool $maintainRatio = false)
    {
        if ($maintainRatio) {
            // If maintaining a ratio, we need a custom approach
            $this->ensureResource();

            // Use thumbnailImage which preserves an aspect ratio
            $this->resource->thumbnailImage($this->width, $this->height, true);

            return $this;
        }

        // Use the common process() method for normal resizing
        return $this->process('resize');
    }

    /**
     * Crops the image.
     *
     * @return $this
     *
     * @throws ImagickException
     */
    public function _crop()
    {
        // Use the common process() method for cropping
        $result = $this->process('crop');

        // Handle a case where crop dimensions exceed the original image size
        if ($this->resource instanceof Imagick) {
            $imgWidth  = $this->resource->getImageWidth();
            $imgHeight = $this->resource->getImageHeight();

            if ($this->xAxis >= $imgWidth || $this->yAxis >= $imgHeight) {
                // Create transparent background
                $background = new Imagick();
                $background->newImage($this->width, $this->height, new ImagickPixel('transparent'));
                $background->setImageFormat($this->resource->getImageFormat());

                // Composite our image on the background
                $background->compositeImage($this->resource, Imagick::COMPOSITE_OVER, 0, 0);

                // Replace our resource
                $this->resource = $background;
            }
        }

        return $result;
    }

    /**
     * Handles the rotation of an image resource.
     * Doesn't save the image, but replaces the current resource.
     *
     * @return $this
     *
     * @throws ImagickException
     */
    protected function _rotate(int $angle)
    {
        $this->ensureResource();

        // Create transparent background
        $this->resource->setImageBackgroundColor(new ImagickPixel('transparent'));
        $this->resource->rotateImage(new ImagickPixel('transparent'), $angle);

        // Reset canvas dimensions
        $this->resource->setImagePage($this->resource->getImageWidth(), $this->resource->getImageHeight(), 0, 0);

        return $this;
    }

    /**
     * Flattens transparencies, default white background
     *
     * @return $this
     *
     * @throws ImagickException|ImagickPixelException
     */
    protected function _flatten(int $red = 255, int $green = 255, int $blue = 255)
    {
        $this->ensureResource();

        // Create background
        $bg = new ImagickPixel("rgb({$red},{$green},{$blue})");

        // Create a new canvas with the background color
        $canvas = new Imagick();
        $canvas->newImage(
            $this->resource->getImageWidth(),
            $this->resource->getImageHeight(),
            $bg,
            $this->resource->getImageFormat(),
        );

        // Composite our image on the background
        $canvas->compositeImage(
            $this->resource,
            Imagick::COMPOSITE_OVER,
            0,
            0,
        );

        // Replace our resource with the flattened version
        $this->resource->clear();
        $this->resource = $canvas;

        return $this;
    }

    /**
     * Flips an image along its vertical or horizontal axis.
     *
     * @return $this
     *
     * @throws ImagickException
     */
    protected function _flip(string $direction)
    {
        $this->ensureResource();

        if ($direction === 'horizontal') {
            $this->resource->flopImage();
        } else {
            $this->resource->flipImage();
        }

        return $this;
    }

    /**
     * Get a driver version
     *
     * @return string
     */
    public function getVersion()
    {
        $version = Imagick::getVersion();

        if (preg_match('/ImageMagick\s+(\d+\.\d+\.\d+)/', $version['versionString'], $matches)) {
            return $matches[1];
        }

        return '';
    }

    /**
     * Check if a given image format is supported
     *
     * @return void
     *
     * @throws ImageException
     */
    protected function supportedFormatCheck()
    {
        if (! $this->resource instanceof Imagick) {
            return;
        }

        if ($this->image()->imageType === IMAGETYPE_WEBP && ! in_array('WEBP', Imagick::queryFormats(), true)) {
            throw ImageException::forInvalidImageCreate(lang('images.webpNotSupported'));
        }
    }

    /**
     * Saves any changes that have been made to the file. If no new filename is
     * provided, the existing image is overwritten; otherwise a copy of the
     * file is made at $target.
     *
     * Example:
     *    $image->resize(100, 200, true)
     *          ->save();
     *
     * @param non-empty-string|null $target
     *
     * @throws ImagickException
     */
    public function save(?string $target = null, int $quality = 90): bool
    {
        $original = $target;
        $target   = ($target === null || $target === '') ? $this->image()->getPathname() : $target;

        // If no new resource has been created, then we're
        // simply copy the existing one.
        if (! $this->resource instanceof Imagick && $quality === 100) {
            if ($original === null) {
                return true;
            }

            $name = basename($target);
            $path = pathinfo($target, PATHINFO_DIRNAME);

            return $this->image()->copy($path, $name);
        }

        $this->ensureResource();

        $this->resource->setImageCompressionQuality($quality);

        if ($target !== null) {
            $extension = pathinfo($target, PATHINFO_EXTENSION);
            $this->resource->setImageFormat($extension);
        }

        try {
            $result = $this->resource->writeImage($target);

            chmod($target, $this->filePermissions);

            $this->resource->clear();
            $this->resource = null;

            return $result;
        } catch (ImagickException) {
            throw ImageException::forSaveFailed();
        }
    }

    /**
     * Handler-specific method for overlaying text on an image.
     *
     * @throws ImagickDrawException|ImagickException|ImagickPixelException
     */
    protected function _text(string $text, array $options = [])
    {
        $this->ensureResource();

        $draw = new ImagickDraw();

        if (isset($options['fontPath'])) {
            $draw->setFont($options['fontPath']);
        }

        if (isset($options['fontSize'])) {
            $draw->setFontSize($options['fontSize']);
        }

        if (isset($options['color'])) {
            $color = $options['color'];

            // Shorthand hex, #f00
            if (strlen($color) === 3) {
                $color = implode('', array_map(str_repeat(...), str_split($color), [2, 2, 2]));
            }

            [$r, $g, $b] = sscanf("#{$color}", '#%02x%02x%02x');
            $opacity     = $options['opacity'] ?? 1.0;
            $draw->setFillColor(new ImagickPixel("rgba({$r},{$g},{$b},{$opacity})"));
        }

        // Calculate text positioning
        $imgWidth  = $this->resource->getImageWidth();
        $imgHeight = $this->resource->getImageHeight();
        $xAxis     = 0;
        $yAxis     = 0;

        // Default padding
        $padding = $options['padding'] ?? 0;

        if (isset($options['hAlign'])) {
            $hOffset = $options['hOffset'] ?? 0;

            switch ($options['hAlign']) {
                case 'left':
                    $xAxis = $hOffset + $padding;
                    $draw->setTextAlignment(Imagick::ALIGN_LEFT);
                    break;

                case 'center':
                    $xAxis = $imgWidth / 2 + $hOffset;
                    $draw->setTextAlignment(Imagick::ALIGN_CENTER);
                    break;

                case 'right':
                    $xAxis = $imgWidth - $hOffset - $padding;
                    $draw->setTextAlignment(Imagick::ALIGN_RIGHT);
                    break;
            }
        }

        if (isset($options['vAlign'])) {
            $vOffset = $options['vOffset'] ?? 0;

            switch ($options['vAlign']) {
                case 'top':
                    $yAxis = $vOffset + $padding + ($options['fontSize'] ?? 16);
                    break;

                case 'middle':
                    $yAxis = $imgHeight / 2 + $vOffset;
                    break;

                case 'bottom':
                    // Note: Vertical offset is inverted for bottom alignment as per original implementation
                    $yAxis = $vOffset < 0 ? $imgHeight + $vOffset - $padding : $imgHeight - $vOffset - $padding;
                    break;
            }
        }

        if (isset($options['withShadow'])) {
            $shadow = clone $draw;

            if (isset($options['shadowColor'])) {
                $shadowColor = $options['shadowColor'];

                // Shorthand hex, #f00
                if (strlen($shadowColor) === 3) {
                    $shadowColor = implode('', array_map(str_repeat(...), str_split($shadowColor), [2, 2, 2]));
                }

                [$sr, $sg, $sb] = sscanf("#{$shadowColor}", '#%02x%02x%02x');
                $shadow->setFillColor(new ImagickPixel("rgb({$sr},{$sg},{$sb})"));
            } else {
                $shadow->setFillColor(new ImagickPixel('rgba(0,0,0,0.5)'));
            }

            $offset = $options['shadowOffset'] ?? 3;

            $this->resource->annotateImage(
                $shadow,
                $xAxis + $offset,
                $yAxis + $offset,
                0,
                $text,
            );
        }

        // Draw the main text
        $this->resource->annotateImage(
            $draw,
            $xAxis,
            $yAxis,
            0,
            $text,
        );
    }

    /**
     * Return the width of an image.
     *
     * @return int
     *
     * @throws ImagickException
     */
    public function _getWidth()
    {
        $this->ensureResource();

        return $this->resource->getImageWidth();
    }

    /**
     * Return the height of an image.
     *
     * @return int
     *
     * @throws ImagickException
     */
    public function _getHeight()
    {
        $this->ensureResource();

        return $this->resource->getImageHeight();
    }

    /**
     * Reads the EXIF information from the image and modifies the orientation
     * so that displays correctly in the browser. This is especially an issue
     * with images taken by smartphones who always store the image up-right,
     * but set the orientation flag to display it correctly.
     *
     * @param bool $silent If true, will ignore exceptions when PHP doesn't support EXIF.
     *
     * @return $this
     */
    public function reorient(bool $silent = false)
    {
        $orientation = $this->getEXIF('Orientation', $silent);

        return match ($orientation) {
            2       => $this->flip('horizontal'),
            3       => $this->rotate(180),
            4       => $this->rotate(180)->flip('horizontal'),
            5       => $this->rotate(90)->flip('horizontal'),
            6       => $this->rotate(90),
            7       => $this->rotate(270)->flip('horizontal'),
            8       => $this->rotate(270),
            default => $this,
        };
    }

    /**
     * Clears metadata from the image.
     *
     * @return $this
     *
     * @throws ImagickException
     */
    public function clearMetadata(): static
    {
        $this->ensureResource();

        $this->resource->stripImage();

        return $this;
    }
}
