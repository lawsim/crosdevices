<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {
	function __construct()
	{
		parent::__construct();
		
		$this->load->model('welcome_model');
		
		set_time_limit(5*60);
		
		$this->load->helper('url');
	}
	
	public function index()
	{
		echo "<h2>First run/installation</h2>";
		echo anchor('welcome/install',"First run readme/install") . "<br />";
		echo anchor('welcome/update_schools',"Update enrollment and device targets for schools (do this after importing)") . "<br />";
		echo "<br /><br />";
		
		echo anchor('device_activity',"Proceed to device activity after install") . "<br />";
	}
	
	public function install()
	{
		$dir = getcwd();
		$file = $dir . "\\" . "README.md";
		
		$out = file_get_contents($file);
		
		echo "<pre>";
		echo $out;
		
		echo "\n\n";
		echo anchor("welcome/install_run", "Run database install");
	}
	
	public function install_run()
	{
		$this->load->library('migration');

		if ($this->migration->current() === FALSE)
		{
			show_error($this->migration->error_string());
		}
		
		echo "<pre>";
		echo "Upgraded to current schema on DB";
		print_r($this->migration->find_migrations());
	}
	
	public function update_schools()
	{
		$this->load->helper('form');
		$data['schools'] = $this->welcome_model->get_schools();
		
		$data['main_content'] = "update_schools";
		$this->load->view('includes/template',$data);
	}
	
	public function update_schools_submit()
	{
		echo "<pre>";
		
		$post_values = $this->input->post();
		foreach($post_values as $key => $value)
		{
			if($key == "mysubmit")
				continue;
			
			preg_match("/(\d+)_(.+)/", $key, $matches);
			
			// print_r($matches);
			
			$school_id = $matches[1];
			$type = $matches[2];
			
			if($type == "enrollment")
			{
				$this->welcome_model->update_school_enrollment($school_id, $value);
			}
			else if($type == "target_devices")
			{
				$this->welcome_model->update_target_devices($school_id, $value);
			}
		}
		
		echo "Updated school enrollments and target_devices";
	}
}