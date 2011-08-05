<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Examine vault files
 *
 * @author Anakeen 2004
 * @version $Id: VaultExamine.php,v 1.5 2006/12/08 17:51:17 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */
ini_set("max_execution_time", "36000");

include_once ('FDL/Class.Doc.php');
include_once ('FDL/Class.DocVaultIndex.php');
include_once ('VAULT/Class.VaultFile.php');

$appl = new Application();
$appl->Set("FDL", $core);
$dbaccess = $appl->GetParam("FREEDOM_DB");
if ($dbaccess == "") {
    print "Database not found : param FREEDOM_DB";
    exit;
}

$vaultname = GetHttpVars("vault", "FREEDOM");
$test = GetHttpVars("test", false);
$command = GetHttpVars("cmd", "");
$csvp = GetHttpVars("csv", 0);
$csv = false;
if ($csvp == 1) $csv = true;

switch ($command) {
    case "check-all":
        $t = view($dbaccess, $vaultname);
        printres($t, $csv);
        break;

    case "check-noref":
        $t = view($dbaccess, $vaultname, array(
            "unref" => true
        ));
        printres($t, $csv);
        break;

    case "check-nofile":
        $t = view($dbaccess, $vaultname, array(
            "unread" => true
        ));
        printres($t, $csv);
        break;

    case "clean-unref":
        $t = view($dbaccess, $vaultname, array(
            "unref" => true
        ));
        cleanVault($dbaccess, $vaultname, $t, $test);
        break;

    default:
        usage();
        exit;
}
exit;

function usage()
{
    echo "\n";
    echo "  wsh.php --api=VaultExamine --vault=[vaultname] --cmd=[command] [--test]\n";
    echo "     --vault=[vaultname]  : The vault name (see NAME.vault), by default set to FREEDOM\n";
    echo "     --test               : In case of destructive command, no actions are done, only gives todo messages.\n";
    echo "     --cmd=[command]      : Run command, where command are :\n";
    echo "                             - check-all    : return the full listing of vault files\n";
    echo "                             - check-noref  : return the unreferenced vault file (file not used by documents)\n";
    echo "                             - check-nofile : return the vault file entries without file (...)\n";
    echo "                             - clean-unref  : clear vault file not referenced by documents.\n";
    echo "\n";
    return;
}

function printres($t, $csv = false)
{
    $filel = 30;
    if (!$csv) $fmt = " %-5s | %-10s | %-" . $filel . "s | %s\n";
    else $fmt = "%s;%s;%s;%s\n";
    if (!is_array($t) || count($t) == 0) return;
    if (!$csv) {
        $s = sprintf($fmt, "Vid", "Access", "Filename", "Doc Id's");
        echo $s;
        echo "---------------------------------------------------------------------------------------------\n";
    }
    foreach ($t as $k => $v) {
        $ds = "";
        $first = true;
        if (is_array($v["docs"]) && count($v["docs"]) > 0) {
            foreach ($v["docs"] as $kk => $vv) {
                if ($vv != - 1) {
                    $ds.= ($first ? "" : "|") . $vv;
                    $first = false;
                }
            }
        }
        if (!$csv && $ds == "") $ds = "(none)";
        if (strlen($v["file"]) > $filel && !$csv) $f = "..." . substr($v["file"], -($filel - 3));
        else $f = $v["file"];
        $s = sprintf($fmt, $v["vid"], ($v["access"] ? "Ok" : "No") , $f, $ds);
        echo $s;
    }
}

function view($dbaccess, $vaultname, $filter = array())
{
    
    $dvi = new DocVaultIndex($dbaccess);
    
    $vault = new VaultFile($dbaccess, $vaultname);
    $vault->ListFiles($alls);
    
    $unref = false;
    if (isset($filter["unref"])) $unref = true;
    $unread = false;
    if (isset($filter["unread"])) $unread = true;
    
    $all = array();
    $if = 0;
    
    foreach ($alls as $k => $v) {
        $vid = $v["id_file"];
        $file = "";
        $access = false;
        $docs = array();
        $docids = $dvi->GetDocIds($vid);
        if (is_array($docids) && count($docids) > 0) {
            foreach ($docids as $kk => $vv) if ($vv->docid != - 1) $docs[] = $vv->docid;
        } else {
            $dvi->docid = - 1;
            $dvi->vaultid = $vid;
            $dvi->Add();
        }
        $vault->Show($vid, $inf);
        $file = $inf->path;
        if (is_readable($file)) $access = true;
        if (((!$unref && !$unread) || ($unref && count($docs) == 0) || ($unread && !$access))) {
            $all[$if]["vid"] = $vid;
            $all[$if]["file"] = $file;
            $all[$if]["access"] = $access;
            $all[$if]["docs"] = $docs;
            $if++;
        }
    }
    return $all;
}

function loclog($s)
{
    echo "SetDocVaultIndex> $s\n";
}

function cleanVault($dbaccess, $vaultname, $vt, $test)
{
    if (!is_array($vt) || count($vt) == 0) return;
    
    $pref = "";
    if ($test) $pref = " [test] ";
    $dvi = new DocVaultIndex($dbaccess);
    $vault = new VaultFile($dbaccess, $vaultname);
    
    foreach ($vt as $k => $v) {
        $vid = $v["vid"];
        $vname = $v["file"];
        $used = (is_array($v["docs"]) && count($v["docs"]) > 0 ? true : false);
        if (!$used) {
            loclog("$pref Suppress vault id $vid, filename $vname");
            if (!$test) {
                $vault->Destroy($vid);
                $dvi->DeleteVaultId($vid);
            }
        } else loclog(" *** ERROR *** $vid used (referenced in doc(s))");
    }
}
?>
