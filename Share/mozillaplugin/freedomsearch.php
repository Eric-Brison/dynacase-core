<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * Search come from mozilla seach engine
 *
 * @author Anakeen 2005
 * @version $Id: freedomsearch.php,v 1.6 2007/10/22 07:20:53 marc Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage
 */
/**
 */
include_once ("Lib.Http.php");
$famid = GetHttpVars("famid", 0);
$latest = GetHttpVars("latest", true);
$fromdir = GetHttpVars("fromdir", false);
$sensitive = GetHttpVars("sensitive", false);
$viewone = GetHttpVars("viewone", "N");
$keyword = GetHttpVars("keyword", "");
if ($keyword == "") {
    $location = "index.php?sole=Y&&app=FGSEARCH&action=FULLSEARCH";
    Header("Location: $location");
} else {
    
    $location = "index.php?sole=Y&&app=FGSEARCH&action=SEARCH&famid=$famid&latest=$latest&fromdir=$fromdir&sensitive=$sensitive&viewone=$viewone&keyword=$keyword";
    Header("Location: $location");
}
?>
