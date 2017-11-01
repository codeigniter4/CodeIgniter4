<?php namespace CodeIgniter\Arrayz;
/**
* Array Manipulations
* Developer - Giri Annamalai M
* Version - 1.0
*/
class Arrayz
{
	private $source;
	private $operator;
	public function __construct($array=[])
	{
		$this->source = [];
		if($this->_chk_arr($array))
		{
			$this->source = $array;
		}
	}

	/*
	* Object to callable conversion
	*/
	public function __invoke($source=[])
	{
		$this->source = $source;
		return $this;
	}

	/*
	* Match and return the array. supports regex
	*/
	public function pluck()
	{	
		$args = func_get_args();
		$search = $args[0];
		$this->intersected = [];
		if($search !='')
		{			
			array_walk_recursive($this->source, function(&$value, &$key) use(&$search){				
				if( preg_match('/'.$search.'/', $key) )
				{
					$this->intersected[][$key] = $value;
				}
			});	
			$this->source = $this->intersected;
		}
		return $this;
	}
	/*
	* To increase performance select+where are combined to work along.
	*/
	public function select_where()
	{
		$args = func_get_args();
		$select = array_map('trim', explode(",", $args[0]));
		$op = $o = [];	
		$cond = $args[1];//Format conditions		
		array_walk($cond, function($v, $k) use(&$o) {
			$key = array_map('trim', explode(" ", $k));
			$key[1] = (isset($key[1]) && $key[1] != "") ? $key[1] : '==';
			$key[2] = $v;
			$o[] = $key;
		});
		 //Filter and select it
		array_filter($this->source, function($src, $key) use ($o, $select, &$op){
			$resp = FALSE;
			foreach ($o as $k => $v) {
				$resp = (isset($src[$v[0]])) ? ($this->_operator_check($src[$v[0]], $v[1], $v[2])) : $resp;
				if($resp==FALSE)  break; //If one condition fails break it. It's not the one, we are searching
			}
			($resp==TRUE) ? ($op[$key] = array_intersect_key($src, array_flip($select))) : FALSE;
			return $resp;
		},ARRAY_FILTER_USE_BOTH);		
		$preserve = isset($args[2]) && $args[2] ? TRUE : FALSE;
		$this->_preserve_keys($op, $preserve);
		return $this;
	}
	
	/*
	* Like SQL Where . Supports operators. @param3 return actual key of element
	*/
	public function where()
	{
		$args = func_get_args();
		$op = [];
		$operator = '=';
		if(is_string($args[0])) //Single where condition
		{
			if (func_num_args() == 3 || func_num_args() == 4)  
			{
				$search_key = $args[0];
				$operator = $args[1];
				$search_value = $args[2];					
			}
			else
			{
			    $search_key = $args[0];
			    $search_value = $args[1];
			}
			$preserve = isset($args[3]) && $args[3] ? TRUE : FALSE;
			$op = array_filter($this->source, function($src) use ($search_key, $search_value, $operator){
				return isset($src[$search_key]) && $this->_operator_check($src[$search_key], $operator, $search_value);			
			},ARRAY_FILTER_USE_BOTH);
			$this->_preserve_keys($op, $preserve);
		}
		/* Support multiple AND Conditions similar to CI DB-where */
		if(is_array($args[0]))
		{
			$cond = $args[0];
			$o = [];
			$preserve = isset($args[1]) && $args[1] ? TRUE : FALSE;
			//Format conditions
			array_walk($cond, function($v, $k) use(&$resp, &$o) {
				$key = array_map('trim', explode(" ", $k));
				$key[1] = (isset($key[1]) && $key[1] != "") ? $key[1] : '='; //Default is =
				$key[2] = $v;
				$o[] = $key;
			});
			$op = array_filter($this->source, function($src) use ($o){
				$resp = FALSE;
				foreach ($o as $k => $v) {
					$resp = (isset($src[$v[0]])) ? ($this->_operator_check($src[$v[0]], $v[1], $v[2])) : $resp;
					if($resp==FALSE)  break; //If one condition fails break it. It's not the one, we are searching
				}
				return $resp;
			},ARRAY_FILTER_USE_BOTH);
			$this->_preserve_keys($op, $preserve);
		}
		return $this;
	}

