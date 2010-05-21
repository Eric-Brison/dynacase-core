<?php
// ---------------------------------------------------------------
// $Id: FDC.app,v 1.3 2008/11/05 10:10:41 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/fdc/FDC.app,v $
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
		   "name"	 =>"FDC",		//Name
		   "short_name"	=>N_("Common functions"),    	//Short name
		   "description"=>N_("Common function for FREEDOM client API"),  //long description
		   "access_free"=>"Y",			//Access free ? (Y,N)
		   "icon"	=>"freecommon.gif",	//Icon
		   "displayable"=>"N",			//Should be displayed on an app list (Y,N)
		   "with_frame"	=>"N",			//Use multiframe ? (Y,N)
		   "childof"	=>""		        // instance of FREEDOM GENERIC application	
		   );

  

$action_desc = array (  
		      array( 
			    "name"		=>"GETDOCVALUE",
			    "short_name"	=>N_("get value for an attribute of document")
			    ),
		      array( 
			    "name"		=>"GETDOCVALUES",
			    "short_name"	=>N_("get all values of document")
			    ),
		      array( 
			    "name"		=>"GETDOCSVALUE",
			    "short_name"	=>N_("get a value for a set of document")
			    ),
		      array( 
			    "name"		=>"GETDOCPROPERTIES",
			    "short_name"	=>N_("get all properties of document")
			    ),
		      array( 
			    "name"		=>"SETPARAMU",
			    "short_name"	=>N_("set user parameter")
			    )); 
		
?>
