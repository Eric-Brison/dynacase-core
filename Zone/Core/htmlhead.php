<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: htmlhead.php,v 1.4 2005/01/21 17:47:40 eric Exp $
 * @package FDL
 * @subpackage CORE
 */
/**
 */
// $Id: htmlhead.php,v 1.4 2005/01/21 17:47:40 eric Exp $
include_once ('Class.QueryDb.php');
include_once ('Class.Application.php');

function htmlhead(Action & $action)
{
    
    $title = GetHttpVars("title");
    $action->lay->eset("TITLE", $title);
    $action->parent->addCssRef("css/dcp/core.css");
}
?>
