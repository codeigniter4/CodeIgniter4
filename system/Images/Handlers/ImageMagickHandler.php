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

use CodeIgniter\I18n\Time;
use CodeIgniter\Images\Exceptions\ImageException;
use Config\Images;
use Exception;
use Imagick;

/**
 * Class ImageMagickHandler
 *
 * FIXME - This needs conversion & unit testing, to use the imagick extension
 */
class ImageMagickHandler extends BaseHandler
{
    /**
     * Stores image resource in memory.
     *
     * @var string|null
     */
    protected $resource;

    /**
     * @param Images $config
     *
     * @throws ImageException
     */
    public function __construct($config = null)
    {
        parent::__construct($config);

        if (! (extension_loaded('imagick') || class_exists(Imagick::class))) {
            throw ImageException::forMissingExtension('IMAGICK'); // @codeCoverageIgnore
        }

        $cmd = $this->config->libraryPath;

        if ($cmd === '') {
            throw ImageException::forInvalidImageLibraryPath($cmd);
        }

        if (preg_match('/convert$/i', $cmd) !== 1) {
            $cmd = rtrim($cmd, '\/') . '/convert';

            $this->config->libraryPath = $cmd;
        }

        if (! is_file($cmd)) {
            throw ImageException::forInvalidImageLibraryPath($cmd);
        }
    }

    /**
     * Handles the actual resizing of the image.
     *
     * @return ImageMagickHandler
     *
     * @throws Exception
     */
    public function _resize(bool $maintainRatio = false)
    {
        $source      = ! empty($this->resource) ? $this->resource : $this->image()->getPathname();
        $destination = $this->getResourcePath();

        $escape = '\\';

        if (PHP_OS_FAMILY === 'Windows') {
            $escape = '';
        }

        $action = $maintainRatio === true
            ? ' -resize ' . ($this->width ?? 0) . 'x' . ($this->height ?? 0) . ' "' . $source . '" "' . $destination . '"'
            : ' -resize ' . ($this->width ?? 0) . 'x' . ($this->height ?? 0) . "{$escape}! \"" . $source . '" "' . $destination . '"';

        $this->process($action);

        return $this;
    }

    /**
     * Crops the image.
     *
     * @return bool|ImageMagickHandler
     *
     * @throws Exception
     */
    public function _crop()
    {
        $source      = ! empty($this->resource) ? $this->resource : $this->image()->getPathname();
        $destination = $this->getResourcePath();

        $extent = ' ';
        if ($this->xAxis >= $this->width || $this->yAxis > $this->height) {
            $extent = ' -background transparent -extent ' . ($this->width ?? 0) . 'x' . ($this->height ?? 0) . ' ';
        }

        $action = ' -crop ' . ($this->width ?? 0) . 'x' . ($this->height ?? 0) . '+' . ($this->xAxis ?? 0) . '+' . ($this->yAxis ?? 0) . $extent . escapeshellarg($source) . ' ' . escapeshellarg($destination);

        $this->process($action);

        return $this;
    }

    /**
     * Handles the rotation of an image resource.
     * Doesn't save the image, but replaces the current resource.
     *
     * @return $this
     *
     * @throws Exception
     */
    protected function _rotate(int $angle)
    {
        $angle = '-rotate ' . $angle;

        $source      = ! empty($this->resource) ? $this->resource : $this->image()->getPathname();
        $destination = $this->getResourcePath();

        $action = ' ' . $angle . ' ' . escapeshellarg($source) . ' ' . escapeshellarg($destination);

        $this->process($action);

        return $this;
    }

    /**
     * Flattens transparencies, default white background
     *
     * @return $this
     *
     * @throws Exception
     */
    protected function _flatten(int $red = 255, int $green = 255, int $blue = 255)
    {
        $flatten = "-background 'rgb({$red},{$green},{$blue})' -flatten";

        $source      = ! empty($this->resource) ? $this->resource : $this->image()->getPathname();
        $destination = $this->getResourcePath();

        $action = ' ' . $flatten . ' ' . escapeshellarg($source) . ' ' . escapeshellarg($destination);

        $this->process($action);

        return $this;
    }

