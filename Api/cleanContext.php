<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @subpackage
 */
/**
 */
// remove all tempory doc and orphelines values
include_once ("FDL/Class.Doc.php");
include_once ("WHAT/Class.SessionUtils.php");
global $action;

$usage = new ApiUsage();

$usage->setDefinitionText("Clean base");
$real = ($usage->addHiddenParameter("real", "real (yes or no)") == "yes");
$full = ($usage->addEmptyParameter("full", "clean also obsolete permission, log, folder contains"));

if ($full !== true) {
    $full = ($full == "yes");
}

$usage->verify();

$dbaccess = $action->dbaccess;
if ($dbaccess == "") {
    print "Database not found : action->dbaccess";
    exit;
}

$duration = intval($action->GetParam("CORE_LOGDURATION", 60)); // default 60 days
$logdelete = sprintf("DELETE FROM doclog where date < '%s'", Doc::getDate(-($duration)));

simpleQuery($dbaccess, $logdelete);

global $_SERVER;

cleanTmpDoc();

if ($real || $full) {
    print "Full clean.\n";
    fullDbClean($action);
} else {
    print "Basic clean.\n";
    basicDbClean($action);
}
// Cleanup session files
$core_db = $action->dbaccess;
$sessionUtils = new SessionUtils($core_db);
$sessionUtils->deleteExpiredSessionFiles();
// Clean token open access
UserToken::deleteExpired();

cleanTmpFiles();
\Dcp\VaultManager::destroyTmpFiles($action->GetParam('CORE_TMPDIR_MAXAGE', '2'));

function mkTmpScript($script, $prefix)
{
    $tmpDir = getTmpDir();
    $tmpScript = tempnam($tmpDir, $prefix);
    if ($tmpScript === false) {
        throw new Exception(sprintf("Error creating temporary file in '%s'.", $tmpDir));
    }
    if (file_put_contents($tmpScript, $script) === false) {
        throw new Exception(sprintf("Error writing to temporary file '%s'.", $tmpScript));
    }
    return $tmpScript;
}

function getSqlFiles($prefix)
{
    $sqlDir = implode(DIRECTORY_SEPARATOR, array(
        DEFAULT_PUBDIR,
        'CORE',
        'cleanContext'
    ));
    if (($fh = opendir($sqlDir)) === false) {
        throw new Exception(sprintf("Error opening directory '%s'", $sqlDir));
    }
    $files = array();
    while (($file = readdir($fh)) !== false) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        if (preg_match(sprintf('/^%s_.*\.sql$/', preg_quote($prefix)) , $file) !== 1) {
            continue;
        }
        $files[] = implode(DIRECTORY_SEPARATOR, array(
            $sqlDir,
            $file
        ));
    }
    uasort($files, function ($a, $b)
    {
        return strcmp($a, $b);
    });
    return $files;
}

function execSqlFile(Action & $action, $sqlFile)
{
    $pgService = getServiceName($action->dbaccess);
    $script = <<<'EOF'
#!/bin/bash
PGSERVICE=%s psql --set ON_ERROR_STOP=1 -c '\timing' -a -f %s 2>&1 | logger -s -t %s
exit ${PIPESTATUS[0]}
EOF;
    $script = sprintf($script, escapeshellarg($pgService) , escapeshellarg($sqlFile) , escapeshellarg("cleanContext(" . $action->GetParam("CORE_CLIENT") . ")"));
    $tmpScript = mkTmpScript($script, 'basicDbClean');
    $out = array();
    $ret = 0;
    exec(sprintf("bash %s 2>&1", escapeshellarg($tmpScript)) , $out, $ret);
    if ($ret !== 0) {
        unlink($tmpScript);
        throw new Exception(sprintf("Error executing SQL file '%s': %s", $sqlFile, join("\n", $out)));
    }
    unlink($tmpScript);
}

