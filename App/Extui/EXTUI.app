<?php
// ---------------------------------------------------------------
// $Id:  $
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
		   "name"	 =>"EXTUI",		//Name
		   "short_name"	=>N_("Ext ui api"),    	//Short name
		   "description"=>N_("Ext user interface api"),  //long description
		   "access_free"=>"Y",			//Access free ? (Y,N)
		   "icon"	=>"extuiapi.png",	//Icon
		   "displayable"=>"N",			//Should be displayed on an app list (Y,N)
		   "with_frame"	=>"Y",			//Use multiframe ? (Y,N)
		   "childof"	=>""		        // instance of 
		   );

  


$action_desc = array (
                      array( 
                            "name"              =>"EUI_XMLMENU",
                            "short_name"        =>N_("xml menu parsing"))
		      );
?>
