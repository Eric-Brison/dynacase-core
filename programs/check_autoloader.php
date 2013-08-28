#!/usr/bin/env php
<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
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
