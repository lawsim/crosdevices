<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Device_activity extends CI_Controller {
	function __construct()
	{
		parent::__construct();
		
		$this->load->model('device_activity_model');
		
		set_time_limit(5*60);
		
		$this->load->helper('url');
		
		$this->build_schools();
	}
	
	public function index()
	{
		echo anchor('device_activity/get_device_activity',"Device activity") . "<br />";
		
		echo "<br /><br />";
		echo anchor_popup('device_activity/parse_crosdev',"Import devices from crosdevices.csv") . "<br />";
		echo anchor('device_activity/print_crosdev',"Print devices") . "<br />";
	}
	
	public function print_crosdev()
	{
		$devices = $this->device_activity_model->get_devices();
		
		header("Content-type: application/csv");
		header("Content-Disposition: attachment; filename=workshop_summary.csv");
		header("Pragma: no-cache");
		header("Expires: 0");
		
		$header = $devices->row_array();
		end($header);
		$lastKey = key($header);
		foreach($header as $key=>$value)
		{
			echo '"' . $key . '"';
			if($key === $lastKey)
				echo "\n";
			else
				echo ",";
		}
		
		// echo "<pre>";
		$count = 0;
		foreach($devices->result_array() as $row)
		{
			// echo $count++;
			// print_r($row);
			end($row);
			$lastKey = key($row);
			foreach($row as $key=>$value)
			{
				echo '"' . $value . '"';
				if($key === $lastKey)
					echo "\n";
				else
					echo ",";
			}
		}
	}
	
	public function parse_crosdev()
	{
		ini_set('display_errors', 1);
		ini_set('memory_limit','512M');
		set_time_limit(20*60);
		echo "<pre>";
		
		/*
		// -- Only if auto update enrollment info and device counts
		// school code switch
		$school_switch = array(
			65 => 50
		);
		
		// update enrollment info
		$enrollment_info = $this->device_activity_model->get_enrollment_by_school();
		
		foreach($enrollment_info->result() as $row)
		{
			// print_r($row);
			$this->device_activity_model->update_school_enrollment($row->school, $row->count);
		}
		
		// die();
		
		// device counts
		$numbers = array();
		$numbers_1to1 = array();
		$numbers_pods = array();

		// 1 to 1 pull
		$grades_1_to_1 = array("03","04","05","06","07","08","09","10","11","12");
		$student_counts = $this->device_activity_model->get_student_counts_by_grade_school($grades_1_to_1);
		
		foreach($student_counts->result() as $row)
		{
			if(array_key_exists($row->school, $school_switch))
				$school = $school_switch[$row->school];
			else
				$school = $row->school;
			
			// print_r($row);
			if(!isset($numbers[$school]))
				$numbers[$school] = 0;
			$numbers[$school]+= $row->count;
		}
		// print_r($numbers);
		
		// pod of 8 per
		$grades_8_per = array("TK","00","01","02");
		$student_counts = $this->device_activity_model->get_section_summary_by_grades($grades_8_per);
		foreach($student_counts->result() as $row)
		{
			if(array_key_exists($row->schoolcode, $school_switch))
				$school = $school_switch[$row->schoolcode];
			else
				$school = $row->schoolcode;
			
			// print_r($row);
			if(!isset($numbers[$school]))
				$numbers[$school] = 0;
			$numbers[$school] += ($row->count * 8);
			
			$cart_per_grade_level = round($row->count/3,0);
			$numbers[$school]+= $cart_per_grade_level*28;
		}
		// print_r($numbers);
		
		foreach($numbers as $schid => $target)
		{
			$this->device_activity_model->update_school_device_targets($schid, $target);
		}
		
		// die();
		*/
		
		
		
		$dir = getcwd();
		$dir = $dir . "\\csv_files\\";
		
		$file = $dir . "crosdevices.csv";
		
		$devices = array();
		$count = 0;
		if (($handle = fopen($file, "r")) !== FALSE)
		{
			$header = fgetcsv($handle, 100000, ",");
			while (($data = fgetcsv($handle, 100000, ",")) !== FALSE)
			{
				// see if data is header length
				if(count($header) != count($data))
				{
					// echo "not header length\n";
					continue;
				}
				else
				{
					// echo "is right\n";
				}
				
				// parse school from OU
				preg_match("/\/" . ROOT_SCHOOL_ORG . "\/([^\/]+)/", $data[array_search("orgUnitPath", $header)], $matches);
				if(isset($matches[1]))
				{
					$school = $matches[1];
					if(array_key_exists($matches[1],$this->schools))
						$school = $this->schools[$matches[1]];
					else
					{
						// school doesn't exist add it and re-build the schools array
						$this->device_activity_model->add_school($matches[1]);
						$this->build_schools();
						
						if(array_key_exists($matches[1],$this->schools))
							$school = $this->schools[$matches[1]];
						else
						{
							$school = 1;
						}
					}
				}
				else
					$school = 1;
				
				$devices[$data[array_search("serialNumber", $header)]] = array(
					"deviceId" => $data[array_search("deviceId", $header)],
					"serialNumber" => $data[array_search("serialNumber", $header)],
					"school" => $school,
					"macAddress" => $data[array_search("macAddress", $header)],
					"lastSync" => $data[array_search("lastSync", $header)],
					"orgUnitPath" => $data[array_search("orgUnitPath", $header)],
					"osVersion" => $data[array_search("osVersion", $header)],
					"model" => $data[array_search("model", $header)],
					"notes" => $data[array_search("notes", $header)],
					"annotatedAssetId" => $data[array_search("annotatedAssetId", $header)],
					"annotatedLocation" => $data[array_search("annotatedLocation", $header)],
					"activeTimeRanges" => array(),
					"recentUsers" => array()
				);
				
				// get recent users and activetimeranges
				foreach($header as $key => $field)
				{
					preg_match("/([a-zA-Z]+)\.(\d+)\.(.+)/", $field, $matches);
					if(count($matches) == 4)
					{
						if(isset($data[array_search($matches[0], $header)]) && $data[array_search($matches[0], $header)] != "")
							$devices[$data[array_search("serialNumber", $header)]][$matches[1]][$matches[2]][$matches[3]] = $data[array_search($matches[0], $header)];
					}
						// print_r($matches);
				}
				
				// $count++;
				// if($count > 500)
					// break;
			}
			fclose($handle);
		}
		
		// print_r($header);
		// echo "Count of devices: " . count($devices) . "\n";
		// print_r($devices);
		// return;
		
		
		foreach($devices as $device)
		{
			$this->device_activity_model->add_device($device["deviceId"], $device["serialNumber"], $device["school"], $device["macAddress"], $device["lastSync"], $device["orgUnitPath"], $device["osVersion"], $device["model"], $device["notes"], $device["annotatedAssetId"], $device["annotatedLocation"]);

			// echo "Serial: " . $device["serialNumber"] . "\n";
			// echo "MAC: " . $device["macAddress"] . "\n";
			// echo "Last Sync: " . $device["lastSync"] . "\n";
			
			foreach($device["activeTimeRanges"] as $activetime)
			{
				// echo $activetime["date"];
				// echo $activetime["activeTime"];
				
				$this->device_activity_model->add_activetime($device["serialNumber"], $activetime["date"], $activetime["activeTime"]);
			}
			
			foreach($device["recentUsers"] as $user)
			{
				if(isset($user["email"]))
				{
					// echo $user["email"];
					// echo $user["type"];
					
					$this->device_activity_model->add_recentuser($device["serialNumber"], $user["email"], $user["type"]);
				}
			}
			
			// echo "Added device " . $device["serialNumber"] . "\n";
		}
		
		$this->device_activity_model->update_last_updated();
		
		echo "Finished";
	}
	
	public function get_device_activity()
	{
		// echo "<pre>";
		
		$data['last_updated'] = $this->device_activity_model->get_static_value('last_updated')->row();
		
		$devarr = array();
		
		$devices = $this->device_activity_model->get_devices();
		foreach($devices->result() as $dev)
		{
			$devarr[$dev->serial] = array(
				"school" => $dev->school
			);
			
			$devactivity = $this->device_activity_model->get_device_last_activity($dev->serial);
			if($devactivity->num_rows() > 0)
			{
				$devarr[$dev->serial]["activity"] = $devactivity->row()->date;
			}
			else
			{
				$devarr[$dev->serial]["activity"] = "2012-01-01";
			}
		}
		
		// pull school enrollment
		$data['schooldata'] = array();
		$sd = $this->device_activity_model->get_schools();
		foreach($sd->result() as $s)
		{
			$data['schooldata'][$s->school_id] = array(
				"enrollment" => $s->enrollment,
				"target_devices" => $s->target_devices,
			);
		}
		
		$data['schoolarr'] = array();
		foreach($this->schools as $name => $school)
		{
			$data['schoolarr'][$school] = array(
				"name" => $name,
				"enrollment" => $data['schooldata'][$school]["enrollment"],
				"target_devices" => $data['schooldata'][$school]["target_devices"],
				"total" => 0,
				"last30" => 0,
				"last60" => 0,
				"older" => 0,
				"averageperday_last30" => 0,
				"averageperday_total" => 0
			);
		}
		
		foreach($devarr as $dev)
		{
			$data['schoolarr'][$dev["school"]]["total"]++;
			
			if(strtotime($dev["activity"]) > (time()-(60*60*24*30)))
			{
				$data['schoolarr'][$dev["school"]]["last30"]++;
			}
			elseif(strtotime($dev["activity"]) > (time()-(60*60*24*60)))
			{
				$data['schoolarr'][$dev["school"]]["last60"]++;
			}
			else
			{
				$data['schoolarr'][$dev["school"]]["older"]++;
			}
		}
		
		$avg_school_use = $this->device_activity_model->get_avg_school_time_per_device();
		$avg_school_use30 = $this->device_activity_model->get_avg_school_time_per_device(date("Y-m-d",strtotime('-30 days')));
		foreach($avg_school_use->result() as $use)
		{
			$data['schoolarr'][$use->school]["averageperday_total"] = $use->average_minutes_per_day;
		}
		foreach($avg_school_use30->result() as $use)
		{
			$data['schoolarr'][$use->school]["averageperday_last30"] = $use->average_minutes_per_day;
		}
		
		$begin = new DateTime( '2016-02-22' );
		$end = new DateTime( '2016-06-10' );

		$interval = DateInterval::createFromDateString('1 week');
		$period = new DatePeriod($begin, $interval, $end);

		$first = true;
		$lastperiod = $begin;
		foreach ( $period as $dt )
		{
			if($first)
			{
				$first = false;
				continue;
			}
			// echo $lastperiod->format( "Y-m-d" );
			// echo " to ";
			// echo $dt->format( "Y-m-d" );
			// echo "<br />\n";
			$avg_in_period = $this->device_activity_model->get_total_minutes_range($lastperiod->format( "Y-m-d" ),$dt->format( "Y-m-d" ));

			foreach($avg_in_period->result() as $use)
			{
				// $data['schoolarr'][$use->school]["average_periods"][$lastperiod->format( "Y-m-d" )] = $use->average_minutes_per_day;
				$data['schoolarr'][$use->school]["average_periods"][$lastperiod->format( "Y-m-d" )] = $use->total_minutes;
			}
			$lastperiod = $dt;
		}
		// print_r($data['schoolarr']);
		
		
		
		// get minutes per day per school for last 14 days
		$data['minarr'] = array();
		$data['avgminarr'] = array();
		$data['totalminutes'] = $this->device_activity_model->get_total_minutes(date("Y-m-d",strtotime('-14 days')));
		foreach($data['totalminutes']->result() as $site)
		{
			if(strtotime($site->date) > time())
				continue;
			
			if($site->school == 0)
				continue;
			
			if($this->isWeekend($site->date))
				continue;
			
			$data['minarr'][$site->date][$site->school] = array(
				"schoolname" => $site->name,
				"minutes" => round($site->total_minutes)
			);
			
			$data['avgminarr'][$site->date][$site->school] = array(
				"schoolname" => $site->name,
				"aminutes" => round($site->total_minutes / $data['schooldata'][$site->school]["enrollment"])
			);
		}
		
		
				
		$data['schools'] = $this->schools;
		
		// -- debug
		// echo "<pre>";
		// print_r($data['schoolarr']);
		// print_r($data['minarr']);
		// return;
		
		$data['main_content'] = "get_device_activity";
		$this->load->view('includes/template',$data);
	}
	
	public function get_school_device_activity()
	{
		$data['last_updated'] = $this->device_activity_model->get_static_value('last_updated')->row();
		$schoolid = $this->input->get("schoolid");
		$data['school_info'] = $this->device_activity_model->get_school_info($schoolid);
		
		// build devices and locations arrays
		$devarr = array();
		$data['locations'] = array();
		$devices = $this->device_activity_model->get_devices($schoolid, true);
		foreach($devices->result() as $dev)
		{
			// build array of carts
			$data['locations'][$dev->annotatedLocation] = array();
			
			$devarr[$dev->serial] = array(
				"location" => $dev->annotatedLocation
			);
			
			$devactivity = $this->device_activity_model->get_device_last_activity($dev->serial);
			if($devactivity->num_rows() > 0)
			{
				$devarr[$dev->serial]["activity"] = $devactivity->row()->date;
			}
			else
			{
				$devarr[$dev->serial]["activity"] = "2012-01-01";
			}
		}
		
		$i = 1;
		foreach($data['locations'] as $cartname => &$cartarr)
		{
			// if($cartname == '')
			// {
				// $cartname = "Unassigned";
				// $data['locations']
			// }
			
			
			$cartarr = array(
				"locid" => $i,
				"name" => $cartname,
				"total" => 0,
				"last30" => 0,
				"last60" => 0,
				"older" => 0,
				"averageperday_last30" => 0,
				"averageperday_total" => 0
			);
			
			$i++;
		}
		
		
		
		foreach($devarr as $dev)
		{
			$data['locations'][$dev["location"]]["total"]++;
			
			if(strtotime($dev["activity"]) > (time()-(60*60*24*30)))
			{
				$data['locations'][$dev["location"]]["last30"]++;
			}
			elseif(strtotime($dev["activity"]) > (time()-(60*60*24*60)))
			{
				$data['locations'][$dev["location"]]["last60"]++;
			}
			else
			{
				$data['locations'][$dev["location"]]["older"]++;
			}
		}
		
		
		
		$avg_school_use = $this->device_activity_model->get_avg_cart_time_per_device($schoolid);
		$avg_school_use30 = $this->device_activity_model->get_avg_cart_time_per_device($schoolid, date("Y-m-d",strtotime('-30 days')));
		foreach($avg_school_use->result() as $use)
		{
			$data['locations'][$use->annotatedLocation]["averageperday_total"] = $use->average_minutes_per_day;
		}
		foreach($avg_school_use30->result() as $use)
		{
			$data['locations'][$use->annotatedLocation]["averageperday_last30"] = $use->average_minutes_per_day;
		}
		
		// echo "<pre>";
		// print_r($data['locations']);
		// return;
		
		// get minutes per day per location for last 14 days
		$data['minarr'] = array();
		$data['avgminarr'] = array();
		$data['totalminutes'] = $this->device_activity_model->get_total_minutes_by_location($schoolid, date("Y-m-d",strtotime('-14 days')));
		foreach($data['totalminutes']->result() as $loc)
		{
			if(strtotime($loc->date) > time())
				continue;
			
			if($loc->school == 0)
				continue;
			
			if($this->isWeekend($loc->date))
				continue;
			
			$data['minarr'][$loc->date][$loc->annotatedLocation] = array(
				"locid" => $data['locations'][$loc->annotatedLocation]["locid"],
				"schoolname" => $loc->name,
				"minutes" => round($loc->total_minutes)
			);
			
			$data['avgminarr'][$loc->date][$loc->annotatedLocation] = array(
				"locid" => $data['locations'][$loc->annotatedLocation]["locid"],
				"schoolname" => $loc->name,
				"minutes" => round($loc->total_minutes / $data['locations'][$loc->annotatedLocation]['total'])
			);
		}
		
		// echo "<pre>";
		// print_r($data['minarr']);
		// return;
		
		$data['main_content'] = "get_school_device_activity";
		$this->load->view('includes/template',$data);
	}
	
	public function get_location_activity()
	{
		$data['last_updated'] = $this->device_activity_model->get_static_value('last_updated')->row();
		$schoolid = $this->input->get("schoolid");
		$location = $this->input->get("location");
		if($location == "")
			$data['location'] = "Unassigned";
		else
			$data['location'] = $location;
		$data['school_info'] = $this->device_activity_model->get_school_info($schoolid);
		
		// build devices and locations arrays
		$devarr = array();
		$devices = $this->device_activity_model->get_devices($schoolid, true, $location);
		// echo $this->db->last_query();
		foreach($devices->result() as $dev)
		{
			$avguse_last30 = $this->device_activity_model->get_computer_usage_stats($dev->serial, date("Y-m-d",strtotime('-30 days')))->row();
			
			$devarr[$dev->serial] = array(
				"serial" => $dev->serial,
				"asset" => $dev->annotatedAssetId,
				"model" => $dev->model,
				"osVersion" => $dev->osVersion,
				"notes" => $dev->notes,
				"annotatedLocation" => $dev->annotatedLocation,
			);
			
			if($avguse_last30)
				$devarr[$dev->serial]["uselast30"] = $avguse_last30->average_minutes_used;
			else
				$devarr[$dev->serial]["uselast30"] = 0;
			
			
			
			$devactivity = $this->device_activity_model->get_device_last_activity($dev->serial);
			if($devactivity->num_rows() > 0)
			{
				$devarr[$dev->serial]["last_activity"] = $devactivity->row()->date;
			}
			else
			{
				$devarr[$dev->serial]["last_activity"] = "N/A";
			}
		}
		
		

		// build days detail
		$days_detail_arr = array();
		$days_used = $this->device_activity_model->get_cart_days_used($schoolid,$location);
		// echo $schoolid;
		// echo $location;
		
		foreach($days_used->result() as $day)
		{
			$usage = $this->device_activity_model->get_cart_usage_on_date($schoolid, $location, $day->date)->row();
			$days_detail_arr[$day->date] = array(
				"average_minutes_used"	=> $usage->average_minutes_used,
				"count_used_on_day"	=> $usage->count_used_on_day,
			);
		}
		
		// echo "<pre>";
		// print_r($devarr);
		// print_r($days_detail_arr);
		// die();
		
		$data['devices'] = $devarr;
		$data['days_detail_arr'] = $days_detail_arr;
		
		$data['main_content'] = "get_location_activity";
		$this->load->view('includes/template',$data);
	}
	
	public function get_unused_devices()
	{
		$data['last_updated'] = $this->device_activity_model->get_static_value('last_updated')->row();
		$schoolid = $this->input->get("schoolid");
		$data['num_days'] = $this->input->get("num_days");
		
		if($schoolid)
		{
			$data['school_info'] = $this->device_activity_model->get_school_info($schoolid);
			$data['location'] = $data['school_info']->name;
			
			$devices = $this->device_activity_model->get_devices_unused_in_days($schoolid, $data['num_days']);
		}
		else
		{
			$data['location'] = "All sites";
			
			$devices = $this->device_activity_model->get_devices_unused_in_days(false, $data['num_days']);
		}
		
		// build devices and locations arrays
		$devarr = array();
		$sumarr = array();
		
		// echo "<pre>";
		// echo $this->db->last_query();
		// print_r($data['school_info']);
		// print_r($devices);
		// die();
		
		foreach($devices->result() as $dev)
		{
			// if last sync greater use that
			$lastsyncf = date("Y-m-d",strtotime($dev->lastsync));
			$lastuseractive = $dev->rct_date;
			
			if(strtotime($lastsyncf) > strtotime($lastuseractive))
				$lastactivity = $lastsyncf;
			else
				$lastactivity = $lastuseractive;
			
			if(strtotime($lastactivity) > strtotime('-' . $data['num_days'] . ' days'))
			{
				continue;
			}
			
			// print_r($dev);
			$devarr[$dev->serial] = array(
				"serial" => $dev->serial,
				"asset" => $dev->annotatedAssetId,
				"model" => $dev->model,
				"osVersion" => $dev->osVersion,
				"notes" => $dev->notes,
				"annotatedLocation" => $dev->annotatedLocation,
				"last_activity" => $lastactivity,
				"school" => $dev->name,
				"school_id" => $dev->school_id,
			);
			
			if(isset($sumarr[$dev->name][$dev->annotatedLocation]))
				$sumarr[$dev->name][$dev->annotatedLocation]++;
			else
				$sumarr[$dev->name][$dev->annotatedLocation] = 1;
		}
		
		// sort array
		usort($devarr, function($a, $b) {
			return strtotime($a["last_activity"]) - strtotime($b["last_activity"]);
		});
		
		// echo "<pre>";
		// print_r($devarr);
		// print_r($sumarr);
		// die();
		
		$data['devices'] = $devarr;
		$data['sumarr'] = $sumarr;
		
		$data['main_content'] = "get_unused_devices";
		$this->load->view('includes/template',$data);
	}
	
	private function isWeekend($date) {
		return (date('N', strtotime($date)) >= 6);
	}
	
	private function build_schools()
	{
		$schools = $this->device_activity_model->get_schools();
		$this->schools = array();
		foreach($schools->result() as $school)
		{
			$this->schools[$school->name] = $school->school_id;
		}
	}
}