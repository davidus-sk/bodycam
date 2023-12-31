<?php

include 'bootstrap.php';

$db->select('tbl_receivers');
$receivers = $db->asArray();

$db->select('tbl_streams');
$streams = $db->asArray();

$db->select('tbl_thumbs', null, 80, 'date_d DESC');
$thumbs = $db->asArray();

?>
<html>

<head>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
</head>

<body>

	<div class="container" style="padding-top: 2rem;">

		<nav class="navbar navbar-dark bg-dark mb-4">
			<span class="navbar-brand">Body Cameras</span>
		</nav>

		<div class="card mb-4">
			<h5 class="card-header">Current Live Receivers</h5>

			<table class="table card-table">
				<tr style="background:#eee">
					<th style="width: 80px;">ID</th>
					<th style="width: 80px;">VPN IP</th>
					<th style="width: 160px;">Resolution</th>
					<th style="width: 120px;">Last Ping</th>
					<th style="width: 150px;">Uptime</th>
					<th style="width: 160px;">Modem</th>
					<th style="width: auto;">Streams</th>
				</tr>

				<?php
				if (!empty($receivers)) {
					foreach ($receivers as $row) {
				?>

				<tr>
					<td><?=$row['id_c'];?></td>
					<td><?=$row['vpnIp_c'];?></td>
					<td><?=$row['resolution_c']=="0x0" ? "No monitor" : $row['resolution_c'];?></td>
					<td><?=relativeTime($row['lastPing_d']);?></td>
					<td><?=relativeTime(time() - $row['uptime_n']);?></td>
					<td>
					<?php
					$modem = json_decode($row['modem_c'], TRUE);

					if ($modem) {
					echo "{$modem['network_provider']} ({$modem['network_type']}: {$modem['signalbar']} bars)";
					}//if
					?>
					</td>
					<td>
					<?php
					$ss = json_decode($row['streams_c'], TRUE);

					$a = [];
					foreach ($ss as $key=>$val) {
						$a[] = substr($key, -8);
					}//foreach

					echo join(",", $a);
					?>
					</td>
				</tr>

				<?php
					}//foreach
				} else {
				?>

				<tr>
					<td colspan="7">No receivers found.</td>
				</tr>

				<?php
				}//if
				?>


			</table>
		</div>

		<div class="card mb-4">
			<h5 class="card-header">Current Live Senders</h5>

			<table class="table card-table">
				<thead>
					<tr style="background:#eee">
						<th style="width: 80px;">ID</th>
						<th style="width: 80px;">VPN IP</th>
						<th style="width: 160px;">Format</th>
						<th style="width: 120px;">Last Ping</th>
						<th style="width: 150px;">Uptime</th>
						<th style="width: 160px;">Modem</th>
						<th style="width: 120px;">Elapsed</th>
						<th style="width: auto;">Latency</th>
					</tr>
				</thead>
				<tbody>
				
				<?php
				if (!empty($streams)) {
					foreach ($streams as $row) {
				?>

					<tr>
						<td><?=$row['id_c'];?></td>
						<td><?=$row['vpnIp_c'];?></td>
						<td><?=$row['resolution_c'];?> @ <?=$row['fps_n'];?>fps</td>
						<td><?=relativeTime($row['lastPing_d']);?></td>
						<td><?=relativeTime(time() - $row['uptime_n']);?></td>
						<td>
						<?php
						$modem = json_decode($row['modem_c'], TRUE);

						if ($modem) {
							echo "{$modem['network_provider']} ({$modem['network_type']}: {$modem['signalbar']} bars)";
						}//if
						?>
						</td>
						<td><?=relativeTime($row['date_d']);?></td>
						<td><?=$row['latency_n'];?> ms</td>
					</tr>

				<?php
					}//foreach
				} else {
				?>

				<tr>
					<td colspan="8">No streams found.</td>
				</tr>

				<?php
				}//if
				?>

				</tbody>
			</table>
		</div>

		<?php /* ?>

		<div class="card mb-4">
			<h5 class="card-header">Last Images</h5>

			<div class="card-body">
				<div class="row">

				<?php
				if (!empty($thumbs)) {
					foreach ($thumbs as $row) {
				?>

					<div class="col-md-3">
						<div class="card mb-4">
							<img class="card-img-top" src="/data/<?=$row['image_c'];?>" alt="<?=$row['id_c'];?>">
							<div class="card-body">
								<h6 class="card-subtitle mb-2 text-muted">Streamer: <?=$row['id_c'];?></h6>
								<p>Timestamp: <?=date('Y-m-d H:i:s', $row['date_d']);?></p>
							</div>
						</div>
					</div>

				<?php
					}//foreach
				}//if
				?>

				</div>
			</div>
		</div>

		<?php */ ?>

	</div>

</body>
</html>
