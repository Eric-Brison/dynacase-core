#!/usr/bin/env php
<?php
/*
 * @author Anakeen
 * @package FDL
*/

require_once 'WHAT/classAutoloader.php';
require_once 'WHAT/classAutoloaderIgnoreDotD.php';
include_once 'WHAT/Lib.Prefix.php';

try {
    \Dcp\DirectoriesAutoloader::instance(DEFAULT_PUBDIR, '.autoloader.cache')->addDirectory('./')->addCustomFilter('\Dcp\autoloaderIgnoreDotD')->dryRun()->forceRegenerate('');
}
catch(\Exception $e) {
    printf("Autoloader error: %s\n", $e->getMessage());
    exit(1);
}

exit(0);
