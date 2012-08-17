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
    $packName = $action->getArgument("pack");
    
    if ($packName) {
        $packSession = $action->read("RSPACK_" . $packName);
        $action->lay->template = '';
        $action->lay->noparse = true;
        /**
         * @var array $packSession
         */
        foreach ($packSession as $resource) {
            if ($resource["needparse"]) {
                $action->lay->noparse = false;
                if (preg_match("/([A-Z_-]+):([^:]+):{0,1}[A-Z]{0,1}/", $resource["ref"], $reg)) {
                    $lfile = getLayoutFile($reg[1], strtolower($reg[2]));
                    if ($lfile) $action->lay->template.= file_get_contents($lfile);
                }
            } else {
                $action->lay->template.= file_get_contents($resource["ref"]);
            }
        }
    } else {
        if (preg_match("/([A-Z_-]+):([^:]+):{0,1}[A-Z]{0,1}/", $layout, $reg)) {
            $lfile = getLayoutFile($reg[1], strtolower($reg[2]));
            if ($lfile) $action->lay = new Layout($lfile, $action);
        }
    }
    if ($type == '' || $type == 'css') {
        $type = 'text/css';
    } elseif ($type == 'js') {
        $type = 'text/javascript';
    }
    
    setHeaderCache($type);
}
?>