<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: queryfile.php,v 1.4 2005/06/28 08:37:46 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: queryfile.php,v 1.4 2005/06/28 08:37:46 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/queryfile.php,v $
// ---------------------------------------------------------------


include_once("FDL/Class.Doc.php");
include_once("FDL/Class.DocAttr.php");

function queryfile(&$action) 
{
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $docid = GetHttpVars("id",0);

  $doc= new_Doc($dbaccess,$docid);
  $action->lay->Set("APP_TITLE", _($action->parent->description));
  $action->lay->Set("docid",$docid);
  $action->lay->Set("title",$doc->title);
  $action->lay->Set("iconsrc",$doc->geticon());
  


    

}

?>
