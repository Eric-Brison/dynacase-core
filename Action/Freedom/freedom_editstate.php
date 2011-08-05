<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: freedom_editstate.php,v 1.5 2005/06/28 08:37:46 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
 /**
 */



include_once("FDL/Class.Doc.php");

function freedom_editstate(&$action) 
{
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $docid = GetHttpVars("id",0);


  $doc = new_Doc($dbaccess, $docid);
  $action->lay->Set("docid",$docid);
  $action->lay->Set("title",$doc->title);


  $action->lay->set("tablehead","tableborder");

  if ($action->Read("navigator","")=="NETSCAPE") {
    if (preg_match("/rv:([0-9.]+).*/",$_SERVER['HTTP_USER_AGENT'],$reg)) {
      if (floatval($reg[1] >= 1.6)) {
	$action->lay->set("tablehead","tablehead");	
      }
    }
    
  } 
  

}

?>
