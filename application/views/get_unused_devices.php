<h1>Unused devices for <?php echo $location; ?></h1>
<h2><?php echo count($devices) . " unused in last $num_days days";?></h2>
<h2>Summary Table</h2>
<table class="greentable">
<tr>
  <th>School</th>
  <th>Location</th>
  <th># unused in last <?php echo $num_days; ?> days</th>
</tr>
<?php
	foreach($sumarr as $school=>$locationsarr)
	{
		foreach($locationsarr as $location => $count)
		{
			echo "<tr>";
			echo "<td>" . $school . "</td>";
			echo "<td>" . $location . "</td>";
			echo "<td>" . $count . "</td>";
			echo "</tr>";
		}
		
	}
?>
</table>



<h2>Detail Table</h2>
<table class="greentable">
<tr>
  <th>School</th>
  <th>Serial</th>
  <th>Asset</th>
  <th>Model</th>
  <th>OS Ver</th>
  <th>Notes</th>
  <th>Location</th>
  <th>Last Activity</th>
</tr>
<?php
	foreach($devices as $serial=>$devarr)
	{
		echo "<tr>";
		// echo "<td>" . $devarr['school'] . "</td>";
		echo "<td>" . anchor('device_activity/get_unused_devices?schoolid=' . $devarr['school_id'] . '&num_days=' . $num_days, $devarr['school']) . "</td>";
		// echo anchor('device_activity/get_unused_devices?schoolid=' . $school_info->school_id . '&num_days=14', "14 days") . " ";
		echo "<td>" . $devarr['serial'] . "</td>";
		echo "<td>" . $devarr['asset'] . "</td>";
		echo "<td>" . $devarr['model'] . "</td>";
		echo "<td>" . $devarr['osVersion'] . "</td>";
		echo "<td>" . $devarr['notes'] . "</td>";
		echo "<td>" . $devarr['annotatedLocation'] . "</td>";
		echo "<td>" . $devarr['last_activity'] . "</td>";
		echo "</tr>";
	}
?>
</table>

