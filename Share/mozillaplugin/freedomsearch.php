<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Search come from mozilla seach engine
 *
 * @author Anakeen
 * @version $Id: freedomsearch.php,v 1.6 2007/10/22 07:20:53 marc Exp $
 * @package FDL
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
