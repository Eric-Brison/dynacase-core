<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * send css file
 *
 * @author Anakeen
 * @package FDL
 * @subpackage CORE
 */
/**
 */
function core_css(Action & $action)
{
    
    $layout = $action->getArgument("layout");
    $type = $action->getArgument("type");
    
    if (preg_match("/([A-Z_-]+):([^:]+):{0,1}[A-Z]{0,1}/", $layout, $reg)) {
        $lfile = getLayoutFile($reg[1], strtolower($reg[2]));
        if ($lfile) $action->lay = new Layout(getLayoutFile($reg[1], strtolower($reg[2])) , $action);
    }
    
    if ($type == '' || $type == 'css') {
        $type = 'text/css';
    } elseif ($type == 'js') {
        $type = 'text/javascript';
    }
    
    setHeaderCache($type);
}
?>