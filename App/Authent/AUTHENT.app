<?

global $app_desc,$action_desc;

$app_desc = array (
"name"		=>"AUTHENT",		//Name
"short_name"	=>"Authent",		//Short name
"description"	=>"Authentification Application",	//long description
"access_free"	=>"Y",			//Access free ? (Y,N)
"icon"		=>"login.png",		//Icon
"displayable"	=>"N"			//Should be displayed on an app list (Y,N)
);

$action_desc = array (
  array( 
   "name"		=>"LOGINFORM",
   "short_name"		=>"login",
   "root"		=>"Y"
  ) ,
  array(
   "name"		=>"LOGINHELP"
  ),
  array(
   "name"		=>"LOGOUT"
  )
                      );
   
?>
