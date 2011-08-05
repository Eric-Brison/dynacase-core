<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * progress bar tool
 *
 * @author Anakeen 2000
 * @version $Id: progressbar.php,v 1.3 2007/02/16 07:38:55 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage CORE
 */
/**
 */

function progressbar(&$action)
{
    $fbar = GetHttpVars("bar");
    
    $action->lay->set("bar", $fbar);
}

function progressbar2(&$action)
{
    $fbar = GetHttpVars("bar");
    static $lastline;
    
    if ($fbar) {
        $rf = file($fbar);
        // if (count($rf) > 0) {
        $ffbar = fopen("$fbar.lck", "w");
        if ($ffbar) fclose($ffbar);
        //}
        
    }
    $texts = array();
    $reste = 0;
    $total = 0;
    
    if ($rf) {
        foreach ($rf as $line) {
            if (preg_match("|([0-9\-]+)/([0-9\-]+)/(.*)|", $line, $reg)) {
                $texts[] = addslashes(trim($reg[3]));
                if (intval($reg[2]) >= 0) $total = intval($reg[2]);
                if (intval($reg[1]) >= 0) $reste = intval($reg[1]);
            }
        }
        // $action->lay->set("text",trim(addslashes($text)));
        $action->lay->set("texts", "['" . implode("','", ($texts)) . "']");
        $action->lay->set("reste", $reste);
        $action->lay->set("done", $total - $reste);
        $action->lay->set("total", $total);
        if ($total > 0) {
            $action->lay->set("pc", round(($total - $reste) / $total * 100) . "%");
            $action->lay->set("pc1", round(($total - $reste) / $total * 100));
        } else $action->lay->set("pc", "100%");
        $action->lay->set("waiting", 0);
    } else {
        $action->lay->set("waiting", 1);
    }
    unlink($fbar);
}
?>