	/*
	* Like SQL WhereIN . Supports operators.
	*/
	public function whereIn()
	{
		$args = func_get_args();
		$op = [];
		$search_key = $args[0];
		$search_value = $args[1];
		$op = array_filter($this->source, function($src) use ($search_key, $search_value) {
			return (isset($src[$search_key])) && in_array( $src[$search_key], $search_value);
		},ARRAY_FILTER_USE_BOTH);
		$preserve = isset($args[2]) && $args[2] ? TRUE : FALSE;
		$this->_preserve_keys($op, $preserve);//Preserve keys or not		
		return $this;
	}

	/*
	* search and return true. 
	*/
	public function contains()
	{
		$args = func_get_args();
		$isValid = false;
		if ( func_num_args() == 2 ) 
		{			    
			$search_key = $args[0];
			$search_value = $args[1];
		}
		else
		{
			$search_key = '';
		    $search_value = $args[1];			
		}
		//If search value founds, to stop the iteration using try catch method for faster approach
		try {
			  array_walk_recursive($this->source, function(&$value, &$key) use(&$search_key, &$search_value){
		    	if($search_value != ''){
		    		if($search_value == $value && $key == $search_key){
		    			$isThere = true;	
		    		}
		    	}
		    	else
		    	{
		    		if($search_value == $value){
		    			$isThere = true;	
		    		}
		    	}
		    	// If Value Exists
		        if ($isThere) {
		            throw new Exception;
		        } 
		    });
		   }
		   catch(Exception $exception) {
			  $isValid = true;
		   }
		return $this->source = $isValid;
	}	


	/*
	* Converting Multidimensional Array into single array with/without null or empty 
	*/

	public function collapse()
	{
		$args = func_get_args();
		$empty_remove = !empty ($args[0]) ? $args[0] : false ;
		$op = [];			
		array_walk_recursive($this->source, function(&$value, &$key) use(&$op, &$empty_remove){
			if( $empty_remove ){
				if( $value != '' || $value != NULL )
				{
					$op[][$key] = $value;					
				}
			}
			else
			{
				$op[][$key] = $value;
			}								
		});
		$this->source = $op;		
		return $this;		
	}

	/*
	* Converting Two Dimensional Array with lImit offset 
	*/

	public function limit()
	{
		$this->source = array_values($this->source);
		$args = func_get_args();
		$limit = $args[0];
		$offset = !empty ($args[1]) ? $args[1] : 0 ;
		$op = [];
		$cnt = count($this->source);
		if($limit > $cnt )	
		{
			$limit = $cnt;
		}
		$i = 0;
		if( $limit <= 1)
		{
			$op = isset($this->source[$offset]) ? $this->source[$offset] : $op;
		}
		else
		{
			for($i=0; $i<$limit; $i++)
			{
				if(isset($this->source[$offset]))
				{
					$op[] = $this->source[$offset];
					$offset++;
				}
			}
		}
		$this->source = $op;
		return $this;
	}

	/*
	* Select the keys and return only them	
	* @param1: 'id, name, address', must be comma seperated.
	*/
	public function select()
	{
		$args = func_get_args();
		$select = $args[0];
		$op = [];		
		$select = array_map('trim', explode(",", $select));
		if(isset($args[1]) && $args[1]==TRUE) //Flat array if only one return key/value exists
		{
			array_walk($this->source, function(&$value, &$key) use(&$select, &$op){
				$op[] = array_values(array_intersect_key($value, array_flip($select)))[0];
			});
		}
		else
		{
			array_walk($this->source, function(&$value, &$key) use(&$select, &$op){
				$op[] = array_intersect_key($value, array_flip($select));				
			});			
		}
		$this->source = $op;
		return $this;
	}

