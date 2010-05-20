<?php
/**
 * Specific menu for family
 *
 * @author Anakeen 2000 
 * @version $Id: editattribute.php,v 1.4 2006/11/13 16:06:39 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */


include_once("FDL/Class.Doc.php");
/**
 * Edit an attribute inline
 * @param Action &$action current action
 * @global docid Http var : document identificator to see
 * @global attrid Http var : the id of attribute to edit
 */
function editattribute(&$action) {
  $docid = GetHttpVars("docid");
  $attrid = GetHttpVars("attrid");
  $modjsft = GetHttpVars("modjsft","modattr");
  $dbaccess = $action->GetParam("FREEDOM_DB");


  header('Content-type: text/xml; charset=utf-8'); 

  $mb=microtime();

  $action->lay->set("CODE","OK");
  $action->lay->set("warning","");
  if ($modjsft=="undefined") $modjsft="modattr";
  $action->lay->set("modjsft",$modjsft);
 

  
  $doc = new_Doc($dbaccess, $docid);
  if (! $doc->isAffected()) $err=sprintf(_("cannot see unknow reference %s"),$docid);
  if ($err == "") {
    $action->lay->set("docid",$doc->id);
    $err = $doc->lock(true); // autolock
    if ($err=="") $action->AddActionDone("LOCKFILE",$doc->id);
    if ($err != "") {    
      // test object permission before modify values (no access control on values yet)
      $err=$doc->CanUpdateDoc();
    }


    if ($err=="") {
      $a=$doc->getAttribute($attrid);
      if (! $a)  $err=sprintf(_("unknown attribute %s for document %s"),$attrid,$doc->title);
      $action->lay->set("attrid",$a->id);
      $action->lay->set("longtext",($a->type=="longtext"));
      if ($err=="") {
      }
    }
    $action->lay->set("thetext",$doc->getValue($attrid));
  }

  if ($err != "")   $action->lay->set("CODE","KO");
  $action->lay->set("warning",$err);
  $action->lay->set("delay",microtime_diff(microtime(),$mb));

  // notify actions done
  $action->getActionDone($actcode,$actarg);
  $tact=array();
  foreach ($actcode as $k=>$v) {
    $tact[]=array("acode"=>$v,
		  "aarg"=>$actarg[$k]);
  }
  $action->lay->setBlockData("ACTIONS",$tact);
  $action->clearActionDone();
}


?>