    /**
     * Flips an image along it's vertical or horizontal axis.
     *
     * @return $this
     *
     * @throws Exception
     */
    protected function _flip(string $direction)
    {
        $angle = $direction === 'horizontal' ? '-flop' : '-flip';

        $source      = ! empty($this->resource) ? $this->resource : $this->image()->getPathname();
        $destination = $this->getResourcePath();

        $action = ' ' . $angle . ' ' . escapeshellarg($source) . ' ' . escapeshellarg($destination);

        $this->process($action);

        return $this;
    }

    /**
     * Get driver version
     */
    public function getVersion(): string
    {
        $versionString = $this->process('-version')[0];
        preg_match('/ImageMagick\s(?P<version>[\S]+)/', $versionString, $matches);

        return $matches['version'];
    }

    /**
     * Handles all of the grunt work of resizing, etc.
     *
     * @return array Lines of output from shell command
     *
     * @throws Exception
     */
    protected function process(string $action, int $quality = 100): array
    {
        if ($action !== '-version') {
            $this->supportedFormatCheck();
        }

        $cmd = $this->config->libraryPath;
        $cmd .= $action === '-version' ? ' ' . $action : ' -quality ' . $quality . ' ' . $action;

        $retval = 1;
        $output = [];
        // exec() might be disabled
        if (function_usable('exec')) {
            @exec($cmd, $output, $retval);
        }

        // Did it work?
        if ($retval > 0) {
            throw ImageException::forImageProcessFailed();
        }

        return $output;
    }

    /**
     * Saves any changes that have been made to file. If no new filename is
     * provided, the existing image is overwritten, otherwise a copy of the
     * file is made at $target.
     *
     * Example:
     *    $image->resize(100, 200, true)
     *          ->save();
     *
     * @param non-empty-string|null $target
     */
    public function save(?string $target = null, int $quality = 90): bool
    {
        $original = $target;
        $target   = ($target === null || $target === '') ? $this->image()->getPathname() : $target;

        // If no new resource has been created, then we're
        // simply copy the existing one.
        if (empty($this->resource) && $quality === 100) {
            if ($original === null) {
                return true;
            }

            $name = basename($target);
            $path = pathinfo($target, PATHINFO_DIRNAME);

            return $this->image()->copy($path, $name);
        }

        $this->ensureResource();

        // Copy the file through ImageMagick so that it has
        // a chance to convert file format.
        $action = escapeshellarg($this->resource) . ' ' . escapeshellarg($target);

        $this->process($action, $quality);

        unlink($this->resource);

        return true;
    }

    /**
     * Get Image Resource
     *
     * This simply creates an image resource handle
     * based on the type of image being processed.
     * Since ImageMagick is used on the cli, we need to
     * ensure we have a temporary file on the server
     * that we can use.
     *
     * To ensure we can use all features, like transparency,
     * during the process, we'll use a PNG as the temp file type.
     *
     * @return string
     *
     * @throws Exception
     */
    protected function getResourcePath()
    {
        if ($this->resource !== null) {
            return $this->resource;
        }

        $this->resource = WRITEPATH . 'cache/' . Time::now()->getTimestamp() . '_' . bin2hex(random_bytes(10)) . '.png';

        $name = basename($this->resource);
        $path = pathinfo($this->resource, PATHINFO_DIRNAME);

        $this->image()->copy($path, $name);

        return $this->resource;
    }

    /**
     * Make the image resource object if needed
     *
     * @return void
     *
     * @throws Exception
     */
    protected function ensureResource()
    {
        $this->getResourcePath();

        $this->supportedFormatCheck();
    }

