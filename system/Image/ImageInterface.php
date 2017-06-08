<?php

namespace CodeIgniter\Image;

interface ImageInterface
{
    /**
     * @param int $new_width
     * @param int $new_height
     * @return resource
     */
    public function resize(int $new_width, int $new_height): resource;
    
    // ------------------------------------------------------------------
    
    /**
     * @param int $x
     * @param int $y
     * @param int $new_width
     * @param int $new_height
     * @return resource
     */
    public function crop(int $x, int $y, int $new_width, int $new_height): resource;
    
    // ------------------------------------------------------------------
    
    /**
     * @param int $angle
     * @param string $bg_color
     * @param string $transparency
     * @return resource
     */
    public function rotate(int $angle, string $bg_color = 'fff', string $transparency = 0): resource;
    
    // ------------------------------------------------------------------
    
    /**
     * @param string $mode
     * @return resource
     */
    public function flip(string $mode): resource;
    
    // ------------------------------------------------------------------
    
    /**
     * @param int $thumbWidth
     * @return resource
     */
    public function thumbnail(int $thumbWidth): resource;
    
    // ------------------------------------------------------------------
    
    /**
     * @param string $text
     * @param int $width
     * @param int $height
     * @param int $font
     * @return resource
     */
    public function watermark(string $text, int $width = 100,
            int $height = 100, int $font = 5): resource;
    
    // -----------------------------------------------------------------
    
    /**
     * @param string $final_name
     * @param string $format
     * @return resource
     */
    public function convert(string $final_name, string $format): resource;
    
    // -----------------------------------------------------------------
    
    public function resetOrientation();
    
    public function compress();
    
    public function copy();
}
