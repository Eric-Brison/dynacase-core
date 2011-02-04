<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: revcomment.php,v 1.5 2005/06/28 08:37:46 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: revcomment.php,v 1.5 2005/06/28 08:37:46 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/revcomment.php,v $
// ---------------------------------------------------------------


include_once("FDL/Class.Doc.php");
include_once("FDL/Class.DocAttr.php");
function revcomment(&$action) 
{
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $docid = GetHttpVars("id",0);


  $doc= new_Doc($dbaccess,$docid);


  $err= $doc -> lock(true); // auto lock
  if ($err != "")    $action-> ExitError($err);
  
  $err = $doc->CanUpdateDoc();
  if ($err != "") $action->ExitError($err);
  

  $action->lay->Set("APP_TITLE", _($action->parent->description));
  $action->lay->Set("title", $doc->title);
  $action->lay->Set("docid", $doc->id);


    


}

?>
