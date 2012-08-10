<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Compose html code to insert input
 *
 * @author Anakeen 2006
 * @version $Id: inputattribute.php,v 1.4 2008/09/15 16:29:24 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Dir.php");
include_once ("FDL/editutil.php");
/**
 * Compose html code to insert input
 * @param Action &$action current action
 * @global string $type Http var : attribute type
 * @global string $id Http var : identificator of input generated
 * @global string $label Http var : label of attribute (only for doclink type when no choice is possible)
 * @global string $famid Http var : family identificator criteria (only for doclink type)
 * @global string $value Http var : predefined value
 * @global string $esize Http var : number of character visible (for text input)
 */
function inputattribute(&$action)
{
    
    $attrid = strtolower(GetHttpVars("id"));
    $type = stripslashes(GetHttpVars("type"));
    $jsevent = stripslashes(GetHttpVars("jsevent"));
    $label = GetHttpVars("label");
    $esize = GetHttpVars("esize");
    $value = GetHttpVars("value");
    $phpfunc = GetHttpVars("phpfunc");
    $phpfile = GetHttpVars("phpfile");
    $eformat = GetHttpVars("eformat");
    $options = GetHttpVars("options");
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $doc = new doc($dbaccess);
    $htmlinput = "";
    
    $value = str_replace('\\n', "\n", $value);
    if ($type == "doclink") {
        $famid = GetHttpVars("famid");
        
        $index = "";
        $jsevent = "";
        $format = "";
        $repeat = false;
        $order = 0;
        $link = "";
        $visibility = "H";
        $needed = "N";
        $isInTitle = false;
        $isInAbstract = false;
        $fieldSet = null;
        
        if (!$phpfunc) $phpfunc = "::getTitle(id_$attrid):$attrid";
        $elink = "";
        $phpconstraint = "";
        $usefor = "";
        $eformat = "";
        $options = "";
        if (!isUTF8($label)) $label = utf8_encode($label);
        
        $oattr1 = new NormalAttribute("id_$attrid", $doc->id, "id $label", "docid", $format, $repeat, $order, $link, $visibility, $needed, $isInTitle, $isInAbstract, $fieldSet, $phpfile, $phpfunc, $elink, $phpconstraint, $usefor, $eformat, $options);
        
        $index = "";
        $jsevent = "";
        $format = "";
        $repeat = false;
        $order = 0;
        $link = "";
        $visibility = "W";
        $needed = "N";
        $isInTitle = false;
        $isInAbstract = false;
        $phpfile = "fdl.php";
        $phpfunc = "lfamily(D,$famid,$attrid):id_$attrid,$attrid";
        $fieldSet = null;
        $elink = "";
        $phpconstraint = "";
        $usefor = "";
        $eformat = "";
        $options = "elabel=" . ($label);
        if ($esize) $options.= "|esize=$esize";
        $oattr = new NormalAttribute($attrid, $doc->id, $label, "text", $format, $repeat, $order, $link, $visibility, $needed, $isInTitle, $isInAbstract, $fieldSet, $phpfile, $phpfunc, $elink, $phpconstraint, $usefor, $eformat, $options);
        
        $doc->attributes->attr[$oattr1->id] = $oattr1;
        $htmlinput = getHtmlInput($doc, $oattr1, $value, $index, $jsevent, true);
        $doc->attributes->attr[$oattr->id] = $oattr;
    } else {
        
        $format = "";
        if (preg_match('/^(.*)\("([^"]*)"/', $type, $reg)) {
            $type = trim($reg[1]);
            $format = trim($reg[2]);
        }
        
        $index = "";
        $repeat = false;
        $order = 0;
        $link = "";
        $visibility = "W";
        $needed = "N";
        $isInTitle = false;
        $isInAbstract = false;
        $fieldSet = null;
        
        $elink = "";
        $phpconstraint = "";
        $usefor = "";
        
        if (!$options) $options = "elabel=" . ($label);
        if ($esize) $options.= "|esize=$esize";
        $oattr = new NormalAttribute($attrid, $doc->id, $label, $type, $format, $repeat, $order, $link, $visibility, $needed, $isInTitle, $isInAbstract, $fieldSet, $phpfile, $phpfunc, $elink, $phpconstraint, $usefor, $eformat, $options);
        $doc->attributes->attr[$attrid] = $oattr;
    }
    
    $htmlinput.= getHtmlInput($doc, $oattr, $value, $index, $jsevent, true);
    
    $action->lay->template = $htmlinput;
}
?>
