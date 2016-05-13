<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: editicon.php,v 1.1 2006/11/16 16:41:19 eric Exp $
 * @package FDL
 * @subpackage GED
 */
/**
 */

include_once ("FDL/Class.Doc.php");
include_once ("FDL/Class.DocAttr.php");
include_once ("VAULT/Class.VaultDiskStorage.php");

function editicon(Action & $action)
{
    $docid = GetHttpVars("id", 0);
    
    $doc = new_Doc($action->dbaccess, $docid);
    $action->lay->eSet("APP_TITLE", _($action->parent->description));
    $action->lay->Set("docid", urlencode($docid));
    $action->lay->eset("title", $doc->title);
    $action->lay->Set("iconsrc", $doc->geticon());
    
    $q = new QueryDb($action->dbaccess, "VaultDiskStorage");
    $q->dbaccess = $action->dbaccess;
    $q->basic_elem->dbaccess = $action->dbaccess; // correct for special constructor
    $q->AddQuery("public_access");
    $l = $q->Query(0, 0, "TABLE");
    $action->lay->setBlockData("ICONLIST", $l);
}
