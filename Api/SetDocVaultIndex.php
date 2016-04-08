<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Construct vault index database
 *
 * @author Anakeen 2004
 * @version $Id: SetDocVaultIndex.php.in,v 1.2 2008/05/07 10:24:02 jerome Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ('Class.Action.php');
include_once ('Class.Application.php');
include_once ('Class.Session.php');
include_once ('Class.Log.php');

include_once ('FDL/Class.Doc.php');
include_once ('FDL/Class.DocVaultIndex.php');

$usage = new ApiUsage();
$usage->setDefinitionText("Construct vault index database");
$usage->verify();

$appl = new Application();
$appl->Set("FDL", $core);
$dbaccess = $appl->dbaccess;
if ($dbaccess == "") {
    print "Database not found : appl->dbaccess";
    exit;
}

$dvi = new DocVaultIndex($dbaccess);

$doc = new_Doc($dbaccess);
$doc->exec_query("select * from doc where id > 0 and doctype!='Z'");
$idoc = $doc->numrows();

loclog("Base $dbaccess, " . $idoc . " document" . ($idoc ? "s" : "") . " to process");
$dvi->exec_query("select * from docvaultindex");
loclog("Doc/Vault Index contains " . $dvi->numrows() . " associations");

for ($c = 0; $c < $idoc; $c++) {
    $row = $doc->fetch_array($c, PGSQL_ASSOC);
    $tdoc = new_Doc($dbaccess, $row["id"]);
    UpdateVaultIndex($c, $tdoc, $dvi);
    unset($tdoc);
}
$dvi->exec_query("select * from docvaultindex");
loclog("Doc/Vault Index contains " . $dvi->numrows() . " associations");

exit;
/**
 * @param int $i
 * @param Doc $doc
 * @param DocVaultIndex $dvi
 */
function UpdateVaultIndex($i, &$doc, &$dvi)
{
    $vl = "";
    $vic = 0;
    $err = $dvi->DeleteDoc($doc->id);
    $fa = $doc->GetFileAttributes();
    foreach ($fa as $aid => $oattr) {
        if ($oattr->inArray()) {
            $ta = $doc->getMultipleRawValues($aid);
        } else {
            $ta = array(
                $doc->getMultipleRawValues($aid)
            );
        }
        foreach ($ta as $k => $v) {
            $vid = "";
            if (preg_match(PREGEXPFILE, $v, $reg)) {
                $vid = $reg[2];
                $dvi->docid = $doc->id;
                $dvi->vaultid = $vid;
                $dvi->Add();
                $vl.= " " . $vid;
                $vic++;
            }
        }
    }
    if ($vic > 0) loclog("[$i] document [" . $doc->id . "::" . $doc->title . "]  added vault file" . ($vic > 1 ? "s" : "") . " " . $vl);
}

function loclog($s)
{
    echo "SetDocVaultIndex> $s\n";
}
