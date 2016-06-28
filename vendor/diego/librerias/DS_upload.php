<?php

namespace Diego\Librerias;

class DS_upload extends DS_base
{
    public function __construct()
    {
    	parent::__construct();
    }
	
	public function form($params = '')
    {
        $conf = [
        	'controlador' => $params['controlador'],
        	'titulo' => 'Subir un archivo',
        	'funcion' => 'cargar',
        	'campos' => [
        		['input', 'texto', 'titulo_archivo', 'e' => 'Título del archivo', 'p' => '5'],
        		['file', 'texto', '20', 'c' => '5'],
        	],
        ];
        
        return gen_formulario_bs($conf);
    }
    
    public function subir_documento($params = '')
    {
        $config['upload_path']          = APPPATH.$params['directorio'].'/';
        $config['allowed_types']        = 'gif|jpg|png|pdf|txt|doc|xps';
        $config['max_size']             = 0;
        $config['max_width']            = 0;
        $config['max_height']           = 0;
        $config['file_name']            = $params['file_name'];
        
        $this->CI->load->library('upload', $config);
        
        if (! $this->CI->upload->do_upload('userfile'))
        {
            $config['error'] = $this->CI->upload->display_errors();
        }
        return $config;
    }
    
    public function subir_imagen($params = '')
    {
    	$config['upload_path']          = APPPATH.$params['directorio'].'/';
    	$config['allowed_types']        = 'gif|jpg|png|pdf|txt|jpeg|png|tif';
    	$config['max_size']             = 0;
    	$config['max_width']            = 0;
    	$config['max_height']           = 0;
    	$config['file_name']            = $params['file_name'];
    
    	$this->CI->load->library('upload', $config);
    
    	if (! $this->CI->upload->do_upload('userfile'))
    	{
    		$config['error'] = $this->CI->upload->display_errors();
    	}
    	return $config;
    }
    


    public function abre_documento($data)
    {
    	$file = APPPATH.'documentos/'.$data;
    	$finfo = finfo_open(FILEINFO_MIME_TYPE);
    		
    	header('Content-type: '.finfo_file($finfo, $file));
    	header('Content-Transfer-Encoding: binary');
    	header('Content-Length: ' . filesize($file));
    	header('Accept-Ranges: bytes');
    		
    	readfile($file);
    }
    
    public function abre_imagen($data)
    {
    	$file = APPPATH.'imagenes/'.$data;
    	$finfo = finfo_open(FILEINFO_MIME_TYPE);
    		
    	header('Content-type: '.finfo_file($finfo, $file));
    	header('Content-Transfer-Encoding: binary');
    	header('Content-Length: ' . filesize($file));
    	header('Accept-Ranges: bytes');
    		
    	readfile($file);
    }
}