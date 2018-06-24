<?php

spl_autoload_register(function($class) {
	// echo $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . str_replace("\\", DIRECTORY_SEPARATOR, trim($class, "\\")) .'.php'; exit;
	require_once str_replace("\\", DIRECTORY_SEPARATOR, trim($class, "\\")) .'.php';
});

$db = new \Adapter\PDODatabase(
	\Config\DbConfig::DB_HOST,
	\Config\DbConfig::DB_NAME,
	\Config\DbConfig::DB_USER,
	\Config\DbConfig::DB_PASS
);
