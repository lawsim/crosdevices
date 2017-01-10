<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class device_activity_model extends CI_Model {
	function __construct()
	{
		parent::__construct();
	}
	
	// gapps db queries
	function get_enrollment_by_school()
	{
		$gapps = $this->load->database('gapps', TRUE);
		
        $gapps->select('school, COUNT(permid) as count');
        $gapps->from('sis_students s');
        $gapps->where('s.date_exited','0000-00-00 00:00:00');
        $gapps->group_by('s.school');
		
		$gapps->order_by('s.school');
        
        $q = $gapps->get();
        
        return $q;
	}
	
	function get_student_counts_by_grade_school($gradefilter)
    {
        $gapps = $this->load->database('gapps', TRUE);
		
		

        // $synergydb->select('TOP 100 *');
        $gapps->select('school, grade, COUNT(*) as count');
        $gapps->from('sis_students s');
        $gapps->where_in('s.grade',$gradefilter);
        $gapps->where('s.date_exited','0000-00-00 00:00:00');
        $gapps->group_by('s.grade');
        $gapps->group_by('s.school');
		
		$gapps->order_by('s.school');
        $gapps->order_by('s.grade');
        
        $q = $gapps->get();
        
        return $q;
    }
	
	function get_section_summary_by_grades($gradefilter)
    {
        $gapps = $this->load->database('gapps', TRUE);

        // $synergydb->select('TOP 100 *');
        $gapps->select('schoolcode, gradehigh, COUNT(*) as count');
        $gapps->from('sis_sections s');
        $gapps->where_in('s.gradehigh',$gradefilter);
        // $gapps->where('s.date_exited','0000-00-00 00:00:00');
        $gapps->group_by('s.schoolcode');
        $gapps->group_by('s.gradehigh');
		
		$gapps->order_by('s.schoolcode');
        $gapps->order_by('s.gradehigh');
        
        $q = $gapps->get();
        
        return $q;
    }
	
	// reg queries
	function add_school($school_name)
	{
		$data = array(
			"name" => $school_name,
			"enrollment" => '0',
			"target_devices" => '0',
		);
		
		$this->db->insert('schools', $data);
	}
	
	function update_school_device_targets($school_id, $target_devices)
	{
		$data = array(
			'target_devices'		=> $target_devices
		);
		
		$this->db->where('school_id', $school_id);
		$this->db->update('schools', $data);
	}
	
	function update_school_enrollment($school_id, $enrollment)
	{
		$data = array(
			'enrollment'		=> $enrollment
		);
		
		$this->db->where('school_id', $school_id);
		$this->db->update('schools', $data);
	}
	
	function update_last_updated()
	{
		$data = array(
			'value'		=> date('Y-m-d H:m:s')
		);
		
		$this->db->where('static_id', 'last_updated');
		$this->db->update('static_values', $data);
	}
	
	function get_static_value($static_id = false)
	{
		$this->db->select();
		
		$this->db->from('static_values');
		
		$static_id ? $this->db->where('static_id', $static_id) : false;
		
		$q = $this->db->get();
		
		return $q;
	}
	
	function get_school_info($school)
	{
		$this->db->select();
		
		$this->db->from('schools');
		
		$this->db->where('school_id', $school);
		
		$q = $this->db->get();
		
		return $q->row();
	}
	
	function get_devices_unused_in_days($school = false, $unused_in_last)
	{
		$this->db->select();
		
		$this->db->from('devices d');
		$this->db->join('(SELECT dev_serial, MAX( DATE ) AS rct_date
			FROM  `activetimes` 
			GROUP BY dev_serial
			ORDER BY dev_serial DESC) AS ds','d.serial = ds.dev_serial','left');
		$this->db->join('schools s','d.school = s.school_id');
		
		$school ? $this->db->where('d.school', $school) : $this->db->where('d.school <>', "0");
		$this->db->where('ds.rct_date <', date("Y-m-d", strtotime('-' . $unused_in_last . ' days')));
		
		$this->db->order_by('s.name','ASC');
		$this->db->order_by('ds.rct_date','ASC');
		
		$q = $this->db->get();
		
		return $q;
	}
	
	function get_devices($school = false, $iscart = false, $location = false, $unused_in_last = false)
	{
		$this->db->select();
		
		$this->db->from('devices');
		
		$school ? $this->db->where('school', $school) : false;
		$iscart ? $this->db->like('orgUnitPath', 'Carts') : false;
		$location !== false ? $this->db->where('annotatedLocation', $location) : false;
		
		$q = $this->db->get();
		
		return $q;
	}
	
	function get_device_last_activity($serial)
	{
		$this->db->select();
		
		$this->db->from('activetimes');
		$this->db->where('dev_serial', $serial);
		$this->db->limit(1);
		$this->db->order_by('date', "desc");
		
		$q = $this->db->get();
		
		return $q;
	}
	
	function get_device_time_used($serial, $since)
	{
		$this->db->select("dev_serial, sum(activetime) as timeused");
		
		$this->db->from('activetimes');
		$this->db->where('dev_serial', $serial);
		$this->db->where('date >=', $since);
		$this->db->group_by('dev_serial');
		
		$q = $this->db->get();
		
		return $q;
		// SELECT dev_serial, sum(activetime) as active
// FROM `activetimes` 
// WHERE date > '2016-03-07'
// GROUP BY dev_serial
	}
	
	// cart specific info page
	
	function get_cart_days_used($school, $location)
	{
		$this->db->select("a.date");
		
		$this->db->from('activetimes a');
		$this->db->join('devices d','a.dev_serial = d.serial');
		$this->db->where('d.school', $school);
		$this->db->where('d.annotatedLocation', $location);
		$this->db->group_by('a.date');
		
		$this->db->order_by('a.date','DESC');
		
		$q = $this->db->get();
		
		return $q;
	}
	
	function get_cart_usage_on_date($school, $location, $date)
	{
		$this->db->select("d.annotatedLocation, (AVG(a.activetime)/(1000*60)) as average_minutes_used, COUNT(a.activeid) as count_used_on_day");
		
		$this->db->from('activetimes a');
		$this->db->join('devices d','a.dev_serial = d.serial');
		$this->db->where('a.date', $date);
		$this->db->where('d.school', $school);
		$this->db->where('d.annotatedLocation', $location);
		$this->db->group_by('d.annotatedLocation');
		
		$q = $this->db->get();
		
		return $q;
	}
	
	function get_computer_usage_stats($serial, $since = false)
	{
		$this->db->select("d.serial, (AVG(a.activetime)/(1000*60)) as average_minutes_used");
		
		$this->db->from('activetimes a');
		$this->db->join('devices d','a.dev_serial = d.serial');
		$since ? $this->db->where('a.date >=', $since) : false;
		$this->db->where('d.serial', $serial);
		$this->db->group_by('d.serial');
		
		$q = $this->db->get();
		
		return $q;
	}
	
	// location funcs
	function get_avg_cart_time_per_device($school, $since = false)
	{
		$this->db->select("d.annotatedLocation, (AVG(a.activetime)/(1000*60)) as average_minutes_per_day");
		
		$this->db->from('activetimes a');
		$this->db->join('devices d','a.dev_serial = d.serial');
		$since ? $this->db->where('a.date >=', $since) : false;
		$this->db->where('d.school', $school);
		$this->db->group_by('d.annotatedLocation');
		
		$q = $this->db->get();
		
		return $q;
	}
	
	function get_avg_school_time_per_device($from = false, $to = false)
	{
		$this->db->select("d.school, (AVG(a.activetime)/(1000*60)) as average_minutes_per_day");
		
		$this->db->from('activetimes a');
		$this->db->join('devices d','a.dev_serial = d.serial');
		$from ? $this->db->where('a.date >=', $from) : false;
		$to ? $this->db->where('a.date <=', $to) : false;
		$this->db->group_by('d.school');
		
		$q = $this->db->get();
		
		return $q;
	}
	
	function get_total_minutes_range($from = false, $to = false)
	{
		$this->db->select("d.school, s.name, a.date, (SUM(a.activetime)/(1000*60)) as total_minutes");
		
		$this->db->from('activetimes a');
		$this->db->join('devices d','a.dev_serial = d.serial');
		$this->db->join('schools s','d.school = s.school_id');
		
		$from ? $this->db->where('a.date >=', $from) : false;
		$to ? $this->db->where('a.date <=', $to) : false;
		
		$this->db->group_by('d.school');
		
		$q = $this->db->get();
		
		return $q;
	}
	
	function get_total_minutes($from = false, $to = false)
	{
		$this->db->select("d.school, s.name, a.date, (SUM(a.activetime)/(1000*60)) as total_minutes");
		
		$this->db->from('activetimes a');
		$this->db->join('devices d','a.dev_serial = d.serial');
		$this->db->join('schools s','d.school = s.school_id');
		
		$from ? $this->db->where('a.date >=', $from) : false;
		$to ? $this->db->where('a.date <=', $to) : false;
		
		$this->db->where('d.school != 1');
		
		$this->db->group_by('d.school');
		$this->db->group_by('a.date');
		
		$q = $this->db->get();
		
		return $q;
	}
	
	function get_total_minutes_by_location($school, $since = false)
	{
		$this->db->select("d.annotatedLocation, d.school, s.name, a.date, (SUM(a.activetime)/(1000*60)) as total_minutes");
		
		$this->db->from('activetimes a');
		$this->db->join('devices d','a.dev_serial = d.serial');
		$this->db->join('schools s','d.school = s.school_id');
		
		$since ? $this->db->where('a.date >=', $since) : false;
		$this->db->where('d.school', $school);
		
		$this->db->group_by('d.annotatedLocation');
		$this->db->group_by('a.date');
		
		$q = $this->db->get();
		
		return $q;
	}
	
	function get_schools()
	{
		$this->db->select();
		
		$this->db->from('schools s');
		$q = $this->db->get();
		
		return $q;
	}
	
	function add_device($deviceid, $serial, $school, $mac, $lastsync, $orgUnitPath, $osVersion, $model, $notes, $annotatedAssetId, $annotatedLocation)
	{
		$sql = "INSERT INTO devices(deviceid,serial,school,mac,lastsync,orgUnitPath,osVersion,model,notes,annotatedAssetId,annotatedLocation)
				VALUES(%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)
				ON DUPLICATE KEY UPDATE
					deviceid=%s, school=%s, mac=%s, lastsync=%s, orgUnitPath=%s, osVersion=%s, model=%s, notes=%s, annotatedAssetId=%s, annotatedLocation=%s";
		$sql = sprintf($sql,
			$this->db->escape($deviceid),
			$this->db->escape($serial),
			$this->db->escape($school),
			$this->db->escape($mac),
			$this->db->escape($lastsync),
			$this->db->escape($orgUnitPath),
			$this->db->escape($osVersion),
			$this->db->escape($model),
			$this->db->escape($notes),
			$this->db->escape($annotatedAssetId),
			$this->db->escape($annotatedLocation),
			$this->db->escape($deviceid),
			$this->db->escape($school),
			$this->db->escape($mac),
			$this->db->escape($lastsync),
			$this->db->escape($orgUnitPath),
			$this->db->escape($osVersion),
			$this->db->escape($model),
			$this->db->escape($notes),
			$this->db->escape($annotatedAssetId),
			$this->db->escape($annotatedLocation)
		);
		
		// echo $sql;
		
		return $this->db->query($sql);
	}
	
	function add_activetime($serial, $date, $time)
	{
		$sql = "INSERT INTO activetimes(dev_serial,date,activetime)
				VALUES(%s,%s,%s)
				ON DUPLICATE KEY UPDATE
					date=%s,activetime=%s";
		$sql = sprintf($sql,
			$this->db->escape($serial),
			$this->db->escape($date),
			$this->db->escape($time),
			$this->db->escape($date),
			$this->db->escape($time)
		);
		
		// echo $sql;
		
		return $this->db->query($sql);
	}
	
	function add_recentuser($serial, $email, $type)
	{
		$sql = "INSERT INTO recentusers(dev_serial,email,type)
				VALUES(%s,%s,%s)
				ON DUPLICATE KEY UPDATE
					recentid=recentid";
		$sql = sprintf($sql,
			$this->db->escape($serial),
			$this->db->escape($email),
			$this->db->escape($type)
		);
		
		// echo $sql;
		
		return $this->db->query($sql);
	}
}