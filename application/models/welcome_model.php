<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Welcome_model extends CI_Model {
	function __construct()
	{
		parent::__construct();
	}
	
	function get_schools()
	{
		$this->db->select();
		
		$this->db->from('schools s');
		$q = $this->db->get();
		
		return $q;
	}
	
	function update_school_enrollment($schoolid, $enrollment)
	{
		$data = array(
			'enrollment'	=> $enrollment
		);
		
		$this->db->where('school_id', $schoolid);
		$this->db->update('schools', $data);
	}
	
	function update_target_devices($schoolid, $target_devices)
	{
		$data = array(
			'target_devices'	=> $target_devices
		);
		
		$this->db->where('school_id', $schoolid);
		$this->db->update('schools', $data);
	}
}