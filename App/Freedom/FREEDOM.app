<?php
// ---------------------------------------------------------------
// $Id: FREEDOM.app,v 1.53 2008/12/30 17:14:40 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/App/Freedom/FREEDOM.app,v $
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
"name"		=>"FREEDOM",		//Name
"short_name"	=>N_("Docs admin"),		//Short name
"description"	=>N_("Documents administration"),//long description
"access_free"	=>"N",			//Access free ? (Y,N)
"icon"		=>"freedom.png",	//Icon
"displayable"	=>"Y",			//Should be displayed on an app list (Y,N)
"with_frame"	=>"Y",			//Use multiframe ? (Y,N)
"iorder"        =>130,
"tag"           =>"CORE"
);

$app_acl = array (
  array(
   "name"               =>"FREEDOM_MASTER",
   "description"        =>N_("Access Families Management")),
  array(
   "name"               =>"FREEDOM_ADMIN",
   "description"        =>N_("Access Batch Management")),
  array(
   "name"               =>"FREEDOM",
   "description"        =>N_("Access To My Own account"),
   "group_default"       =>"Y"),
  array(
   "name"               =>"FREEDOM_READ",
   "description"        =>N_("Access To Read Only"),
   "group_default"       =>"Y"),
  array(
   "name"               =>"FREEDOM_GED",
   "description"        =>N_("Access To Main Interface"),
   "group_default"       =>"N")
);

