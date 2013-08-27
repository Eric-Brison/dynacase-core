<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

require_once 'WHAT/classAutoloader.php';
require_once 'WHAT/classAutoloaderIgnoreDotD.php';
include_once 'WHAT/Lib.Prefix.php';

\Dcp\DirectoriesAutoloader::instance(DEFAULT_PUBDIR, '.autoloader.cache')->addDirectory('./')->addCustomFilter('\Dcp\autoloaderIgnoreDotD')->register();
