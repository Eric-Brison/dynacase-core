<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: group_table.php,v 1.2 2003/08/18 15:46:41 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package WHAT
 * @subpackage USERS
 */
/**
 */
// ---------------------------------------------------------------
// $Id: group_table.php,v 1.2 2003/08/18 15:46:41 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Users/group_table.php,v $
// ---------------------------------------------------------------
// $Log: group_table.php,v $
// Revision 1.2  2003/08/18 15:46:41  eric
// phpdoc
//
// Revision 1.1  2002/01/08 12:41:33  eric
// first
//
// Revision 1.1  2001/08/28 12:12:37  eric
// prise en compte des groupes d'utilisateurs
//
// ---------------------------------------------------------------
include_once ("USERS/user_table.php");
// -----------------------------------
function group_table(&$action)
{
    // -----------------------------------
    user_table($action, true);
}
?>
