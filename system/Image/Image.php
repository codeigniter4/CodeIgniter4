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
 * Image manipulation factory.
 *
 * Creates and returns an instance of the appropriate image manipulation processor.
 *
 * @package CodeIgniter\Image
 */
class Image
{
    protected $driver;
    protected $resource;
    protected $imageFullPath;
    protected $imageFolder;
    protected $imageFilename;
    protected $imageExtension;  
    protected $mime;
    
    protected $imageTypes = [
            'jpg',
            'jpeg',
            'png',
            'gif',
            'tiff'
    ];
    
    // ------------------------------------------------------------------
    
    /**
     * @param string $driver
     * @return resource
     */
    public function load(string $driver): resource
    {
        $classname = 'Codeigniter\Image\\'.$driver;
        return new $classname;
    }
    
    // ------------------------------------------------------------------
    
    /**
     * @param string $folder
     * @return resource
     */
    public function setFolder(string $folder): resource
    {
        $this->imageFolder = $folder;
        return $this;
    }
    
    // ------------------------------------------------------------------
    
    /**
     * @param string $filename
     * @return resource
     */
    public function setFilename(string $filename): resource
    {
        $this->imageFilename = $filename;
        return $this;
    }
    
    // ------------------------------------------------------------------
    
    /**
     * @param string $extension
     * @throws \InvalidArgumentException
     * @return resource
     */
    public function setExtension(string $extension): resource
    {
        if (in_array($extension, $this->imageTypes))
        {
            $this->imageExtension = strtolower($extension);
            return $this;
        }
        else
        {
            throw new \InvalidArgumentException('Image extension is not allowed');
        }
    }
    
    // ------------------------------------------------------------------
    
    /**
     * @param string $full_image_path
     * @throws \InvalidArgumentException
     * @return resource
     */
    public function image(string $full_image_path = false, $driver): resource
    {
        if ($full_image_path)
        {
            $route = pathinfo($full_image_path);
            $this->imageFilename = $route['filename'];
            $this->imageFolder = $route['dirname'];
            $extension = $rute['extension'];
            
            if (in_array($extension, $this->imageTypes))
            {
                $this->imageExtension = strtolower($extension);
            }
            else
            {
                throw new \InvalidArgumentException('Image extension is not allowed');
            }
        }
        
        $this->imageFullPath = BASEPATH.$this->imageFolder.'/'.$this->imageFilename.'.'.
                $this->imageExtension;
        
        $this->mime = "image/$this->imageExtension";
        
        if(file_exists($this->imageFullPath))
        {
            header('content_type :'.$this->mime);
            $this->resource = readfile($this->imageFullPath);
        }
        else 
        {
            throw new \InvalidArgumentException('No image found in specified location');
        }
        
        return $this->load($driver);
    }
}
