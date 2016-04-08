<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: popup.php,v 1.2 2005/09/27 16:16:50 eric Exp $
 * @package FDL
 * @subpackage GED
 */
/**
 */

function popup(&$action)
{
    $folio = GetHttpVars("folio");
    
    if ($folio) {
        $action->lay->set("ofolio", "&folio=$folio");
    } else {
        $action->lay->set("ofolio", "");
    }
}
?>