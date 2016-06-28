<?php

class Piston extends CI_Controller {
			
	protected $database;
	protected $table;
	protected $fields;
	protected $controller_name;
	protected $model_name;
	protected $directorio = '';
	protected $objeto;
	protected $pk;
	protected $vars;
	protected $cvars;
	
	protected $vista;
	
	private $construct;
    
    function __construct()
    {
		parent::__construct();
    	if (php_sapi_name() != "cli")
		{
			die("This program can only be run from the command line");
		}
	}
	
	public function genera_model(){
	    
	    $this->database = readline("Nombre de la base de datos?: ");
	    $this->table = readline("Nombre de la TABLA DE LA BASE DE DATOS en la que se basará el nuevo modelo?: ");
	    $this->model_name = readline("Nombre para el nuevo MODELO?: ");
	    $this->directorio = readline("Estrá el nuevo modelo en un subdirectorio. Cúal? ");
	    
		if(file_exists(APPPATH."/models/".$this->directorio.$this->model_name.".php")){
			if(readline("The model ".$this->model_name.".php already exists. Overwrite (Y/n)?")!=="Y"){
				exit(PHP_EOL);
			}
		}
		
	    $this->pk = readline("Nombre de la clave primaria?: ");
		
		$this->load->database($this->database);
		
		if(! $this->db->table_exists($this->table)){
			die("This table doesn't exist. Please create first.".PHP_EOL);
		}
		
		$f = "<?php defined('BASEPATH') or exit('No direct script access allowed');\n\n";
		$f .= 'class '.ucfirst($this->model_name).' extends DS_Model'."\n";
		$f .= "{\n";
		
		$this->vars = "\t"."protected $".$this->table.";\n";
		
		$this->construct = "\t"."public function __construct()\n";
		$this->construct .= "\t"."{\n";
		$this->construct .= "\t\t"."parent::__construct();\n";
		$this->construct .= "\t\t".'$this->'.$this->table.' = ['."\n";
		
		$this->fields = $this->db->field_data($this->table);
		foreach ($this->fields as $field)
		{
		    $etiqueta = $field->name;
		    $reglas = readline("Reglas de validación para el campo ".$field->name." ?: ");
		    $tipo = $field->type;
		    $et = "['$etiqueta', '$reglas', '$tipo']";
		    $this->construct .= "\t \t \t '$field->name' => $et, \n";
		}
		$this->construct .= "\t\t];\n\t}\n";
		
		$get_one = "\t".'public function get_one($p = FALSE)'."\n";
		$get_one .= "\t"."{\n";
		$get_one .= "\t\t".'if (is_array($p))'."\n";
		$get_one .= "\t\t".'{'."\n";
		$get_one .= "\t\t\t".'$this->db->where($p);'."\n";
		$get_one .= "\t\t\t".'return $this->db->get(\''.$this->table.'\')->row_array();'."\n";
		$get_one .= "\t\t".'}'."\n";
		$get_one .= "\t\t".'else'."\n";
		$get_one .= "\t\t".'{'."\n";
		$get_one .= "\t\t\t".'$this->db->where(\''.$this->pk.'\', $p);'."\n";
		$get_one .= "\t\t\t".'return $this->db->get(\''.$this->table.'\')->row_array();'."\n";
		$get_one .= "\t\t".'}'."\n";
		$get_one .= "\t"."}\n";
		
		$get_all = "\t".'public function get_all($p = FALSE)'."\n";
		$get_all .= "\t"."{\n";
		$get_all .= "\t\t".'if ($p)'."\n";
		$get_all .= "\t\t".'{'."\n";
		$get_all .= "\t\t\t".'if ($p)'."\n";
		$get_all .= "\t\t\t".'{'."\n";
		$get_all .= "\t\t\t\t".'$this->db->where($p);'."\n";
		$get_all .= "\t\t\t".'}'."\n";
		$get_all .= "\t\t".'}'."\n";
		$get_all .= "\t\t".'return $this->db->get(\''.$this->table.'\')->result_array();'."\n";
		$get_all .= "\t"."}\n";
		
		$insert = "\t".'public function insert($p)'."\n";
		$insert .= "\t"."{\n";
		$insert .= "\t\t".'$data = $p + $this->sesion($this->'.$this->table.');'."\n";
		$insert .= "\t\t".'$data = $this->puente ($data, $this->'.$this->table.');'."\n";
		$insert .= "\t\t".'if ($this->valida($this->'.$this->table.'))return $this->db->insert(\''.$this->table.'\', $data) ? TRUE : FALSE;'."\n";
		$insert .= "\t"."}\n";
		
		$update = "\t".'public function update($p)'."\n";
		$update .= "\t"."{\n";
		$update .= "\t\t".' if ($id = $p[\''.$this->pk.'\']) unset($p[\''.$this->pk.'\']);'."\n";
		$update .= "\t\t".'$data = $p + $this->sesion($this->'.$this->table.');'."\n";
		$update .= "\t\t".'$data = $this->puente ($data, $this->'.$this->table.');'."\n";
		$update .= "\t\t".'if ($this->valida($this->'.$this->table.')) return $this->db->update(\''.$this->table.'\', $data, [\''.$this->pk.'\' => $id]) ? TRUE : FALSE;'."\n";
		$update .= "\t"."}\n";
		
		$delete = "\t".'public function delete($p)'."\n";
		$delete .= "\t"."{\n";
		$delete .= "\t\t".' if ($id = $p[\''.$this->pk.'\']) ';
		$delete .= 'return $this->db->delete(\''.$this->table.'\', [\''.$this->pk.'\' => $id]) ? TRUE : FALSE;'."\n";
		$delete .= "\t"."}\n";
		
		$f .= $this->vars."\n".$this->construct."\n".$get_one."\n".$get_all."\n".$insert."\n".$update."\n".$delete."\n";
		
		while (readline("Desea crear una vista para su nuevo modelo? s/n: ") == 's')
		{
		    $f .= "\n".$this->genera_vista();
		}
		$f .= "}";
			
		file_put_contents(APPPATH."/models/".$this->directorio.'/'.ucfirst($this->model_name).".php", $f);
		
		echo "Su modelo, $this->model_name, ha sido creado con éxito. Gracias\n\n";
		
		if (readline("Desea crear un controlador para su modelo? s/n: ") == 's') $this->genera_controller();
	}
	
