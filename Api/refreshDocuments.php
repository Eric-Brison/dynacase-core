<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * importation of documents
 *
 * @author Anakeen
 * @version $Id: freedom_import.php,v 1.9 2008/11/13 16:49:16 eric Exp $
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
$famId = $usage->addRequiredParameter("famid", "the family identifier used to filter");
$method = $usage->addOptionalParameter("method", "method to use", function ($value)
{
    if ($value === ApiUsage::GET_USAGE) {
        return '';
    }
    if (!is_string($value)) {
        return sprintf("Invalid %s value for option --method=<methodName>", gettype($value));
    }
    if (strlen($value) <= 0) {
        return sprintf("Invalid empty value for option --method=<methodName>");
    }
    return '';
}
, "refresh");
$arg = $usage->addOptionalParameter("arg", "optional method argument to set when calling method");
$revision = $usage->addOptionalParameter("revision", "use all revision", array(
    "yes",
    "no"
) , "no");
$docid = $usage->addOptionalParameter("docid", "use only for this document id");
$start = $usage->addOptionalParameter("start", "start from offset", array() , 0);
$slice = $usage->addOptionalParameter("slice", "limit from offset", array() , "all");
$fldid = $usage->addOptionalParameter("fldid", "use collection id to limit search");
$filter = $usage->addOptionalParameter("filter", "sql filter to limit search");
$save = $usage->addOptionalParameter("save", "store mode", array(
    "complete",
    "light",
    "none"
) , "light");
$statusFile = $usage->addOptionalParameter("status-file", "refresh's status file or '-' for STDOUT", function ($value)
{
    if ($value === ApiUsage::GET_USAGE) {
        return '';
    }
    if (!is_string($value)) {
        return sprintf("Invalid %s value for option --status-file=<statusFile>|'-'", gettype($value));
    }
    if (strlen($value) <= 0) {
        return sprintf("Invalid empty value for option --status-file=<statusFile>|'-'");
    }
    return '';
});
$stopOnError = ($usage->addEmptyParameter("stop-on-error", "Stop processing when a document returns an error or throws an exception") !== false);
$usage->verify();

$allrev = ($revision == "yes"); // method to use
$dbaccess = $action->dbaccess;
if ($dbaccess == "") {
    print "Database not found : action->dbaccess";
    exit();
}

if ($statusFile === '') {
    $statusFile = null;
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
    if (@pg_prepare(getDbid($dbaccess) , 'refreshDocument', sprintf("select id from doc%d where %s", $s->fromid, $filter)) == false) {
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
$countAll = $card;
$countProcessed = 0;
$countSuccess = 0;
$countFailure = 0;
$exitcode = 0;
while ($doc = $s->getNextDoc()) {
    if (!method_exists($doc, $method)) {
        printf("\nmethod not exists '%s' \n", $method);
        $exitcode = 1;
        break;
    }
    
    $countProcessed++;
    
    $ret = '';
    $err = '';
    $modified = false;
    $smod = '';
    try {
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
        }
    }
    catch(\Exception $e) {
        $err.= $e->getMessage();
    }
    $memory = '';
    //$memory= round(memory_get_usage() / 1024)."Ko";
    if ($err != '') {
        printf("%s)%s[%d] %s %s %s\n", $card, $doc->title, $doc->id, ($modified) ? '-M-' : '', $memory, color_failure($ret . $err));
        $countFailure++;
        $exitcode = 1;
    } else {
        printf("%s)%s[%d] %s %s %s\n", $card, $doc->title, $doc->id, ($modified) ? '-M-' : '', $memory, color_success($ret));
        $countSuccess++;
    }
    if ($smod) print $smod;
    if ($err != '' && $stopOnError) {
        break;
    }
    $card--;
}

$writeStatus = function ($statusFile, $status)
{
    $err = '';
    $fh = STDOUT;
    if ($statusFile !== '-') {
        if (file_exists($statusFile)) {
            unlink($statusFile);
        }
        if (($fh = fopen($statusFile, 'x')) === false) {
            $err = sprintf("Error creating refresh's status file '%s'.", $statusFile);
            return $err;
        }
    }
    if (fwrite($fh, $status) === false) {
        $err = sprintf("Error writing refresh's status to file '%s'.", $statusFile);
        fclose($fh);
        return $err;
    }
    if ($fh !== STDOUT) {
        fclose($fh);
    }
    return $err;
};

if ($statusFile !== null) {
    $status = '';
    $status.= sprintf("ALL: %d\n", $countAll);
    $status.= sprintf("PROCESSED: %d\n", $countProcessed);
    $status.= sprintf("FAILURE: %d\n", $countFailure);
    $status.= sprintf("SUCCESS: %d\n", $countSuccess);
    $err = $writeStatus($statusFile, $status);
    if ($err !== '') {
        printf("%s\n", $err);
    }
}

exit($exitcode);
