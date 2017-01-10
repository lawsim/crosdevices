<h2>Stats for <?php echo $school_info->name; ?></h2>
<p>Last updated <?php echo $last_updated->value; ?></p>
<?php 
	echo '<p>See unused devices in last ';
	echo anchor('device_activity/get_unused_devices?schoolid=' . $school_info->school_id . '&num_days=14', "14 days") . " ";
	echo anchor('device_activity/get_unused_devices?schoolid=' . $school_info->school_id . '&num_days=30', "30 days") . " ";
	echo anchor('device_activity/get_unused_devices?schoolid=' . $school_info->school_id . '&num_days=60', "60 days") . " ";
	echo "</p>";
?>
<table id="schooltable">
<tr>
  <th>Name</th>
  <th>Total Devices</th>
  <th># used in last 30</th>
  <th># used in last 60</th>
  <th>Older</th>
  <th>Average minutes used per device per day last 30</th>
  <th>Average minutes used per device per day total</th>
</tr>
<?php
	foreach($locations as $location)
	{
		echo "<tr>";
		if($location["name"] == "")
			echo "<td>" . anchor('device_activity/get_location_activity?schoolid=' . $school_info->school_id . "&location=", "Unassigned") . "</td>";
		else
			echo "<td>" . anchor('device_activity/get_location_activity?schoolid=' . $school_info->school_id . "&location=" . $location["name"], $location["name"]) . "</td>";
		echo "<td>" . $location["total"] . "</td>";
		echo "<td>" . $location["last30"] . "</td>";
		echo "<td>" . $location["last60"] . "</td>";
		echo "<td>" . $location["older"] . "</td>";
		echo "<td>" . round($location["averageperday_last30"]) . "</td>";
		echo "<td>" . round($location["averageperday_total"]) . "</td>";
		echo "</tr>";
	}
?>
</table>

<h2>Minutes per day per location</h2>
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
		foreach($minarr as $date => $locarray)
		{
			echo "{ date: '". $date . "',";
			
			$lastloc = end($locarray);
			foreach($locarray as $locname => $locvalues)
			{
				echo " " . $locvalues['locid'] . ": " . $locvalues["minutes"];
				if($locvalues == $lastloc)
					echo " ";
				else
					echo  ", ";
			}
			
			if($locarray == $lastDate)
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
	$lastschoolarr = end($locations);
	foreach($locations as $locationname => $locationarr)
	{
		echo "'" . $locationarr['locid'] . "'";
		if($locationarr != $lastschoolarr)
			echo ",";
	}
	echo "],\n";

	// Labels for the ykeys -- will be displayed when you hover over the chart.	
	echo "labels: [";
	$lastschoolarr = end($locations);
	foreach($locations as $locationname => $locationarr)
	{
		echo "'" . ($locationarr['name'] == "" ? "Unassigned" : $locationarr['name']) . "'";
		if($locationarr != $lastschoolarr)
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
<h2>Average minutes per day per machine in location</h2>
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
		foreach($avgminarr as $date => $locarray)
		{
			echo "{ date: '". $date . "',";
			
			$lastloc = end($locarray);
			foreach($locarray as $locname => $locvalues)
			{
				echo " " . $locvalues['locid'] . ": " . $locvalues["minutes"];
				if($locvalues == $lastloc)
					echo " ";
				else
					echo  ", ";
			}
			
			if($locarray == $lastDate)
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
	$lastschoolarr = end($locations);
	foreach($locations as $locationname => $locationarr)
	{
		echo "'" . $locationarr['locid'] . "'";
		if($locationarr != $lastschoolarr)
			echo ",";
	}
	echo "],\n";

	// Labels for the ykeys -- will be displayed when you hover over the chart.	
	echo "labels: [";
	$lastschoolarr = end($locations);
	foreach($locations as $locationname => $locationarr)
	{
		echo "'" . ($locationarr['name'] == "" ? "Unassigned" : $locationarr['name']) . "'";
		if($locationarr != $lastschoolarr)
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