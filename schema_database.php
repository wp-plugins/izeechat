<?php

$myTables = array(

	"izeechat" => array(
		"id"           => "INT(11) NOT NULL AUTO_INCREMENT",
		"email"        => "VARCHAR(120) NOT NULL",
		"password"     => "VARCHAR(125) NOT NULL",
		"api_rtckey"   => "VARCHAR(32) NOT NULL",
		"site_domain"  => "VARCHAR(255) NOT NULL",
		"site_key"     => "VARCHAR(32) NOT NULL",
		"users"        => "TEXT NOT NULL",
		"enterpriseId" => "INT(11) NOT NULL",
		"activation"   => "INT(1) NOT NULL",
		"box_display"  => "VARCHAR(10) NOT NULL",
		"users"        => "VARCHAR(1000) NOT NULL",
		"created_at"   => "DATETIME NOT NULL",
		"UNIQUE KEY"   => "(id)"
/*		"UNIQUE KEY"   => "(email)"*/
	),

	"izeechat_users" => array(
		"id"             => "INT(11) NOT NULL AUTO_INCREMENT",
		"first_name"     => "VARCHAR(128) NOT NULL",
		"last_name"      => "VARCHAR(128) NOT NULL",
		"email"          => "VARCHAR(255) NOT NULL",
		"password"       => "VARCHAR(125) NOT NULL",
		"cloud_password" => "VARCHAR(125) NOT NULL",
		"status"         => "INT(1) DEFAULT 0",
		"UNIQUE KEY"     => "(id)"
		//"UNIQUE KEY"     => "(email)"
	)
);

return $myTables;

?>