	public function genera_vista()
	{
	    $nombre_vista = readline("Nombre para la vista: ");
	    $this->vars = "\t"."protected $".$nombre_vista.";\n";
	    if ($nombre_vista == '') die("Debe dar un nombre a la función vista");
	    
	    $campos = FALSE;
	    $joins = FALSE;
	    $tp_prefix = readline("Prefijo de la tabla principal :");
	    
	    foreach ($this->fields as $field)
	    {
	        $campos .= "\t\t".'$this->db->select(\''.$tp_prefix.'.'.$field->name.' as '.$field->name.'\');'."\n";
	    }
	    
	    while (readline("Desea definir un SELECT especial para la tabla principal? s/n") == 's')
	    {
	        if (readline("Recuerde prefijar los campos de esta tabla con ".$tp_prefix))
	        {
	            $sql = readline("Escriba su SQL: ");
	            $campos .= "\t\t".'$this->db->select("'.$sql.'", "FALSE");'."\n";
	        }
	    }
	    
	    while (readline("Desea unir una tabla? s/n ") == 's')
	    {
	        $tabla_join = readline("Nombre de la tabla a unir: ");
	        $tb_prefix = readline("Prefijo de la tabla a unir: ");
	        
	        $campos_join = $this->db->field_data($tabla_join);
	        foreach ($campos_join as $cj)
	        {
	            if (readline("Desea añadir el campo $cj->name al join? s/n ") == 's')
	            {
	                $campo_join = $cj->name;
	                $alias = readline("Alias del campo: ");
	                if ($alias != '')
	                {
	                    $campos .= "\t\t".'$this->db->select(\''.$tb_prefix.'.'.$campo_join.' as '.$alias.'\');'."\n";
	                }
	                else
	                {
	                    $campos .= "\t\t".'$this->db->select(\''.$tb_prefix.'.'.$campo_join.' as '.$campo_join.'\');'."\n";
	                }
	            }
	        }
	            
            while (readline("Desea definir un SELECT especial para la tabla ".$tabla_join."? s/n") == 's')
            {
                if (readline("Recuerde prefijar los campos de esta tabla con ".$tb_prefix))
                {
                    $sql = readline("Escriba su SQL: ");
                    $campos .= "\t\t".'$this->db->select("'.$sql.'", "FALSE");'."\n";
                }
            }
	            
	        $campo_join_principal = readline("Campo independiente de la tabla principal: ");
	        $campo_join_secundario = readline("Campo dependiente de la tabla subordinada: ");
	        $sentido_join = readline("Sentido del join: ");
	        
	        $joins .= "\t\t".'$this->db->join(\''.$tabla_join.' '.$tb_prefix.'\', \''.$tp_prefix.'.'.$campo_join_principal.
	               ' = '.$tb_prefix.'.'.$campo_join_secundario.'\', \''.$sentido_join.'\');'."\n";
	    }
	    $out = "\t".'public function '.$nombre_vista.'()'."\n";
	    $out .= "\t"."{\n";
	    $out .= $campos;
	    $out .= "\t\t".'$this->db->from(\''.$this->table.' '.$tp_prefix.'\');'."\n";
	    $out .= $joins;
	    $out .= "\t"."}\n";
	    
	    return $out;
	}
	