	/*
	* Group by a key value 
	*/
	public function group_by()
	{
		$args = func_get_args();		
		$grp_by = $args[0];
		$op = [];
		foreach ($this->source as $data) {
		  $grp_val = $data[$grp_by];
		  if (isset($op[$grp_val])) {
		     $op[$grp_val][] = $data;
		  } else {
		     $op[$grp_val] = array($data);
		  }
		}
		$this->source = $op;
		return $this;
	}

	/*
	* Check with operators
	*/
    private function _operator_check($retrieved, $operator , $value)
	{
		switch ($operator) {
		    default:
		    case '=':
		    case '==':  return $retrieved == $value;
		    case '!=':
		    case '<>':  return $retrieved != $value;
		    case '<':   return $retrieved < $value;
		    case '>':   return $retrieved > $value;
		    case '<=':  return $retrieved <= $value;
		    case '>=':  return $retrieved >= $value;
		    case '===': return $retrieved === $value;
		    case '!==': return $retrieved !== $value;
		}
	}

	private function _chk_arr($array)
	{
		if(is_array($array) && count($array) > 0 )
		{
			return true;
		}
	}

	/* Return output as Array */
	public function get()
	{		
		return (empty($this->source)) ? NULL : $this->source;
	}
	
	/* Return output as JSON */
	public function toJson()
	{
		return (empty($this->source)) ? NULL : json_encode($this->source);
	}

	/* Return output as Single Row Array */
	public function get_row()
	{		
		return (is_array($this->source) && !(empty($this->source))) ? array_values($this->source)[0] : NULL;		 
	}
	/* Return array keys */
	public function keys()
	{		
		$this->source = array_keys($this->source);
		return $this;
	}
	
	/* Return array values */
	public function values()
	{		
		$this->source = array_values($this->source);
		return $this;
	}

	/*
	* Like SQL WhereIN . Supports operators.
	*/
	public function whereNotIn()
	{
		$args = func_get_args();
		$op = [];
		$search_key = $args[0];
		$search_value = $args[1];
		$op = array_filter($this->source, function($src) use ($search_key, $search_value) {
			return (isset($src[$search_key])) && !in_array( $src[$search_key], $search_value);
		},ARRAY_FILTER_USE_BOTH);
		$preserve = isset($args[2]) && $args[2] ? TRUE : FALSE;
		$this->_preserve_keys($op, $preserve);//Preserve keys or not		
		return $this;
	}

	/*
	* search the key exists and return true if found.
	*/
	public function has()
	{
		$args = func_get_args();
		$array = $args[0];
		$search_key = $args[1];
		$isValid = false;
		//If search value founds, to stop the iteration using try catch method for faster approach
		try {
			  array_walk_recursive($array, function(&$value, &$key) use(&$search_key){
	    		if($search_key == $key){
	    			$isThere = true;	
	    		}		    	
		    	// If Value Exists
		        if ($isThere) {
		            throw new Exception;
		        } 
		    });
		   }
		   catch(Exception $exception) {
			  $isValid = true;
		   }
	    return $isValid;	 	
	}

	/* Select a key and sum its values. @param1: single key of array to sum */
	public function sum()
	{
		$args = func_get_args();		
		$op = [];
		$key = $args[0];
		$this->select($key, TRUE);		
		$this->source = array_sum($this->source);
		return $this->source;
	}

	public function count()
	{
		return count($this->source);
	}

	private function _preserve_keys($op=[], $preserve=TRUE)
	{
		$this->source = ($preserve==TRUE) ? $op : array_values($op);
	}

