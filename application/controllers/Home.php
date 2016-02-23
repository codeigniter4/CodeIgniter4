<?php

class Home extends \CodeIgniter\Controller {


	public function index()
	{
//		$iterator = new \CodeIgniter\Debug\Iterator();
//
//		$placeholders = [
//			'one' => 1,
//		    'two' => 2,
//		    'three' => 3,
//		    'four' => 4,
//		    'five' => 5,
//		    'six' => 6,
//		    'seven' => 7,
//		    'eight' => 8,
//		    'nine' => 9,
//		    'ten' => 10
//		];
//
//		$string = 'Here is :one :two :three :four :five :six :seven :eight :nine :ten things to replace';
//
//		$iterator->add('str_ireplace', function() use ($placeholders, $string) {
//			foreach ($placeholders as $tag => $pattern)
//			{
//				$string = str_ireplace(':'.$tag, $pattern, $string);
//			}
//		});
//
//		$iterator->add('strtr', function() use ($placeholders, $string) {
//
//			foreach ($placeholders as &$tag => $pattern)
//			{
//				$tag = ':'.$tag;
//			}
//die(var_dump($placeholders));
//			$string = strtr($string, $placeholders);
//		});
//
//		echo $iterator->run(3000);

//		die(var_dump(realpath_cache_size()));

		echo view('welcome_message');
	}

	//--------------------------------------------------------------------

}