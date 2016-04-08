<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: gencss.php,v 1.3 2003/08/18 15:47:04 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */
// ---------------------------------------------------------------
// $Id: gencss.php,v 1.3 2003/08/18 15:47:04 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Zone/Fdl/gencss.php,v $
// ---------------------------------------------------------------
// -----------------------------------
function gencss(&$action)
{
    // -----------------------------------
    // Set Css
    $cssfile = $action->GetLayoutFile("freedom.css");
    $csslay = new Layout($cssfile, $action);
    $action->parent->AddCssCode($csslay->gen());
}
