<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$active_group = 'default';
$query_builder = TRUE;


//  db mvl_ocr_db
// user db mvl_ocr_db
// pass db /GaTGjBg7#bB

switch ($_SERVER['REMOTE_ADDR']) {
    case '2222127.0.0.1':
			$host = 'srv1073.hstgr.io';
			$host = 'localhost';
			$userdb ='root';
			$pass = '';
			$base ='u117285061_mvl_ocr_db';
			break;
       case '38.52.86.75':
			// mysetup hostng
			$host = 'localhost';
			$userdb ='u117285061_mvl_ocr_db';
			$pass = '/GaTGjBg7#bB';
			$base ='u117285061_mvl_ocr_db';
			break;
   
		default:

			$host = '34.123.36.213';
			$userdb ='u117285061_mvl_ocr_db';
			$pass = '/GaTGjBg7#bB';
			$base ='u117285061_mvl_ocr_db';
			break;
}

$db['default'] = array(
	'dsn'	=> '',
	'hostname' => $host,
	'username' => $userdb,
	'password' => $pass,
	'database' => $base,
	'dbdriver' => 'mysqli',
	'dbprefix' => '',
	'pconnect' => FALSE,
	'db_debug' => (ENVIRONMENT !== 'production'),
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8',
	'dbcollat' => 'utf8_general_ci',
	'swap_pre' => '',
	'encrypt' => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'save_queries' => TRUE
);
