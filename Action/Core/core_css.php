<?php
/*
 * @author Anakeen
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
    $nfFile = array();
    
    if ($layout != '') {
        $action->lay->template = '';
    }
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
                    else $nfFile[] = $resource["ref"];
                }
            } else {
                $action->lay->template.= file_get_contents($resource["ref"]);
            }
        }
    } else {
        
        if (preg_match("/([A-Z_0-9-]+):([^:]+):{0,1}[A-Z]{0,1}/", $layout, $reg)) {
            $action->lay->template = '';
            $lfile = getLayoutFile($reg[1], strtolower($reg[2]));
            if ($lfile) $action->lay = new Layout($lfile, $action);
        } else {
            if (preg_match("/^css/", $layout, $reg) && strpos($layout, '..') === false) {
                
                if (is_file($layout)) $action->lay = new Layout($layout, $action);
            }
        }
    }
    
    if ($action->lay->template == '') {
        $nfFile[] = $layout;
    }
    
    $mimetype = '';
    if ($type == '' || $type == 'css') {
        
        $mimetype = 'text/css';
    } elseif ($type == 'js') {
        $mimetype = 'text/javascript';
    }
    if (count($nfFile) > 0) {
        header(sprintf("HTTP/1.0 404 %s layout [%s] not found", $type, implode(" ,", $nfFile)));
    } else {
        setHeaderCache($mimetype);
    }
}
?>