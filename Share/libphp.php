<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * Set of include functions
 *
 * @author Anakeen 1999
 * @version $Id: libphp.php,v 1.2 2003/08/18 15:46:42 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package WHAT
 * @subpackage CORE
 * @deprecated
 */
/**
 */
// ---------------------------------------------------------------
//
// $Id: libphp.php,v 1.2 2003/08/18 15:46:42 eric Exp $
// (c) anakeen 1999       marc.claverie@anakeen.com
//                    yannick.lebriquer@anakeen.com
//                   marianne.lebriquer@anakeen.com
//
//
//
// Include libphp and control if they are not already included
//  Should be included at the top level of your Php app
//  All calls to libphpinclude should be done outside functions or class
//  definition
// ---------------------------------------------------------------
$LIBPHP_PHP = "";
$LEVEL = "";
/**
 * like include_once
 *
 * @deprecated until PHP 4.0
 */
function libphpinclude($module)
{
    
    $defname = strtoupper($module);
    $defname = strtr($defname, ".", "_");
    
    global $$defname;
    global $LEVEL;
    if (!isset($$defname)) {
        include ($module);
    }
}
/**
 * like print_r
 *
 * @deprecated until PHP 4.0
 */
function libphpshowvar($name, $var, $f = 1)
{
    $out = "";
    if ($f) $out = $out . '<font face="sans-serif" size="-1">';
    $out = $out . '<table width="100%" border="' . $f . '" align="center" cellpadding="0" border="0" cellspacing="0">';
    $out = $out . '<tr valign="top">';
    $out = $out . '<td width="20%" align="left" bgcolor="#CCFFEE">' . $name . '</th>';
    if (!isset($var)) {
        $out = $out . '<td width="10%" align="left">-- none --</th>';
        $out = $out . '<td width="70%" align="left">-- no value --</th>';
    } else {
        $out = $out . '<td width="10%" align="left">' . gettype($var) . '</th>';
        $out = $out . '<td width="70%" align="left">';
        if (is_array($var)) {
            while (list($k, $v) = each($var)) {
                $out = $out . libphpshowvar($k, $v, 1);
            }
            reset($var);
        } else {
            if (strlen(strval($var)) > 0) $out = $out . strval($var);
            else $out = $out . "-- empty --";
        }
        $out = $out . '</td>';
    }
    $out = $out . '</tr>';
    $out = $out . '</table>';
    if ($f) $out = $out . '</font>';
    return ($out);
}
?>
