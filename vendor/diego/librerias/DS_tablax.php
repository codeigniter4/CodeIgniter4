<?php

namespace Diego\Librerias;

class DS_tablax extends DS_base
{
	public function __construct()
	{
		parent::__construct();
	}
	
	// *************************************
	// Variables que se pasan por parámetro.
	// *************************************
    protected $base_datos = 'default';
    // Tabla desde dónde se recuperan los datos.
    protected $db_tabla = '';
    // Array de campos de la tabla que servirán para generar la tabla html.
    protected $db_campos = [];
    // Datos pasados a la clase en lugar de la variable anterior, "db_tabla".
    protected $db_data = '';
    // Datos pasados con sentencias directas.
    protected $db_datos = '';
    // Límite de registros a recuperar por página.
    protected $db_limit = '30';
    // Campo clave. Se utiliza en los reportes.
    protected $db_clave = '';
    // Array de encabezados de la tabla.
    protected $encabezados = FALSE;
    // Título de la tabla para mostrar.
    protected $titulo_tabla = '';
    // Controlador al que se se dirigen los links.
	protected $controlador = '';
	// Nombre del link opcional para insertar un nuevo registro.
    protected $insertar = FALSE;
    // Parámetros del formulario de búsqueda.
	protected $busca_form = FALSE;
	protected $busca_titulo = FALSE;
	// Array de campos para añadir una fila de insert a la tabla.
	protected $insert = FALSE;
	// ID de la tabla que se va a generar.
	protected $div_id = FALSE;
	// Indica si un campo debe ser sumatoria final.
	protected $suma = FALSE;
	// Indica qué campo agrupa los reportes.
	protected $campo_grupo = '';
	// Indica el número de filas cuando se trata de una galería.
	protected $filas_galeria = FALSE;
	// Opción para mostrar el número de fila
	protected $n_fila = 'si';
	// Total de filas. El valor por defecto es "si", entonces se calcula.
	protected $total_filas = 'si';
	// Opción para establecer el ancho de la tabla.
	protected $ancho = '';
	// Opción para generar una tabla de varias columnas.
	protected $columnas = FALSE;
	// Opción para la clase de tabla que se va a generar.
	protected $clase = 'table table-condensed';
	
	// Variables recuperadas de la sesión y de la petición ajax.
	protected $db_where = [];
	protected $db_like = [];
	protected $db_busca = '';
	protected $page = 1;
	protected $db_sort_by = '';
	protected $db_sort_order = FALSE;
	
	// Variables calculadas por la clase.
	protected $fields;
	protected $tabla;
	protected $total = FALSE;
	
	// Función que genera el contenido completo de la sección y recupera datos GET y POST.
	protected function inicia($params)
	{
	    // Asignamos los valores de los parámetros de configuración a las variables privadas.
	    
		foreach ($params as $key => $val)
		{
			if (isset($this->$key)) $this->$key = $val;
		}
		
		// Asignamos el id del div que contiene toda la tabla html.
		
		if ($this->div_id == '') $this->div_id = str_replace('/', '_', $this->controlador);
		else str_replace('/', '_', $this->div_id);

 		// Si se ha enviado el formulario de búsqueda, guardamos los parámetros de búsqueda
		// en la variable de sesión $_SESSION[$this->div_id.'_busca'].
		
		if ($this->CI->input->post('buscar'))
		{
			$_SESSION[$this->div_id.'_busca'] = $this->CI->input->post();
			unset($_SESSION[$this->div_id.'_sorter'], $_SESSION[$this->div_id.'_order'], $_SESSION[$this->div_id.'_page']);
		}
		
		// Si se pasan la página, el campo de ordenación y el orden, se guardan en variables de sesión.
		
		if ($this->CI->input->get())
		{
			if (isset($_GET['page'])) $_SESSION[$this->div_id.'_page'] = $_GET['page'];
			if (isset($_GET['sorter'])) $_SESSION[$this->div_id.'_sorter'] = $_GET['sorter'];
			if (isset($_GET['order'])) $_SESSION[$this->div_id.'_order'] = $_GET['order'];
		}

 		// Si existen las variables de sesion, se asignan los valores a las variables internas.
		
		if (isset($_SESSION[$this->div_id.'_busca'])) $this->db_busca = $_SESSION[$this->div_id.'_busca'];
 		if (isset($_SESSION[$this->div_id.'_page'])) $this->page = $_SESSION[$this->div_id.'_page'];
 		if (isset($_SESSION[$this->div_id.'_sorter'])) $this->db_sort_by = $_SESSION[$this->div_id.'_sorter'];
 		if (isset($_SESSION[$this->div_id.'_order'])) $this->db_sort_order = $_SESSION[$this->div_id.'_order'];
		
		// Se invoca la función que introduce la consulta con los campos de búsqueda.
 		
 		$this->campos_busca();
		
		// Se asignan los valores de configuración para generar la tabla.
		
		$this->tabla = [
				'data' => $this->get_campos_db(),
				'campos' => $this->db_campos,
				'encabezados' => $this->encabezados(),
				'limit' => $this->db_limit,
		        'total_filas' => $this->total_filas,
		        'page' => $this->page ? ($this->db_limit * ($this->page - 1)) + 1 : FALSE,
		        'controlador' => $this->controlador,
				'campo_grupo' => $this->campo_grupo,
				'filas_galeria' => $this->filas_galeria,
				'n_fila' => $this->n_fila,
				'ancho' => $this->ancho,
				'columnas' => $this->columnas,
				'clase' => $this->clase,
		];
		if ($this->insert) $this->tabla['insert'] = $this->insert;
 		
 		// Si la propiedad suma es "si", se genera la etiqueta para el total al final de la tabla.
 		if ($this->suma !== FALSE)
 		{
 			$this->suma = 	crea_pre('div', ['align' => 'right']).
 			crea_pre('b').'Total: '.number_format($this->total, 2, ',', '.').
 			crea_pos('b,div');
 		}
	}

