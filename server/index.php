<?php

include 'bootstrap.php';

$db->select('tbl_receivers');
$receivers = $db->asArray();

$db->select('tbl_streams');
$streams = $db->asArray();
?>
<html>
<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
</head>
<body>
    <div class="container" style="padding-top: 2rem;">
        <h2>Current Live Receivers</h2>

        <table class="table">
            <tr style="background:#eee">
                <th style="width: 240px;">ID</th>
                <th style="width: 240px;">VPN IP</th>
                <th style="width: 150px;">Resolution</th>
                <th style="width: auto;">Last Ping</th>
            </tr>

            <?php
            if (!empty($receivers)) {
                foreach ($rows as $row) {
                ?>

            <tr>
                <td><?=$row['id_c'];?></td>
                <td><?=$row['vpnIp_c'];?></td>
                <td><?=$row['fps_n'];?></td>
                <td><?=$row['resolution_c'];?></td>
                <td><?=date(DATE_ATOM, $row['lastPing_d']);?> (<?=relativeTime($row['lastPing_d']);?>)</td>
            </tr>

                <?php
                }
            } else {
            ?>

            <tr>
                <td colspan="5">No streams found.</td>
            </tr>

            <?php
            }
            ?>


        </table>


        <h2>Current Live Streams</h2>

        <table class="table">

            <tr style="background:#eee">
                <th style="width: 240px;">ID</th>
                <th style="width: 240px;">VPN IP</th>
                <th style="width: 100px;">FPS</th>
                <th style="width: 150px;">Resolution</th>
                <th style="width: auto;">Last Ping</th>
            </tr>

            <?php
            if (!empty($streams)) {
                foreach ($rows as $row) {
                ?>

            <tr>
                <td><?=$row['id_c'];?></td>
                <td><?=$row['vpnIp_c'];?></td>
                <td><?=$row['fps_n'];?></td>
                <td><?=$row['resolution_c'];?></td>
                <td><?=date(DATE_ATOM, $row['lastPing_d']);?> (<?=relativeTime($row['lastPing_d']);?>)</td>
            </tr>

                <?php
                }
            } else {
            ?>

            <tr>
                <td colspan="5">No streams found.</td>
            </tr>

            <?php
            }
            ?>

        </table>
    </div>
</body>
</html>
