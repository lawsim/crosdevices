<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Initial_schema extends CI_Migration {

	public function up() {
		## Create Table activetimes
		$fields = array(
			'activeid' => array(
				'type' => 'bigint',
				'constraint' => '20',
				'auto_increment' => TRUE,
			),
			'dev_serial' => array(
				'type' => 'varchar',
				'constraint' => '200',
			),
			'date' => array(
				'type' => 'date',
			),
			'activetime' => array(
				'type' => 'int',
				'constraint' => '11',
			),
		);
		$this->dbforge->add_field($fields);

		$this->dbforge->add_key('activeid', TRUE);
		$this->dbforge->add_key(array('dev_serial','date'));

		$this->dbforge->create_table('activetimes');


		## Create Table devices
		$fields = array(
			'deviceid' => array(
				'type' => 'varchar',
				'constraint' => '200',
			),
			'serial' => array(
				'type' => 'varchar',
				'constraint' => '200',
			),
			'school' => array(
				'type' => 'int',
				'constraint' => '11',
			),
			'mac' => array(
				'type' => 'varchar',
				'constraint' => '100',
			),
			'lastsync' => array(
				'type' => 'varchar',
				'constraint' => '100',
			),
			'orgUnitPath' => array(
				'type' => 'varchar',
				'constraint' => '200',
			),
			'osVersion' => array(
				'type' => 'varchar',
				'constraint' => '100',
			),
			'model' => array(
				'type' => 'varchar',
				'constraint' => '200',
			),
			'notes' => array(
				'type' => 'text',
			),
			'annotatedAssetId' => array(
				'type' => 'varchar',
				'constraint' => '200',
			),
			'annotatedLocation' => array(
				'type' => 'varchar',
				'constraint' => '200',
			),
		);
		$this->dbforge->add_field($fields);

		$this->dbforge->add_key('serial', TRUE);
		$this->dbforge->add_key('deviceid');

		$this->dbforge->create_table('devices');


		## Create Table recentusers
		$fields = array(
			'recentid' => array(
				'type' => 'bigint',
				'constraint' => '20',
				'auto_increment' => TRUE,
			),
			'dev_serial' => array(
				'type' => 'varchar',
				'constraint' => '200',
			),
			'email' => array(
				'type' => 'varchar',
				'constraint' => '200',
			),
			'type' => array(
				'type' => 'varchar',
				'constraint' => '100',
			),
		);
		$this->dbforge->add_field($fields);

		$this->dbforge->add_key('recentid', TRUE);
		$this->dbforge->add_key(array('dev_serial','email','type'));

		$this->dbforge->create_table('recentusers');


		## Create Table schools
		$fields = array(
			'school_id' => array(
				'type' => 'int',
				'constraint' => '11',
				'auto_increment' => TRUE,
			),
			'name' => array(
				'type' => 'varchar',
				'constraint' => '250',
			),
			'enrollment' => array(
				'type' => 'int',
				'constraint' => '11',
			),
			'target_devices' => array(
				'type' => 'int',
				'constraint' => '11',
			),
		);
		$this->dbforge->add_field($fields);

		$this->dbforge->add_key('school_id', TRUE);

		$this->dbforge->create_table('schools');
		
		$data = array(
			"school_id" => '1',
			"name" => 'Non-Student',
			"enrollment" => '0',
			"target_devices" => '0',
		);
		
		$this->db->insert('schools', $data);


		## Create Table static_values
		$fields = array(
			'static_id' => array(
				'type' => 'varchar',
				'constraint' => '50',
			),
			'value' => array(
				'type' => 'varchar',
				'constraint' => '250',
			),
		);
		$this->dbforge->add_field($fields);

		$this->dbforge->add_key('static_id', TRUE);

		$this->dbforge->create_table('static_values');

		$data = array(
			"static_id" => 'last_updated',
			"value" => '0000-00-00 00:00:00',
		);
		
		$this->db->insert('static_values', $data);

	}

	public function down()	{
		### Drop table activetimes ##
		$this->dbforge->drop_table("activetimes", TRUE);

		### Drop table devices ##
		$this->dbforge->drop_table("devices", TRUE);

		### Drop table recentusers ##
		$this->dbforge->drop_table("recentusers", TRUE);

		### Drop table schools ##
		$this->dbforge->drop_table("schools", TRUE);

		### Drop table static_values ##
		$this->dbforge->drop_table("static_values", TRUE);


	}
}