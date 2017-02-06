<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: setactpar.php,v 1.3 2004/03/22 15:21:40 eric Exp $
 * @package FDL
 * @subpackage CORE
 */
/**
 */
// ---------------------------------------------------------------
// $Id: setactpar.php,v 1.3 2004/03/22 15:21:40 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Core/setactpar.php,v $
// ---------------------------------------------------------------
// $Log: setactpar.php,v $
// Revision 1.3  2004/03/22 15:21:40  eric
// change HTTP variable name to put register_globals = Off
//
// Revision 1.2  2003/08/18 15:46:41  eric
// phpdoc
//
// Revision 1.1  2002/01/08 12:41:33  eric
// first
//
// Revision 1.2  2001/02/07 11:31:11  yannick
// Ménage sur debug
//
// Revision 1.1  2001/02/06 16:32:45  yannick
// Add Action-Session set action
//
// Revision 1.2  2000/10/10 19:09:11  marc
// Mise au point
//
// Revision 1.1  2000/10/06 19:37:44  marc
// Creation
//
//
// ---------------------------------------------------------------
include_once ("Class.Action.php");
// -----------------------------------
function setactpar(&$action)
{
    // -----------------------------------
    global $_POST;
    reset($_POST);
    $sapp = GetHttpVars("sapp", $action->name);
    $sact = GetHttpVars("sact", $action->parent->name);
    
    $key = GetHttpVars("key", "");
    
    $app = new Application($action->dbaccess);
    $app->Set($sapp, $action->parent);
    $act = new Action($action->dbaccess);
    $act->Set($sact, $app, $action->session);
    
    if ($key != "") {
        if ($key != "all") {
            $val = GetHttpVars("$key", "");
            $act->ActRegister($key, $val);
        } else {
            global $_POST;
            foreach ($_POST as $k => $v) {
                if (($k != "key") && ($k != "sapp") && ($k != "sact")) {
                    $act->ActRegister($k, $v);
                }
            }
        }
    }
    
    redirect($action, $sapp, $sact);
}
