<?php

include_once('WHAT/Lib.Common.php');
include_once('FDL/freedom_util.php');
include_once('FDL/Class.SearchDoc.php');
include_once('WHAT/Lib.System.php');

include_once('FDL/Class.Form1NF.php');

ini_set('memory_limit', '1G');

function usage() {
	echo "Usage:\n";
	echo "  " . __FILE__ . "\n";
	echo "      --config=<config.xml>\n";
	echo "      --outputname=<output_name>\n";
	echo "      [--outputtype=<sql|pgservice>] (default sql)\n";
	echo "      [--tmppgservice=<tmp_pgservice_name>] (default tmp_1nf)\n";
	echo "      [--tmpschemaname=<tmp_schemaname>] (default 1nf)\n";
	echo "      [--tmpemptydb=<yes|no>] (default yes)\n";
	echo "      [--sqllog=<file>] (default none)\n";
	exit(1);
}

/**
 * Args
 */
$parms = array(
	'config' => '',
	'outputtype' => 'sql',
	'outputname' => '',
	'tmppgservice' => 'tmp_1nf',
	'tmpschemaname' => 'tmp_1nf',
	'tmpemptydb' => 'yes',
	'sqllog' => '',
);

foreach($parms as $key => $value) {
	$parms[$key] = getHttpVars($key, $value);
}

/**
 * Checks
 */
if (empty($parms['config'])) {
	$action->error(sprintf(_("Error: missing or empty --config")));
	usage();
}
if (!is_file($parms['config'])) {
	$action->error(sprintf(_("Error: config file '%s' is not a file.", $parms['config'])));
	usage();
}
if(!in_array($parms['outputtype'], array('sql', 'pgservice'))) {
	$action->error(sprintf(_("Error: Output type '%s' is not valid.", $parms['outputtype'])));
	usage();
}
if (empty($parms['outputname'])) {
	$action->error(sprintf(_("Error: missing or empty --outputname")));
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
if ($parms['tmpemptydb'] != '' && in_array($parms['tmpemptydb'], array('no', 'n', 'non', '0', 'false'))) {
	$parms['tmpemptydb'] = 'no';
}
elseif ($parms['tmpemptydb'] == '' || in_array($parms['tmpemptydb'], array('yes', 'y', 'oui', 'o', '1', 'true'))) {
	$parms['tmpemptydb'] = 'yes';
}
else {
	$action->error(sprintf(_("Error: parameter tmpemptydb '%s' is not allowed.")));
	usage();
}

/**
 * RUN
 */
$form1NF = new Form1NF($parms);

$ret = $form1NF->run();


?>