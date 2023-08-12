#!/usr/bin/php
<?php

include __DIR__  . '/bootstrap.php';

// run query
$db->delete('tbl_streams', 'lastPing_d < (UNIX_TIMESTAMP() - 5)');
