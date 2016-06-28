<?php

namespace Diego\Librerias;

class DS_base {

	protected $CI;
	public $csrf;
	protected $regla;
	protected $var_sesion = FALSE;

	public function __construct($var_sesion = FALSE)
	{
		$this->CI =& get_instance();
		$this->csrf = crea_pre('input', ['type' => 'hidden', 'name' => $this->CI->security->get_csrf_token_name(),
				'value' => $this->CI->security->get_csrf_hash()]);
		$this->var_sesion = $var_sesion;
	}
	
	public function normaliza($p, $objeto)
	{
		if ($this->valida($objeto))
		{
			$data = $p + $this->sesion($objeto);
			$data = $this->puente($data, $objeto);
			return $data;
		}
	}
	
	public function puente($params, $objeto)
	{
		$datos = array_intersect_key($params, $objeto);
		foreach ($datos as $k => $v)
		{
			$d = $objeto[$k];
			if (is_array($d) && isset($d[2]) && $v === '')
			{
				if ($d[2] === 'date' OR $d[2] === 'integer' OR $d[2] === 'timestamp without time zone')
				{
					$this->CI->db->set("$k", 'NULL', FALSE);
					unset ($datos[$k]);
				}
			}
		}
		return $datos;
	}
	
	public function sesion($objeto, $var_sesion = FALSE)
	{
		$datos = [];
		foreach ($objeto as $k => $v)
		{
			foreach ($this->var_sesion as $var => $ses)
			{
				if (substr_count($k, $var) > 0 && isset($_SESSION[$ses])) $datos[$k] = $_SESSION[$ses];
			}
		}
		return $datos;
	}
	
	public function valida($objeto = FALSE)
	{
		if ($objeto) foreach ($objeto as $k => $v)
		{
			if (is_array($v) && $v[1] !== '')
			{
				$r['field'] = $k;
				$r['label'] = $v[0];
				if (! empty($v[1])) $r['rules'] = $v[1];
				$this->regla[] = $r;
			}
		}
	
		if (! empty($this->regla))
		{
			$this->CI->load->library('form_validation');
			$this->CI->form_validation->set_rules($this->regla);
			return $this->CI->form_validation->run();
		}
		else
		{
			return TRUE;
		}
	}
	
	public function objecto_tabla ($tabla = FALSE)
	{
		$campos = $this->CI->db->field_data($tabla);
		foreach ($campos as $campo)
		{
			$objeto["$campo->name"] = ["ucfirst($campo->name)", '', "$campo->type"];
		}
		return $objeto;
	}
}
