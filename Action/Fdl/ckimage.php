<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Image browser from CKeditor
 *
 * @author Anakeen
 * @version $Id: fckimage.php,v 1.5 2007/12/04 16:04:52 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once "FDL/Lib.Dir.php";
/**
 * Image browser from CKeditor
 * @param Action &$action current action
 *
 */
function ckimage(Action & $action)
{
    $err = "";
    
    $usage = new ActionUsage($action);
    /* Internal numFunc */
    $numFunc = $usage->addRequiredParameter("CKEditorFuncNum", "CKEditorFuncNum");
    
    $startpage = intval($usage->addOptionalParameter("page", "pageNumber", array() , "0"));
    $key = $usage->addOptionalParameter("key", "key", array() , "");
    
    $usage->setStrictMode(false);
    
    $usage->verify();
    
    $slice = 28;
    $dbaccess = $action->dbaccess;
    
    if ($startpage == 0) $start = 0;
    else $start = ($startpage * $slice + 1);
    $sqlfilters = array();
    if ($key) $sqlfilters[] = "svalues ~* '" . pg_escape_string($key) . "'";
    $limg = internalGetDocCollection($dbaccess, 0, $start, $slice, $sqlfilters, $action->user->id, "TABLE", "IMAGE");
    $wimg = createDoc($dbaccess, "IMAGE", false);
    $oaimg = $wimg->getAttribute("img_file");
    foreach ($limg as $k => $img) {
        $wimg->id = $img["id"];
        $limg[$k]["imgsrc"] = $wimg->GetHtmlValue($oaimg, $img["img_file"]);
        $limg[$k]["imgcachesrc"] = str_replace("cache=no", "", $limg[$k]["imgsrc"]);
        if (preg_match("/^file/", $limg[$k]["imgsrc"], $vids)) {
            $limg[$k]["imgcachesrc"] = $limg[$k]["imgsrc"] . "&width=100";
        }
    }
    
    $action->lay->eSet("key", $key);
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
    $action->lay->set("FUNCNUM", urlencode($numFunc));
}
