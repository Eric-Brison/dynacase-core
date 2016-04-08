#!/usr/bin/env php
<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Initialize core application
 *
 * @author Anakeen
 */
/**
 */
include_once ("WHAT/Lib.Prefix.php");
include_once ("Class.Application.php");
$core = new Application();
$core->Set("CORE", $CoreNull, null, true);
?>