<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * initViewPrivileges
 *
 * @author Anakeen
 * @version $Id: freedom_import.php,v 1.9 2008/11/13 16:49:16 eric Exp $
 * @package FDL
 * @subpackage WSH
 */

global $action;

include_once ("FDL/Class.Doc.php");
include_once ("FDL/Class.SearchDoc.php");

$usage = new ApiUsage();
$usage->setDefinitionText("Init view privilege ");
// --reset-account
$accountOnly = $usage->addOptionalParameter("reset-account", "reset account members of", array(
    "yes",
    "no"
) , "no");
$accountOnly = ($accountOnly == "yes");
// --famid=<id>
$famid = $usage->addHiddenParameter("famid", "Sub-process a single family");
if (!is_string($famid)) {
    $famid = '';
}
// --progress=<str>
$progress = $usage->addHiddenParameter("progress", "Parent process progress");
if (!is_string($progress)) {
    $progress = '';
}
// --experimental-parallel=<number_of_concurrent_jobs>
$parallel = $usage->addHiddenParameter("experimental-parallel", "[EXPERIMENTAL] Run sub-processes in parallel. This parameter sets the number of concurrent jobs.");
if (!is_string($parallel)) {
    $parallel = '';
}
$usage->verify();

if ($accountOnly) {
    recomputeAccounts($action);
} else {
    if ($famid !== '') {
        initViewSingleFamily($action, $famid, $progress);
    } else {
        initViewAllFamily($action, $parallel);
    }
}

function recomputeAccounts(Action & $action)
{
    if (($err = simpleQuery($action->dbaccess, "SELECT * FROM users ORDER BY id", $tusers)) !== '') {
        $action->exitError(sprintf("Query error: %s", $err));
        return false;
    }
    $card = count($tusers);
    printf("[+] %d user privilege to update.\n", $card);
    $u = new Account($action->dbaccess);
    $count = count($tusers);
    $i = 1;
    $pom = (new \Dcp\ConsoleProgressOMeter())->setInteractive(false)->setMax($count)->setTimeInterval(10)->start();
    foreach ($tusers as $tu) {
        printf("%d) %s \n", $card, $tu["login"]);
        $u->affect($tu);
        $u->updateMemberOf();
        $card--;
        $pom->progress($i++);
    }
    $pom->finish();
    printf("[+] Done.\n");
    return true;
}
/**
 * Process a single family (used when running in a sub-process)
 *
 * @param Action $action
 * @param $famid
 * @param $progress
 * @return bool
 * @throws \Dcp\Core\Exception
 */
function initViewSingleFamily(Action & $action, $famid, $progress)
{
    if ($famid != - 1) {
        $fam = new_Doc($action->dbaccess, $famid);
        if (!is_object($fam) || !$fam->isAlive()) {
            $action->exitError(sprintf("Could not find family with id or logical name '%s'.", $famid));
        }
        $famid = $fam->name;
    }
    /* Count number of documents in family */
    $s = new \SearchDoc($action->dbaccess, $famid);
    $s->only = true;
    $s->addFilter("views IS NULL");
    $s->addFilter("profid > 0");
    $s->setObjectReturn();
    $s->setOrder('id');
    $s->latest = false;
    $s->search();
    if (($err = $s->searchError()) !== '') {
        $action->exitError(sprintf("Error: search error on family '%s': %s", $famid, $err));
        return false;
    }
    $count = $s->count();
    /*
     * Process each documents in groups of 1000 documents. The documents are processed ordered by increasing id, so the
     * last document's id of the group is memorized in $lastId in order to select the next 1000 documents to process.
    */
    printf("[+] (%s) Processing %d document%s from family '%s'...\n", $progress, $count, (($count == 1) ? '' : 's') , $famid);
    $processTitle = sprintf('initViewPrivileges (family %s %s)', $progress, $famid);
    $pom = (new \Dcp\ConsoleProgressOMeter())->setPrefix($processTitle)->setInteractive(false)->setMax($count)->setTimeInterval(10)->setUpdateProcessTitle($processTitle)->start();
    $i = 1;
    $lastId = 0;
    $subcount = $count;
    while ($subcount > 0) {
        $s = new \SearchDoc($action->dbaccess, $famid);
        $s->setStart(0);
        $s->setSlice(1000);
        $s->only = true;
        $s->addFilter("views IS NULL");
        $s->addFilter("profid > 0");
        $s->addFilter(sprintf("id > %s", pg_escape_literal($lastId)));
        $s->setObjectReturn();
        $s->setOrder('id');
        $s->latest = false;
        $s->search();
        if (($err = $s->searchError()) !== '') {
            $action->exitError(sprintf("Error: search error on family '%s': %s", $famid, $err));
            return false;
        }
        $subcount = $s->count();
        while ($doc = $s->getNextDoc()) {
            $doc->setViewProfil();
            $lastId = $doc->id;
            $pom->progress($i++);
        }
    }
    $pom->finish();
    printf("[+] Done.\n");
    return true;
}
/**
 * Process the family in a sub-process
 *
 * @param $famid
 * @param $progress
 * @return int
 */
