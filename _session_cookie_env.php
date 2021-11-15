<?php
//ini_set('session.use_only_cookies', '0');
ini_set('session.use_trans_sid', '1');
//ini_set('session.save_path', './tmp/session');
ini_set('session.gc_maxlifetime', '1800');
ini_set('session.gc_probability', '1');
ini_set('session.gc_divisor', '100');
session_start();
//session_regenerate_id(true);
$sess_name = session_name();
$sess_id = session_id();
$_SESSION[$sess_name] = $sess_id;