	public function genera_controller()
	{
	    $tablas = FALSE;
	    $formularios = FALSE;
	    
	    $nombre_controller = readline("Nombre para el controlador (en minúsculas): ");
	    $directorio_controller = readline("Desea colocar el controlador en un subdirectorio dentro del directorio 'controllers'. En cuál? : ");
	    $extend_controller = readline("A quién extiende el controlador, a CI_Controller, DS_Controller, H_Controller? : ");
	    $model_controller = readline("Nombre del modelo en el que se basa el controlador (con la ruta completa): ");
	    $model_id = readline("Cuál es la clave primaria del modelo? :");
	    $a = readline("Desea definir un alias para el modelo? :");
	    $model_alias = $a != '' ? $a : $model_controller;
	    
	    $f = "<?php defined('BASEPATH') or exit('No direct script access allowed');\n\n";
	    $f .= 'class '.ucfirst($nombre_controller).' extends '.$extend_controller."\n";
	    $f .= "{\n";
	    
	    $c_construct = "\t"."public function __construct()\n";
	    $c_construct .= "\t"."{\n";
	    $c_construct .= "\t\t"."parent::__construct();\n";
	    $c_construct .= "\t\t".'$this->load->model(\''.$model_controller.'\', \''.$model_alias.'\');'."\n";
	    $c_construct .= "\t\t"."\$this->d['title'] = '".ucfirst($nombre_controller)."';"."\n";
	    $c_construct .= "\t"."}\n";
	    
	    if (readline("DESEA definir una TABLA para los datos? s/n: ") == 's')
	    {
	        $tabla = $this->genera_tabla($model_id);
	    }
	    if (readline("DESEA definir un FORMULARIO para los datos? s/n: ") == 's')
	    {
	        $formulario = $this->genera_formulario();
	    }
	    
	    $f .= $c_construct."\n";
	    
	    $f .= "\t".'public function index()'."\n";
	    $f .= "\t"."{\n";
	    $f .= "\t\t"."\$this->$model_alias"."->get_all();"."\n\n";
	    $f .= "\t\t"."\$params = ''; // Parámetros a pasar a la función que muestra los resultados."."\n";
	    $f .= "\t\t"."\$this->d['c']['container']['row']['b.8'] = \$this->tablax->form(\$params);"."\n";
	    $f .= "\t\t"."\$this->load->view('template', \$this->d);"."\n";
	    $f .= "\t"."}\n\n";
	    $f .= "\t".'public function insert()'."\n";
	    $f .= "\t"."{\n";
	    $f .= "\t\t"."if (\$data = \$this->input->post())"."\n";
	    $f .= "\t\t"."{"."\n";
	    $f .= "\t\t\t"."if (\$this->$model_alias"."->insert(\$data)) redirect ('$directorio_controller/$nombre_controller');"."\n";
	    $f .= "\t\t"."}"."\n";
	    $f .= "\t\t"."\$params = ''; // Parámetros a pasar a la función que muestra los resultados."."\n";
	    $f .= "\t\t"."\$this->d['c']['container']['row']['b.8'] = gen_formulario_bs(\$params);"."\n";
	    $f .= "\t\t"."\$this->load->view('template', \$this->d);"."\n";
	    $f .= "\t"."}\n\n";
	    $f .= "\t".'public function edit($data = FALSE)'."\n";
	    $f .= "\t"."{\n";
	    $f .= "\t\t"."\$id = \$data;"."\n";
	    $f .= "\t\t"."if (\$this->input->post())"."\n";
	    $f .= "\t\t"."{"."\n";
	    $f .= "\t\t\t\t"."\$data = \$this->input->post();"."\n";
	    $f .= "\t\t\t\t"."if (isset (\$data['save']))"."\n";
		$f .= "\t\t\t\t"."{"."\n";
		$f .= "\t\t\t\t\t"."if (isset(\$data['$model_id']))"."\n";
		$f .= "\t\t\t\t\t"."{"."\n";
		$f .= "\t\t\t\t\t\t"."\$this->$model_alias"."->update(\$data);"."\n";
		$f .= "\t\t\t\t\t\t"."\$id = \$data['$model_id'];"."\n";
		$f .= "\t\t\t\t\t"."}"."\n";
		$f .= "\t\t\t\t\t"."else"."\n";
		$f .= "\t\t\t\t\t"."{"."\n";
		$f .= "\t\t\t\t\t\t"."if (\$this->$model_alias"."->insert(\$data)) redirect('$directorio_controller/$nombre_controller');"."\n";
		$f .= "\t\t\t\t\t\t"."\$id = \$data['$model_id'];"."\n";
		$f .= "\t\t\t\t\t"."}"."\n";
		$f .= "\t\t\t\t"."}"."\n";
		$f .= "\t\t\t\t"."if (isset (\$data['delete']))"."\n";
		$f .= "\t\t\t\t"."{"."\n";
		$f .= "\t\t\t\t\t"."if (\$this->$model_alias"."->delete(\$data)) redirect('$directorio_controller/$nombre_controller');"."\n";
		$f .= "\t\t\t\t"."}"."\n";
	    $f .= "\t\t"."}"."\n";
	    $f .= "\t\t"."\$params = \$this->$f_nombre();\n";
	    $f .= "\t\t"."\$this->d['c']['container']['row']['b.8'] = gen_formulario_bs(\$params);"."\n";
	    $f .= "\t\t"."\$this->load->view('template', \$this->d);"."\n";
	    $f .= "\t"."}\n\n";
	    
	    if ($tabla) $f .= $tabla;
	    
	    if ($formulario) $f .= $formulario;
	    
	    if ($tabla)
	    {
	    	$f .= "\t"."public function ajax_sort()\n";
	   		$f .= "\t"."{\n";
	    	$f .= "\t\t\$params = \$this->$t_nombre();\n";
	    	$f .= "\t\t echo \$this->tablax->form_ajax(\$params);\n";
	    	$f .= "\t"."}\n";
		    $f .= "\t".'public function ajax_update()'."\n";
		    $f .= "\t"."{\n";
		    $f .= "\t\t"."\$data = \$this->input->post();"."\n";
		    $f .= "\t\t"."\$this->$model_alias"."->update(\$data);"."\n";
		    $f .= "\t\t"."\$params = \$this->$t_nombre();\n";
		    $f .= "\t\t"."echo \$this->tablax->form_ajax(\$params);"."\n";
		    $f .= "\t"."}\n\n";
		    $f .= "\t".'public function ajax_delete()'."\n";
		    $f .= "\t"."{\n";
		    $f .= "\t\t"."\$data = \$this->input->post();"."\n";
		    $f .= "\t\t"."\$this->$model_alias"."->delete(\$data);"."\n";
		    $f .= "\t\t"."\$params = \$this->$t_nombre();\n";
		    $f .= "\t\t"."echo \$this->tablax->form_ajax(\$params);"."\n";
		    $f .= "\t"."}\n\n";
	    }
	    
	    $f .= "}\n";
	    
	    file_put_contents(APPPATH."/controllers/".$directorio_controller.'/'.ucfirst($nombre_controller).".php", $f);
	    
	    echo "Su controlador, $nombre_controller, ha sido creado. Gracias\n\n";
	}
	
