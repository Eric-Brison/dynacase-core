<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: generic_editchangecatg.php,v 1.10 2005/06/28 08:37:46 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage 
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: generic_editchangecatg.php,v 1.10 2005/06/28 08:37:46 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Generic/generic_editchangecatg.php,v $
// ---------------------------------------------------------------


include_once("FDL/Class.Dir.php");
include_once("GENERIC/generic_util.php");

// -----------------------------------
function generic_editchangecatg(&$action) {
  // -----------------------------------
  global $docid;
  global $dbaccess;

  $docid=GetHttpVars("id"); // the user to change catg

  
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $homefld = new_Doc( $dbaccess,getDefFld($action) );

  $doc = new_Doc($dbaccess,$docid);
  $action->lay->Set("username",$doc->title);



  $stree=getChildCatg($homefld->id, 1, true);

  reset($stree);
  
  while (list($k,$v) = each($stree)) {
    if (isInDir($dbaccess, $v["id"], $doc->initid)) $checked="checked";
    else  $checked="";
    $stree[$k]["checked"]=$checked;
  }
  
  $action->lay->SetBlockData("CATG",$stree);
  $action->lay->Set("topdir",getDefFld($action));
  $action->lay->Set("docid",$docid);
  

}

?>
