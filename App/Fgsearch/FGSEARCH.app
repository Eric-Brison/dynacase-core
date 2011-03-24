<?php
// ---------------------------------------------------------------
// $Id: FGSEARCH.app,v 1.3 2007/10/19 04:08:05 marc Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/App/Fgsearch/FGSEARCH.app,v $
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
"name"		=>"FGSEARCH",		//Name
"short_name"	=>N_("fgsearch"),		//Short name
"description"	=>N_("freedom global search"),//long description
"access_free"	=>"N",			//Access free ? (Y,N)
"icon"		=>"fgsearch.png",		//Icon
"displayable"	=>"Y",			//Should be displayed on an app list (Y,N)
"with_frame"	=>"Y",			//Use multiframe ? (Y,N)
"childof"	=>"",			//
"iorder"	=>131,                   // install order
"tag"           => "CORE"
);

$app_acl = array (
  array(
   "name"               =>"FGSEARCH_READ",
   "description"        =>N_("freedom global search"),
   "group_default"       =>"Y",
  ),
);


$action_desc = array (
  array( 
   "name"		=>"FULLSEARCH",
   "short_name"		=>N_("freedom global search"),
   "acl"		=>"FGSEARCH_READ",
   "root"		=>"Y"
  )  ,
  array( 
   "name"		=>"FULLDSEARCH",
   "short_name"		=>N_("freedom  global search"),
   "acl"		=>"FGSEARCH_READ"
  ) ,
  array( 
   "name"		=>"FULLEDITDSEARCH",
   "short_name"		=>N_("freedom detailled global search"),
   "acl"		=>"FGSEARCH_READ"
  ) ,
);

?>
