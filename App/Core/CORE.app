<?
// ---------------------------------------------------------------
// $Id: CORE.app,v 1.2 2002/04/15 14:19:59 eric Exp $
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
// ---------------------------------------------------------------
// $Log: CORE.app,v $
// Revision 1.2  2002/04/15 14:19:59  eric
// ajout clear cache objet
//
// Revision 1.1  2002/01/08 12:41:34  eric
// first
//
// Revision 1.15  2001/11/14 15:19:29  eric
// change name action CORE -> LOGO
//
// Revision 1.14  2001/10/17 10:01:43  eric
// mise en place de i18n via gettext
//
// Revision 1.13  2001/08/29 13:01:42  yannick
// Ajout du tabindex multiframe
//
// Revision 1.12  2001/08/10 08:06:03  eric
// ajout action ERROR
//
// Revision 1.11  2001/06/28 10:31:32  eric
// multiframe support
//
// Revision 1.10  2001/06/13 13:24:03  eric
// multi frame support
//
// ---------------------------------------------------------------
global $app_desc,$action_desc;

$app_desc= array (
"name" 		=> "CORE",              //Name
"short_name"	=> N_("Core"),              //Short name
"description"	=> N_("Core Application Manager"),   //long description
"access_free"	=>"Y",                   //Access type (ALL,RESTRICT)
"icon"		=>"core.png",            //Icon
"displayable"	=>"N",                    //Should be displayed on an app list
);

$action_desc = array (
  array(
   "name"               =>"LOGO",
   "layout"             =>"logo.xml",
   "root"               =>"Y"
  ) ,
  array(
   "name"               =>"MAIN",
   "layout"		=>"core.xml"
  ),
  array(
   "name"               =>"GENCSS",
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
  )
                     );
  

?>