$action_desc = array (
  array( 
   "name"		=>"FREEDOM_FRAME",
   "short_name"		=>N_("Freedoms frame"),
   "acl"		=>"FREEDOM_GED",
   "root"		=>"Y"
  ) ,
  array( 
   "name"		=>"FREEDOM_LIST",
   "short_name"		=>N_("Freedoms list"),
   "acl"		=>"FREEDOM_READ"
  ) ,
  array( 
   "name"		=>"ADMIN_TIMERS",
   "short_name"		=>N_("Timers management"),
   "acl"		=>"FREEDOM_ADMIN"
  ) ,
  array( 
   "name"		=>"TIMERS_ADMIN_RESULT",
   "short_name"		=>N_("Timers management result"),
   "acl"		=>"FREEDOM_ADMIN"
  ) ,
  array( 
   "name"		=>"BATCHEXEC",
   "short_name"		=>N_("batch execution"),
   "acl"		=>"FREEDOM_ADMIN"
  ) ,
  array( 
   "name"		=>"FREEDOM_APPLYBATCH",
   "short_name"		=>N_("construct batch document"),
   "acl"		=>"FREEDOM_ADMIN"
  ) ,
  array( 
   "name"		=>"FREEDOM_CHOOSEACTION",
   "short_name"		=>N_("choose action from batch document"),
   "acl"		=>"FREEDOM_ADMIN"
  ) ,
  array( 
   "name"		=>"FREEDOM_ADDBATCH",
   "short_name"		=>N_("add batch document"),
   "acl"		=>"FREEDOM_ADMIN"
  ) ,
  array( 
   "name"		=>"FREEDOM_COLUMN",
   "short_name"		=>N_("Freedoms list by column"),
   "acl"		=>"FREEDOM_READ"
  ) ,
  array( 
   "name"		=>"ENUM_CHOICE",
   "short_name"		=>N_("to choose value from set"),
   "acl"		=>"FREEDOM"
  ) ,
  array( 
   "name"		=>"FREEDOM_EDITIMPORT",
   "short_name"		=>N_("query document import"),
   "acl"		=>"FREEDOM_MASTER"
  ) ,
  array( 
   "name"		=>"FREEDOM_EDITIMPORTTAR",
   "short_name"		=>N_("query tar document import"),
   "acl"		=>"FREEDOM_MASTER"
  ) ,
  array( 
   "name"		=>"EDITEXPORT",
   "short_name"		=>N_("interface to export documents"),
   "acl"		=>"FREEDOM_MASTER"
  ) ,
  array( 
   "name"		=>"EDITEXPORTCHOOSECOLS",
   "short_name"		=>N_("interface to choose column export documents"),
   "acl"		=>"FREEDOM"
  ) ,
  array( 
   "name"		=>"MODEXPORTCHOOSECOLS",
   "short_name"		=>N_("interface to save choice of column export documents"),
   "acl"		=>"FREEDOM"
  ) ,
  array( 
   "name"		=>"FREEDOM_BGIMPORT",
   "short_name"		=>N_("background document import"),
   "acl"		=>"FREEDOM_MASTER"
  ) ,
  array( 
   "name"		=>"FREEDOM_IMPORT",
   "short_name"		=>N_("add document import"),
   "acl"		=>"FREEDOM_MASTER"
  ) ,
  
  array( 
   "name"               =>"FREEDOM_IMPORT_XML",
   "short_name"         =>N_("add document import from XML"),
   "acl"                =>"FREEDOM_MASTER"
  ) ,
  array( 
   "name"		=>"FREEDOM_IMPORT_TAR",
   "short_name"		=>N_("import archive file"),
   "acl"		=>"FREEDOM_MASTER"
  ) ,
  array( 
   "name"		=>"FREEDOM_ANA_TAR",
   "short_name"		=>N_("analyze archive file"),
   "acl"		=>"FREEDOM_MASTER"
  ) ,
  array( 
   "name"		=>"FREEDOM_IMPORT_DIR",
   "short_name"		=>N_("add document from directories file import"),
   "acl"		=>"FREEDOM_MASTER"
  ) ,
  array( 
   "name"		=>"FREEDOM_VIEW_TAR",
   "short_name"		=>N_("view imported tar"),
   "acl"		=>"FREEDOM_MASTER"
  ) ,
  array( 
   "name"		=>"FREEDOM_DEL_TAR",
   "short_name"		=>N_("delete imported tar"),
   "acl"		=>"FREEDOM_MASTER"
  ) ,
  array( 
   "name"		=>"FREEDOM_VIEW",
   "short_name"		=>N_("Freedoms view folder"),
   "layout"		=>"freedom_list.xml",
   "acl"		=>"FREEDOM_READ"
  ) ,
  array( 
   "name"		=>"FREEDOM_PREVIEW",
   "short_name"		=>N_("Freedoms preview document"),
   "acl"		=>"FREEDOM"
  ) ,
  array( 
   "name"		=>"POPUP",
   "short_name"		=>N_("popup menu"),
   "acl"		=>"FREEDOM_READ",
  ) ,
  array( 
   "name"		=>"ADDDIRFILE",
   "short_name"		=>N_("add file query into directory"),
   "acl"		=>"FREEDOM"
  ) ,
  array( 
   "name"		=>"FREEDOM_INSERTFLD",
   "short_name"		=>N_("insert containt of a folder into another"),
   "acl"		=>"FREEDOM"
  ) ,
  array( 
   "name"		=>"FREEDOM_CLEARFLD",
   "short_name"		=>N_("clear containt of a folder"),
   "acl"		=>"FREEDOM"
  ) ,
  array( 
   "name"		=>"MOVEDIRFILE", 
   "short_name"		=>N_("move file query into directory"),
   "acl"		=>"FREEDOM"
  ) ,
  array( 
   "name"		=>"DELDIRFILE",
   "short_name"		=>N_("delete file query into directory"),
   "acl"		=>"FREEDOM"
  ) ,
  array( 
   "name"		=>"FOLDERS",
   "short_name"		=>N_("folder tree"),
   "acl"		=>"FREEDOM_READ"
  ) ,
  array( 
   "name"		=>"EXPANDFLD",
   "short_name"		=>N_("expand folder tree"),
   "acl"		=>"FREEDOM_READ"
  ) ,
  array( 
   "name"		=>"FREEDOM_ICONS",
   "short_name"		=>N_("Freedoms icon list"),
   "acl"		=>"FREEDOM_READ"
  ) , 
  array( 
   "name"		=>"FREEDOM_LISTDETAIL",
   "short_name"		=>N_("Freedoms detail list"),
   "acl"		=>"FREEDOM_READ"
  ) ,
  array( 
   "name"		=>"FREEDOM_RSS",
   "short_name"		=>N_("Freedoms RSS syndication"),
   "openaccess"         => "Y",
   "acl"		=>"FREEDOM_READ"
  ) ,
  array( 
   "name"		=>"FOLIOLIST",
   "short_name"		=>N_("folio icon list"),
   "acl"		=>"FREEDOM_READ"
  ) ,
   array( 
    "name"		=>"FOLIOSEARCH",
   "short_name"		=>N_("folio icon list"),
   "acl"		=>"FREEDOM_READ"
  ) ,
  array(
   "name"		=>"FOLIOSEL",
   "short_name"		=>N_("folio select doc"),
   "acl"		=>"FREEDOM_READ"
  ) ,
  array( 
   "name"		=>"FOLIOPARAMS",
   "short_name"		=>N_("folio params ajax request"),
   "acl"		=>"FREEDOM_READ"
  ) ,
  array( 
   "name"		=>"FREEDOM_IFLD",
   "short_name"		=>N_("access path folder list"),
   "acl"		=>"FREEDOM_READ"
  ) ,
  array( 
   "name"		=>"BARMENU",
   "short_name"		=>N_("bar menu"),
   "acl"		=>"FREEDOM_READ"
  ) ,
  array( 
   "name"		=>"FREEDOM_CARD",
   "short_name"		=>N_("Freedoms card"),
   "acl"		=>"FREEDOM_READ"
  ) ,
  array( 
   "name"		=>"FREEDOM_DUPLICATE",
   "short_name"		=>N_("duplicate document"),
   "acl"		=>"FREEDOM"
  ) ,
  array( 
   "name"		=>"DEFATTR",
   "short_name"		=>N_("attributes definitions"),
   "acl"		=>"FREEDOM_MASTER"
  ) ,
  array( 
   "name"		=>"CREATEFAM",
   "short_name"		=>N_("edit create family"),
   "acl"		=>"FREEDOM_MASTER"
  ) ,
  array( 
   "name"		=>"CREATETHEFAM",
   "short_name"		=>N_("create family"),
   "acl"		=>"FREEDOM_MASTER"
  ) ,
  array( 
   "name"		=>"REFRESHDIR",
   "short_name"		=>N_("refresh directory"),
   "acl"		=>"FREEDOM"
  ) ,
  array( 
   "name"		=>"MODATTR",
   "short_name"		=>N_("attributes modification"),
   "acl"		=>"FREEDOM_MASTER"
  ) ,
  array( 
   "name"		=>"FREEDOM_EDITSTATE",
   "short_name"		=>N_("edit state "),
   "acl"		=>"FREEDOM"
  ) ,
array( 
   "name"		=>"FREEDOM_IEDIT",
   "short_name"		=>N_("edit icard "),
   "acl"		=>"FREEDOM"
  ) ,
array( 
   "name"		=>"FREEDOM_IEDIT2",
   "short_name"		=>N_("edit icard 2 "),
   "acl"		=>"FREEDOM"
  ) ,

array( 
   "name"		=>"EDITRANSITION",
   "short_name"		=>N_("edit workflow transitions "),
   "acl"		=>"FREEDOM"
  ) ,
array( 
   "name"		=>"RECUP_ARGS",
   "short_name"		=>N_("edit args of actions in workflow edition "),
   "acl"		=>"FREEDOM"
  ) ,

  array( 
   "name"		=>"EDITPROF",
   "short_name"		=>N_("edit profile access"),
   "acl"		=>"FREEDOM"
  ) ,
  array( 
   "name"		=>"MODPROF",
   "short_name"		=>N_("change profile access"),
   "acl"		=>"FREEDOM"
  ) ,
  array( 
   "name"		=>"CREATEPROF",
   "short_name"		=>N_("create profile access"),
   "acl"		=>"FREEDOM"
  ) ,
  array( 
   "name"		=>"MODSTATE",
   "short_name"		=>N_("change state transition"),
   "acl"		=>"FREEDOM"
  ) ,
  array( 
   "name"		=>"EDITDFLD",
   "short_name"		=>N_("edit default folder"),
   "acl"		=>"FREEDOM_MASTER"
  ) ,
  array( 
   "name"		=>"MODDFLD",
   "short_name"		=>N_("change default folder"),
   "acl"		=>"FREEDOM_MASTER"
  ) ,
  array( 
   "name"		=>"EDITWDOC",
   "short_name"		=>N_("choose workflow"),
   "acl"		=>"FREEDOM_MASTER"
  ) ,
  array( 
   "name"		=>"MODWDOC",
   "short_name"		=>N_("modify associated worflow"),
   "acl"		=>"FREEDOM_MASTER"
  ) ,
  array( 
   "name"		=>"CTRLDOC",
   "short_name"		=>N_("set the document controlled"),
   "acl"		=>"FREEDOM"
  ) ,
  array( 
   "name"		=>"CHANGEICON",
   "short_name"		=>N_("change icon document"),
   "acl"		=>"FREEDOM"
  ) ,
  array( 
   "name"		=>"CHANGETITLE",
   "short_name"		=>N_("change title family"),
   "acl"		=>"FREEDOM_MASTER"
  ) ,
  array( 
   "name"		=>"QUERYTITLE",
   "short_name"		=>N_("query icon"),
   "acl"		=>"FREEDOM_MASTER"
  ) ,
  array( 
   "name"		=>"SEARCH",
   "short_name"		=>N_("search document"),
   "acl"		=>"FREEDOM_READ"
  ) ,
  array( 
   "name"		=>"FULLSEARCH",
   "short_name"		=>N_("full text search document"),
   "acl"		=>"FREEDOM_READ"
  ) ,
  array( 
   "name"		=>"EDIT_SEARCH",
   "short_name"		=>N_("search document criteria"),
   "acl"		=>"FREEDOM_READ"
  ) ,
  array( 
   "name"		=>"QUERYFILE",
   "short_name"		=>N_("ask for a new file revision"),
   "acl"		=>"FREEDOM"
  ) ,
  array(
   "name"               =>"GENCSS",
   "layout"		=>"freedom.css"
  ),
  array(
   "name"		=>"FREEDOM_EDIT",
   "short_name"		=>N_("edit document properties"),
   "acl"		=>"FREEDOM"
  ),
  array(
   "name"		=>"FREEDOM_DEDIT",
   "short_name"		=>N_("edit default document properties"),
   "acl"		=>"FREEDOM_MASTER"
  ),
  array( 
   "name"		=>"REVCOMMENT",
   "short_name"		=>N_("add comment before revise document"),
   "acl"		=>"FREEDOM"
  ) ,
  array(
   "name"		=>"REVISION",
   "short_name"		=>N_("make a new document revision"),
   "acl"		=>"FREEDOM"
  ),
  array(
   "name"		=>"HISTO",
   "short_name"		=>N_("view history revision"),
   "acl"		=>"FREEDOM_READ"
  ),
  array(
   "name"		=>"GENCSS",
   "short_name"		=>N_("style sheet"),
   "acl"		=>"FREEDOM_READ"
  ),
  array(
   "name"		=>"POPUPCARD",
   "short_name"		=>N_("widget display popup for a description card"),
   "acl"		=>"FREEDOM_READ"
  ),
  array(
   "name"		=>"FREEDOM_LOGO",
   "acl"		=>"FREEDOM_READ"
  ),
  array(
   "name"		=>"FREEDOM_MOD",
   "short_name"		=>N_("Freedom modification"),
   "acl"		=>"FREEDOM"
  ),
 array(
   "name"		=>"FREEDOM_IMOD",
   "short_name"		=>N_("Freedom imodification"),
   "acl"		=>"FREEDOM"
  ),
 array(
   "name"		=>"VIEWICARD",
   "short_name"		=>N_("view idoc attribute card"),
   "acl"		=>"FREEDOM"
  ),
  array(
   "name"		=>"FREEDOM_DEL",
   "short_name"		=>N_("Freedom deletion"),
   "acl"		=>"FREEDOM"
  ),
  array(
   "name"		=>"FREEDOM_UPDATETITLE",
   "short_name"		=>N_("Freedom update title fields"),
   "acl"		=>"FREEDOM_MASTER",
   "function"           =>"freedom_updatetitle",
   "layout"		=>"freedom_admin.xml",
   "script"		=>"freedom_admin.php"
	
  ),
  array(
   "name"		=>"FREEDOM_ACCESS",
   "short_name"		=>N_("Freedom accessibilities"),
   "acl"		=>"FREEDOM"	
  ),
  array(
   "name"		=>"FREEDOM_GACCESS",
   "short_name"		=>N_("Freedom group accessibilities"),
   "acl"		=>"FREEDOM"	
  ),
  array(
   "name"		=>"FREEDOM_MODACCESS",
   "short_name"		=>N_("Freedom modify accessibilities"),
   "acl"		=>"FREEDOM"	
  ),
  array(
   "name"		=>"FREEDOM_INIT",
   "short_name"		=>N_("Freedom initialisation"),
   "acl"		=>"FREEDOM"	
  ),
  array( 
   "name"		=>"OPENFOLIO",
   "short_name"		=>N_("open portfolio"),
   "acl"		=>"FREEDOM_READ"
  ) ,
  array( 
   "name"		=>"SETSYSRSS",
   "short_name"		=>N_("set RSS usable"),
   "acl"		=>"FREEDOM_MASTER"
  ) ,
  array( 
   "name"		=>"FOLIOTAB",
   "short_name"		=>N_("portfolio tab"),
   "acl"		=>"FREEDOM_READ"
  ) ,
  array( 
   "name"		=>"FREEDOM_EDITPREFFAM",
   "short_name"		=>N_("choose preferred families"),
   "acl"		=>"FREEDOM"
  )  ,
  array( 
   "name"		=>"FREEDOM_MODPREFFAM",
   "short_name"		=>N_("modify preferred families"),
   "acl"		=>"FREEDOM"
  )  ,
  array( 
   "name"		=>"FREEDOM_ADDBOOKMARK",
   "short_name"		=>N_("add folder in bookmark"),
   "acl"		=>"FREEDOM"
  ) ,
  array( 
   "name"		=>"FREEDOM_PLANEXEC",
   "short_name"		=>N_("processes execution plan"),
   "acl"		=>"FREEDOM_ADMIN"
  )  ,
  array( 
   "name"		=>"FREEDOM_PROCESSTOEXEC",
   "short_name"		=>N_("creation processes"),
   "acl"		=>"FREEDOM_ADMIN"
  )  ,
  array( 
   "name"		=>"FREEDOM_SEARCHPROCESS",
   "short_name"		=>N_("search processes"),
   "acl"		=>"FREEDOM"
  )   ,
  array( 
   "name"		=>"RNAVIGATE",
   "short_name"		=>N_("navigate between relations"),
   "acl"		=>"FREEDOM_READ"
  )  ,
  array( 
   "name"		=>"RNAVIGATE2",
   "short_name"		=>N_("navigate between relations next"),
   "acl"		=>"FREEDOM_READ",
   "function"           =>"rnavigate2",
   "layout"		=>"rnavigate2.xml",
   "script"		=>"rnavigate.php"
  ),

  array( 
   "name"		=>"FORUM_SETDEFAULT",
   "short_name"		=>N_("disable/enable forum on documents"),
   "acl"		=>"FREEDOM_MASTER"
  ) ,
  array( 
   "name"		=>"SETLOGICALNAME",
   "short_name"		=>N_("set logical name identificator"),
   "acl"		=>"FREEDOM_MASTER"
  ) ,
                      );
   
?>
