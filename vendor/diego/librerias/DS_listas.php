<?php

namespace Diego\Librerias;

class DS_listas extends DS_base
{
	public function __construct()
	{
		parent::__construct();
	}
	
	/**************************************************************************
	 * Función para devolver las opciones que se utilizan en un form_dropdown
	 * dese una tabla de la DB.
	 **************************************************************************
	 *
	 * @param unknown $tabla --> La tabla de donde se recuperan los valores.
	 * @param unknown $clave --> Clave que se pasará como valor al campo.
	 * @param unknown $valor --> Valor que se mostrará en el form_dropdown.
	 * @param string $condiciones --> Condiciones de selección en la tabla.
	 * @return --> array de pares clave - valor.
	 */
	
	public function lista($tabla, $clave, $valor, $condiciones = FALSE, $selec = TRUE, $order = FALSE)
	{
		$this->CI->db->select($clave);
		$this->CI->db->select($valor);
		if ($selec) $opcion = ['' => 'Seleccionar'];
		if ($condiciones)
		{
			$this->CI->db->where($condiciones);
		}
		if ($order)
		{
			$this->CI->db->order_by($order);
		}
		else 
		{
			$this->CI->db->order_by($valor, 'asc');
		}
		$query = $this->CI->db->get($tabla)->result_array();
		foreach ($query as $row){
			$opcion[$row[$clave]] = $row[$valor];
		}
		return $opcion;
	}
	
	/************************************************************************* 
	 * Función para generar una lista de clave-valor desde una función contenida
	 * en un modelo determinado.
	 * *************************************************************************
	 * 
	 * @param $modelo -> Modelo del cual se extraen los datos.
	 * @param $funcion -> Función del modelo que devuelve los datos.
	 * @param $clave -> Campo clave.
	 * @param $valor -> Campo valor.
	 * @param $todos -> TRUE si queremos que nos devuelva la opción "Todos".
	 */
	public function lista_model($modelo, $funcion, $clave, $valor, $todos = FALSE)
	{
		if ($todos) $opcion = ['' => 'Todos'];
		$this->CI->load->model($modelo, '_modelo');
		$query = $this->CI->_modelo->$funcion();
		foreach ($query as $row){
			$opcion[$row[$clave]] = $row[$valor];
		}
		return $opcion;
	}
	
	/*************************************************************************
	 * Función para generar un listado con grupos de opciones
	 * ***********************************************************************
	 *
	 * $params['tabla'] -> Tabla de la DB de dónde salen los datos.
	 * $params['padre_id'] -> Campo de la ID del parent.
	 * $params['hijo_id'] -> Campo de la DB que tiene el ID del hijo.
	 * $params['nombre'] -> Campo de la DB que tiene el nombre de la opción.
	 * $params['donde'] -> Sentencia SQL para ejecutar como "Where".
	 */
	
	public function agrega_mysql($params)
	{
		$tabla = $params['tabla'];
		$padre = $params['padre_id'];
		$hijo = $params['hijo_id'];
		$nombre = $params['nombre'];
		$donde = isset($params['donde']) ? $params['donde'] : FALSE;
		$out = '';
		
		$this->CI->db->select("$hijo, $nombre");
		if ($donde !== FALSE) $this->CI->db->where($donde);
		$query = $this->CI->db->get($tabla)->result_array();
		
		foreach ($query as $q => $y)
		{
			$datos[$y[$hijo]] = $y[$nombre];
		}
		
		$this->CI->db->select("$padre, group_concat($hijo) as $hijo", FALSE);
		if ($donde !== FALSE) $this->CI->db->where($donde);
		$this->CI->db->group_by($padre);
		$arbol = $this->CI->db->get($tabla)->result_array();
		
		$r = '';
		foreach ($arbol as $p)
		{
			$h = '';
			$val = '';
			$h = explode(',', $p[$hijo]);
			foreach ($h as $k => $v)
			{
				$val[$v] = $datos[$v];
			}
			if (isset($datos[$p[$padre]]))
			{
				$r[$datos[$p[$padre]]] = $val;
			}
		}
		return $r;
	}
}