	// Genera una tabla HTML con todos los elementos para mostrar.
	
	public function form($params)
	{
		// Se invoca la función que configura la clase.
		
		$this->inicia($params);
		
		// Si existe el parámetro "insertar" que es un array con el nombre del anchor y el
		// URL al que se dirige, se crea el elemento HTML.
		
		if ($this->insertar !== FALSE)
		{
			if (! is_array($this->insertar))
			{
				$campos = explode(',', $this->insertar);
				if (count($campos) > 1)
				{
					$this->insertar = campo_boton(['anchor' => $this->controlador.'/'.$campos[0], 'nombre'=> $campos[1]]);
				}
				else 
				{
					$this->insertar = campo_boton(['anchor' => $this->controlador.'/insert', 'nombre' => $this->insertar]);
				}
			}
			elseif (is_array($this->insertar))
			{
				$this->insertar = campo_boton($this->insertar);
			}
		}
		
		// Generador del contenido completo de la tabla html.
		
		if ($this->busca_titulo !== FALSE)
		{
			$out = crea_titulo_tabla($this->titulo_tabla, $this->insertar);
			//$out = $this->titulo_tabla ? crea_titulo($this->titulo_tabla, 3) : '';
			//$out .= $this->insertar !== FALSE ? crea_pre('div', ['align' => 'right']).$anchor.crea_pos('div') : '';
			$out .= crea_pre('ul', ['class' => 'nav nav-tabs']).
					crea_pre('li', ['class' => 'active']).
					crea_pre('a', ['data-toggle' => 'tab', 'href' => '#tabs-1']).$this->titulo_tabla.
					crea_pos('a,li').
					crea_pre('li').
					crea_pre('a', ['data-toggle' => 'tab', 'href' => '#tabs-2']).$this->busca_titulo.
					crea_pos('a,li,ul').
					crea_pre('div', ['class' => 'tab-content']).
					crea_pre('div', ['id' => 'tabs-1', 'class' => 'tab-pane fade in active']);
			if (is_int($this->total_filas)) $out .= 'Registos: '.$this->total_filas;
			$out .= crea_pre('div', ['id' => $this->div_id]);
			$out .= gen_tabla($this->tabla).$this->suma;
			$out .= $this->pagina().crea_pos('div,div').crea_pre('div', ['id' => 'tabs-2', 'class' => 'tab-pane fade']);
			$out .= gen_form_busca($this->busca_form).crea_pos('div,div');
		}
		else
		{
			$out = crea_titulo_tabla($this->titulo_tabla, $this->insertar);
			//$out = $this->titulo_tabla ? crea_titulo($this->titulo_tabla, 3) : '';
			//$out .= $this->insertar !== FALSE ? crea_pre('div', ['align' => 'right']).$anchor.crea_pos('div') : '';
			if (is_int($this->total_filas)) $out .= 'Registos: '.$this->total_filas;
			$out .= crea_pre('div', ['id' => $this->div_id]);
			$out .= gen_tabla($this->tabla).$this->suma;
			$out .= $this->pagina().crea_pos('div');
		}
		return $out;
	}
	
	public function form_ajax($params)   
	{
	   $this->inicia($params);
	   $out = gen_tabla($this->tabla).$this->suma;
	   $out .= $this->pagina().crea_pos('div');
	   return $out;
	}
	