	public function genera_tabla($model_id)
	{
        $t_encabezados = FALSE;
	    $t_filas = FALSE;
	    $t_filas_insert = FALSE;
	    
	    $t_nombre = readline("Nombre de la función que define la tabla: ");
	    $t_db = readline("Nombre de la base de datos: ");
	    $t_db_limit = readline("Límite de filas por página: ");
	    $t_controlador = readline("URI del controlador: ");
	    $t_tabla = readline("Tabla de la DB que se extraen los datos, si los va a extraer de una tabla: ");
	    $t_titulo = ucfirst($t_controlador);
	    $t_insertar = 'Nuevo registro';
	    $t_sort_by = readline("Campo por el que se ordenan los resultados: ");
	    $dame_div = readline("Identificador DOM de la tabla: ");
	    $t_div_id = $dame_div != '' ? $dame_div : $t_controlador;
	     
	    while (readline("DESEA añadir encabezados a la tabla? s/n: ") == 's')
	    {
	        $e = readline("Etiqueta del encabezado: ");
	        $c = readline("Campo que define el encabezado: ");
	        $t_encabezados[] = "['$e', '$c']";
	    }
	     
	    $texto = "Qué tipo de campo desea añadir? :\n";
	    $texto .= "    password \n";
	    $texto .= "    hidden \n";
	    $texto .= "    label \n";
	    $texto .= "    input \n";
	    $texto .= "    link \n";
	    $texto .= "    texto \n";
	    $texto .= "    dropdown_ajax \n";
	    $texto .= "    dropdown_submit \n";
	    $texto .= "    dropdown \n";
	    $texto .= "    check \n";
	    $texto .= "    submit \n";
	    $texto .= "    button_ajax \n\n";
	    $texto .= "    imagen \n\n";
	    $texto .= "    file \n\n";
	     
	    while (readline("DESEA añadir filas a la tabla? s/n: ") == 's')
	    {
	        $c_f = readline($texto);
	        if ($c_f != '')
	        {
	        	$funcion = 'f_'.$c_f;
	        	$t_filas[] = $this->$funcion();
	        }      
	    }
	    while (readline("DESEA añadir filas insert? s/n: ") == 's')
	    {
	    	$c_fi = readline($texto);
	        if ($c_fi != '')
	        {
	        	$funcion = 'f_'.$c_fi;
	        	$t_filas_insert[] = $this->$funcion();
	        }
	    }
	    $ta = "\t"."public function $t_nombre()\n";
	    $ta .= "\t"."{\n";
	    $ta .= "\t\t".'$params = ['."\n";
	    $ta .= "\t\t\t"."'base_datos' => '$t_db',"."\n";
	    $ta .= "\t\t\t"."'controlador' => '$t_controlador',"."\n";
	    $ta .= "\t\t\t"."'db_tabla' => '$t_tabla',"."\n";
	    $ta .= "\t\t\t"."'db_limit' => '$t_db_limit',"."\n";
	    $ta .= "\t\t\t"."'db_sort_by' => '$t_sort_by',"."\n";
	    $ta .= "\t\t\t"."//'db_where' => '[campo => valor]',"."\n";
	    $ta .= "\t\t\t"."'div_id' => '$t_div_id',"."\n";
	    $ta .= "\t\t\t"."//'db_data' => ['modelo', 'metodo'],"."\n";
	    $ta .= "\t\t\t"."'titulo_tabla' => '$t_titulo',"."\n";
	    $ta .= "\t\t\t"."'insertar' => '$t_insertar',"."\n";
	    if ($t_encabezados)
	    {
	        $ta .= "\t\t\t"."'encabezados' => ["."\n";
	        foreach ($t_encabezados as $kt => $vt)
	        {
	            //$te = "'".implode("', '", $vt)."'";
	            $ta .= "\t\t\t\t$vt,\n";
	        }
	        $ta .= "\t\t\t"."],\n";
	    }
	    if ($t_filas)
	    {
	        $ta .= "\t\t\t"."'db_campos' => ["."\n";
	        $ta .= "\t\t\t\t/*['csrf', 'texto', \$this->csrf, 'c' => '_antes'],*/"."\n";
	        $ta .= "\t\t\t\t/*['hidden', 'texto', '$model_id', 'c' => '_antes'],*/"."\n";
	        foreach ($t_filas as $kfi => $vfi)
	        {
	            $ta .= "\t\t\t\t$vfi,\n";
	        }
	        $ta .= "\t\t\t\t/*['boton', 'actualizar', 'Ok', 'url' => '$controlador/ajax_update', 'ajax' => 'actualiza_tabla', 'c' => '_antes'],*/"."\n";
	        $ta .= "\t\t\t\t/*['boton', 'actualizar', 'Ok', 'url' => '$controlador/ajax_delete', 'ajax' => 'actualiza_tabla', 'c' => '_despues'],*/"."\n";
	        $ta .= "\t\t\t"."],\n";
	    }
	    if ($t_filas_insert)
	    {
	        $ta .= "\t\t\t"."'insert' => ["."\n";
	        $ta .= "\t\t\t\t"."'campos' => ["."\n";
	        foreach ($t_filas_insert as $kfin => $vfin)
	        {
	            $ta .= "\t\t\t\t\t$vfin,\n";
	        }
	        $ta .= "\t\t\t\t"."],\n";
	        $ta .= "\t\t\t"."],\n";
	    }
	    $ta .= "\t\t"."];\n";
	    $ta .= "\t\t".'return $params;'."\n";
	    $ta .= "\t"."}\n\n";
	     
	    return $ta;
	}
	
