<?php

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
 * @package    CodeIgniter
 * @author     CodeIgniter Dev Team
 * @copyright  Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license    http://opensource.org/licenses/MIT    MIT License
 * @link       http://codeigniter.com
 * @since      Version 3.0.0
 * @filesource
 */

// --------------------------------------------------------------------

/**
 * CodeIgniter HTML Helper
 *
 * @package     CodeIgniter
 * @subpackage  Helpers
 * @category    Helpers
 * @author      CodeIgniter Dev Team
 * @link        http://codeigniter.com/user_guide/helpers/html_helper.html
 */

if ( ! function_exists('heading'))
{
    /**
     * Heading
     *
     * Generates an HTML heading tag.
     *
     * @param   string  $content
     * @param   int     $level
     * @param   string  $attributes
     * @return  string
     */
    function heading
    (
        string $content    = '', 
        int    $level      = 1, 
        string $attributes = ''
    ): string
    {
        return '<h' . $level . _stringify_attributes($attributes) . '>' 
            . $content 
            . '</h' . $level . '>';
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('ul'))
{
    /**
     * Unordered List
     *
     * Generates an HTML unordered list from an single or 
     * multi-dimensional array.
     *
     * @param   array   $list
     * @param   string  $attributes
     * @return  string
     */
    function ul(array $list, string $attributes = ''): string
    {
        return _list('ul', $list, $attributes);
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('ol'))
{
    /**
     * Ordered List
     *
     * Generates an HTML ordered list from an single or multi-dimensional array.
     *
     * @param   array   $list
     * @param   string  $attributes
     * @return  string
     */
    function ol(array $list, string $attributes = ''): string
    {
        return _list('ol', $list, $attributes);
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('_list'))
{
    /**
     * Generates the list
     *
     * Generates an HTML ordered list from an single or multi-dimensional array.
     *
     * @param   string   $type
     * @param   array    $list
     * @param   string   $attributes
     * @param   int      $depth
     * @return  string
     */
    function _list
    (
        string  $type       = 'ul', 
        array   $list       = [], 
        string  $attributes = '', 
        int     $depth      = 0
    ): string
    {
        // If an array wasn't submitted there's nothing to do...
        if ( ! is_array($list))
        {
            return $list;
        }

        // Set the indentation based on the depth
        $out = str_repeat(' ', $depth)
            // Write the opening list tag
            . '<' . $type . _stringify_attributes($attributes) . ">\n";


        // Cycle through the list elements.  If an array is
        // encountered we will recursively call _list()

        static $_last_list_item = '';
        foreach ($list as $key => $val)
        {
            $_last_list_item = $key;

            $out .= str_repeat(' ', $depth + 2) . '<li>';

            if ( ! is_array($val))
            {
                $out .= $val;
            }
            else
            {
                $out .= $_last_list_item 
                    . "\n"
                    . _list($type, $val, '', $depth + 4)
                    . str_repeat(' ', $depth + 2);
            }

            $out .= "</li>\n";
        }

        // Set the indentation for the closing tag and apply it
        return $out . str_repeat(' ', $depth) . '</' . $type . ">\n";
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('img'))
{
    /**
     * Image
     *
     * Generates an <img /> element
     *
     * @param   mixed   $src
     * @param   bool    $indexPage
     * @param   string  $attributes
     * @return  string
     */
    function img
    (
        $src                = '', 
        bool    $indexPage  = false, 
        string  $attributes = ''
    ): string
    {
        if ( ! is_array($src) )
        {
            $src = ['src' => $src];
        }

        // If there is no alt attribute defined, set it to an empty string
        if ( ! isset($src['alt']))
        {
            $src['alt'] = '';
        }

        $img = '<img';

        foreach ($src as $k => $v)
        {
            if ($k === 'src' && ! preg_match('#^([a-z]+:)?//#i', $v))
            {
                //$config = new \Config\App();
                if ($indexPage === true)
                {
                    $img .= ' src="' . get_instance()->config->site_url($v) 
                    . '"';
                }
                else
                {
                    $img .= ' src="'
                        . get_instance()->config->slash_item('base_url')
                        . $v
                        . '"';
                }
            }
            else
            {
                $img .= ' ' . $k . '="' . $v . '"';
            }
        }

        return $img . _stringify_attributes($attributes) . ' />';
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('doctype'))
{
    /**
     * Doctype
     *
     * Generates a page document type declaration
     *
     * Examples of valid options: html5, xhtml-11, xhtml-strict, xhtml-trans,
     * xhtml-frame, html4-strict, html4-trans, and html4-frame.
     * All values are saved in the doctypes config file.
     *
     * @param   mixed  $type    The doctype to be generated
     * @return  string
     */
    function doctype($type = 'xhtml1-strict'): string
    {
        $doctypes           = null;
        $env                = ENVIRONMENT;
        $doctypes           = Config\DocTypes::$list;
        $customDocTypesPath = APPPATH . "Config/{$env}/DocTypes.php";
        if (file_exists($customDocTypesPath))
        {
            $customDocTypesNs = "Config\{$env}\DocTypes";
            $doctypes         = $customDocTypesNs::$list;
        }
        return isset($doctypes[$type]) ? $doctypes[$type] : false;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('script_tag'))
{
    /**
     * Script
     *
     * Generates link to a JS file
     *
     * @param   mixed   script hrefs or an array
     * @param   string  title
     * @param   bool    should indexPage be added to the css path
     * @return  string
     */
    function script_tag
    (
        $source           = '', 
        string $title   = '', 
        bool $indexPage = false
    ): string
    {
        $CI =& get_instance();
        $script = '<script ';

        if (is_array($source))
        {
            foreach ($source as $k => $v)
            {
                if ($k === 'src' && ! preg_match('#^([a-z]+:)?//#i', $v))
                {
                    if ($indexPage === true)
                    {
                        $script .= 'src="'.$CI->config->site_url($v).'" ';
                    }
                    else
                    {
                        $script .= 'src="'.$CI->config->slash_item('base_url').$v.'" ';
                    }
                }
                else
                {
                    $script .= $k.'="'.$v.'" ';
                }
            }
        }
        else
        {
            if (preg_match('#^([a-z]+:)?//#i', $source))
            {
                $script .= 'src="'.$source.'" ';
            }
            elseif ($indexPage === true)
            {
                $script .= 'src="'.$CI->config->site_url($source).'" ';
            }
            else
            {
                $script .= 'src="'.$CI->config->slash_item('base_url').$source.'" ';
            }

            $script .= 'rel="'.$rel.'" type="'.$type.'" ';

            if ($media !== '')
            {
                $script .= 'media="'.$media.'" ';
            }

            if ($title !== '')
            {
                $script .= 'title="'.$title.'" ';
            }
        }

        return $script."></script>\n";
    }
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('link_tag'))
{
    /**
     * Link
     *
     * Generates link to a CSS file
     *
     * @param   mixed   stylesheet hrefs or an array
     * @param   string  rel
     * @param   string  type
     * @param   string  title
     * @param   string  media
     * @param   bool    should indexPage be added to the css path
     * @return  string
     */
    function link_tag
    (
        $href             = '', 
        string $rel       = 'stylesheet', 
        string $type      = 'text/css', 
        string $title     = '', 
        string $media     = '', 
        bool   $indexPage = false
    ): string
    {
        $CI =& get_instance();
        $link = '<link ';

        if (is_array($href))
        {
            foreach ($href as $k => $v)
            {
                if ($k === 'href' && ! preg_match('#^([a-z]+:)?//#i', $v))
                {
                    if ($indexPage === true)
                    {
                        $link .= 'href="' 
                            .$CI->config->site_url($v)
                            . '" ';
                    }
                    else
                    {
                        $link .= 'href="'
                            . $CI->config->slash_item('base_url')
                            . $v
                            . '" ';
                    }
                }
                else
                {
                    $link .= $k
                        . '="'
                        . $v
                        . '" ';
                }
            }
        }
        else
        {
            if (preg_match('#^([a-z]+:)?//#i', $href))
            {
                $link .= 'href="' . $href . '" ';
            }
            elseif ($indexPage === true)
            {
                $link .= 'href="' . $CI->config->site_url($href) . '" ';
            }
            else
            {
                $link .= 'href="'
                    . $CI->config->slash_item('base_url')
                    . $href
                    . '" ';
            }

            $link .= 'rel="' . $rel . '" type="' . $type . '" ';

            if ($media !== '')
            {
                $link .= 'media="' . $media . '" ';
            }

            if ($title !== '')
            {
                $link .= 'title="' . $title . '" ';
            }
        }

        return $link . "/>\n";
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('video'))
{
    /**
     * @param  array  $options            Either source or track.
     * @param  string $unsupportedMessage [<description>]
     * @param  string $attributes         [<description>]
     * @return string
     * 
     * Example:
     * video
     *   (
     *      [
     *            source('movie.mp4', 'video/mp4'),
     *            source('movie.ogg', 'video/ogg')
     *        ],
     *        'Your browser does not support the video tag.'
     *    );
     */
    function video
    (
        array  $options, 
        string $unsupportedMessage = '',
        string $attributes         = ''
    ): string
    {
        return _media('video', $options, $unsupportedMessage, $attributes);
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('audio'))
{
    /**
     * @param  array  $options            Either source or track.
     * @param  string $unsupportedMessage [<description>]
     * @param  string $attributes         [<description>]
     * @return string
     *
     * Example:
     * audio
     * (
     *      [
     *          source('sound.ogg', 'audio/ogg'),
     *          source('sound.mpeg', 'audio/mpeg')
     *      ],
     *      'Your browser does not support the audio tag.'
     * );
     */
    function audio
    (
        array  $options, 
        string $unsupportedMessage = '',
        string $attributes         = ''
    ): string
    {
        return _media('audio', $options, $unsupportedMessage, $attributes);
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('_media'))
{
    /**
     * 
     */
    function _media
    (
        string $name,
        array  $options, 
        string $unsupportedMessage = '',
        string $attributes         = ''
    ): string
    {
        $media = '<' . $name;

        if(!empty($attributes))
        {
            $media .= " $attributes";
        }

        foreach($options as $option)
        {
            $media .= $option;
            if(! empty($unsupportedMessage))
            {
                $media .= $unsupportedMessage;
            }
        }
        $media .= '></' . $name . '>';

        return $media;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('source'))
{
    /**
     * 
     */
    function source(string $name, string $mimeType): string
    {
        return '<source src="'. $name . '" type="' . $mimeType . '" />';
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('object'))
{
    /**
     * 
     */
    function track
    (
        string $source, 
        string $kind, 
        string $sourceLanguage, 
        string $label
    ): string
    {
        return '<track src="' . $source
            . '" kind="'      . $kind
            . '" srclang="'   . $sourceLanguage 
            . '" label="'     . $label
            . '" />';
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('object'))
{
    function object(string $data): string
    {
        return '<object data="' . $data . '"></object>';
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('embed'))
{
    function embed(string $source): string
    {
        return '<embed src="' . $source . '" />';
    }
}

//http://www.w3schools.com/html/html_media.asp
//http://www.w3schools.com/tags/ref_av_dom.asp