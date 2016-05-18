<?php
/*
 * Examine vault files
 *
 * @author Anakeen
 * @package FDL
*/

global $appl, $action;

include_once ('FDL/Class.Doc.php');
include_once ('FDL/Class.DocVaultIndex.php');
include_once ('VAULT/Class.VaultFile.php');

$appl = new Application();
$appl->Set("FDL", $core);
$dbaccess = $appl->dbaccess;
if ($dbaccess == "") {
    print "Database not found : appl->dbaccess";
    exit;
}
/**
 * Parse arguments
 */
$usage = new ApiUsage();
$usage->setDefinitionText("Examine vault files");
/* --vaultname */
$vaultname = $usage->addOptionalParameter("vault", "Name of the vault to examine", null, "FREEDOM");
/* --test */
$test = $usage->addEmptyParameter("test", "Enable/disable test mode: do not delete anything, just print what would be done");

$test = (($test == "yes" || $test === true) ? true : false);
/* --cmd=check */
$command = $usage->addRequiredParameter("cmd", "Examine command", array(
    "check-all",
    "check-noref",
    "check-nofile",
    "clean-unref"
));
/* --csv */
$csv = $usage->addEmptyParameter("csv", "Output in CSV format");
$csv = ($csv === "1" || $csv === true || $csv == "yes") ? true : false;

$usage->verify();

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
        $action->exitError("Use \"cleanVaultOrphans\" wsh script to delete orphans files");
        break;

    default:
        print sprintf("Unknown command '%s'.\n", $command);
        exit;
}
exit;

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
