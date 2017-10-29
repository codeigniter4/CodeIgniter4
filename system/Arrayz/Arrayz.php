<?php namespace CodeIgniter\Arrayz;
/**
* Array Manipulations for Codeigniter
* All features and examples avail following url:
* https://github.com/giriannamalai/Arrayz/blob/master/README.md
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
	* Object to callable conversion, to chain other methods
	*/
	public function __invoke($source=[])
	{
		$this->source = $source;
		return $this;
	}

	/*
	* Match and return the array. supports regex
	* @param1: search key
	*/
	public function pluck()
	{	
		$args = func_get_args();
		$search = $args[0];
		if($search !='')
		{			
			array_walk_recursive($this->source, function(&$value, &$key) use(&$search){				
				if( preg_match('/^'.$search.'/', $key) )
				{
					$this->intersected[][$key] = $value;
				}
			});	
			$this->source = $this->intersected;			
		}
		return $this;
	}

	/*
	* Similar to codeigniter query builder where
	* Supports operators. @param4 return actual key of element
	*/
	public function where()
	{
		$args = func_get_args();
		$op = [];
		$operator = '=';
		if(is_string($args[0]))
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
			$preserve = isset($args[4]) && $args[4] ? TRUE : FALSE;

			$op = array_filter($this->source, function($src) use ($search_key, $search_value, $operator) {							 
				return $this->_operator_check($src[$search_key], $operator, $search_value);			  	
			},ARRAY_FILTER_USE_BOTH);
			$this->_preserve_keys($op, $preserve);
		}
		/* Support Condition similar to CI Array-where */
		if(is_array($args[0]))
		{
			$cond = $args[0];
			$preserve = isset($args[1]) && $args[1] ? TRUE : FALSE;
			array_walk($this->source, function(&$value, &$key) use(&$op, &$cond, &$preserve){
				$resp = $i = 0;
				array_walk($cond, function($v, $k) use(&$resp, &$value) {
					$k = explode(' ',$k);
					if(isset($k[1]))
					{
						$resp = !($this->_operator_check($value[$k[0]], $k[1], $v)) ? 1 : $resp;						
					}
					else
					{
						$resp = !($this->_operator_check($value[$k[0]], '=', $v)) ? 1 : $resp;
					}
				});
				if($resp==0)
				{
					if($preserve) //Preserve key
					{
						$op[$key] = $value;						
					}
					else
					{
						$op[] = $value;
					}
				}
			});			
			$this->source = $op;
		}
		return $this;
	}

	/*
	* CI Query builder WhereIN. Supports operators.
	*/
	public function whereIn()
	{
		$args = func_get_args();
		$op = [];
		$search_key = $args[0];
		$search_value = $args[1];

		$op = array_filter($this->source, function($src) use ($search_key, $search_value) {							 
			return in_array( $src[$search_key], $search_value);
		},ARRAY_FILTER_USE_BOTH);
		$preserve = isset($args[2]) && $args[2] ? TRUE : FALSE;
		$this->_preserve_keys($op, $preserve);//Preserve keys or not		
		return $this;
	}

	/*
	* Check the key and value exists and return true
	*/
	public function contains(): boolean
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
	* Collapse the array
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
	* Similar to mysql limit offset with Array
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
			if(isset($this->source[$offset]))
			{
				$op = $this->source[$offset];				
			}
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
	* Group by the array by its key & value 
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
	* Check the operators
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

	/* Check its an valid array */
	private function _chk_arr($array)
	{
		if(is_array($array) && count($array) > 0 )
		{
			return true;
		}
	}

	/* Return output */
	public function get(): array
	{
		return $this->source;
	}

	/* Array keys to use with chaining */
	public function keys()
	{		
		$this->source = array_keys($this->source);
		return $this;
	}
	
	/* Array values to use with chaining */
	public function values()
	{		
		$this->source = array_values($this->source);
		return $this;
	}

	/*
	* Similar to mysql WhereIN and CI QB whereIn.
	*/
	public function whereNotIn()
	{
		$args = func_get_args();
		$op = [];
		$search_key = $args[0];
		$search_value = $args[1];

		$op = array_filter($this->source, function($src) use ($search_key, $search_value) {							 
			return !in_array( $src[$search_key], $search_value);
		},ARRAY_FILTER_USE_BOTH);
		$preserve = isset($args[2]) && $args[2] ? TRUE : FALSE;
		$this->_preserve_keys($op, $preserve);//Preserve keys or not		
		return $this;
	}

	/*
	* search the key and return true if found.
	*/
	public function has(): boolean
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

	/* Count the no of elements in array. */
	public function count(): int
	{
		return count($this->source);
	}

	/* To preserve the key while using where. */
	private function _preserve_keys($op=[], $preserve=TRUE)
	{
		if($preserve==TRUE)
		{
		  $this->source = $op;
		}
		else
		{
			$this->source = array_values($op);				
		}
	}

	/*
	* Orderby by Key and order.
	*/
	public function order_by()
	{
		$args = func_get_args();
		$op = [];
		$sort_order = ['asc' => SORT_ASC, 'desc' => SORT_DESC];
		$this->to_order = $this->source;
		$args[1] = isset($args[1]) ? $args[1] : 'asc'; 
		//Select the key
		$sort_by = $this->select($args[0], TRUE);
		//Sort
		array_multisort($sort_by->source, $sort_order[strtolower($args[1])], $this->to_order);
		$this->source =$this->to_order;
		return $this;
	}

	/*
	* SImilar to CI QB like
	*/
	public function like()
	{	
		$args = func_get_args();
		$search_key = $args[0];
		$this->op = [];
		if(is_string($search_key))
		{
			$search_string = $args[1];
			array_walk($this->source, function(&$value, &$key) use(&$search_key, &$search_string){
				$this->matcher($search_string, $value[$search_key], $value);
			});			
		}
		$this->source = $this->op;
		$this->op = [];
		return $this;
	}

	/* Match key value by regex */
	private function matcher($search_string, $search_value, $value)
	{
		if( preg_match('/'.$search_string.'/', $search_value) )
		{
			$this->op[] = $value;
		}
	}

	/* Select a key and sum its values. @param1: single key of array to sum */
	public function select_sum()
	{
		$args = func_get_args();		
		$op = [];
		$key = $args[0];
		$this->select($key, TRUE);		
		$this->source = array_sum($this->source);
		return $this->source;
	}

	/* Select the maximum value using the key */
	public function select_max()
	{
		$args = func_get_args();		
		$op = [];
		$key = $args[0];
		$this->select($key, TRUE);
		if(isset($args[1]) && $args[1])
		{
			$this->source = $source[array_keys($this->source, max($this->source))[0]];			
			return $this;
		}
		else
		{
			$this->source = min($this->source);			
			return $this->source;
		}
	}

	/* Select the min value using the key */
	public function select_min()
	{
		$args = func_get_args();		
		$op = [];
		$key = $args[0];
		$source = $this->source;	
		$this->select($key, TRUE);
		if(isset($args[1]) && $args[1])
		{
			$this->source = $source[array_keys($this->source, min($this->source))[0]];			
			return $this;
		}
		else
		{
			$this->source = min($this->source);
			return $this->source;
		}
	}	

	/* Calculate avg value by key */
	public function select_avg()
	{
		$args = func_get_args();		
		$op = [];
		$key = $args[0];
		$this->select($key, TRUE);
		$this->source = (array_sum($this->source)/count($this->source));
		return $this->source;
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
}
/* End of the file arrayz.php */
