<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * HTML Header
 *
 * @author Anakeen
 * @version $Id: htmlhead.php,v 1.2 2006/04/03 14:56:26 eric Exp $
 * @package FDL
 */
/**
 */

function htmlhead(Action & $action)
{
    $title = $action->getArgument("title");
    $action->lay->eset("doctitle", $title);
    $action->parent->addCssRef("css/dcp/main.css");
}
?>