    /**
     * Check if given image format is supported
     *
     * @return void
     *
     * @throws ImageException
     */
    protected function supportedFormatCheck()
    {
        switch ($this->image()->imageType) {
            case IMAGETYPE_WEBP:
                if (! in_array('WEBP', Imagick::queryFormats(), true)) {
                    throw ImageException::forInvalidImageCreate(lang('images.webpNotSupported'));
                }
                break;
        }
    }

    /**
     * Handler-specific method for overlaying text on an image.
     *
     * @return void
     *
     * @throws Exception
     */
    protected function _text(string $text, array $options = [])
    {
        $xAxis   = 0;
        $yAxis   = 0;
        $gravity = '';
        $cmd     = '';

        // Reverse the vertical offset
        // When the image is positioned at the bottom
        // we don't want the vertical offset to push it
        // further down. We want the reverse, so we'll
        // invert the offset. Note: The horizontal
        // offset flips itself automatically
        if ($options['vAlign'] === 'bottom') {
            $options['vOffset'] *= -1;
        }

        if ($options['hAlign'] === 'right') {
            $options['hOffset'] *= -1;
        }

        // Font
        if (! empty($options['fontPath'])) {
            $cmd .= " -font '{$options['fontPath']}'";
        }

        if (isset($options['hAlign'], $options['vAlign'])) {
            switch ($options['hAlign']) {
                case 'left':
                    $xAxis   = $options['hOffset'] + $options['padding'];
                    $yAxis   = $options['vOffset'] + $options['padding'];
                    $gravity = $options['vAlign'] === 'top' ? 'NorthWest' : 'West';
                    if ($options['vAlign'] === 'bottom') {
                        $gravity = 'SouthWest';
                        $yAxis   = $options['vOffset'] - $options['padding'];
                    }
                    break;

                case 'center':
                    $xAxis   = $options['hOffset'] + $options['padding'];
                    $yAxis   = $options['vOffset'] + $options['padding'];
                    $gravity = $options['vAlign'] === 'top' ? 'North' : 'Center';
                    if ($options['vAlign'] === 'bottom') {
                        $yAxis   = $options['vOffset'] - $options['padding'];
                        $gravity = 'South';
                    }
                    break;

                case 'right':
                    $xAxis   = $options['hOffset'] - $options['padding'];
                    $yAxis   = $options['vOffset'] + $options['padding'];
                    $gravity = $options['vAlign'] === 'top' ? 'NorthEast' : 'East';
                    if ($options['vAlign'] === 'bottom') {
                        $gravity = 'SouthEast';
                        $yAxis   = $options['vOffset'] - $options['padding'];
                    }
                    break;
            }

            $xAxis = $xAxis >= 0 ? '+' . $xAxis : $xAxis;
            $yAxis = $yAxis >= 0 ? '+' . $yAxis : $yAxis;

            $cmd .= " -gravity {$gravity} -geometry {$xAxis}{$yAxis}";
        }

        // Color
        if (isset($options['color'])) {
            [$r, $g, $b] = sscanf("#{$options['color']}", '#%02x%02x%02x');

            $cmd .= " -fill 'rgba({$r},{$g},{$b},{$options['opacity']})'";
        }

        // Font Size - use points....
        if (isset($options['fontSize'])) {
            $cmd .= " -pointsize {$options['fontSize']}";
        }

        // Text
        $cmd .= " -annotate 0 '{$text}'";

        $source      = ! empty($this->resource) ? $this->resource : $this->image()->getPathname();
        $destination = $this->getResourcePath();

        $cmd = " '{$source}' {$cmd} '{$destination}'";

        $this->process($cmd);
    }

    /**
     * Return the width of an image.
     *
     * @return int
     */
    public function _getWidth()
    {
        return imagesx(imagecreatefromstring(file_get_contents($this->resource)));
    }

    /**
     * Return the height of an image.
     *
     * @return int
     */
    public function _getHeight()
    {
        return imagesy(imagecreatefromstring(file_get_contents($this->resource)));
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
}
