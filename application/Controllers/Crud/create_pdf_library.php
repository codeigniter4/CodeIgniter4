<?php 

$string = "<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
 
class pdf {
    
    function pdf()
    {
        \$CI = & get_instance();
        log_message('Debug', 'mPDF class is loaded.');
    }
 
    function load(\$param=NULL)
    {
        include_once APPPATH.'/third_party/mpdf/mpdf.php';
         
        if (\$params == NULL)
        {
            \$param = '\"en-GB-x\",\"A4\",\"\",\"\",10,10,10,10,6,3';         
        }
         
        return new mPDF(\$param);
    }
}";

$hasil_pdf = createFile($string, $target."libraries/pdf.php");
