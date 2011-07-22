<?php
/**
 * Definition of bar menu for folder navigation
 *
 * @author Anakeen 2000 
 * @version $Id: barmenu.php,v 1.19 2007/08/10 16:09:49 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
 /**
 */




include_once("FDL/Class.Dir.php");
include_once("FDL/Class.QueryDir.php");
include_once("FDL/freedom_util.php");  

include_once("FDL/popup_util.php");



// -----------------------------------
function barmenu(&$action) {
  // -----------------------------------
  $dbaccess = $action->GetParam("FREEDOM_DB");
  popupInit("newmenu",    array('newdoc','newsystem','newfld','newprof','newfam','newwf','newact'));
  popupInit("searchmenu", array( 'speedsearch','newsearch','newdsearch','newsearchfulltext'));
 
  popupInit("helpmenu", array('help','import','importtar','planexec'));

  $tmark=array();
  $tid=array();
  $tbook=array('managebook','addtobook','broot');
  $ubook=$action->GetParam("FREEDOM_UBOOK");
  if (strlen($ubook) > 2) {
    
    $tubook = explode('][',substr($ubook,1,-1));
    
    foreach ($tubook as $k=>$v) {
      list($id,$label)=explode("|",$v);
      $tid[$id]=$label;
      $tbook[]="bookmark$id";
      $tmark[]=array("idmark"=>"bookmark$id",
		     "markid"=>$id,
		     "labelmark"=>$label);
    }
    popupInit("bookmarks",$tbook );
    foreach ($tid as $k=>$v) {
       popupActive("bookmarks",1,"bookmark$k");
    }
    
  } else {
    popupInit("bookmarks",$tbook );
  }
  

  popupActive("newmenu",1,'newdoc'); 
  popupActive("newmenu",1,'newfld'); 
  popupActive("newmenu",1,'newact'); 
  popupActive("newmenu",1,'newprof');
  popupInvisible("newmenu",1,'newsystem');
  if ($action->HasPermission("FREEDOM_ADMIN")) {
    popupActive("helpmenu",1,'planexec');
  } 
  if ($action->HasPermission("FREEDOM_MASTER")) {
    popupActive("helpmenu",1,'import'); 
    popupActive("helpmenu",1,'importtar'); 
    popupActive("newmenu",1,'newact'); 
    popupActive("newmenu",1,'newsystem');
    popupActive("newmenu",1,'newfam');
    popupActive("newmenu",1,'newwf'); 
  } else {
    popupInvisible("helpmenu",1,'import');
    popupInvisible("helpmenu",1,'importtar');
    popupInvisible("newmenu",1,'newfam');
    popupInvisible("newmenu",1,'newact'); 
    popupInvisible("newmenu",1,'newwf'); 
  }
  popupActive("searchmenu",1,'newsearch');
  popupActive("searchmenu",1,'newdsearch');
  popupInvisible("searchmenu",1,'speedsearch');
  popupActive("searchmenu",1,'newsearchfulltext');
  // if ($action->GetParam("TE_ACTIVATE") == "yes") popupActive("searchmenu",1,'newsearchfulltext');
  //else popupInvisible("searchmenu",1,'newsearchfulltext');
 
  popupActive("helpmenu",1,'help');
  popupActive("bookmarks",1,'managebook');
  popupActive("bookmarks",1,'addtobook');
  $rootlabel=getTDoc($dbaccess,9);
  if ($rootlabel) {
    $action->lay->set("rootlabel",$rootlabel["title"]);
    popupActive("bookmarks",1,'broot');
  } else {
    popupInvisible("bookmarks",1,'broot');
  }

  $action->lay->setBlockData("MARKS",$tmark);
  popupGen(1);

}
?>
