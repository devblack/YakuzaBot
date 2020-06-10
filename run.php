<?php
date_default_timezone_set('Asia/Tokyo');
set_time_limit(0);


require_once 'loader.php';

$irc = new irc;
$irc::Connect();