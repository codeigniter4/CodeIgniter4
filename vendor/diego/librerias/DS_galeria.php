<?php

namespace Diego\Librerias;

use Diego\Librerias\DS_upload;

class DS_galeria extends DS_tablax
{
	protected $n_filas = '3';
	protected $n_columnas = '1';
	protected $tipo_fila = 'fila_imagen';
	protected $subir = FALSE;
	
	public function __construct()
	{
		parent::__construct();
	}

	public function form($params)
	{
		$this->inicia($params);
		if (isset($params['subir']))
		{
			foreach ($params['subir'] as $k => $v)
			{
				$this->subir[$k] = $v;
			}
		}
		$this->db_limit = $this->n_columnas * $this->n_filas;
		
		if ($this->insertar !== FALSE)
		{
			if (is_array($this->insertar))
			{
				$nombre = $this->insertar[0];
				$funcion = $this->insertar[1];
				$anchor = anchor($this->controlador.'/'.$funcion, $nombre);
			}
			else 
			{
				$anchor = anchor($this->controlador.'/insert', $this->insertar);
			}
		}
		
		// Generador del contenido completo de la tabla html.
		if ($this->busca_titulo !== FALSE)
		{
			$out = $this->titulo_tabla ? crea_titulo($this->titulo_tabla, 3): '';
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
			$out .= $this->galeria ($this->tabla).$this->suma;
			$out .= $this->pagina().crea_pos('div,div').crea_pre('div', ['id' => 'tabs-2', 'class' => 'tab-pane fade']);
			$out .= gen_form_busca($this->busca_form).crea_pos('div,div');
		}
		else
		{
			$out = $this->titulo_tabla ? crea_titulo($this->titulo_tabla, 3): '';
			$out .= $this->insertar !== FALSE ? crea_pre('div', ['align' => 'right']).$anchor.crea_pos('div') : '';
			if (is_int($this->total_filas)) $out .= 'Registos: '.$this->total_filas."\n";
			$out .= crea_pre('div', ['id' => $this->div_id]);
			$out .= $this->galeria ($this->tabla).$this->suma;
			$out .= $this->pagina().crea_pos('div');
		}
		return $out;
	}
	
	public function form_ajax($params, $pdf = '')   
	{
		$this->inicia($params);
		$out = $this->galeria ($this->tabla);
		$out .= $this->pagina().crea_pos('div');
		return $out;
	}
	
	/********************************************************************************************
	 /********************************************************************************************
	 * @param $params:
	 * 		['campos'] => Campos que incluirá la tabla, definidos según las funciones de cada campo.
	 * 		['data'] => Datos de la DB para poblar los campos.
	 * 		['ancho'] => Ancho de la tabla.
	 * 		['encabezados'] => El nombre lo dice todo.
	 * 		['page'] => Si hay paginador, la página en la que se encuentra la vista de la tabla.
	 */
	
	private function galeria($params)
	{
		$ancho = isset($params['ancho']) ? $params['ancho'] : '100%';
		$controlador = isset($params['controlador']) ? $params['controlador'] : FALSE;
		$page = isset($params['page']) ? $params['page'] : FALSE;
		$volver = isset($params['volver']) ? $params['volver'] : FALSE;
		$filas = isset($params['filas_galeria']) ? $params['filas_galeria'] : 3;

		$t = '';
		if (isset($params['data']))
		{
			//$t .= crea_pre('div', ['class' => 'g_img']);
			$fila = 0;
			$r = $this->n_columnas;
			$rm = 12 / $r;
			$t .= crea_pre('div', ['class' => 'row']);
			foreach ($params['data'] as $row)
			{
				if ($fila < $r)
				{
					$t .= crea_pre('div', ['class' => "col-xs-$rm"]);
					$t .= crea_pre('div', ['class' => $this->tipo_fila]);
					foreach ($params['campos'] as $campo)
					{
						$t .= crea_datos($campo, $row);
					}
					$t .= crea_pos('div,div');
					$fila++;
				}
				else 
				{
					$t .= crea_pre('div', ['class' => "col-xs-$rm"]);
					$t .= crea_pre('div', ['class' => $this->tipo_fila]);
					foreach ($params['campos'] as $campo)
					{
						$t .= crea_datos($campo, $row);
					}
					$t .= crea_pos('div,div');
					$fila = 1;
				}
			}
			$t .= crea_pos('div');
		}
	
		// Está definida una fila adicional para insertar valores?
		if ($this->subir)
		{
			$subir = new DS_upload();
			$t .= crea_pre('div');
			$t .= $subir->form($this->subir);
			$t .= crea_pos('div');
		}
		return $t;
	}
}
