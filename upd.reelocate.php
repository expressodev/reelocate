<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * Reelocate module by Crescendo (info@crescendo.net.nz)
 */

class Reelocate_upd { 

    var $version = '1.0';
	
    function Reelocate_upd() 
    {
		$this->EE =& get_instance();
    }
    
    function install()
	{	
		// register module
		$this->EE->db->insert('modules', array(
			'module_name' => 'Reelocate',
			'module_version' => $this->version,
			'has_cp_backend' => 'y',
			'has_publish_fields' => 'n'));
		
		return TRUE;
    }
	
	function update($current = '')
	{
		return FALSE;
	}
	
    function uninstall()
	{
		$this->EE->db->where('module_name', 'Reelocate');
		$this->EE->db->delete('modules');
		
		return TRUE;
    }
}

/* End of file upd.reelocate.php */