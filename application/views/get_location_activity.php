<h1>Stats for <?php echo $location; ?></h1>
<h2>Per day</h2>

<table class="greentable">
<tr>
  <th>Date</th>
  <th>Average Minutes Used</th>
  <th># Used on day</th>
</tr>
<?php
	foreach($days_detail_arr as $date=>$datearr)
	{
		echo "<tr>";
		echo "<td>" . $date . "</td>";
		echo "<td>" . $datearr["average_minutes_used"] . "</td>";
		echo "<td>" . $datearr["count_used_on_day"] . "</td>";
		echo "</tr>";
	}
?>
</table>

<h2>Devices</h2>
<table class="greentable">
<tr>
  <th>Serial</th>
  <th>Asset</th>
  <th>Model</th>
  <th>OS Ver</th>
  <th>Notes</th>
  <th>Location</th>
  <th>Last Activity</th>
  <th>Avg usage last 30 days</th>
</tr>
<?php
	foreach($devices as $serial=>$devarr)
	{
		echo "<tr>";
		echo "<td>" . $devarr['serial'] . "</td>";
		echo "<td>" . $devarr['asset'] . "</td>";
		echo "<td>" . $devarr['model'] . "</td>";
		echo "<td>" . $devarr['osVersion'] . "</td>";
		echo "<td>" . $devarr['notes'] . "</td>";
		echo "<td>" . $devarr['annotatedLocation'] . "</td>";
		echo "<td>" . $devarr['last_activity'] . "</td>";
		echo "<td>" . round($devarr['uselast30'],0) . "</td>";
		echo "</tr>";
	}
?>
</table>

