<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Full Text Search document
 *
 * @author Anakeen
 * @version $Id: fgsearch_addsearch.php,v 1.1 2007/10/17 05:52:35 marc Exp $
 * @package FDL
 * @subpackage GED
 */
/**
 */

include_once ("FDL/Lib.Dir.php");
include_once ("FDL/Class.DocSearch.php");
include_once ("FDL/freedom_util.php");

function fgsearch_addsearch(&$action)
{
    $host = $_SERVER["HTTP_HOST"];
    $action->lay->set("HOST", $host);
}
?>