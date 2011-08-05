<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * HTML Header
 *
 * @author Anakeen 2006
 * @version $Id: htmlhead.php,v 1.2 2006/04/03 14:56:26 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 */

function htmlhead(&$action)
{
    $title = GetHttpVars("title");
    $action->lay->set("doctitle", $title);
}
?>
