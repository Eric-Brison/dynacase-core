<?

global $app_desc,$app_acl,$action_desc;
$app_desc = array (
"name"		=>"APPMNG",		//Name
"short_name"	=>N_("Application manager"),		//Short name
"description"	=>N_("What Application Management"),//long description
"access_free"	=>"N",			//Access free ? (Y,N)
"icon"		=>"appmng.gif",		//Icon
"displayable"	=>"Y"			//Should be displayed on an app list (Y,N)
);

$app_acl = array (
  array (
   "name"		=>"ADMIN",
   "description"	=>N_("SuperUser permission"),
   "admin"		=>TRUE),
  array (
   "name"		=>"USER",
   "description"	=>N_("user preference"),
   "group_default"	=>"Y")
);

$action_desc = array (
  array(
   "name"               =>"STYLELIST",
   "short_name"         =>N_("styles parameters"),
   "toc"                =>"Y",
   "acl"                =>"ADMIN"
  ) ,
  array( 
   "name"		=>"STYLESLIST",
   "toc"                =>"Y",
   "short_name"		=>N_("styles"),
   "acl"		=>"ADMIN",
   "root"		=>"Y"
  ) ,
  array(
   "name"               =>"PARAM_ALIST",
   "short_name"         =>N_("application parameters"),
   "toc"                =>"Y",
   "acl"                =>"ADMIN"
  ) ,
  array(
   "name"               =>"PARAM_ULIST",
   "short_name"         =>N_("user parameters"),
   "toc"                =>"Y",
   "acl"                =>"ADMIN"
  ) ,
  array(
   "name"               =>"PARAM_CULIST",
   "short_name"         =>N_("current user parameters"),
   "toc"                =>"N",
   "acl"                =>"USER"
  ) ,
  array(
   "name"               =>"ACTIONLIST",
   "short_name"         =>N_("actions"),
   "toc"                =>"Y",
   "acl"                =>"ADMIN"
  ) ,
  array( 
   "name"		=>"APPLIST",
   "toc"                =>"Y",
   "short_name"		=>N_("applications"),
   "acl"		=>"ADMIN",
   "root"		=>"Y"
  ) ,
  array(
   "acl"		=>"ADMIN",
   "name"		=>"APP_EDIT"
  ),
  array(
   "acl"		=>"ADMIN",
   "name"		=>"ACTION_MOD"
  ),
  array(
   "acl"		=>"ADMIN",
   "name"		=>"APP_MOD"
  ),
  array(
   "acl"		=>"ADMIN",
   "name"		=>"STYLES_UPDATE"
  ),
  array(
   "acl"		=>"ADMIN",
   "name"		=>"APP_UPDATE"
  ),
  array(
   "acl"		=>"ADMIN",
   "name"		=>"APP_UPDATEALL",
   "function"           =>"app_updateAll",
   "script"		=>"app_update.php"
  ),
  array(
   "acl"		=>"ADMIN",
   "name"		=>"APP_DELETE"
  ),
  array(
   "acl"		=>"ADMIN",
   "name"		=>"APP_STOP"
  ),
  array(
   "acl"                =>"ADMIN",
   "name"               =>"ACTION_EDIT"
  ),
  array(
   "acl"                =>"ADMIN",
   "name"               =>"PARAM_EDIT"
  ),
  array(
   "acl"                =>"ADMIN",
   "short_name"         =>N_("delete parameters"),
   "name"               =>"PARAM_DELETE"
  ),
  array(
   "acl"                =>"ADMIN",
   "short_name"         =>N_("modify parameters"),
   "name"               =>"PARAM_MOD"
  ),
  array(
   "acl"                =>"USER",
   "short_name"         =>N_("delete user parameters"),
   "name"               =>"PARAM_UDELETE",
   "function"           =>"param_udelete",
   "script"		=>"param_delete.php"
  ),
  array(
   "acl"                =>"USER",
   "short_name"         =>N_("modify user parameters"),
   "name"               =>"PARAM_UMOD",
   "function"           =>"param_umod",
   "script"		=>"param_mod.php"
  ),
  array(
   "acl"                =>"ADMIN",
   "name"               =>"STYLES_EDIT"
  ),
  array(
   "acl"                =>"ADMIN",
   "name"               =>"STYLES_DELETE"
  ),
  array(
   "acl"                =>"ADMIN",
   "name"               =>"STYLES_MOD"
  ),
  array(
   "acl"                =>"ADMIN",
   "name"               =>"STYLE_EDIT"
  ),
  array(
   "acl"                =>"ADMIN",
   "name"               =>"STYLE_DELETE"
  ),
  array(
   "acl"                =>"ADMIN",
   "name"               =>"STYLE_MOD"
  ),
  array(
   "name"               =>"PARAM_STYLE_CHG",
   "acl"                =>"ADMIN"
  ),
  array(
   "name"               =>"ACTION_APPL_CHG",
   "acl"                =>"ADMIN"
  ),
  array(
   "name"               =>"PARAM_APPL_CHG",
   "acl"                =>"ADMIN"
  )
                      );
   
?>
