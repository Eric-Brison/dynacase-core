<?php


$app_desc = array (
"name"		=>"DAV",		//Name
"short_name"	=>N_("Dav"),		//Short name
"description"	=>N_("WebDav FREEDOM system file"),//long description
"access_free"	=>"N",			//Access free ? (Y,N)
"icon"		=>"dav.gif",	//Icon
"displayable"	=>"N",			//Should be displayed on an app list (Y,N)
"with_frame"	=>"Y",			//Use multiframe ? (Y,N)
"childof"	=>"",		// 	
"tag"		=>"CORE",		// 	
);


$app_acl = array (
  array(
   "name"               =>"DAV_USER",
   "description"        =>N_("Access for exchange documents"),
   "group_default"  => "Y")
);
   
$action_desc = array (
  array( 
   "name"		=>"GETSESSIONID",
   "short_name"		=>N_("create a new session id"),
   "acl"		=>"DAV_USER"
  )
);

?>
