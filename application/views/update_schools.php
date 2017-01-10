<?php

	echo form_open('welcome/update_schools_submit');
	
	echo '<table id="schooltable">';
	echo '<thead>';
	echo "<tr>";
	echo "<th>Name</th>";
	echo "<th>Enrollment</th>";
	echo "<th>Target Devices</th>";
	echo "</tr>";
	echo '</thead>';
	foreach($schools->result() as $school)
	{
		// print_r($school);
		echo "<tr>";
		echo "<td>" . $school->name . "</td>";
		
		$enroll_field_arr = array(
			'name'		=> $school->school_id . "_enrollment",
			'id'		=> $school->school_id . "_enrollment",
			'value'		=> $school->enrollment,
		);
		echo "<td>" . form_input($enroll_field_arr) . "</td>";
		
		$target_field_arr = array(
			'name'		=> $school->school_id . "_target_devices",
			'id'		=> $school->school_id . "_target_devices",
			'value'		=> $school->target_devices,
		);
		echo "<td>" . form_input($target_field_arr) . "</td>";
		
		echo "</tr>";
	}
	echo "</table>";
	
	echo form_submit('mysubmit', 'Update');
	
	echo form_close();