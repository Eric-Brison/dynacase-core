<?php
// ---------------------------------------------------------------
// $Id: tabindex.php,v 1.1 2002/01/08 12:41:34 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Zone/Core/tabindex.php,v $
// ---------------------------------------------------------------
//    O   Anakeen - 2000
//   O*O  Anakeen Development Group
//    O   dev@anakeen.com
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
// $Log: tabindex.php,v $
// Revision 1.1  2002/01/08 12:41:34  eric
// first
//
// Revision 1.13  2001/10/17 14:45:16  eric
// mise en place de i18n via gettext
//
// Revision 1.12  2001/08/30 15:07:45  eric
// correction = / ==
//
// Revision 1.11  2001/08/29 16:07:57  yannick
// See changelog
//
// Revision 1.10  2001/08/29 12:51:04  yannick
// Bouchage du trou dans tabindex
//
// Revision 1.9  2000/11/14 17:35:31  yannick
// Affichage des TabIndex
//
// Revision 1.8  2000/11/13 11:36:19  marc
// Gestion des droits pour affichage dans Tabindex
//
// Revision 1.7  2000/11/08 11:33:44  marc
// Show tabs according acl
//
// Revision 1.6  2000/10/23 14:11:22  yannick
// Gestion des droits
//
// Revision 1.5  2000/10/22 14:19:06  marc
// Gestion des langues
//
// Revision 1.4  2000/10/19 16:45:39  marc
// Mise au point
//
// Revision 1.3  2000/10/11 19:44:41  marc
// Mise au point (accès graphiques, Css pour TABINDEX...)
//
// Revision 1.2  2000/10/10 19:09:11  marc
// Mise au point
//
// Revision 1.1  2000/10/09 19:00:33  marc
// Creation
//
// Revision 1.1  2000/10/06 19:37:44  marc
// Creation
//
//
// ---------------------------------------------------------------
include_once("Class.Action.php");


// -----------------------------------
function tabindex(&$action) {
// -----------------------------------
  global $HTTP_GET_VARS;
  $appname = $HTTP_GET_VARS["app"];
  $actname = $HTTP_GET_VARS["action"];

  $appcalled = new Application();
  $appcalled->Set($appname, $action->parent);
  $actcalled = new Action();
  $actcalled->Set($actname, $appcalled, $action->session);

  $query = new QueryDb($action->dbaccess, "Action");
  $query->order_by = "toc_order";
  $query->basic_elem->sup_where =
    array( "toc='Y'", 
	   "available='Y'",
	   "id_application=".$appcalled->id);
  $query->Query();
  $itoc = 0;
  if ($query->nb>0) { 
    while(list($k, $v) = each($query->list)) {
      $v->Set($v->name, $actcalled->parent, $actcalled->session);
      if ($v->HasPermission($v->acl)) {
        $toc[$itoc]["classlabel"]   = ($v->name==$actcalled->name?"TABLabelSelected":"TABLabel");
        $toc[$itoc]["classcell"]   = ($v->name ==$actcalled->name?"TABBackgroundSelected":"TABBackground");
        $toc[$itoc]["base"]    = $action->parent->GetParam("CORE_BASEURL");
        $toc[$itoc]["app"]     = $actcalled->parent->name;
        $toc[$itoc]["action"]  = $v->name;
        $limg = ($v->name==$actcalled->name?"tabselected.png":"tab.png");
        $toc[$itoc]["img"]   = $action->parent->GetImageUrl($limg);;
        if (substr($v->short_name,0,1) == '&' ) {
          $sn = substr($v->short_name,1,strlen($v->short_name));
          $toc[$itoc]["label"]   = $actcalled->text($sn);
        } else {
          $toc[$itoc]["label"]   = _($v->short_name);
        }
        $itoc++;
      }
    }
  }
  if (isset($toc)) {
    $action->lay->SetBlockCorresp("TAG", "TAG_LABELCLASS", "classlabel");
    $action->lay->SetBlockCorresp("TAG", "TAG_CELLBGCLASS", "classcell");
    $action->lay->SetBlockCorresp("TAG", "TAG_ENTRYURLROOT", "base");
    $action->lay->SetBlockCorresp("TAG", "TAG_ENTRYAPP", "app");
    $action->lay->SetBlockCorresp("TAG", "TAG_ENTRYPAGE", "action");
    $action->lay->SetBlockCorresp("TAG", "TAG_ENTRYIMG", "img");
    $action->lay->SetBlockCorresp("TAG", "TAG_ENTRYLABEL", "label");
    $action->lay->SetBlockData("TAG", $toc);
    $action->lay->SetBlockCorresp("COMPLETE", "TAG_CELLBGCLASS", "classcell");
    $action->lay->SetBlockData("COMPLETE", $toc);
    if ($appcalled->with_frame == "Y") {
      $action->lay->set("TARGET","main");
    } else {
      $action->lay->set("TARGET","_self");
    }
    $action->lay->SetBlockData("NOTAG", NULL);
  } else {
    $action->lay->SetBlockData("TAG", NULL);
    $action->lay->SetBlockCorresp("NOTAG", "TAG_NONE", "notag");
    $action->lay->SetBlockData("NOTAG", array(array("notag"=>" ")));
  }
}
?>
