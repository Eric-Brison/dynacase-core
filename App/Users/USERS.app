<?

$app_desc = array (
"name"		=>"USERS",		//Name
"short_name"	=>"Users",		//Short name
"description"	=>N_("What User Management"),//long description
"access_free"	=>"N",			//Access free ? (Y,N)
"icon"		=>"users.gif",		//Icon
"displayable"	=>"Y"			//Should be displayed on an app list (Y,N)
);

$app_acl = array (
  array(
   "name"		=>"ADMIN",
   "description"	=>N_("Access To All Users"),
   "grant_level"	=>10,
   "admin"		=>TRUE),
  array(
   "name"               =>"DOMAIN_MASTER",
   "description"        =>N_("Access To All Users in my domain"),
   "grant_level"        =>8),
  array(
   "name"               =>"USER",
   "description"        =>N_("Access To My Own account"),
   "grant_level"        =>2,
   "group_default"       =>"Y" )
);

$action_desc = array (
  array( 
   "name"		=>"USER_TABLE",
   "short_name"		=>N_("users list"),
   "toc"		=>"Y",
   "acl"		=>"USER",
   "root"		=>"Y"
  ) ,
  array( 
   "name"		=>"GROUP_TABLE",
   "short_name"		=>N_("group list"),
   "layout"		=>"user_table.xml",
   "toc"		=>"Y",
   "acl"		=>"DOMAIN_MASTER"
  ) ,
  array(
   "name"		=>"USER_EDIT",
   "acl"		=>"USER"
  ),
  array(
   "name"		=>"USER_SEARCH",
   "acl"		=>"USER"
  ),
  array(
   "name"		=>"USER_MOD",
   "acl"		=>"USER"
  ),
  array(
   "name"		=>"USER_DEL",
   "acl"		=>"USER"
  )
                      );
   
?>