function runSubProcessFam($famid, $progress)
{
    $wsh = getWshPath();
    if ($famid == '') {
        /* Prevent end-less recursive execution */
        return 0;
    }
    $cmd = sprintf("php %s --api=initViewPrivileges --famid=%s --progress=%s", escapeshellarg($wsh) , escapeshellarg($famid) , escapeshellarg($progress));
    passthru($cmd, $ret);
    return $ret;
}
/**
 * Process families sequentially
 *
 * @param Action $action
 * @param $famIdList
 * @throws \Dcp\Core\Exception
 */
function sequentialSubProcessFam(Action & $action, $famIdList)
{
    $count = count($famIdList);
    $i = 1;
    foreach ($famIdList as $famId) {
        $ret = runSubProcessFam($famId, sprintf("%s/%s", $i, $count));
        if ($ret !== 0) {
            $action->exitError(sprintf("Error processing family '%s': sub-process ended with exit code %d", $famId, $ret));
        }
        $i++;
    }
}
/**
 * Process families in parallel
 *
 * @param Action $action
 * @param $famIdList
 * @param $parallel
 * @throws \Dcp\Core\Exception
 */
function parallelSubProcessFam(Action & $action, $famIdList, $parallel)
{
    $wsh = getWshPath();
    $count = count($famIdList);
    if (($jobsFile = tempnam(getTmpDir('') , 'initViewPrivileges')) === false) {
        $action->exitError(sprintf("Error: could not create temporary jobs file!\n"));
    }
    $jobs = array();
    $i = 1;
    foreach ($famIdList as $famId) {
        $jobs[] = sprintf("%s --api=initViewPrivileges --famid=%s --progress=%s", escapeshellarg($wsh) , escapeshellarg($famId) , escapeshellarg(sprintf("%s/%s", $i, $count)));
        $i++;
    }
    if (file_put_contents($jobsFile, join("\n", $jobs)) === false) {
        $action->exitError(sprintf("Error: error writing content to '%s'!\n", $jobsFile));
    }
    $cmd = sprintf("%s/programs/parallel -j %s -f %s", escapeshellarg(DEFAULT_PUBDIR) , escapeshellarg($parallel) , escapeshellarg($jobsFile));
    passthru($cmd, $ret);
    unlink($jobsFile);
}
/**
 * Process all families
 *
 * @param Action $action
 * @param $parallel
 * @return bool
 * @throws \Dcp\Core\Exception
 * @throws \Dcp\Db\Exception
 * @throws \Dcp\Exception
 */
function initViewAllFamily(Action & $action, $parallel)
{
    if (($err = simpleQuery($action->dbaccess, "SELECT * FROM users WHERE memberof IS NULL", $tusers)) !== '') {
        $action->exitError(sprintf("Query error: %s", $err));
        return false;
    }
    $card = count($tusers);
    printf("[+] %d user privilege to update.\n", $card);
    $i = 1;
    $pom = (new \Dcp\ConsoleProgressOMeter())->setInteractive(false)->setMax($card)->setTimeInterval(10)->start();
    $u = new Account($action->dbaccess);
    foreach ($tusers as $tu) {
        printf("%d) %s \n", $card, $tu["login"]);
        $u->affect($tu);
        $u->updateMemberOf();
        $card--;
        $pom->progress($i++);
    }
    $pom->finish();
    printf("[+] Done.\n");
    /*
     * Get list of PDOC (3) family and it's childs
    */
    if (($err = simpleQuery($action->dbaccess, "WITH RECURSIVE child_fams(id, fromid) AS ( SELECT id, fromid FROM docfam WHERE id = 3 UNION SELECT docfam.id, docfam.fromid FROM docfam, child_fams WHERE child_fams.id = docfam.fromid ) SELECT id FROM child_fams ORDER BY id", $rows)) !== '') {
        $action->exitError(sprintf("Query error: %s", $err));
    }
    $pdocs = array();
    foreach ($rows as $row) {
        $pdocs[] = $row['id'];
    }
    /*
     * Sequentially process PDOC and it's childs first
    */
    $i = 1;
    foreach ($pdocs as $famid) {
        $ret = runSubProcessFam($famid, sprintf("%d/%d", $i, count($pdocs)));
        if ($ret !== 0) {
            $action->exitError(sprintf("Error processing family '%s': sub-process ended with exit code %d", $famid, $ret));
        }
        $i++;
    }
    /*
     * Get remaining families excluding PDOC and it's childs
    */
    if (($err = simpleQuery($action->dbaccess, sprintf("SELECT id, name FROM docfam WHERE id NOT IN (%s) ORDER BY id", join(', ', $pdocs)) , $rows)) !== '') {
        $action->exitError(sprintf("Query error: %s", $err));
    }
    $famIdList = array();
    foreach ($rows as $row) {
        $famIdList[] = $row['id'];
    }
    /*
     * Process docfam itself
    */
    $famIdList[] = - 1;
    if ($parallel === '') {
        sequentialSubProcessFam($action, $famIdList);
    } else {
        parallelSubProcessFam($action, $famIdList, $parallel);
    }
    printf("[+] Update empty profil...\n");
    if (($err = simpleQuery($action->dbaccess, "UPDATE doc SET views = '{0}' WHERE (profid=0 OR profid IS NULL)")) !== '') {
        $action->exitError(sprintf("Query error: %s", $err));
    }
    printf("[+] Finished profiling.\n");
    return true;
}
/**
 * Return the absolute path to the `wsh.php` script.
 */
function getWshPath()
{
    return sprintf("%s/wsh.php", DEFAULT_PUBDIR);
}
