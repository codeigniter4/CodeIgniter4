<?php

namespace Diego\Librerias;

class DS_menus extends DS_base
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function menu($p)
	{
		if ($data = apc_fetch(implode('.', $p)))
		{
			$this->CI->db->select($p['id'], $p['nombre'], $p['url']);
			if ($p['donde'] != '')
			{
				foreach ($p['donde'] as $donde)
				{
					$this->CI->db->where($donde);
				}
			}
			$this->CI->db->order_by($p['nombre']);
			$data = $this->CI->db->get($p['tabla'])->result_array();
			apc_add(implode('.', $p), $data);
		}
		
		$conf = [
				'menu_tipo' => $p['tipo'] == '' ? 'vmenu' : 'hmenu',
				'url' => $p['url'] != '' ? $p['url'] : '',
				'nombre' => $p['nombre'],
		];
		
		return gen_menu($data, $conf);
	}
}