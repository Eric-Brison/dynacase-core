<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Main program to activate action in WHAT software in guest mode
 *
 * @author Anakeen
 * @version $Id: guest.php,v 1.24 2008/12/16 15:51:53 jerome Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ('WHAT/Class.ActionRouter.php');

if (ActionRouter::inMaintenance()) {
    include_once ('TOOLBOX/stop.php');
    exit(0);
}

include_once ('WHAT/Lib.Main.php');
include_once ('WHAT/Class.ActionRouter.php');

$account = new Account();
if ($account->setLoginName("anonymous") === false) {
    throw new \Dcp\Exception(sprintf("anonymous account not found."));
}
$actionRouter = new ActionRouter($account);
$actionRouter->executeAction();
