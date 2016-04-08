<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Concatenation of the 2 css file : style and size
 *
 * @author Anakeen
 * @version $Id: systemcss.php,v 1.1 2006/07/27 16:04:19 eric Exp $
 * @package FDL
 * @subpackage CORE
 */
/**
 */

function systemcss(Action & $action)
{
    
    $file = DEFAULT_PUBDIR . "/css/dcp/main.css";
    
    $tsize = file_get_contents($file);
    
    $action->lay->template = $tsize;
}
