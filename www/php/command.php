<?php
// Sabai Technology - Apache v2 licence
// Copyright 2015 Sabai Technology, LLC
 $filter = array("<", ">","="," (",")",";","/","|");
 $_REQUEST['act']=str_replace ($filter, "#", $_REQUEST['act']);

$act=$_REQUEST['act'];
if ( $act == "halt" ){
	echo "Shutting Down";
}
exec("$act");

?>
