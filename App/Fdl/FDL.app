<?php
// ---------------------------------------------------------------
// $Id: FDL.app,v 1.58 2008/12/19 16:55:40 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/App/Fdl/FDL.app,v $
// ---------------------------------------------------------------
//  O   Anakeen - 2002
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
"name"		=>"FDL",		//Name
"short_name"	=>N_("Freedoms lib"),		//Short name
"description"	=>N_("Freedoms library"),//long description
"access_free"	=>"N",			//Access free ? (Y,N)
"icon" 		=>"fdl.gif",
"displayable"	=>"N",			//Should be displayed on an app list (Y,N)
"iorder"	=>20,                   // install order
"tag"           => "CORE"
);

$app_acl = array (
  array(
   "name"		=>"NORMAL",
   "description"	=>N_("Access Action Library"),
   "group_default"       =>"Y"),
  array(
   "name"		=>"EDIT",
   "description"	=>N_("Access to edit action"),
   "group_default"       =>"Y"),
  array(
   "name"		=>"EXPORT",
   "description"	=>N_("For export functions"),
   "group_default"       =>"N"),
  array(
   "name"		=>"FAMILY",
   "description"	=>N_("Manage families"),
   "group_default"       =>"N")
);

$action_desc = array (

  array(
   "name"		=>"NONE",
   "short_name"		=>N_("nothing action"),
   "acl"		=>"NORMAL",
   "root"		=>"Y"
  ) ,
  array(
   "name"		=>"ENUM_CHOICE",
   "short_name"		=>N_("to choose value from set"),
   "acl"		=>"NORMAL",
  ) ,
  array(
   "name"               =>"SPECIALHELP",
   "short_name"         =>N_("to choose value from special interface"),
   "acl"                =>"NORMAL",
  ) ,
  array(
   "name"		=>"AUTOCOMPLETION",
   "short_name"		=>N_("to choose value from input help"),
   "acl"		=>"NORMAL",
  ) ,
  array(
   "name"               =>"FDL_FAMILYSCHEMA",
   "short_name"         =>N_("get family import schema"),
   "acl"                =>"NORMAL",
  ) ,
  array(
   "name"		=>"EDITICON",
   "short_name"		=>N_("change icon of document"),
   "acl"		=>"NORMAL",
  ) ,
  array(
   "name"       =>"VIEWEXTDOC",
   "short_name"     =>N_("view document in extjs"),
   "acl"        =>"NORMAL",
  ) ,
  array(
   "name"       =>"EDITEXTDOC",
   "short_name"     =>N_("edit document in extjs"),
   "acl"        =>"NORMAL",
  ) ,
  array(
   "name"       =>"OPENDOC",
   "short_name"     =>N_("open document to edit or view it"),
   "acl"        =>"NORMAL",
  ) ,
  array(
   "name"               =>"FDL_CSS",
   "layout"		=>"freedom.css"
  ),
  array(
   "name"               =>"VIEWDOCJS",
   "layout"		=>"viewdoc.js"
  ),
  array(
   "name"               =>"ALLEDITJS",
   "short_name"         =>N_("All js in one for edition"),
   "acl"                =>"NORMAL"
  ),
  array(
   "name"               =>"ALLEDITCSS",
   "short_name"         =>N_("All css in one for edition"),
   "acl"                =>"NORMAL"
  ),
  array(
   "name"               =>"EDITJS",
   "script"		=>"cacheone.php",
   "function"		=>"cacheone",
   "layout"		=>"editcommon.js"
  ),
  array(
   "name"               =>"EDITIJS",
   "script"		=>"cacheone.php",
   "function"		=>"cacheone",
   "layout"		=>"editidoc.js"
  ),
  array(
   "name"               =>"ENUMCHOICEJS",
   "script"		=>"cacheone.php",
   "function"		=>"cacheone",
   "layout"		=>"enum_choice.js"
  ),
  array(
   "name"		=>"FREEDOM_INIT",
   "short_name"		=>N_("Freedom initialisation"),
   "acl"		=>"NORMAL"
  ),
  array(
   "name"		=>"POPUPDOCDETAIL",
   "short_name"		=>N_("Document context menu"),
   "acl"		=>"NORMAL"
  ),
  array(
   "name"		=>"POPUPEDITSTATE",
   "short_name"		=>N_("Edit state bar menu"),
   "acl"		=>"EDIT"
  ),
  array(
   "name"		=>"POPUPFAMDETAIL",
   "short_name"		=>N_("Document context menu"),
   "acl"		=>"FAMILY"
  ),
  array(
   "name"		=>"EXPORTFLD",
   "short_name"		=>N_("export folder"),
   "acl"		=>"EXPORT"
  ) ,

  array(
   "name"               =>"EXPORTXMLFLD",
   "short_name"         =>N_("export folder in xml format"),
   "acl"                =>"EXPORT"
  ) ,
  array(
   "name"		=>"EXPORTFILE",
   "short_name"		=>N_("export file to consulting"),
   "acl"		=>"NORMAL"
  ) ,
  array(
   "name"		=>"VIEWWASK",
   "short_name"		=>N_("view specific ask"),
   "acl"		=>"NORMAL"
  ) ,
  array(
   "name"		=>"EDITWASK",
   "short_name"		=>N_("edit specific ask"),
   "acl"		=>"NORMAL"
  ),
  array(
   "name"		=>"GOTOWASK",
   "short_name"		=>N_("goto latest specific ask"),
   "acl"		=>"NORMAL"
  ),
  array(
   "name"		=>"SETWASK",
   "short_name"		=>N_("set specific ask"),
   "acl"		=>"NORMAL"
  ) ,
  array(
   "name"		=>"EXPORTFIRSTFILE",
   "short_name"		=>N_("export first file to consulting"),
   "acl"		=>"NORMAL",
   "script"		=>"exportfile.php",
   "function"		=>"exportfirstfile"
  ) ,
  array(
   "name"		=>"MAILCARD",
   "short_name"		=>N_("send a document"),
   "acl"		=>"NORMAL"
  ) ,
  array(
   "name"		=>"DIFFDOC",
   "short_name"		=>N_("difference between 2 documents"),
   "acl"		=>"NORMAL"
  ) ,
  array(
   "name"               =>"VIEWDESTROYDOC",
   "short_name"         =>N_("view last historic items for a destroyed document"),
   "acl"                =>"NORMAL"
  ) ,
  array(
   "name"		=>"EDITMAIL",
   "short_name"		=>N_("edit mail"),
   "acl"		=>"NORMAL"
  )  ,
  array(
   "name"		=>"FAMILY_HELP",
   "short_name"		=>N_("help manual for family"),
   "acl"		=>"NORMAL"
  )  ,
  array(
   "name"               =>"DOCHELP",
   "short_name"         =>N_("help inline"),
   "acl"                =>"NORMAL"
  )  ,
  array(
   "name"		=>"CONFIRMMAIL",
   "short_name"		=>N_("confirm mail sended before change state"),
   "acl"		=>"NORMAL"
  ),
  array(
   "name"		=>"MODACL",
   "short_name"		=>N_("modify acl"),
   "acl"		=>"NORMAL"
  ),
  array(
   "name"		=>"VIEWSCARD",
   "short_name"		=>N_("view standalone card"),
   "acl"		=>"NORMAL"
  ) ,
  array(
   "name"		=>"IMPCARD",
   "short_name"		=>N_("printed view card"),
   "acl"		=>"NORMAL"
  ) ,
  array(
   "name"		=>"FDL_CARD",
   "short_name"		=>N_("view card"),
   "acl"		=>"NORMAL"
  ) ,
  array(
   "name"		=>"FDL_GETVALUE",
   "short_name"		=>N_("get value of an attribute"),
   "acl"		=>"NORMAL"
  ) ,
  array(
   "name"		=>"FDL_CONFIDENTIAL",
   "short_name"		=>N_("display a confidential doc"),
   "acl"		=>"NORMAL"
  ) ,

  array(
   "name"		=>"LOCKFILE",
   "short_name"		=>N_("lock file to edit"),
   "acl"		=>"EDIT"
  ) ,
  array(
   "name"		=>"RESTOREDOC",
   "short_name"		=>N_("restore document from trash"),
   "acl"		=>"EDIT"
  ) ,
  array(
   "name"		=>"EDITOPTION",
   "short_name"		=>N_("interface to change document option"),
   "acl"		=>"EDIT"
  ) ,
  array(
   "name"		=>"MODOPTION",
   "short_name"		=>N_("modify document option"),
   "acl"		=>"EDIT"
  ) ,

  array(
   "name"		=>"VIEWOPTION",
   "short_name"		=>N_("view document option"),
   "script"		=>"editoption.php",
   "function"           =>"viewoption",
   "acl"		=>"NORMAL"
  ) ,

  array(
   "name"		=>"VIEWXML",
   "short_name"		=>N_("view xml"),
   "acl"		=>"NORMAL"
  ) ,

  array(
   "name"		=>"UNLOCKFILE",
   "short_name"		=>N_("unlock file to discard edit"),
   "acl"		=>"EDIT",
   "layout"		=>"unlockfile.xml"
  ) ,

  array(
   "name"		=>"FDL_METHOD",
   "short_name"		=>N_("apply a method to a document"),
   "acl"		=>"EDIT"
  ) ,
  array(
   "name"		=>"WORKFLOW_INIT",
   "short_name"		=>N_("init workflow profile attributes"),
   "acl"		=>"NORMAL"
  ) ,
  array(
   "name"		=>"MVIEW_SAVEGEO",
   "short_name"		=>N_("save geometry of mini view"),
   "acl"		=>"NORMAL"
  ) ,
  array(
   "name"		=>"VCONSTRAINT",
   "short_name"		=>N_("verify constraint attribute"),
   "acl"		=>"NORMAL"
  )  ,
  array(
   "name"		=>"EDITCHANGESTATE",
   "short_name"		=>N_("interface to change state"),
   "acl"		=>"NORMAL"
  ) ,
  array(
   "name"		=>"FDL_PUBMAIL",
   "short_name"		=>N_("emailing"),
   "acl"		=>"NORMAL"
  )  ,
  array(
   "name"		=>"FDL_PUBPRINT",
   "short_name"		=>N_("eprinting"),
   "acl"		=>"NORMAL"
  )  ,
  array(
   "name"		=>"FDL_PUBPREVIEW",
   "short_name"		=>N_("epreview"),
   "acl"		=>"NORMAL"
  )  ,
  array(
   "name"		=>"FDL_PUBNAVPREVIEW",
   "short_name"		=>N_("enavpreview"),
   "acl"		=>"NORMAL"
  )  ,
  array(
   "name"		=>"EDITATTRIBUTE",
   "short_name"		=>N_("edit attribute inline"),
   "acl"		=>"NORMAL"
  )  ,
  array(
   "name"		=>"MODATTRIBUTE",
   "short_name"		=>N_("modify attribute inline"),
   "acl"		=>"NORMAL"
  ) ,
  array(
   "name"		=>"EDITAFFECT",
   "short_name"		=>N_("edition to affect user"),
   "acl"		=>"NORMAL"
  )  ,
  array(
   "name"		=>"ADDENUMITEM",
   "short_name"		=>N_("add enum html input attribute"),
   "acl"		=>"NORMAL"
  )  ,
  array(
   "name"		=>"AFFECT",
   "short_name"		=>N_("affect user to a document"),
   "acl"		=>"NORMAL"
  ) ,
  array(
   "name"		=>"DESAFFECT",
   "short_name"		=>N_("unaffect user to a document"),
   "acl"		=>"NORMAL"
  ) ,
  array(
   "name"		=>"VIEW_WORKFLOW_GRAPH",
   "short_name"		=>N_("view graph of workflow"),
   "acl"		=>"NORMAL"
  ) ,
  array(
   "name"		=>"WORKFLOW_GRAPH",
   "short_name"		=>N_("view graph of workflow"),
   "acl"		=>"NORMAL"
  ) ,
  array(
   "name"		=>"PARAM_WORKFLOW_GRAPH",
   "short_name"		=>N_("view graph of workflow"),
   "acl"		=>"NORMAL"
  ) ,
  array(
   "name"		=>"SETTXTFILE",
   "short_name"		=>N_("update text file for fulltext"),
   "openaccess"		=>"Y",
   "acl"		=>"EDIT"
  ),
  array(
   "name"		=>"INSERTFILE",
   "short_name"		=>N_("insert converted file"),
   "openaccess"		=>"Y",
   "acl"		=>"EDIT"
  ),
  array(
   "name"		=>"INSERTDOCUMENT",
   "short_name"		=>N_("insert document in a folder"),
   "acl"		=>"EDIT"
  ),
  array(
   "name"		=>"EDITINSERTDOCUMENT",
   "short_name"		=>N_("interface insert document in a folder"),
   "acl"		=>"EDIT"
  ),
  array(
   "name"		=>"SEARCHDOCUMENT",
   "short_name"		=>N_("search document to insert in a folder"),
   "acl"		=>"NORMAL"
  ),

  array(
   "name"		=>"FDL_FORUMADDENTRY",
   "short_name"		=>N_("add entry in a document forum"),
   "acl"		=>"NORMAL"
  ),
  array(
   "name"		=>"FDL_FORUMDELENTRY",
   "short_name"		=>N_("del entry from a document forum"),
   "acl"		=>"NORMAL"
  ),
  array(
   "name"		=>"FDL_FORUMOPEN",
   "short_name"		=>N_("open forum"),
   "acl"		=>"NORMAL"
  ),
  array(
   "name"		=>"FDL_FORUMCLOSE",
   "short_name"		=>N_("close forum"),
   "acl"		=>"NORMAL"
  ),
  array(
   "name"		=>"FDL_FORUMCREATE",
   "short_name"		=>N_("create forum"),
   "acl"		=>"NORMAL"
  ),
  array(
   "name"		=>"FDL_FORUMMENU",
   "short_name"		=>N_("menu forum"),
   "acl"		=>"NORMAL"
  ),
  array(
   "name"		=>"FCKIMAGE",
   "short_name"		=>N_("fck image browser"),
   "acl"		=>"EDIT"
  ),
  array(
   "name"		=>"FCKUPLOAD",
   "short_name"		=>N_("fck image upload"),
   "acl"		=>"EDIT"
  ),
  array(
   "name"		=>"VERIFYCOMPUTEDFILES",
   "short_name"		=>N_("verify files status"),
   "acl"		=>"NORMAL"
  ),
  array(
   "name"		=>"GETFILETRANSFORMATION",
   "short_name"		=>N_("retrieve file converted"),
   "acl"		=>"NORMAL"
  ),
  array(
   "name"		=>"GETFILETRANSSTATUS",
   "short_name"		=>N_("retrieve task status"),
   "acl"		=>"NORMAL"
  ),
  array(
   "name"		=>"FCKDOCATTR",
   "short_name"		=>N_("get attribute for fck toobar"),
   "acl"		=>"NORMAL"
  ),
  array(
   "name"		=>"VIEWTIMERS",
   "short_name"		=>N_("view timers attached to a document"),
   "acl"		=>"NORMAL"
  ),
  array(
      "name"	=> "REPORT_EXPORT_CSV",
      "short_name" => N_("export a report"),
      "acl" => "NORMAL"
  )



			);

?>
