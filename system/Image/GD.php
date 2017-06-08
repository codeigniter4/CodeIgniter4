<?php namespace CodeIgniter\Image;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2016, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	CodeIgniter Dev Team
 * @copyright	Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	http://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */

/**
 * Image manipulation GD API.
 *
 * @package CodeIgniter\Image
 */
class GD extends Image implements ImageInterface
{
    /**
     * Function to resize image.
     * 
     * @param resource $image
     * @param int $new_width
     * @param int $new_height
     * @return resource
     */
    public function resize(int $new_width, int $new_height): resource
    {
        imagescale($this->resource, $new_width, $new_height);
        
        return $this->resource;
    }
    
    // ------------------------------------------------------------------
    
    /**
     * {@inheritDoc}
     * @see \CodeIgniter\Image\ImageInterface::crop()
     */
    public function crop(int $x, int $y, int $new_width, int $new_height): resource
    {
        imagecrop($this->resource, ['x' => $x, 'y' => $y,
                        'width' => $new_width, 'height' => $new_height]);
        
        return $this->resource;
    }
    
    // ------------------------------------------------------------------
    
    /**
     * {@inheritDoc}
     * @see \CodeIgniter\Image\ImageInterface::rotate()
     */
    public function rotate(int $angle, string $bg_color = 'fff', string $transparency = 0): resource
    {
        imagerotate($this->resource, $angle, $bgd_color, $transparency);
        
        return $this->resource;
    }
    
    // ------------------------------------------------------------------
    
    /**
     * {@inheritDoc}
     * @see \CodeIgniter\Image\ImageInterface::flip()
     */
    public function flip(string $mode): resource
    {
        imageflip($this->resource, $mode);
        
        return $this->resource;
    }
    
    // ------------------------------------------------------------------
    
    /**
     * {@inheritDoc}
     * @see \CodeIgniter\Image\ImageInterface::thumbnail()
     */
    public function thumbnail(int $thumbWidth): resource
    {
        $width = imagesx($this->resource);
        $height = imagesy($this->resource);
        
        $new_width = $thumbWidth;
        $new_height = floor( $height * ( $thumbWidth / $width ) );
        
        $thumb = imagecreatetruecolor($new_width, $new_height);
        
        imagecopyresized($thumb, $this->resource, 0, 0, 0, 0, $new_width, $new_height,
                $width, $height);
        
        return $thumb;
    }
    
    // ------------------------------------------------------------------
    
    /**
     * {@inheritDoc}
     * @see \CodeIgniter\Image\ImageInterface::watermark()
     */
    public function watermark(string $text, int $width = 100, int $height = 50, int $font = 5): resource
    {
        $width = imagesx($this->resource) * ($width / 100);
        $height = imagesy($this->resource) * ($height / 100);
        
        $stamp = imagecreate($width, $height);
        
        imagefilltoborder($stamp, 0, 0, imagecolorallocate($this->resource, 0, 0, 0),
                imagecolorallocate($this->resource, 010, 010, 010));
        
        $x = $width / 10;
        $y = $height / 10;
        $color = imagecolorallocate($stamp, 0, 0, 255);
        imagestring($stamp, $font, $x, $y, $text, $color);
        
        imagecopymerge($this->resource, $stamp, 0, 0, 0, 0, $width, $height, 50);
        
        return $this->resource;
        
    }
    
    // ------------------------------------------------------------------
    
    /**
     * {@inheritDoc}
     * @see \CodeIgniter\Image\ImageInterface::convert()
     */
    public function convert(string $final_name, string $format): resource
    {
        switch ($format)
        {
            case 'jpg':
                imagejpeg($this->resoruce, $final_name);
            case 'png':
                imagepng($this->resource, $final_name);
        } 
    }
    
    // ------------------------------------------------------------------
    
    /**
     * {@inheritDoc}
     * @see \CodeIgniter\Image\ImageInterface::restoreOrientation()
     */
    public function resetOrientation()
    {
        
    }
    
    // ------------------------------------------------------------------
    
    /**
     * {@inheritDoc}
     * @see \CodeIgniter\Image\ImageInterface::compress()
     */
    public function compress()
    {
        
    }
    
    // ------------------------------------------------------------------
    
    /**
     * {@inheritDoc}
     * @see \CodeIgniter\Image\ImageInterface::copy()
     */
    public function copy()
    {
        
    }
}
