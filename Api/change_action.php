<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Change an attribute of an WHAT Action
 *
 *
 * @param string $appname internal name of the application
 * @param string $actname internal name of the action
 * @param string $attribute internal name of the field of the action
 * @param string $value new value for the attribute
 * @author Anakeen
 * @version $Id: change_action.php,v 1.2 2003/08/18 15:46:41 eric Exp $
 * @package FDL
 * @subpackage WSH
 */
/**
 */
// ---------------------------------------------------------------
// $Id: change_action.php,v 1.2 2003/08/18 15:46:41 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Api/change_action.php,v $
// ---------------------------------------------------------------
include_once ("Class.Application.php");

$usage = new ApiUsage();
$usage->setDefinitionText("Change an attribute of an WHAT Action");
$appname = $usage->addRequiredParameter("appname", "application name");
$actionname = $usage->addRequiredParameter("actname", "action name");
$attribute = $usage->addRequiredParameter("attribute", "attribute name");
$value = $usage->addOptionalParameter("value", "value to set", null, "");
$usage->verify();

$app = new Application();
$null = "";
$app->Set($appname, $null);
if ($app->id > 0) {
    $action = new Action($app->dbaccess);
    $action->Set($actionname, $app);
    if ($action->id > 0) {
        reset($action->fields);
        foreach ($action->fields as $k => $v) {
            if ($v == $attribute) {
                $action->$attribute = $value;
                $action->Modify();
                return true;
            }
        }
    }
}
return false;
?>
