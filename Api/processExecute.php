<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 *  Execute Freedom Processes when needed
 *
 * @author Anakeen
 * @version $Id: processExecute.php,v 1.4 2008/12/31 14:39:52 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */
// refreah for a classname
// use this only if you have changed title attributes
global $action;

processExecuteAPI::run($action);
