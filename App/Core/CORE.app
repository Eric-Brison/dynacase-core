<?
// ---------------------------------------------------------------
// $Id: CORE.app,v 1.15 2005/11/16 16:36:26 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/App/Core/CORE.app,v $
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
// ------------------------------------------------------
global $app_desc,$action_desc;

$app_desc= array (
"name" 		=> "CORE",              //Name
"short_name"	=> N_("Core"),              //Short name
"description"	=> N_("Core Application Manager"),   //long description
"access_free"	=>"Y",                   //Access type (ALL,RESTRICT)
"icon"		=>"core.png",            //Icon
"displayable"	=>"N",                    //Should be displayed on an app list
"with_frame"	=>"Y",			//Use multiframe ? (Y,N)
"iorder"         =>0                     // install order first
);

$action_desc = array (
  array(
   "name"               =>"LOGO",
   "layout"             =>"logo.xml",
   "root"               =>"N"
  ) ,
  array(
   "name"               =>"MAIN",
   "layout"		=>"core.xml"
  ),
  array(
   "name"               =>"GATE",
   "root"               =>"Y"
  ),
  array(
   "name"               =>"GATE_EDIT"
  ),
  array(
   "name"               =>"GATE_SAVEGEO"
  ),
  array(
   "name"               =>"GATE_EDITURL"
  ),
  array(
   "name"               =>"GATE_MODURL"
  ),
  array(
   "name"               =>"BLANK"
  ),
  array(
   "name"               =>"CLOSE"
  ),
  array(
   "name"               =>"DOCPDF"
  ),
  array(
   "name"               =>"GENCSS",
   "layout"		=>"core.css"
  ),
  array(
   "name"               =>"CORE_CSS",
   "layout"		=>"core.css"
  ),
  array(
   "name"               =>"HEAD"
  ),
  array(
   "name"               =>"CLEARCACHE"
  ),
  array(
   "name"               =>"TABINDEX"
  ),
  array(
   "name"               =>"TABINDEX_FRAME"
  ),
  array(
   "name"               =>"FOOTER"
  ),
  array(
   "name"               =>"USERNAME"
  ),
  array(
   "name"               =>"PATH"
  ),
  array(
   "name"               =>"LOGIN"
  ),
  array(
   "name"		=>"SETACTPAR"
  ),
  array(
   "name"		=>"HTMLHEAD"
  ),
  array(
   "name"		=>"HTMLFOOT"
  ),
  array(
   "name"               =>"ERROR",
  ),
  array(
   "name"               =>"HELPVIEW", 
  ),
  array(
   "name"               =>"PROGRESSBAR", 
  ),
  array(
   "name"               =>"PROGRESSBAR1", 
  ),
  array(
   "name"               =>"PROGRESSBAR2", 
   "script"             =>"progressbar.php",
   "function"           =>"progressbar2"	
  ),
  array(
   "name"               =>"WVERSION", 
  ),
  array(
   "name"               =>"MSGCACHE", 
  )


	
        	             );
  

?>
