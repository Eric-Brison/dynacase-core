<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * importation of documents
 *
 * @author Anakeen
 * @version $Id: freedom_import.php,v 1.9 2008/11/13 16:49:16 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage WSH
 */
/**
 */

global $action;
// refreah for a classname
// use this only if you have changed title attributes
include_once ("FDL/Class.Doc.php");
include_once ("FDL/Class.SearchDoc.php");
function color_failure($msg)
{
    if ($msg) return chr(0x1b) . "[1;31m" . $msg . chr(0x1b) . "[0;39m";
}

function color_success($msg)
{
    if ($msg) return chr(0x1b) . "[1;32m" . $msg . chr(0x1b) . "[0;39m";
}

function color_warning($msg)
{
    if ($msg) return chr(0x1b) . "[1;33m" . $msg . chr(0x1b) . "[0;39m";
}

$usage = new ApiUsage();
$usage->setText("Refresh documents ");
$famId = $usage->addNeeded("famid", "the family identificator used to filter");
$method = $usage->addOption("method", "method to use)", array() , "refresh");
$arg = $usage->addOption("arg", "optional method argument to set when calling method");
$revision = $usage->addOption("revision", "use all revision", array(
    "yes",
    "no"
) , "no");
$docid = $usage->addOption("docid", "use only for this document id");
$start = $usage->addOption("start", "start from offset", array() , 0);
$slice = $usage->addOption("slice", "limit from offset", array() , "all");
$fldid = $usage->addOption("fldid", "use collection id to limit search");
$filter = $usage->addOption("filter", "sql filter to limit search");
$save = $usage->addOption("save", "store mode", array(
    "complete",
    "light",
    "none"
) , "light");
$usage->verify();

$allrev = ($revision == "yes"); // method to use
$dbaccess = $action->getParam("FREEDOM_DB");
if ($dbaccess == "") {
    print "Database not found : param FREEDOM_DB";
    exit();
}

if ($famId) {
    $f = new_doc($dbaccess, $famId);
    if (!$f->isAlive()) {
        $action->exitError(sprintf("family %s not exists", $famId));
    }
    if ($f->doctype != 'C') {
        $action->exitError(sprintf("document %s not a family", $famId));
    }
    $famId = $f->id;
}

$s = new SearchDoc($dbaccess, $famId);
$s->setObjectReturn();
$s->orderby = 'id desc';
$s->slice = $slice;
$s->start = $start;
if ($docid > 0) $s->addFilter('id = %d', $docid);
if ($fldid > 0) $s->dirid = $fldid;
if ($allrev) $s->latest = false;
if ($filter) {
    // verify validity and prevent hack
    if (@pg_prepare($s->dbid, sprintf("select id from doc%d where %s", $s->fromid, $filter)) == false) {
        $action->exitError(sprintf("filter not valid :%s", pg_last_error()));
    } else {
        $s->addFilter($filter);
    }
}
$s->search();

if ($s->searchError()) {
    $action->exitError(sprintf("search error : %s", $s->getError()));
}
$targ = array();
if ($arg != "") $targ[] = $arg;
$card = $s->count();
printf("\n%d %s to update with %s\n", $card, $f->getTitle() , $method);

while ($doc = $s->nextDoc()) {
    $usemethod = ($method && (method_exists($doc, $method)));
    if ($method && (!method_exists($doc, $method))) {
        printf("\nmethod not exists %s \n", $method);
        break;
    }
    
    $modified = false;
    $smod = '';
    if ($usemethod) {
        $ret = call_user_func_array(array(
            $doc,
            $method
        ) , $targ);
        if ($doc->isChanged()) {
            $olds = $doc->getOldValues();
            foreach ($olds as $k => $v) {
                $smod.= sprintf("\t- %s [%s]:[%s]\n", $k, $v, $doc->getValue($k));
            }
            switch ($save) {
                case "light":
                    $err = $doc->modify(true);
                    $modified = true;
                    break;

                case "complete":
                    $err = $doc->save();
                    $modified = true;
                    break;
            }
            $ret.= $err;
        }
    }
    $memory = '';
    //$memory= round(memory_get_usage() / 1024)."Ko";
    printf("%s)%s[%d] %s %s %s\n", $card, $doc->title, $doc->id, ($modified) ? '-M-' : '', $memory, color_failure($ret));
    if ($smod) print $smod;
    
    $card--;
}
?>
