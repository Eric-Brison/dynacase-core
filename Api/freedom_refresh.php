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
 * @version $Id: freedom_refresh.php,v 1.22 2008/12/12 17:48:25 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */
// refreah for a classname
// use this only if you have changed title attributes
include_once ("FDL/Class.Doc.php");
include_once ("FDL/Class.SearchDoc.php");

$usage = new ApiUsage();
$usage->setText("Refresh documents");
$famId = $usage->addNeeded("famid", "family identifier (name or id)");
$docid = $usage->addOption("docid", "document identifier to apply refresh only on this document");
$method = $usage->addOption("method", "method to apply instead of refresh method");
$arg = $usage->addOption("arg", "option arg of the method");
$fldid = $usage->addOption("fldid", "collection identifier where apply refresh");
$allrev = (strtoupper(substr($usage->addOption("revision", "collection identifier where apply refresh", array(
    "yes",
    "no",
    "Y",
    "N"
)), 0, 1)) == "Y");
$usage->verify();

$appl = new Application();
$appl->Set("FDL", $core);

$dbaccess = $appl->GetParam("FREEDOM_DB");
if ($dbaccess == "") {
    print "Database not found : param FREEDOM_DB";
    exit;
}

if ($famId) {
    $f = new_doc($dbaccess, $famId);
    if (!$f->isAlive()) {
        $action->exitError(sprintf("family %s not exists", $famId));
    }
    if ($f->doctype != 'C') {
        $action->exitError(sprintf("document %s not a family", $famId));
    }
}

$s = new SearchDoc($dbaccess, $famId);
$s->setObjectReturn();
if ($docid) {
    $d = new_doc($dbaccess, $docid);
    if (!$d->isAlive()) {
        $action->exitError(sprintf("document %s not exists", $docid));
    } else $docid = $d->id;
}
if ($docid > 0) $s->addFilter("id = $docid");
if ($fldid != "") $s->useCollection($fldid);
if ($allrev) $s->latest = false;
$s->search();

if ($s->searchError()) {
    $action->exitError(sprintf("search error : %s", $s->getError()));
}
$targ = array();
if ($arg != "") $targ[] = $arg;
$card = $s->count();
printf("\n%d documents to refresh\n", $card);;
while ($doc = $s->nextDoc()) {
    $usemethod = ($method && (method_exists($doc, $method)));
    if ($usemethod) {
        $ret = call_user_func_array(array(
            $doc,
            $method
        ) , $targ);
    } else $ret = '';
    print $card . ")" . $doc->title . " " . (($usemethod) ? "(use $method($arg))" : "") . get_class($doc) . ":$ret\n";
    //print $card-$k.")".$doc->title ." - ".$doc->fromid." - ".get_class($doc)." - " .round(memory_get_usage()/1024)."\n";
    $doc->refresh();
    $doc->refreshTitle();
    $doc->Modify();
    $card--;
}
?>
