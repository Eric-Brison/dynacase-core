<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Modify family preferences
 *
 * @author Anakeen
 * @version $Id: generic_modprefs.php,v 1.2 2007/05/04 16:11:40 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Doc.php");
include_once ("GENERIC/generic_util.php");
function generic_modprefs(Action & $action)
{
    
    $famid = GetHttpVars("famid"); // family id
    $dispo = GetHttpVars("dispo"); // last searched
    $letters = GetHttpVars("letters"); // want tab letters
    $inherit = GetHttpVars("inherit"); // search in inherit
    $dbaccess = $action->dbaccess;
    $fdoc = new_doc($dbaccess, $famid);
    if (!$fdoc->isAlive()) {
        $action->exitError(sprintf(_("Family (#%s) not exists") , $famid));
    }
    $split = $visu = '';
    switch ($dispo) {
        case 1:
            $split = 'V';
            $visu = 'abstract';
            break;

        case 2:
            $split = 'H';
            $visu = 'column';
            break;

        case 3:
            $split = 'V';
            $visu = 'column';
            break;

        case 4:
            $split = 'H';
            $visu = 'abstract';
            break;
    }
    if ($dispo > 0) {
        setSplitMode($action, $famid, $split);
        setViewMode($action, $famid, $visu);
    }
    
    if ($letters == 1) setTabLetter($action, $famid, 'Y');
    else setTabLetter($action, $famid, 'N');
    if ($inherit == 1) setInherit($action, $famid, 'Y');
    else setInherit($action, $famid, 'N');
    
    $action->lay->eset("famname", $fdoc->getPropertyValue("name"));
}
