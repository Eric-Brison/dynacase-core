<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Image browser from FCKeditor
 *
 * @author Anakeen 2007
 * @version $Id: fckimage.php,v 1.5 2007/12/04 16:04:52 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Lib.Dir.php");
/**
 * Image browser from FCKeditor
 * @param Action &$action current action
 *
 */
function fckimage(&$action)
{
    
    $startpage = intval(GetHttpVars("page", "0")); // page number
    $key = GetHttpVars("key"); // key filter
    $slice = 28;
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    if ($startpage == 0) $start = 0;
    else $start = ($startpage * $slice + 1);
    $sqlfilters = array();
    if ($key) $sqlfilters[] = "svalues ~* '" . pg_escape_string($key) . "'";
    $limg = getChildDoc($dbaccess, 0, $start, $slice, $sqlfilters, $action->user->id, "TABLE", "IMAGE");
    $wimg = createDoc($dbaccess, "IMAGE", false);
    $oaimg = $wimg->getAttribute("img_file");
    
    foreach ($limg as $k => $img) {
        $wimg->id = $img["id"];
        $limg[$k]["imgsrc"] = $wimg->GetHtmlValue($oaimg, $img["img_file"]);
        $limg[$k]["imgcachesrc"] = str_replace("cache=no", "", $limg[$k]["imgsrc"]);
        if (preg_match("/vid=([0-9]+)/", $limg[$k]["imgsrc"], $vids)) {
            $vid = $vids[1];
            if ($vid > 0) $limg[$k]["imgcachesrc"] = $limg[$k]["imgsrc"] . "&width=100";
        }
    }
    
    $action->lay->set("key", $key);
    if (($startpage == 0) && (count($limg) < $slice)) {
        $action->lay->set("morepages", false);
    } else {
        
        $action->lay->set("morepages", true);
        $action->lay->set("hppage", true);
        if ($startpage > 0) $action->lay->set("ppage", $startpage - 1);
        else $action->lay->set("hppage", false);
        $action->lay->set("cpage", $startpage + 1);
        if ($slice == count($limg)) $action->lay->set("npage", $startpage + 1);
        else $action->lay->set("npage", 0);
    }
    
    $action->lay->setBlockData("IMAGES", $limg);
    $action->lay->set("NOIMAGES", (count($limg) == 0));
}
?>