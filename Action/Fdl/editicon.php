<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: editicon.php,v 1.1 2006/11/16 16:41:19 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
/**
 */

include_once ("FDL/Class.Doc.php");
include_once ("FDL/Class.DocAttr.php");
include_once ("VAULT/Class.VaultDiskStorage.php");

function editicon(&$action)
{
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $docid = GetHttpVars("id", 0);
    
    $doc = new_Doc($dbaccess, $docid);
    $action->lay->Set("APP_TITLE", _($action->parent->description));
    $action->lay->Set("docid", $docid);
    $action->lay->Set("title", $doc->title);
    $action->lay->Set("iconsrc", $doc->geticon());
    
    $q = new QueryDb($dbaccess, "VaultDiskStorage");
    $q->dbaccess = $dbaccess;
    $q->basic_elem->dbaccess = $dbaccess; // correct for special constructor
    $q->AddQuery("public_access");
    $l = $q->Query(0, 0, "TABLE");
    $action->lay->setBlockData("ICONLIST", $l);
}
?>
