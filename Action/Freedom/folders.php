<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: folders.php,v 1.18 2005/06/28 08:37:46 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: folders.php,v 1.18 2005/06/28 08:37:46 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/folders.php,v $
// ---------------------------------------------------------------



include_once("FDL/Lib.Dir.php");
include_once("FDL/Class.QueryDir.php");
include_once("FDL/freedom_util.php");  




// -----------------------------------
function folders(&$action) {
  // -----------------------------------

  
  global  $nbfolders, $dbaccess, $pexport;
  $nbfolders=0;

  


  // Get all the params      
  $dirid=GetHttpVars("dirid",0); // root directory

  $dbaccess = $action->GetParam("FREEDOM_DB");

  include_once("FDL/popup_util.php");
  //barmenu($action); // describe bar menu

  $homefld = new Dir( $dbaccess);
  $homefld = $homefld->GetHome();

  $action->lay->Set("homename", $homefld->title);
  $action->lay->Set("homeid", $homefld->id);
  

  $tmenuaccess = array(); // to define action an each icon



  if ($dirid == 0) $dirid=$action->getParam("ROOTFLD",getFirstDir($dbaccess));

  
  $doc = new_Doc($dbaccess, $dirid);
  $action->lay->Set("dirid", $dirid);
  $action->lay->Set("reptitle", str_replace('"','\"',$doc->title));
  $action->lay->Set("icon", $doc->getIcon());


  // ------------------------------------------------------
  // definition of popup menu
  popupInit("popfld", array('vprop','mkdir','export','refresh','cancel'));
  popupInit("poppaste", array('staticpaste','pastelatest','cancel2'));

  // for the first (top) folder
  popupActive("popfld",$nbfolders,'cancel');
  popupActive("popfld",$nbfolders,'vprop');

  // get export permission
  $appfld=new Application();
  $appfld->Set("FDL",$action->parent->parent);
  $pexport=$appfld->HasPermission("EXPORT");

  if ($pexport)  popupActive("popfld",$nbfolders,'export');
  else popupInvisible("popfld",$nbfolders,'export');

  popupActive("popfld",$nbfolders,'mkdir');  
  popupInvisible("popfld",$nbfolders,'refresh');  
  popupActive("poppaste",$nbfolders,'staticpaste');
  popupActive("poppaste",$nbfolders,'pastelatest');
  popupActive("poppaste",$nbfolders,'cancel2');


  $nbfolders++; // one for the top


  // define icon from style
  $iconfolder = $action->GetImageUrl("ftv2folderopen1.gif");
  $pathicon = explode("/",$iconfolder);
  if (count($pathicon) == 4) $action->lay->set("iconFolderPath",$pathicon[0]."/".$pathicon[1]);
  else $action->lay->set("iconFolderPath","FREEDOM");
  

  // define sub trees

  
  $stree=addfolder($doc, -1, "fldtop", false);
  $action->lay->Set("subtree", $stree);

  $action->lay->Set("idHomeFolder", $nbfolders);

  $htree=addfolder($homefld, 0, "fldtop");
  $action->lay->Set("hometree", $htree);
  

  //-------------- pop-up menu ----------------
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  // display popup js




  // display popup js
  popupGen($nbfolders);
  
}


// -----------------------------------
function addfolder($doc, $level, $treename, $thisfld=true) {
  // -----------------------------------
  global $dbaccess;
  global $tmenuaccess;
  global $nbfolders;
  global $action;
  global $pexport;
  

  if ($thisfld) {
    if ($level == 0) $levelp="";
    else $levelp = $level-1;
    if ($doc->owner < 0) $ftype=3;
    else if ($doc->id == 14) $ftype=5;
    else if ($doc->doctype == 'D') $ftype=1;
    else if ($doc->doctype == 'S') $ftype=2;

    $hasChild='false';
    // if ($doc->doctype != 'S') {
      // no child for a search
	if (hasChildFld($dbaccess,$doc->initid,($doc->doctype == 'S') ))  $hasChild='true';
	//}

    $ftype=$doc->getIcon();
    $ltree = "$treename$level = insFld(".$treename.$levelp.", gFld(\"".str_replace('"','\"',$doc->title)."\", \"#\",".$doc->initid.",\"$ftype\", $hasChild))\n";


    popupActive("popfld",$nbfolders,'cancel');
    popupActive("popfld",$nbfolders,'vprop');
    if ($pexport)  popupActive("popfld",$nbfolders,'export');
    else popupInvisible("popfld",$nbfolders,'export');
    if ($doc->doctype == 'D') {
      popupActive("popfld",$nbfolders,'mkdir');
      popupActive("popfld",$nbfolders,'refresh');
    } else {
      popupInvisible("popfld",$nbfolders,'mkdir');
      popupInvisible("popfld",$nbfolders,'refresh');
    }
    popupActive("poppaste",$nbfolders,'staticpaste');
    popupActive("poppaste",$nbfolders,'pastelatest');
    popupActive("poppaste",$nbfolders,'cancel2');
    $nbfolders++;
  } else $ltree = "";
  if ($doc->doctype == 'D') {

    if ($level < 0) {
    $ldir = getChildDir($dbaccess,$action->user->id, $doc->id);
  

    if (count($ldir) > 0 ) {
     
      while (list($k,$v) = each($ldir)) {
	$ltree .= addfolder($v, $level+1, $treename);
      }
    } 
  }
  }
  return $ltree;
}

?>
