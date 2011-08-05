<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * Full Text Search document
 *
 * @author Anakeen 2007
 * @version $Id: fgsearch_addsearch.php,v 1.1 2007/10/17 05:52:35 marc Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
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