	/*
	* Orderby by flat/normal array
	*/
	public function order_by()
	{
		$args = func_get_args();
		$op = [];
		$this->to_order = $this->source;
		$sort_mode = ['asc', 'desc'];
		//func_num_args()==1 ||  func_num_args()==2 && (in_array(strtolower($args[0]), $sort_mode))		
		if(isset($this->source[0]) && is_array($this->source[0])) //Associatove Array
		{	
			$sort_order = ['asc' => SORT_ASC, 'desc' => SORT_DESC];
			$sort_by = $this->select($args[0], TRUE); //Select the key to Sort
			$args[1] = isset($args[1]) ? $args[1] : 'asc';
			array_multisort($sort_by->source, $sort_order[strtolower($args[1])], $this->to_order);		
			$this->source = $this->to_order;
		}
		else
		{
			$args_sort = strtolower($args[0]);			
			$sort_order = ['asc' => 'asort', 'desc' => 'arsort'];
			$sort_order[$args_sort]($this->source);
			$preserve = isset($args[1]) && $args[1] ? TRUE : FALSE;
			$this->_preserve_keys($this->source, $preserve);
		}
		return $this;
	}
	/*
	* Flat Where
	*/
	public function flat_where()
	{
		$args = func_get_args();
		$op = [];
		if(is_string($args[0])) //Single where condition
		{
			$cond = array_map('trim', explode(" ", $args[0]));
			$op = array_filter($this->source, function($src) use ($cond) {								
				return $this->_operator_check($src, $cond[0], $cond[1]);
			});			
			$preserve = isset($args[1]) && $args[1] ? TRUE : FALSE;
			$this->_preserve_keys($op, $preserve);
		}
		return $this;		
	}
	
	/*
	* Similar to Like query in SQL
	*/
	public function like()
	{	
		$args = func_get_args();
		$search_key = $args[0];
		$this->op = [];
		if(is_string($search_key))
		{
			$search_value = $args[1];
			$op = array_filter($this->source, function($src) use ($search_key, $search_value){
					return isset($src[$search_key]) && preg_match('/'.$search_value.'/', $src[$search_key]);
			},ARRAY_FILTER_USE_BOTH);
			$this->source = $op;
		}
		$preserve = isset($args[3]) && $args[3] ? TRUE : FALSE;
		$this->_preserve_keys($op, $preserve);
		return $this;
	}

	/* Select a key and sum its values. @param1: single key of array to sum */
	public function select_sum()
	{
		$args = func_get_args();		
		$op = [];
		$key = $args[0];
		$this->select($key, TRUE);		
		$this->source = array_sum($this->source);
		return $this;
	}

	/* Select the maximum value using the key */
	public function select_max()
	{
		$args = func_get_args();		
		$op = [];
		$key = $args[0];
		$source = $this->source;
		$this->select($key, TRUE);
		$k = (isset($args[1]) && $args[1]) ? array_keys($this->source, max($this->source))[0] : '';
		$this->source = (isset($args[1]) && $args[1]) ? [$k => $source[$k]] : max($this->source);
		return $this;
	}

	/* Select the min value using the key */
	public function select_min()
	{
		$args = func_get_args();		
		$op = [];
		$key = $args[0];
		$source = $this->source;	
		$this->select($key, TRUE);
		$k = (isset($args[1]) && $args[1]) ? array_keys($this->source, min($this->source))[0] : '';
		$this->source = (isset($args[1]) && $args[1]) ? [$k => $source[$k]] : min($this->source);
		return $this;		
	}	

	/* Calculate avg value by key. @param2 is round off numeric */
	public function select_avg()
	{
		$args = func_get_args();		
		$op = [];
		$key = $args[0];		
		$this->select($key, TRUE);
		$this->source = (isset($args[1]) && is_numeric($args[1])) ? round((array_sum($this->source)/count($this->source)), $args[1]) : (array_sum($this->source)/count($this->source));
		return $this;
	}

	/* Select Distinct values*/
	public function distinct()
	{
		$args = func_get_args();		
		$op = [];
		$key = $args[0];
		$source = $this->source;		
		$this->select($key, TRUE);
		$this->source = $s = array_values(array_flip($this->source));
		array_walk($this->source, function(&$value, &$key) use(&$op, &$source){
			$op[] = $source[$value];
		});	
		$this->source = $op;	
		return $this;
	}
		/*
	* reverse the array
	*/
	public function reverse()
	{	
		$args = func_get_args();
		$preserve = isset($args[0]) && $args[0] ? TRUE : FALSE;		
		$this->source = array_reverse($this->source, $preserve);
		return $this;
	}	
}
/* End of the file arrayz.php */
