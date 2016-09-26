<?php

class Kint_Objects_Smarty extends KintObject
{
	public function parse( & $variable )
	{
		if ( !$variable instanceof Smarty
			|| !defined( 'Smarty::SMARTY_VERSION' ) # lower than 3.x
		) return false;


		$this->name = 'object Smarty (v' . substr( Smarty::SMARTY_VERSION, 7 ) . ')'; # trim 'Smarty-'

		$assigned = $globalAssigns = array();
		foreach ( $variable->tpl_vars as $name => $var ) {
			$assigned[ $name ] = $var->value;
		}
		foreach ( Smarty::$global_tpl_vars as $name => $var ) {
			if ( $name === 'SCRIPT_NAME' ) continue;

			$globalAssigns[ $name ] = $var->value;
		}

		return array(
			'Assigned'          => $assigned,
			'Assigned globally' => $globalAssigns,
			'Configuration'     => array(
				'Compiled files stored in' => isset($variable->compile_dir)
					? $variable->compile_dir
					: $variable->getCompileDir(),
			)
		);

	}
}
