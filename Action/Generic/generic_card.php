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
 * @version $Id: generic_card.php,v 1.7 2005/06/28 08:37:46 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage
 */
/**
 */
// ---------------------------------------------------------------
// $Id: generic_card.php,v 1.7 2005/06/28 08:37:46 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Generic/generic_card.php,v $
// ---------------------------------------------------------------

include_once ("FDL/Class.Doc.php");
// -----------------------------------
// -----------------------------------
function generic_card(&$action)
{
    // -----------------------------------
    // set title
    $docid = GetHttpVars("id");
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $doc = new_Doc($dbaccess, $docid);
    if (!$doc->isAffected()) $action->exitError(sprintf(_("cannot see unknow reference %s") , $docid));
    
    $action->lay->Set("TITLE", $doc->title);
    
    $head = GetHttpVars("head", "no"); // directory to place doc if new doc
    if ($head == "yes") $action->lay->Set("PROPS", "Y");
    else $action->lay->Set("PROPS", "N");
}
?>
