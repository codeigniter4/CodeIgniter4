<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Removing_update_perms extends Migration {
	
	public function up() 
	{
		$this->load->model('permissions/permission_model');
		$this->load->model('roles/role_permission_model');
		
		// Bonfire.Update.Manage
		$id = $this->permission_model->find_by('name', 'Bonfire.Update.Manage');
		
		if ($id)
		{
			$this->permission_model->delete($id->permission_id);
			$this->role_permission_model->delete_for_permission($id->permission_id);
		}
		
		// Bonfire.Update.View
		$id = $this->permission_model->find_by('name', 'Bonfire.Update.View');
		
		if ($id)
		{
			$this->permission_model->delete($id->permission_id);
			$this->role_permission_model->delete_for_permission($id->permission_id);
		}
	}
	
	//--------------------------------------------------------------------
	
	public function down() 
	{
		$this->load->model('permissions/permission_model');
		$this->load->model('roles/role_permission_model');
		
		// Bonfire.Update.Manage
		$perm = array(
			'name'			=> 'Bonfire.Update.Manage',
			'description'	=> 'To manage the Bonfire Update.',
			'status'		=> 'active'
		);
		$this->permission_model->insert($perm);
		$this->role_permission_model->assign_to_role('Administrator', 'Bonfire.Update.Manage');
		
		// Bonfire.Update.View
		$perm = array(
			'name'			=> 'Bonfire.Update.View',
			'description'	=> 'To view the Developer Update menu.',
			'status'		=> 'active'
		);
		$this->permission_model->insert($perm);
		$this->role_permission_model->assign_to_role('Administrator', 'Bonfire.Update.View');
	}
	
	//--------------------------------------------------------------------
}