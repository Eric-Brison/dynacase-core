#!/usr/bin/env php
<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * Initialize core application
 *
 * @author Anakeen 2010
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */
/**
 */
include_once ("WHAT/Lib.Prefix.php");
include_once ("Class.Application.php");
$core = new Application();
$core->Set("CORE", $CoreNull, null, true);
?>