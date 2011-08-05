<?php
// ---------------------------------------------------------------
// $Id: GENERIC.app,v 1.20 2007/07/31 13:49:44 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/App/Generic/GENERIC.app,v $
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
"name"		=>"GENERIC",		//Name
"short_name"	=>N_("Generic"),		//Short name
"description"	=>N_("generic Management"),//long description
"access_free"	=>"N",			//Access free ? (Y,N)
"icon"		=>"generic.gif",	//Icon
"displayable"	=>"N",			//Should be displayed on an app list (Y,N)
"with_frame"	=>"Y",			//Use multiframe ? (Y,N)
"childof"	=>"",			//
"iorder"	=>105,                   // install order
"tag"    	=>"CORE"
);

$app_acl = array (
  
  array(
   "name"               =>"GENERIC_MASTER",
   "description"        =>N_("Access Generic Master Management")),
  array(
   "name"               =>"GENERIC",
   "description"        =>N_("Access To Generic Management"),
   "group_default"       =>"Y"),
  array(
   "name"               =>"GENERIC_READ",
   "description"        =>N_("Access To Read Card"),
   "group_default"       =>"Y")
);

$action_desc = array (
  array( 
   "name"		=>"GENERIC_ROOT",
   "short_name"		=>N_("generic home page"),
   "acl"		=>"GENERIC_READ",
   "root"		=>"Y"
  ) ,
  array( 
   "name"		=>"GENERIC_ROOTV",
   "short_name"		=>N_("generic vertical home page"),
   "acl"		=>"GENERIC_READ"
  ) ,
  array( 
   "name"		=>"GENERIC_EDIT",
   "short_name"		=>N_("edition"),
   "acl"		=>"GENERIC"
  ) ,
  array( 
   "name"		=>"GENERIC_ISEARCH",
   "short_name"		=>N_("inverted search"),
   "acl"		=>"GENERIC_READ"
  ) ,
  array( 
   "name"		=>"GENERIC_DUPLICATE",
   "short_name"		=>N_("duplication"),
   "acl"		=>"GENERIC"
  ) ,
  array( 
   "name"		=>"GENERIC_DEL",
   "short_name"		=>N_("delete"),
   "acl"		=>"GENERIC"
  ) ,
  array( 
   "name"		=>"GENERIC_ADDCATG",
   "short_name"		=>N_("add category"),
   "acl"		=>"GENERIC"
  ) ,
  array( 
   "name"		=>"GENERIC_PREFS",
   "short_name"		=>N_("edit family preference"),
   "acl"		=>"GENERIC_READ"
  ) ,
  array( 
   "name"		=>"GENERIC_MODPREFS",
   "short_name"		=>N_("modify family preference"),
   "acl"		=>"GENERIC_READ"
  ) ,
  array( 
   "name"		=>"GENERIC_MODKIND",
   "short_name"		=>N_("change king in enum attribute"),
   "acl"		=>"GENERIC_MASTER"
  ) ,
  array( 
   "name"		=>"GENERIC_CHOOSEENUMATTR",
   "short_name"		=>N_("choose for edit enum attribute"),
   "acl"		=>"GENERIC_MASTER"
  ) ,
  array( 
   "name"		=>"GENERIC_EDITFAMCATG",
   "short_name"		=>N_("interface to edit enum attribute"),
   "acl"		=>"GENERIC_MASTER"
  ) ,
  array( 
   "name"		=>"GENERIC_EDITNEWCATG",
   "short_name"		=>N_("edit to add category"),
   "acl"		=>"GENERIC"
  ) ,
  array( 
   "name"		=>"GENERIC_EDITCATG",
   "short_name"		=>N_("edit to add or modify category"),
   "acl"		=>"GENERIC_MASTER"
  ) ,
  array( 
   "name"		=>"GENERIC_EDITCHANGECATG",
   "short_name"		=>N_("edit to change category"),
   "acl"		=>"GENERIC"
  ) ,
  array( 
   "name"		=>"GENERIC_CHANGECATG",
   "short_name"		=>N_("change category"),
   "acl"		=>"GENERIC"
  ) ,
  array( 
   "name"		=>"GENERIC_MOD",
   "short_name"		=>N_("modification or creation"),
   "acl"		=>"GENERIC"
  ) ,
  array( 
   "name"		=>"GENERIC_VCARD",
   "short_name"		=>N_("view as vcard"),
   "acl"		=>"GENERIC_READ"
  ) ,
  array( 
   "name"		=>"GENERIC_LOGO",
   "short_name"		=>N_("display logo"),
   "acl"		=>"GENERIC_READ"
  ) ,
  array( 
   "name"		=>"GENERIC_LIST",
   "short_name"		=>N_("view list"),
   "acl"		=>"GENERIC_READ"
  ) ,
  array( 
   "name"		=>"GENERIC_USORT",
   "short_name"		=>N_("define sort attribute"),
   "acl"		=>"GENERIC_READ"
  ) ,
  array( 
   "name"		=>"GENERIC_CARD",
   "short_name"		=>N_("view a generic"),
   "acl"		=>"GENERIC_READ"
  ) ,
  array( 
   "name"		=>"GENERIC_EDITIMPORT",
   "short_name"		=>N_("edit import vcard"),
   "acl"		=>"GENERIC_MASTER"
  ) ,
  array( 
   "name"		=>"GENERIC_TAB",
   "short_name"		=>N_("view a part of list"),
   "acl"		=>"GENERIC_READ",
   "layout"		=>"generic_list.xml"
  ) ,
  array( 
   "name"		=>"GENERIC_TABV",
   "short_name"		=>N_("view a part of list"),
   "acl"		=>"GENERIC_READ",
   "script"		=>"generic_tab.php",
   "function"		=>"generic_tabv",
   "layout"		=>"generic_listv.xml"
  ) ,
  array( 
   "name"		=>"GENERIC_IMPORTCSV",
   "short_name"		=>N_("import csv"),
   "layout"		=>"generic_import.xml",
   "acl"		=>"GENERIC_MASTER"
  ) ,
  array( 
   "name"		=>"GENERIC_SEARCH",
   "short_name"		=>N_("search a generict"),
   "acl"		=>"GENERIC_READ"
  ) ,
  array( 
   "name"		=>"GENERIC_SEARCH_KIND",
   "short_name"		=>N_("search with kind criteria"),
   "acl"		=>"GENERIC_READ"
  ) ,
  array( 
   "name"		=>"GENERIC_INIT",
   "short_name"		=>N_("initialisation"),
   "acl"		=>"GENERIC"
  )  ,
  array( 
   "name"		=>"GENERIC_MEMOSPLIT",
   "short_name"		=>N_("memorisation of split mode"),
   "acl"		=>"GENERIC"
  ) ,
  array( 
   "name"		=>"GENERIC_MEMOSEARCH",
   "short_name"		=>N_("memorisation of pref search"),
   "acl"		=>"GENERIC",
   "script"		=>"generic_memosplit.php",
   "function"		=>"generic_memosearch"
  ),
  array( 
   "name"		=>"POPUPLISTDETAIL",
   "short_name"		=>N_("popup in generic list"),
   "acl"		=>"GENERIC_READ"
  ) 
                      );
   
?>