	public function genera_formulario()
	{
	    $f_campos = FALSE;
	    $f_nombre = readline("Nombre de la función que define el formulario: ");
	    $this->cvars[] = $f_nombre;
	    $f_controlador = readline("Ruta completa del controlador: ");
	    $f_titulo = readline("Título de la tabla: ");
	    $f_volver = readline("URL a la que retorna el formulario: ");
	     
	    $texto = "Qué tipo de campo desea añadir? :\n";
	    $texto .= "    password \n";
	    $texto .= "    hidden \n";
	    $texto .= "    label \n";
	    $texto .= "    input \n";
	    $texto .= "    texto \n";
	    $texto .= "    dropdown \n";
	    $texto .= "    check \n";
	     
	    while (readline("DESEA añadir campos al formulario? s/n: ") == 's')
	    {
	        $c_tipo = readline($texto);
	        $funcion = 'fr_'.$c_tipo;
	        $f_campos[] = $this->$funcion();
	    }
	    
	    $fo = "\t".'public function '.$f_nombre.'($id = FALSE)'."\n";
	    $fo .= "\t"."{\n";
	    $fo .= "\t\t".'$data = ($id) ? $id : FALSE;'."\n";
	    $fo .= "\t\t".'$params = ['."\n";
	    $fo .= "\t\t\t"."'titulo' => '$f_titulo',"."\n";
	    $fo .= "\t\t\t"."'controlador' => '$f_controlador',"."\n";
	    $fo .= "\t\t\t"."'volver' => '$f_volver',"."\n";
	    $fo .= "\t\t\t"."'data' => ".'$data,'."\n";
	    if ($f_campos)
	    {
	        $fo .= "\t\t\t"."'campos' => ["."\n";
	        foreach ($f_campos as $kf => $vf)
	        {
	            $fo .= "\t\t\t\t$vf,\n";
	        }
	        $fo .= "\t\t\t"."],\n";
	    }
	    $fo .= "\t\t"."];\n";
	    $fo .= "\t\t".'return $params;'."\n";
	    $fo .= "\t"."}\n\n";
	    
	    return $fo;
	}
	
