<h2>Stats</h2>
<p>Last updated <?php echo $last_updated->value; ?></p>
<?php 
	echo '<p>See unused devices in last ';
	echo anchor('device_activity/get_unused_devices?num_days=14', "14 days") . " ";
	echo anchor('device_activity/get_unused_devices?num_days=30', "30 days") . " ";
	echo anchor('device_activity/get_unused_devices?num_days=60', "60 days") . " ";
	echo "</p>";
?>
<table id="schooltable">
<tr>
  <th>Name</th>
  <th>Enrollment</th>
  <th>Target Devices</th>
  <th>Total Devices</th>
  <th>% of Target</th>
  <th>Ratio</th>
  <th># used in last 30</th>
  <th># used in last 60</th>
  <th>Older</th>
  <th>Average minutes used per device per day last 30</th>
  <th>Average minutes used per device per day total</th>
</tr>
<?php
	foreach($schoolarr as $schid=>$school)
	{
		$ratio = ($school["enrollment"] == 0 ? "N/A" : round($school["total"]/$school["enrollment"],2) . ":1");
		echo "<tr>";
		echo "<td>" . anchor('device_activity/get_school_device_activity?schoolid=' . $schid, $school["name"]) . "</td>";
		echo "<td>" . $school["enrollment"] . "</td>";
		echo "<td>" . $school["target_devices"] . "</td>";
		echo "<td>" . $school["total"] . "</td>";
		echo "<td>" . ($school["target_devices"] > 0 ? round($school["total"]/$school["target_devices"],2) : 0) . "</td>";
		echo "<td>" . $ratio . "</td>";
		echo "<td>" . $school["last30"] . "</td>";
		echo "<td>" . $school["last60"] . "</td>";
		echo "<td>" . $school["older"] . "</td>";
		echo "<td>" . round($school["averageperday_last30"]) . "</td>";
		echo "<td>" . round($school["averageperday_total"]) . "</td>";
		echo "</tr>";
	}
?>
</table>

<?php
	// something for specific info i did?
	if(false)
	{
		$arr_usage = array();
		$all_dates = array();
		foreach($schoolarr as $schid=>$school)
		{
			foreach($school["average_periods"] as $date => $minutes)
			{
					// echo $school["name"] . "," . $date . ","  . $minutes . "<br >\n";
					$arr_usage[$school["name"]][$date] = $minutes;
					$all_dates[$date] = $date;
			}
		}
		
		// echo "<br><br>\n\n";
		echo "School,";
		foreach($all_dates as $date)
			echo $date . ",";
			
		echo "<br>\n";
		foreach($arr_usage as $school => $datearr)
		{
			echo $school . ",";
			foreach($datearr as $date => $minutes)
			{
				echo $minutes . ",";
			}
			echo "<br>\n";
		}
	}
?>

<h2>Minutes per day per school</h2>
<div id="chart" style="height: 250px;"></div>
<div id="legend1"></div>
<script>
var chart = new Morris.Line({
  // ID of the element in which to draw the chart.
  element: 'chart',
  // Chart data records -- each entry in this array corresponds to a point on
  // the chart.
  data: [
	<?php
		$lastDate = end($minarr);
		foreach($minarr as $date => $schoolarray)
		{
			echo "{ date: '". $date . "',";
			
			$lastSchool = end($school);
			foreach($schoolarray as $schoolid => $schoolvalues)
			{
				echo " " . $schoolid . ": " . $schoolvalues["minutes"];
				if($schoolvalues == $lastSchool)
					echo " ";
				else
					echo  ", ";
			}
			
			if($schoolarray == $lastDate)
				echo "}\n";
			else
				echo "},\n";
				
		}
	?>
  ],
  // The name of the data record attribute that contains x-values.
  xkey: 'date',
  <?php
	// A list of names of data record attributes that contain y-values.
	echo "ykeys: [";
	$lastschoolid = end($schools);
	foreach($schools as $schoolname => $schoolid)
	{
		echo "'" . $schoolid . "'";
		if($schoolid != $lastschoolid)
			echo ",";
	}
	echo "],\n";

	// Labels for the ykeys -- will be displayed when you hover over the chart.	
	echo "labels: [";
	$lastschoolid = end($schools);
	foreach($schools as $schoolname => $schoolid)
	{
		echo "'" . $schoolname . "'";
		if($schoolid != $lastschoolid)
			echo ",";
	}
	echo "]\n";
  ?>
});

// chart.options.labels.forEach(function(label, i){
    // var legendItem = $('<span></span>').text(label).css('color', chart.options.lineColors[i])
    // $('#legend1').append(legendItem)
// })
</script>

<br />
<h2>Average minutes per day per student (by enrollment count)</h2>
<div id="chart2" style="height: 250px;"></div>
<div id="legend2"></div>

<script>
var chart = new Morris.Line({
  // ID of the element in which to draw the chart.
  element: 'chart2',
  // Chart data records -- each entry in this array corresponds to a point on
  // the chart.
  data: [
	<?php
		$lastDate = end($avgminarr);
		foreach($avgminarr as $date => $schoolarray)
		{
			echo "{ date: '". $date . "',";
			
			$lastSchool = end($school);
			foreach($schoolarray as $schoolid => $schoolvalues)
			{
				echo " " . $schoolid . ": " . $schoolvalues["aminutes"];
				if($schoolvalues == $lastSchool)
					echo " ";
				else
					echo  ", ";
			}
			
			if($schoolarray == $lastDate)
				echo "}\n";
			else
				echo "},\n";
				
		}
	?>
  ],
  // The name of the data record attribute that contains x-values.
  xkey: 'date',
  <?php
	// A list of names of data record attributes that contain y-values.
	echo "ykeys: [";
	$lastschoolid = end($schools);
	foreach($schools as $schoolname => $schoolid)
	{
		echo "'" . $schoolid . "'";
		if($schoolid != $lastschoolid)
			echo ",";
	}
	echo "],\n";

	// Labels for the ykeys -- will be displayed when you hover over the chart.	
	echo "labels: [";
	$lastschoolid = end($schools);
	foreach($schools as $schoolname => $schoolid)
	{
		echo "'" . $schoolname . "'";
		if($schoolid != $lastschoolid)
			echo ",";
	}
	echo "]\n";
  ?>
});

// chart.options.labels.forEach(function(label, i){
    // var legendItem = $('<span></span>').text(label).css('color', chart.options.lineColors[i])
    // $('#legend2').append(legendItem)
// })
</script>
<br /><br />


<br />