<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: freedom_imod.php,v 1.6 2005/06/28 08:37:46 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage GED
 */
/**
 */
// ---------------------------------------------------------------
// $Id: freedom_imod.php,v 1.6 2005/06/28 08:37:46 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/freedom_imod.php,v $
// ---------------------------------------------------------------
//include_once("FDL/freedom_util.php");
include_once ("FDL/modcard.php");
include_once ("FDL/Class.Dir.php");
include_once ("FDL/Class.DocFam.php");
include_once ("FDL/Class.Doc.php");
// -----------------------------------
function freedom_imod(&$action)
{
    
    $famid = GetHttpVars("famid");
    $xml = GetHttpVars("xml_initial");
    $attrid = GetHttpVars("attrid");
    $noredirect = GetHttpVars("noredirect"); // if true its a quick save
    $action->lay->Set("attrid", $attrid);
    $action->lay->Set("famid", $famid);
    
    $type_attr = GetHttpVars("type_attr");
    $action->lay->Set("type_attr", $type_attr);
    
    $mod = GetHttpVars("mod");
    $action->lay->Set("mod", $mod);
    if ($noredirect) $action->lay->Set("close", "no");
    
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    $idoc = fromxml($dbaccess, $xml, $famid, true);
    
    SetHttpVar("id", $idoc->id);
    
    $err = modcard($action, $ndocid); // ndocid change if new doc
    if ($err != "") $action->ExitError($err);
    
    $idoc = new_Doc($dbaccess, $idoc->id);
    
    $idoc->RefreshTitle();
    
    $action->lay->Set("title", htmlentities(addslashes($idoc->title) , ENT_COMPAT, "UTF-8"));
    
    $xml2 = $idoc->toxml(false, $attrid);
    
    $xml_send = base64_encode($xml2);
    $action->lay->Set("xml2", $xml_send);
    $action->lay->gen();
}
?>