	public function fr_password()
	{
	    $_2 = readline("Campo de la DB: ");
	    $e = readline("Etiqueta: ");
	    return "['password', 'texto', '$_2', 'E' => '$e']";
	}
	
	public function f_password()
	{
	    $_2 = readline("Campo de la DB: ");
	    return "['password', 'texto', '$_2']";
	}
	
	public function f_hidden()
	{
	    $_1 = readline("Formato del campo (texto, fecha, hora): ");
	    $_2 = readline("Campo de la DB: ");
	    return "['hidden', '$_1', '$_2']";
	}


	public function fr_hidden()
	{
	    $_1 = readline("Formato del campo (texto, fecha, hora): ");
	    $_2 = readline("Campo de la DB: ");
	    $e = readline("Etiqueta: ");
	    return "['hidden', '$_1', '$_2', 'E' => '$e']";
	}
	
	public function f_label()
	{
	    $_1 = readline("Formato del campo (texto, fecha, hora): ");
	    $_2 = readline("Campo de la DB: ");
	    return "['label', '$_1', '$_2']";
	}
	
	public function fr_label()
	{
	    $_1 = readline("Formato del campo (texto, fecha, hora): ");
	    $_2 = readline("Campo de la DB: ");
	    $e = readline("Etiqueta: ");
	    return "['label', '$_1', '$_2', 'E' => '$e']";
	}
	
