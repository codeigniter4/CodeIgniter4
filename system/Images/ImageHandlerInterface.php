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

namespace CodeIgniter\Images;

/**
 * Expected behavior of an Image handler
 */
interface ImageHandlerInterface
{
    /**
     * Resize the image
     *
     * @param bool $maintainRatio If true, will get the closest match possible while keeping aspect ratio true.
     *
     * @return $this
     */
    public function resize(int $width, int $height, bool $maintainRatio = false, string $masterDim = 'auto');

    /**
     * Crops the image to the desired height and width. If one of the height/width values
     * is not provided, that value will be set the appropriate value based on offsets and
     * image dimensions.
     *
     * @param int|null $x X-axis coord to start cropping from the left of image
     * @param int|null $y Y-axis coord to start cropping from the top of image
     *
     * @return $this
     */
    public function crop(?int $width = null, ?int $height = null, ?int $x = null, ?int $y = null, bool $maintainRatio = false, string $masterDim = 'auto');

    /**
     * Changes the stored image type to indicate the new file format to use when saving.
     * Does not touch the actual resource.
     *
     * @param int $imageType A PHP imagetype constant, e.g. https://www.php.net/manual/en/function.image-type-to-mime-type.php
     *
     * @return $this
     */
    public function convert(int $imageType);

    /**
     * Rotates the image on the current canvas.
     *
     * @return $this
     */
    public function rotate(float $angle);

    /**
     * Flattens transparencies, default white background
     *
     * @return $this
     */
    public function flatten(int $red = 255, int $green = 255, int $blue = 255);

    /**
     * Reads the EXIF information from the image and modifies the orientation
     * so that displays correctly in the browser.
     *
     * @return ImageHandlerInterface
     */
    public function reorient();

    /**
     * Retrieve the EXIF information from the image, if possible. Returns
     * an array of the information, or null if nothing can be found.
     *
     * @param string|null $key If specified, will only return this piece of EXIF data.
     *
     * @return mixed
     */
    public function getEXIF(?string $key = null);

    /**
     * Flip an image horizontally or vertically
     *
     * @param string $dir Direction to flip, either 'vertical' or 'horizontal'
     *
     * @return $this
     */
    public function flip(string $dir = 'vertical');

    /**
     * Combine cropping and resizing into a single command.
     *
     * Supported positions:
     *  - top-left
     *  - top
     *  - top-right
     *  - left
     *  - center
     *  - right
     *  - bottom-left
     *  - bottom
     *  - bottom-right
     *
     * @return $this
     */
    public function fit(int $width, int $height, string $position);

    /**
     * Overlays a string of text over the image.
     *
     * Valid options:
     *
     *  - color         Text Color (hex number)
     *  - shadowColor   Color of the shadow (hex number)
     *  - hAlign        Horizontal alignment: left, center, right
     *  - vAlign        Vertical alignment: top, middle, bottom
     *
     * @param array{
     *     color?: string,
     *     shadowColor?: string,
     *     hAlign?: string,
     *     vAlign?: string,
     *     hOffset?: int,
     *     vOffset?: int,
     *     fontPath?: string,
     *     fontSize?: int,
     *     shadowOffset?: int,
     *     opacity?: float,
     *     padding?: int,
     *     withShadow?: bool|string
     * } $options
     *
     * @return $this
     */
    public function text(string $text, array $options = []);

    /**
     * Saves any changes that have been made to file.
     *
     * Example:
     *    $image->resize(100, 200, true)
     *          ->save($target);
     *
     * @param non-empty-string|null $target The path to save the file to.
     *
     * @return bool
     */
    public function save(?string $target = null, int $quality = 90);
}
