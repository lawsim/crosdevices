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
		echo anchor('welcome/install',"First run readme/install") . "<br />";
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
}