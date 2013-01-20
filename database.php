<?php
include("config.php");
include("lib.php");

$db_conn = mysql_connect($db_host, $db_user, $db_pass);
if(!$db_conn){
	$resp = new NetResponse;
        $resp->AddParam("method", "db");
        $resp->AddError("connect");
        $resp->SendResponse();
	exit;
}
$db_select = mysql_select_db($db_name, $db_conn);
mysql_set_charset("UTF-8",$db_conn);
mysql_query("set names 'UTF8'");

class DB {
	public static function SendQuery ($query){
		return mysql_query ($query);
	}
	
	public static function GetFetchArray($query){
	  	return mysql_fetch_array (self::SendQuery($query));
	}

}