<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * to cache rseult one hour
 *
 * @author Anakeen
 * @version $Id: cacheone.php,v 1.3 2008/08/12 12:42:17 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

function cacheone(&$action)
{
    if (substr($action->lay->file, -3) == ".js") $mime = "text/javascript";
    else $mime = "";
    setHeaderCache($mime);
}
?>