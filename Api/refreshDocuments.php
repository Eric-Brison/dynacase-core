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
    if ($msg) $msg = chr(0x1b) . "[1;31m" . $msg . chr(0x1b) . "[0;39m";
    return $msg;
}

function color_success($msg)
{
    if ($msg) $msg = chr(0x1b) . "[1;32m" . $msg . chr(0x1b) . "[0;39m";
    return $msg;
}

function color_warning($msg)
{
    if ($msg) $msg = chr(0x1b) . "[1;33m" . $msg . chr(0x1b) . "[0;39m";
    return $msg;
}

$usage = new ApiUsage();
$usage->setDefinitionText("Refresh documents ");
$famId = $usage->addNeededParameter("famid", "the family identifier used to filter");
$method = $usage->addOptionnalParameter("method", "method to use)", array() , "refresh");
$arg = $usage->addOptionnalParameter("arg", "optional method argument to set when calling method");
$revision = $usage->addOptionnalParameter("revision", "use all revision", array(
    "yes",
    "no"
) , "no");
$docid = $usage->addOptionnalParameter("docid", "use only for this document id");
$start = $usage->addOptionnalParameter("start", "start from offset", array() , 0);
$slice = $usage->addOptionnalParameter("slice", "limit from offset", array() , "all");
$fldid = $usage->addOptionnalParameter("fldid", "use collection id to limit search");
$filter = $usage->addOptionnalParameter("filter", "sql filter to limit search");
$save = $usage->addOptionnalParameter("save", "store mode", array(
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

$famtitle = "";
if ($famId) {
    $f = new_doc($dbaccess, $famId);
    if (!$f->isAlive()) {
        $action->exitError(sprintf("family %s not exists", $famId));
    }
    if ($f->doctype != 'C') {
        $action->exitError(sprintf("document %s not a family", $famId));
    }
    $famId = $f->id;
    $famtitle = $f->getTitle();
}

$s = new SearchDoc($dbaccess, $famId);
$s->setObjectReturn();
$s->orderby = 'id desc';
$s->slice = $slice;
$s->start = $start;
if ($docid != '') {
    if (!is_numeric($docid)) {
        $docName = $docid;
        $docid = getIdFromName($dbaccess, $docName);
        if ($docid === false) {
            $action->exitError(sprintf("document with name '%s' not found", $docName));
        }
    }
    $s->addFilter('id = %d', $docid);
}
if ($fldid != '') {
    $s->useCollection($fldid);
}
if ($allrev) $s->latest = false;
if ($filter) {
    // verify validity and prevent hack
    if (@pg_prepare(getDbid($dbaccess) , sprintf("select id from doc%d where %s", $s->fromid, $filter)) == false) {
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
printf("\n%d %s to update with %s\n", $card, $famtitle, $method);

$ret = "";
while ($doc = $s->getNextDoc()) {
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
            $olds = $doc->getOldRawValues();
            foreach ($olds as $k => $v) {
                $smod.= sprintf("\t- %s [%s]:[%s]\n", $k, $v, $doc->getRawValue($k));
            }
            switch ($save) {
                case "light":
                    $err = $doc->modify(true);
                    $modified = true;
                    break;

                case "complete":
                    $err = $doc->store();
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
