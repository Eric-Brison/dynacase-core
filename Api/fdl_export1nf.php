<?php
/*
 * @author Anakeen
 * @package FDL
*/

include_once ('WHAT/Lib.Common.php');
include_once ('FDL/freedom_util.php');
include_once ('FDL/Class.SearchDoc.php');
include_once ('WHAT/Lib.System.php');

include_once ('FDL/Class.Form1NF.php');

ini_set('memory_limit', '1G');

function usage()
{
    echo "Usage:\n";
    echo "  " . __FILE__ . "\n";
    echo "      --config=<config.xml>\n";
    echo "      --outputsql=<file_name> | --outputpgservice=<pgservice>]\n";
    echo "      [--tmppgservice=<tmp_pgservice_name>] (default tmp_1nf)\n";
    echo "      [--tmpschemaname=<tmp_schemaname>] (default 1nf)\n";
    echo "      [--tmpemptydb=<yes|no>] (default yes)\n";
    echo "      [--sqllog=<file>] (default none)\n";
    exit(1);
}

$usage = new ApiUsage();
$usage->setDefinitionText("Export 1nf");
/**
 * Args
 */
$parms = array(
    'config' => $usage->addRequiredParameter("config", "configuration file") ,
    'outputsql' => $usage->addOptionalParameter("outputsql", "File to output sql", null, "") ,
    'outputpgservice' => $usage->addOptionalParameter("outputpgservice", "Pgservice to output", null, "") ,
    'tmppgservice' => $usage->addOptionalParameter("tmppgservice", "Tmp pgservice name", null, 'tmp_1nf') ,
    'tmpschemaname' => $usage->addOptionalParameter('tmpschemaname', "Tmp shema name", null, 'tmp_1nf') ,
    'tmpemptydb' => $usage->addOptionalParameter("tmpemptydb", "Tmp empty database", null, "yes") ,
    'sqllog' => $usage->addOptionalParameter("sqllog", "File to log", null, "") ,
);

$usage->verify();
/**
 * Checks
 */
if (!is_file($parms['config'])) {
    $action->error(sprintf(_("Error: config file '%s' is not a file.", $parms['config'])));
    usage();
}
if (empty($parms['outputsql']) && empty($parms['outputpgservice'])) {
    $action->error(sprintf(_("Error: at least one of those parameters is mandatory --outputsql --outputpgservice")));
    usage();
}
if (empty($parms['tmppgservice'])) {
    $action->error(sprintf(_("Error: missing or empty --tmppgservice")));
    usage();
}
if (empty($parms['tmpschemaname'])) {
    $action->error(sprintf(_("Error: missing or empty --tmpschemaname")));
    usage();
}
$parms['tmpemptydb'] = strtolower($parms['tmpemptydb']);
if ($parms['tmpemptydb'] != '' && in_array($parms['tmpemptydb'], array(
    'no',
    'n',
    'non',
    '0',
    'false'
))) {
    $parms['tmpemptydb'] = 'no';
} elseif ($parms['tmpemptydb'] == '' || in_array($parms['tmpemptydb'], array(
    'yes',
    'y',
    'oui',
    'o',
    '1',
    'true'
))) {
    $parms['tmpemptydb'] = 'yes';
} else {
    $action->error(sprintf(_("Error: parameter tmpemptydb '%s' is not allowed.")));
    usage();
}
/**
 * RUN
 */
$form1NF = new Form1NF($parms);

$ret = $form1NF->run();
?>