function execSqlFiles(Action & $action, $prefix)
{
    $sqlFiles = getSqlFiles($prefix);
    $errors = [];
    foreach ($sqlFiles as $sqlFile) {
        try {
            printf("Executing '%s': ", $sqlFile);
            execSqlFile($action, $sqlFile);
            printf("[OK]\n");
        }
        catch(\Exception $e) {
            printf("[ERROR]\n%s\n", $e->getMessage());
            $errors[] = $e->getMessage();
        }
    }
    if (count($errors) > 0) {
        throw new Exception(sprintf("Errors:\n%s\n", join("\n", $errors)));
    }
}

function fullDbClean(Action & $action)
{
    execSqlFiles($action, 'cleanFullContext');
}

function basicDbClean(Action & $action)
{
    execSqlFiles($action, 'cleanContext');
}

function cleanTmpFiles()
{
    global $action;
    global $pubdir;
    
    if ($pubdir == '') {
        echo sprintf("Error: Yikes! we got an empty pubdir?");
        return;
    }
    
    $maxAge = $action->GetParam('CORE_TMPDIR_MAXAGE', '');
    if ($maxAge == '') {
        echo sprintf("Error: empty CORE_TMPDIR_MAXAGE parameter.");
        return;
    }
    
    if (!is_numeric($maxAge)) {
        echo sprintf("Error: found non-numeric value '%s' for CORE_TMPDIR_MAXAGE.", $maxAge);
        return;
    }
    
    $tmpDir = getTmpDir('');
    if ($tmpDir == '') {
        echo sprintf("Error: empty directory returned by getTmpDir().");
        return;
    }
    //clean tmp files
    $r = cleanOldFiles($tmpDir, $maxAge);
    if ($r) {
        echo $r;
    }
    //clean mustache cache files
    $r = cleanOldFiles($pubdir . '/var/cache/mustache', $maxAge);
    if ($r) {
        echo $r;
    }
    
    return;
}
/*
 * Delete files oldre than N days in given directory
*/
function cleanOldFiles($dir, $maxAge)
{
    if (!is_dir($dir)) {
        return "";
    }
    /* Values < 0 disable tmp file cleaning */
    if ($maxAge < 0) {
        return "";
    }
    /* We use find & xargs shell commands to do the cleaning. */
    /* First pass: remove expired files */
    $cmd = sprintf('find %s -type f -mtime +%s -print0 | xargs -0 --no-run-if-empty rm', escapeshellarg($dir) , $maxAge);
    exec($cmd, $output, $ret);
    if ($ret != 0) {
        return sprintf("Error: removal of old files from '%s' returned with error: %s", $dir, join("\n", $output));
    }
    /* Second pass: remove expired empty directories */
    $cmd = sprintf('find %s -type d -empty -mtime +%s -print0 | xargs -0 --no-run-if-empty rmdir', escapeshellarg($dir) , $maxAge);
    exec($cmd, $output, $ret);
    if ($ret != 0) {
        return sprintf("Error: removal of empty temporary directories from '%s' returned with error: %s", $dir, join("\n", $output));
    }
    
    return "";
}
/**
 * Delete temporary documents that have reached their end-of-life (CORE_TMPDOC_MAXAGE).
 */
function cleanTmpDoc()
{
    $days = \ApplicationParameterManager::getParameterValue('CORE', 'CORE_TMPDOC_MAXAGE');
    if (!is_int($days) && !ctype_digit($days)) {
        $days = 1;
    }
    
    $sql = <<<'EOF'
BEGIN;

-- Delete expired temporary documents
DELETE FROM doc WHERE doctype = 'T' AND cdate < (now() - INTERVAL '%d day');

-- Delete lingering dochisto entries of temporary documents
DELETE FROM dochisto WHERE id >= 1000000000 AND NOT EXISTS (SELECT 1 FROM doc WHERE doc.id = dochisto.id);

-- Reset temporary id sequence to MAX(id) of temporary documents
SELECT SETVAL('seq_id_tdoc', (SELECT COALESCE(MAX(id), 1000000000) FROM doc WHERE doctype = 'T'));

COMMIT;
EOF;
    try {
        $sql = sprintf($sql, $days);
        simpleQuery('', $sql, $res, true, true, true);
    }
    catch(\Exception $e) {
        printf("Error: removal of expired temporary documents returned with error: %s\n", $e->getMessage());
    }
}
