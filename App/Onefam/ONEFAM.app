<?php
// ---------------------------------------------------------------
// $Id: ONEFAM.app,v 1.5 2005/11/15 12:58:44 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/App/Onefam/ONEFAM.app,v $
// ---------------------------------------------------------------
//  O   Anakeen - 2001
// O*O  Anakeen development team
//  O   dev@anakeen.com
// ---------------------------------------------------------------
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or (at
//  your option) any later version.
//
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
// or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
// for more details.
//
// You should have received a copy of the GNU General Public License along
// with this program; if not, write to the Free Software Foundation, Inc.,
// 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
// ---------------------------------------------------------------
$app_desc = array (
"name"		=>"ONEFAM",		//Name
"short_name"	=>N_("Onefam"),		//Short name
"description"	=>N_("One Familly Management"),//long description
"access_free"	=>"N",			//Access free ? (Y,N)
"icon"		=>"onefam.gif",		//Icon
"displayable"	=>"Y",			//Should be displayed on an app list (Y,N)
"with_frame"	=>"Y",			//Use multiframe ? (Y,N)
"childof"	=>"",			//
"iorder"	=>110,                  // install order
"tag"           =>"CORE"
);

$action_desc = array (
  array( 
   "name"		=>"ONEFAM_ROOT",
   "short_name"		=>N_("one familly root"),
   "acl"		=>"ONEFAM_READ",
   "root"		=>"Y"
  )  ,
  array( 
   "name"               =>"ONEFAM_EXT",
   "short_name"         =>N_("one family extjs view"),
   "acl"                =>"ONEFAM_READ",
   "root"               =>"N"
  )  ,
  array( 
   "name"               =>"ONEFAM_NG",
   "short_name"         =>N_("one family multi document"),
   "acl"                =>"ONEFAM_READ",
   "root"               =>"N"
  ),
  array( 
   "name"		=>"ONEFAM_GENROOT",
   "short_name"		=>N_("one family generic root"),
   "acl"		=>"ONEFAM_READ"
  )  ,
  array( 
   "name"		=>"ONEFAM_LIST",
   "short_name"		=>N_("familly list"),
   "acl"		=>"ONEFAM_READ"
  )  ,
  array( 
   "name"		=>"ONEFAM_TOGEN",
   "short_name"		=>N_("redirect to generic"),
   "acl"		=>"ONEFAM_READ"
  )  ,
  array( 
   "name"		=>"ONEFAM_LOGO",
   "short_name"		=>N_("familly result"),
   "acl"		=>"ONEFAM_READ"
  )  ,
  array( 
   "name"		=>"ONEFAM_EDITPREF",
   "short_name"		=>N_("edit preferences"),
   "acl"		=>"ONEFAM"
  )  ,
  array( 
   "name"		=>"ONEFAM_MODPREF",
   "short_name"		=>N_("modify preferences"),
   "acl"		=>"ONEFAM"
  ) ,
  array( 
   "name"		=>"ONEFAM_EXT_MODPREF",
   "short_name"		=>N_("modify preferences"),
   "acl"		=>"ONEFAM"
  ) ,
  array( 
   "name"               =>"ONEFAM_GETTREEFAMILY",
   "short_name"         =>N_("get data for createtree family"),
   "acl"                =>"ONEFAM"
  ) ,
  array( 
   "name"		=>"ONEFAM_EDITMASTERPREF",
   "short_name"		=>N_("edit master preferences"),
   "layout"		=>"onefam_editpref.xml",
   "script"		=>"onefam_editpref.php",
   "function"		=>"onefam_editmasterpref",
   "acl"		=>"ONEFAM_MASTER"
  )  ,
  array( 
   "name"		=>"ONEFAM_MODMASTERPREF",
   "short_name"		=>N_("modify master preferences"),
   "layout"		=>"onefam_modpref.xml",
   "script"		=>"onefam_modpref.php",
   "function"		=>"onefam_modmasterpref",
   "acl"		=>"ONEFAM_MASTER"
  ) ,
  array( 
   "name"		=>"ONEFAM_EXT_MODMASTERPREF",
   "short_name"		=>N_("modify master preferences"),
   "layout"		=>"onefam_ext_modpref.xml",
   "script"		=>"onefam_ext_modpref.php",
   "function"		=>"onefam_ext_modmasterpref",
   "acl"		=>"ONEFAM_MASTER"
  ) ,
  array( 
   "name"		=>"ONEFAM_EXT_MENU",
   "short_name"		=>N_("get menu with family concordance"),
   "acl"		=>"ONEFAM_READ"
  ) ,
  array( 
   "name"               =>"ONEFAM_EXT_GETPREF",
   "short_name"         =>N_("get preferences"),
   "script"             =>"onefam_ext_getpref.php",
   "function"           =>"onefam_ext_getpref",
   "acl"                =>"ONEFAM"
  ) ,
  array( 
   "name"		=>"ONEFAM_EXT_GETMASTERPREF",
   "short_name"		=>N_("get master preferences"),
   "script"		=>"onefam_ext_getpref.php",
   "function"		=>"onefam_ext_getmasterpref",
   "acl"		=>"ONEFAM_MASTER"
  ) ,
  array( 
   "name"		=>"ONEFAM_EXT_GETDISPLAYCONFIG",
   "short_name"		=>N_("get ext display configuration"),
   "script"		=>"onefam_ext_displayconfig.php",
   "function"		=>"onefam_ext_getdisplayconfig",
   "acl"		=>"ONEFAM"
  ) ,
  array( 
   "name"		=>"ONEFAM_EXT_SETDISPLAYCONFIG",
   "short_name"		=>N_("set ext display configuration"),
   "script"		=>"onefam_ext_displayconfig.php",
   "function"		=>"onefam_ext_setdisplayconfig",
   "acl"		=>"ONEFAM"
  ) 
);

$app_acl = array (
  
  array(
   "name"               =>"ONEFAM",
   "description"        =>N_("To choose other families"),
   "group_default"       =>"Y"),
  array(
   "name"               =>"ONEFAM_READ",
   "description"        =>N_("Access To Read Card"),
   "group_default"       =>"Y"),
  array(
   "name"               =>"ONEFAM_MASTER",
   "description"        =>N_("Access choose masters families"),
   "group_default"       =>"N"),
);
?>
