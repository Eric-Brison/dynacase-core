<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Modify split or seraches
 *
 * @author Anakeen
 * @version $Id: generic_memosplit.php,v 1.5 2007/05/04 10:19:43 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */
include_once ("GENERIC/generic_util.php");
function generic_memosplit(&$action)
{
    $split = GetHttpVars("split"); // split H or V
    $famid = GetHttpVars("famid"); // family id
    setFamilyParameter($action, $famid, 'GENE_SPLITMODE', $split);
}

function generic_memosearch(&$action)
{
    $split = GetHttpVars("psearchid"); // preferential user search
    $famid = GetHttpVars("famid"); // family id
    setFamilyParameter($action, $famid, 'GENE_PREFSEARCH', $split);
}
?>
