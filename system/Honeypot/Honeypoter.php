<?php namespace CodeIgniter\Honeypot;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2018 British Columbia Institute of Technology
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
 * @copyright	2014-2018 British Columbia Institute of Technology (https://bcit.ca/)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Honeypot;

class Honeypoter 
{

    /**
	 * Honeypot Template
	 * @var String
	 */
    protected $template;

    /**
	 * Honeypot text field name
	 * @var String
	 */
    protected $name;

    /**
	 * Honeypot lable content
	 * @var String
	 */
    protected $label;

    /**
	 * Self Instance of Class
	 * @var Honeypoter
	 */
    static protected $selfObject = null;

    //--------------------------------------------------------------------

    function __construct () {
        $honeypotConfig = new Honeypot();
        $this->template = ($honeypotConfig->template === '') ? 
            $this->getDefaultTemplate(): $honeypotConfig->template;

        $this->name = ($honeypotConfig->name === '') ? 
            $this->getDefaultName(): $honeypotConfig->name;

        $this->label = ($honeypotConfig->label === '') ? 
            $this->getDefaultLabel(): $honeypotConfig->label;

    }

    //--------------------------------------------------------------------

    /**
	 * Checks the request if honeypot field has data.
	 *
	 * @param \CodeIgniter\HTTP\RequestInterface $request
	 * 
	 */
    static function honeypotHasContent(RequestInterface $request) 
    {
        self::$selfObject = (self::$selfObject === null) ? 
            new self(): self::$selfObject;
        
        // TODO Will there be need to protect against bad data?
        if($request->getVar(self::$selfObject->name)){
            
            return true;
        }

        if($request->getGet(self::$selfObject->name)){
            
            return true;
        }

        if($request->getPost(self::$selfObject->name)){
            return true;
            
        }
        return false;
    }

    /**
	 * Attachs Honeypot template to response.
	 *
	 * @param \CodeIgniter\HTTP\RequestInterface $request
	 * @param \CodeIgniter\HTTP\ResponseInterface $response
	 */
    static function attachHoneypot(ResponseInterface $response)
    {
        self::$selfObject = (self::$selfObject === null) ? 
            new self(): self::$selfObject;

        $prep_field = self::$selfObject->prepareTemplate(self::$selfObject->template);
        $style = self::$selfObject->getStyle();    
        
        $body = $response->getBody();
        $body = preg_replace('/<\/form>/', $prep_field, $body);
        $body = preg_replace('/<\/head>/', $style, $body);
        $response->setBody($body);
    }

    /**
	 * Prepares the template by adding label
     * content and field name.
	 *
	 * @param string $template
	 * @return string
	 */
    protected function prepareTemplate($template): string
    {
        $template = preg_replace('/{label}/', $this->label, $template);
        $template = preg_replace('/{name}/', $this->name, $template);
        return $template;
    }

    /**
	 * Returns the default template which 
     * is used when the user enters none.
	 *
	 * @return string
	 */
    protected function getDefaultTemplate(): string
    {
        return '<div class="hidden" style="display:none">
                    <label>{label}</label>
                    <input type="text" name="{name}" value=""/>
                </div>
            </form>';
    }

    /**
	 * Returns the css style to hide the 
     * Honeypot template.
	 *
	 * @return string
	 */
    protected function getStyle(): string
    {
        return '<script type="text/css" media="all">
                    .hidden { display:none;}
                </script>
            </head>';
    }

    /**
	 * Returns default label for the template
     * which is used when user enters none
	 *
	 * @return string
	 */
    protected function getDefaultLabel(): string
    {
        return 'Fill This Field';
    }

    /**
	 * Returns default field name for the template
     * which is used when user enters none
	 *
	 * @return string
	 */
    protected function getDefaultName(): string
    {   
        return 'honeypot';
    }

    
}