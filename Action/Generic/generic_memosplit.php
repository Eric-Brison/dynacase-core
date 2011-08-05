<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * Modify split or seraches
 *
 * @author Anakeen 2000
 * @version $Id: generic_memosplit.php,v 1.5 2007/05/04 10:19:43 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
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
