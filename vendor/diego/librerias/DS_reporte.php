<?php

namespace Diego\Librerias;

use Diego\Librerias\DS_tablax;

class DS_reporte extends DS_tablax
{	
	public function __construct()
	{
		parent::__construct();
	}

	public function form($params, $reporte = '')
	{
		$this->n_fila = 'no';
		if (empty($reporte)) $reporte = 'gen_reporte';
		$this->inicia($params);
		
		// Generador del contenido completo de la tabla html.
		if ($this->busca_titulo !== FALSE)
		{
			$out = $this->titulo_tabla ? crea_titulo($this->titulo_tabla, 3) : '';
			$out .= $this->insertar !== FALSE ? crea_pre('div', ['align' => 'right']).$anchor.crea_pos('div') : '';
			$out .= crea_pre('ul', ['class' => 'nav nav-tabs']).
					crea_pre('li', ['class' => 'active']).
					crea_pre('a', ['data-toggle' => 'tab', 'href' => '#tabs-1']).$this->titulo_tabla.
					crea_pos('a,li').
					crea_pre('li').
					crea_pre('a', ['data-toggle' => 'tab', 'href' => '#tabs-2']).$this->busca_titulo.
					crea_pos('a,li,ul').
					crea_pre('div', ['class' => 'tab-content']).
					crea_pre('div', ['id' => 'tabs-1', 'class' => 'tab-pane fade in active']);
			if (is_int($this->total_filas)) $out .= 'Registos: '.$this->total_filas."\n";
			$out .= crea_pre('div', ['id' => $this->div_id]);
			$out .= gen_reporte($this->tabla).$this->suma;
			$out .= $this->pagina().crea_pos('div,div').crea_pre('div', ['id' => 'tabs-2', 'class' => 'tab-pane fade']);
			$out .= gen_form_busca($this->busca_form).crea_pos('div,div');
		}
		else
		{
			$out = $this->titulo_tabla ? crea_titulo($this->titulo_tabla, 3) : '';
			$out .= $this->insertar !== FALSE ? crea_pre('div', ['align' => 'right']).$anchor.crea_pos('div') : '';
			if (is_int($this->total_filas)) $out .= 'Registos: '.$this->total_filas."\n";
			$out .= crea_pre('div', ['id' => $this->div_id]);
			$out .= $reporte($this->tabla).$this->suma;
			$out .= $this->pagina().crea_pos('div');
		}
		return $out;
	}
	
	public function form_bs($params)
	{
		return $this->form($params, 'gen_reporte_bs');
	}
	
	public function form_ajax_bs($params, $pdf = '')
	{
		return $this->form_ajax($params, $pdf, 'gen_reporte_bs');
	}
	
	public function form_ajax($params, $pdf = '', $reporte = '')   
	{
		$out = '';
		$this->n_fila = 'no';
		if (empty($reporte)) $reporte = 'gen_reporte';
		$this->inicia($params);
		//if (is_int($this->total_filas)) $out .= 'Registos: '.$this->total_filas."\n";
		$out .= crea_pre('div', ['id' => $this->div_id]);
	   	$out .= $reporte($this->tabla).$this->suma;
	   	$out .= $this->pagina();
	   	return $out;
	}
	
	// Recuperamos de la DB los campos para rellenar la tabla.
	protected function prep_campos_db()
	{
		$fields = [];
		$c = $this->maestro_detalle($this->db_campos);
		foreach ($c as $camp)
		{
			// Comprobamos si los campos existen en la tabla.
			$kf = $camp[2];
			$this->CI->db->field_exists($kf, $this->db_tabla) ? $fields[] = $kf : '';
			
			// Comprobamos si existe el parámetro 'h' que tiene los valores a pasar en un link
			// en forma de array nombre => valor.
			if (isset($camp['h']))
			{
				foreach ($camp['h'] as $k => $v)
				{	
					$this->CI->db->field_exists($v, $this->db_tabla) &&
					! in_array($v, $fields) ?
					$fields[] = $v : '';
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
		$this->fields = $fields;
	}
	
	private function maestro_detalle ($campos)
	{
		foreach ($campos as $k)
		{
			foreach ($k as $v)
			{
				$c[] = $v;
			}
		}
		return $c;
	}
}