	// Configuramos y establecemos los parámetros de búsqueda.
	protected function campos_busca()
	{
		// Si está definida la variable db_busca, seguimos la rutina.
		if ($this->db_busca != '')
		{	
			foreach ($this->db_busca as $k => $v)
			{
				foreach ($this->busca_form['campos'] as $campo)
				{
					// El campo busca determina si pasamos un parámetro como "like" o ">".
					$busca = isset($campo['busca']) ? $campo['busca'] : '';
					
					// El campo "n" sirve para dar a los campos busca nombres distintos a las variables.
					if (isset($campo['n']) && $campo['n'] == $k && $v != '')
					{
						if ($busca != '' && $busca == 'like') $this->CI->db->like($campo[2], $v);
						if ($busca != '' && $busca != 'like') $this->CI->db->where("$campo[2] $busca", $v);
						if ($busca == '') $this->CI->db->where($campo[2], $v);
					}
					elseif ($campo[2] == $k && $v != '')
					{
						if ($busca != '' && $busca == 'like') $this->CI->db->like($campo[2], $v);
						if ($busca != '' && $busca != 'like') $this->CI->db->where("$campo[2] $busca", $v);
						if ($busca == '') $this->CI->db->where($campo[2], $v);
					}
				}
			}
		}
	}
	
	// Preparamos los encabezados de la tabla.
	protected function encabezados()
	{
		if ($this->encabezados)
		{
			$en = [];
			if ($this->n_fila == 'si') $en[] = ' ';
			foreach ($this->encabezados as $enc)
			{
				if (count($enc) == 2)
				{
				    $sort = $this->db_sort_order == 'asc' && $this->db_sort_by == $enc[1] ? 'desc' : 'asc';
				    $id = 'sorter_'.rand(0, 200);
				    $attr = [
				            'id' => $id,
				            'div_id' => $this->div_id,
				            'class' => 'btn-link',
				            'url' => base_url($this->controlador."/ajax_sort"),
				            'onClick' => "sortea_tablax('$id')",
				            'sorter' => $enc[1],
				            'order' => $sort,
				            'content' => $enc[0],
				    ];
				    $en[] .= form_button($attr)."\n";
				}
				else
				{
					$en[] = ucfirst($enc[0]);
				}
			}
			return $en;
		}
	}
	
// Recuperamos de la DB los campos para rellenar la tabla.
	protected function prep_campos_db()
	{
		$rl = [];
		$fields = [];
		foreach ($this->db_campos as $camp)
		{
			// Comprobamos si los campos existen en la tabla.
			$kf = $camp[2];
			$this->CI->db->field_exists($kf, $this->db_tabla) ? $fields[] = $kf : '';
			
			// Comprobamos si existe el parámetro 'h' que tiene los valores a pasar en un link
			// en forma de array nombre => valor.
			if (isset($camp['h']))
			{
				if (is_array($camp['h']))
				{
					foreach ($camp['h'] as $k => $v)
					{	
						$this->CI->db->field_exists($v, $this->db_tabla) &&
						! in_array($v, $fields) ?
						$fields[] = $v : '';
					}
				}
				else 
				{
					$this->CI->db->field_exists($camp['h'], $this->db_tabla) &&
					! in_array($camp['h'], $fields) ?
					$fields[] = $camp['h'] : '';
				}
			}
			
			if (isset($camp['n']))
			{
				$this->CI->db->field_exists($camp['n'], $this->db_tabla) &&
					! in_array($camp['n'], $fields) ?
					$fields[] = $camp['n'] : '';
			}
			
			// Comprobamos si existe un parámetro 'o' que tiene los valores para ocultar el campo
			// en forma valor, valor.
			if (isset($camp['o']))
			{
			    foreach ($camp['o'] as $k => $v)
			    {
			        $this->CI->db->field_exists($k, $this->db_tabla) &&
			        ! in_array($k, $fields) ?
			        $fields[] = $k : '';
			
			        $this->CI->db->field_exists($v, $this->db_tabla) &&
			        ! in_array($v, $fields) ?
			        $fields[] = $v : '';
			    }
			}
		}
		if ($this->db_clave !== '')
		{
			if ($this->CI->db->field_exists($this->db_clave, $this->db_tabla) &&
			! in_array($this->db_clave, $fields)) $fields[] = $this->db_clave;
		}
		$this->fields = $fields;
	}
		
