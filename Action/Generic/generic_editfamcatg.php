<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Display edition of enum attributes
 *
 * @author Anakeen
 * @version $Id: generic_editfamcatg.php,v 1.1 2006/02/03 17:03:41 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Doc.php");
include_once ("GENERIC/generic_util.php");
/**
 * View interface to modify enumerate attributes
 * @param Action &$action current action
 * @global famid Http var : family document identifier where find enum attributes
 */
function generic_editfamcatg(&$action)
{
    $famid = GetHttpVars("famid", getDefFam($action));
    $action->lay->set("famid", $famid);
    $dbaccess = $action->getParam("FREEDOM_DB");
    $fam = new_doc($dbaccess, $famid);
    $action->lay->set("GTITLE", $fam->getTitle());
}
?>