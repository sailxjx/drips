<?php

$config['MSSQL'] = array(
	'dsn' => 'odbc:webdsn',
	'user' => 'trace',
	'pwd' => 'trace',
	'options' => array()
);

$config['MYSQL'] = array(
	'dsn' => 'mysql:host=192.168.0.178;dbname=51fanli_user',
	'user' => 'root',
	'pwd' => 'root',
	'options' => array(),
	'statments' => array(
		'SET CHARACTER SET utf8'
	)
);