	/***********************************************************************************
	 * Función GET_CAMPOS_DB
	 * Recupera los valores de la base de datos para pintar la tabla
	 * 
	 * *********************************************************************************
	 */
	protected function get_campos_db()
	{
		// Si el parámetro db_data está en blanco es porque pasamos el nombre de la tabla.
		if ($this->db_tabla != '')
        {
            $this->prep_campos_db();
            $this->CI->db->select($this->fields);
            $this->CI->db->from($this->db_tabla);
        }
        
        // Si el parámetro db_data no está en blanco es porque pasamos el nombre de un modelo
        // con la función que devuelve una vista, de dónde se van a extraer los datos.
        if ($this->db_data != '')
        {
            $modelo = $this->db_data[0];
            $func = $this->db_data[1];
            $this->CI->load->model($modelo);
            $this->CI->$modelo->$func();
        }
        
        // Si se pasan los datos directamente.
        if ($this->db_datos != '')
        {
        	$this->db_datos;
        }
        
        if (is_array($this->db_where))
        {
	        foreach ($this->db_where as $val)
	        {
	        	$this->CI->db->where($val);
	        }
        }
        else 
        {
        	$this->CI->db->where($this->db_where);
        }
        
        $sql = $this->CI->db->get_compiled_select('', FALSE);
        $result = $this->CI->db->query($sql)->result_array();
	    
        if ($this->total_filas == 'si')
        {
            if ($this->db_clave !== '')
            {
            	$conteo = array_column($result, $this->db_clave);
            	$unicos = [];
            	foreach ($conteo as $item)
            	{
            		if (! in_array($item, $unicos))
            		{
            			$unicos[] = $item;
            		}
            	}
            	$this->total_filas = count($unicos);
            	
            }
            else
            {
            	$this->total_filas = count($result);
            }
        }
	    
	    if ($this->suma !== FALSE)
	    {
	    	if (is_array($this->suma))
	    	{
	    		$total = array_column($result, $this->suma[0], $this->suma[1]);
	    		if ($this->total_filas == 'si') $this->total_filas = count($total);
	    	}
	    	else
	    	{
	    		$total = array_column($result, $this->suma);
	    		if ($this->total_filas == 'si') $this->total_filas = count($total);
	    	}
	    	$this->total = array_sum($total);
	    }
	    
	    $this->CI->db->limit($this->db_limit);
	    $this->CI->db->offset($this->page == 1 ? NULL : ($this->db_limit * ($this->page - 1)));
	    if ($this->db_sort_by) $this->CI->db->order_by($this->db_sort_by.' '.$this->db_sort_order);
	    return $this->CI->db->get()->result_array();
	}
	
	// Función de paginación.
	protected function pagina()
	{
	    $r = '';
	    $total_paginas = ceil($this->total_filas / $this->db_limit);
	    
	    if ($total_paginas > 1)
	    {
	        $r .= '<br><div class="btn-group-sm" role="group" aria-label="...">';
	        
	        $attr['div_id'] = $this->div_id;
	        $attr['class'] = 'btn btn-default';
	        $attr['sorter'] = $this->db_sort_by;
	        $attr['order'] = $this->db_sort_order;
	        $attr['url'] = base_url($this->controlador.'/ajax_sort');
	        
	        if ($this->page >= 4)
	        {
	            $attr['id'] = 'sort_'.rand(0, 200);
	            $attr['onClick'] = "sortea_tablax('".$attr['id']."')";
	            $attr['page'] = 1;
	            $attr['content'] = 'Primero';
	            $r .= form_button($attr)."\n";
	        }
	        if ($this->page != 1)
	        {
	        	$attr['id'] = 'sort_'.rand(0, 200);
	        	$attr['onClick'] = "sortea_tablax('".$attr['id']."')";
	        	$attr['page'] = $this->page - 1;
	        	$attr['content'] = 'Anterior';
	            $r .= form_button($attr)."\n";
	        }
	        for ($i = $this->page - 3; $i<=$this->page + 3; $i++)
	        {
	            if ($i > 0 && $i <= $total_paginas)
	            {
	              	$attr['id'] = 'sort_'.rand(0, 200);
	            	$attr['onClick'] = "sortea_tablax('".$attr['id']."')";
	            	$attr['content'] = $i == $this->page ? "<font color=red>".$i."</font>" : $i;
	            	$attr['page'] = $i;
    	            if ($i == $this->page) $attr['disabled'] = "disabled";
    	            $r .= form_button($attr)."\n";
    	            if (! empty($attr['disabled'])) unset($attr['disabled']);
	            }
	        }
	        if ($this->page != $total_paginas)
	        {  
	            $attr['id'] = 'sort_'.rand(0, 200);
	            $attr['onClick'] = "sortea_tablax('".$attr['id']."')";
	            $attr['page'] = $this->page + 1;
	            $attr['content'] = 'Siguiente';
    	        $r .= form_button($attr)."\n";
	        }
	        if ($this->page <= ($total_paginas - 3))
	        {
	            $attr['id'] = 'sort_'.rand(0, 200);
	            $attr['onClick'] = "sortea_tablax('".$attr['id']."')";
	            $attr['page'] = $total_paginas;
	            $attr['content'] = 'Último';
    	        $r .= form_button($attr)."\n";
	        }
	            $r .= '</div>';
	    }
	    return $r;
	}
}