	public function f_input()
	{
	    $_1 = readline("Formato del campo (texto, fecha, hora): ");
	    $_2 = readline("Campo de la DB: ");
	    return "['input', '$_1', '$_2'/*,'o' => ['campo' => 'val, val'],*/]";
	}
	
	public function fr_input()
	{
	    $_1 = readline("Formato del campo (texto, fecha, hora): ");
	    $_2 = readline("Campo de la DB: ");
	    $e = readline("Etiqueta: ");
	    return "['input', '$_1', '$_2', 'E' => '$e'/*,'o' => ['campo' => 'val, val'],*/]";
	}
	
	public function f_link()
	{
	    $_1 = readline("Formato del campo (texto, fecha, hora): ");
	    $_2 = readline("Campo de la DB: ");
	    $_3 = readline("Controlador al que se dirige el link: ");
	    return "['link', '$_1', '$_2', '$_3', /*'o' => ['campo' => 'val, val'],*/ 'h' => ['val', 'val']]";
	}
	
	public function f_texto()
	{
	    $_1 = readline("Tipo de ventana de texto (texto, editor, editord): ");
	    $_2 = readline("Campo de la DB: ");
	    $e = readline("Etiqueta del campo: ");
	    $l = readline("Número de líneas del campo: ");
	    $cs = readline("Colspan para el campo: ");
	    return "['texto', '$_1', '$_2', 'E' => '$e', 'l' => '$l', 'cs' => '$cs']";
	}
	
	public function fr_texto()
	{
	    return $this->f_texto();
	}
	
	public function f_dropdown_ajax()
	{
	    $_1 = readline("Formato del campo (texto, fecha): ");
	    $_2 = readline("Campo de la DB: ");
	    $_3 = readline("URL a la que dirige la petición ajax: ");
	    $fa = readline("Función JS que se ejecuta: ");
	    return "['dropdown_ajax', '$_1', '$_2', '$_3', /*'o' => ['campo' => 'val, val'],*/ 'op' => 'opciones', 'fa' => '$fa']";
	}
	
	public function f_dropdown_submit()
	{
	    $_1 = readline("Formato del campo (texto, fecha): ");
	    $_2 = readline("Campo de la DB: ");
	    return "['dropdown_submit', '$_1', '$_2', /*'o' => ['campo' => 'val, val'],*/ 'op' => 'opciones']";
	}
	
	public function f_dropdown()
	{
	    $_1 = readline("Formato del campo (texto, fecha): ");
	    $_2 = readline("Campo de la DB: ");
	    return "['dropdown', '$_1', '$_2', /*'o' => ['campo' => 'val, val'],*/ 'op' => 'opciones']";
	}
	
	public function fr_dropdown()
	{
	    $_1 = readline("Formato del campo (texto, fecha): ");
	    $_2 = readline("Campo de la DB: ");
	    $e = readline("Etiqueta del campo: ");
	    return "['dropdown', '$_1', '$_2', /*'o' => ['campo' => 'val, val'],*/ 'op' => 'opciones', 'E' => '$e']";
	}
	
	public function f_button_ajax()
	{
	    $_1 = readline("Nombre del campo (update, insert, delete): ");
	    $_2 = readline("Etiqueta para mostrar en el botón: ");
	    $_3 = readline("URL a la que dirige la petición: ");
	    $fa = readline("Función JS que se invoca: ");
	    $c = readline("Colocación en la tabla (_solo, _antes, _medio, _después): ");
	    return "['button_ajax', '$_1', '$_2', '$_3', /*'o' => ['campo' => 'val, val'],*/ 'fa' => 'opciones', 'c' => '$c']";
	}
	
	public function fr_check()
	{
	    $_1 = readline("Campo de la DB: ");
	    $_2 = readline("Valor que tiene el campo seleccionato (true ó 1): ");
	    $e = readline("Etiqueta del campo: ");
	    return "['check', '$_1', '$_2', 'E' => '$e']";
	}
	
	public function f_check()
	{
	    $_1 = readline("Campo de la DB: ");
	    $_2 = readline("Valor que tiene el campo seleccionato (true ó 1): ");
	    return "['check', '$_1', '$_2']";
	}
}