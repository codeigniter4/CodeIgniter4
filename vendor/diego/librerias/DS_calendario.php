<?php

namespace Diego\Librerias;

class DS_calendario extends DS_base
{
	public function __construct()
	{
		parent::__construct();
	}
	
	private function inicia($controlador)
	{
		$prefs = [
				'show_next_prev'  => TRUE,
				'next_prev_url' => base_url($controlador.'/mes/'),
				'start_day' => 'monday',
				'template' => '{table_open}<table class="table-calendario table-responsive" border="0" width="100%">{/table_open}
				{heading_title_cell}<th colspan="{colspan}"  style= "text-align: center;">{heading}</th>{/heading_title_cell}
				{cal_cell_start}<td align="center" style="padding: 2px;">{/cal_cell_start}
	            {cal_cell_content}<a href="{content}"><font color=#0489B1><b>{day}</b></font></a>{/cal_cell_content}
				{table_close}</table>{/table_close}',
		];
		$this->CI->load->library('calendar', $prefs);
	} 
	
	/*************************************************************************
	 * Función para generar un calendario y un formulario para escoger fechas.
	 * ***********************************************************************
	 * $params['tabla'] -> Tabla de la BD de donde extraer los datos.
	 * $params['fecha'] -> Campo fecha de la BD.
	 * $params['link'] -> Campo link de la BD.
	 * $params['where'] -> Campos where de la BD en forma 'campo' => 'sentencia where'.
	 * $params['controlador'] -> Controlador.
	 * $params['opciones'] -> Opciones para el desplegable del formulario.
	 * $params['seleccionado'] -> Valor por seleccionado del desplegable
	 */
	public function agenda_citas($params)
	{
	    $c = substr_count($params['controlador'], '/') + 2;
	    $valores = $this->CI->uri->uri_to_assoc($c, ['mes']);
	    if (! empty($valores['mes'])) $_SESSION['mes_citas'] = $valores['mes'];
	    $params['mes'] = isset($_SESSION['mes_citas']) ? $_SESSION['mes_citas'] : date('Y-m');
	    
	    $data['id'] = 'id';
		$data['onChange'] = 'this.form.submit()';
		$data['class'] = "form-control input-sm";
		
		$out =	"<br>".form_fieldset().
			    $this->genera_calendario($params).
				'Sanitario:'.
				form_open($params['controlador']).
				form_dropdown('sanitario', $params['opciones'], $params['seleccionado'], $data).
				form_close().
				form_fieldset_close();
		
		return $out;
	}
	
	public function agenda_iq($params)
	{
	    $c = substr_count($params['controlador'], '/') + 2;
	    $valores = $this->CI->uri->uri_to_assoc($c, ['mes']);
	    if (! empty($valores['mes'])) $_SESSION['mes_iqs'] = $valores['mes'];
	    $params['mes'] = isset($_SESSION['mes_iqs']) ? $_SESSION['mes_iqs'] : date('Y-m');
	    
	    $out =	"<br>".form_fieldset().
		$this->genera_calendario($params).form_fieldset_close();
		return $out;
	}
	
	public function citas_paciente($params)
	{
	    $c = substr_count($params['controlador'], '/') + 2;
	    $valores = $this->CI->uri->uri_to_assoc($c, ['mes']);
	    if (! empty($valores['mes'])) $_SESSION['mes_citas_pac'] = $valores['mes'];
	    $params['mes'] = isset($_SESSION['mes_citas_pac']) ? $_SESSION['mes_citas_pac'] : date('Y-m');
	    
	    $js = 'id="s_id" onChange="this.form.submit()"';
	    
	    $out =	form_fieldset('Agenda');
	    $out .=	'Sanitario:'.
	            form_open($params['controlador']).
	            campo_dropdown_submit(['dropdown_submit', 'texto', 's_id', 'op' => $params['opciones']]).
	            form_close();
	            if ($params['seleccionado'] != 'todos') $out .= $this->genera_calendario($params);
	            $out .=	form_fieldset_close();
	    
	    return $out;
	}
	
	public function genera_calendario($params) {
		
		$this->inicia($params['controlador']);
		
		$fecha = $params['fecha'];
		$link = $params['link'];
		$tabla = $params['tabla'];
		
		$this->CI->db->select("date_part('day', $fecha) as fecha", FALSE);
		$this->CI->db->select("$link as link");
		$this->CI->db->where("to_char($fecha, 'YYYY-mm') = ", $params['mes']);
		if (isset($params['where']))
		{
		    foreach ($params['where'] as $k => $v)
		    {
		        $this->CI->db->where($v);
		        $this->CI->db->distinct($k);
		    }
		}
		$this->CI->db->distinct($fecha);
		$q = $this->CI->db->get($tabla)->result_array();
		 
		$opcion = '';
		foreach ($q as $row)
		{
			$opcion[$row['fecha']]= base_url().$params['controlador'].'/fecha/'.$row['link'];
		}
		
		$fecha_cal = date_parse($params['mes']);
		
		return $this->CI->calendar->generate($fecha_cal['year'], $fecha_cal['month'], $opcion);
	}
}