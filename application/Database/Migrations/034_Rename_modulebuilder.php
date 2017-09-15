<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Rename_modulebuilder extends Migration {
	
	public function up() 
	{
		$this->db->where('name', 'Bonfire.Modulebuilder.View')
				 ->set('name', 'Bonfire.Builder.View')
				 ->update('permissions');
	}
	
	//--------------------------------------------------------------------
	
	public function down() 
	{
		$this->db->where('name', 'Bonfire.Builder.View')
				 ->set('name', 'Bonfire.Modulebuilder.View')
				 ->update('permissions');
	}
	
	//--------------------------------------------------------------------
}