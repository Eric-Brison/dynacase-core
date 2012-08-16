<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: enum_choice.php,v 1.53 2009/01/08 17:48:27 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Doc.php");
include_once ("FDL/Class.DocAttr.php");

function enum_choice(Action & $action)
{
    // list of choice to be insert in attribute values
    $docid = GetHttpVars("docid"); // document being edition
    if ($docid == "") $docid = GetHttpVars("fromid", 0); // in case of docid is null
    $attrid = GetHttpVars("attrid", 0); // attribute need to enum
    $sorm = GetHttpVars("sorm", "single"); // single or multiple
    $index = GetHttpVars("index", ""); // index of the attributes for arrays
    $domindex = GetHttpVars("domindex", ""); // index in dom of the attributes for arrays
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/geometry.js");
    $action->parent->AddJsRef("FDL/Layout/common.js");
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    $doc = new_Doc($dbaccess, $docid);
    
    if ($docid == 0) {
        // specific interface
        $value = "";
        $label = GetHttpVars("label", _("no label"));
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
        $phpfile = GetHttpVars("phpfile");
        $phpfunc = GetHttpVars("phpfunc");
        $fieldSet = $doc->attr["FIELD_HIDDENS"];
        $elink = "";
        $phpconstraint = "";
        $usefor = "";
        $eformat = "";
        $options = "";
        $oattr = new NormalAttribute($attrid, $doc->id, $label, "text", $format, $repeat, $order, $link, $visibility, $needed, $isInTitle, $isInAbstract, $fieldSet, $phpfile, $phpfunc, $elink, $phpconstraint, $usefor, $eformat, $options);
    } else {
        $oattr = $doc->GetAttribute($attrid);
        if (!$oattr) $action->exitError(sprintf(_("unknown attribute %s") , $attrid));
    }
    $notalone = "true";
    
    if (preg_match("/([a-z]*)-alone/", $sorm, $reg)) {
        $sorm = $reg[1];
        $notalone = "false";
    }
    $action->lay->set("notalone", $notalone);
    
    $action->parent->AddJsRef($action->GetParam("CORE_STANDURL") . "app=FDL&action=ENUMCHOICEJS");
    $phpfunc = $oattr->phpfunc;
    // capture title
    $ititle = "";
    
    if ($phpfunc[0] == "[") {
        if (preg_match('/\[(.*)\](.*)/', $phpfunc, $reg)) {
            $oattr->phpfunc = $reg[2];
            
            $ititle = addslashes($reg[1]);
        }
    }
    $action->lay->set("ititle", $ititle);
    
    $res = getResPhpFunc($doc, $oattr, $rargids, $tselect, $tval, true, $index);
    
    if (!is_array($res)) {
        if ($res == "") $res = sprintf(_("error in calling function %s") , $oattr->phpfunc);
        $action->exitError($res);
    }
    if (count($res) == 0) $action->exitError(sprintf(_("no match for %s") , $oattr->getLabel()));
    
    if ($sorm == "single") {
        
        $action->lay->SetBlockData("SELECTSINGLE", array(
            array(
                "zou"
            )
        ));
    } else {
        
        $action->lay->SetBlockData("SELECTMULTIPLE", array(
            array(
                "zou"
            )
        ));
        $action->lay->Set("nselect", (count($tselect) > 7) ? 7 : count($tselect));
    }
    // add  index for return args
    foreach ($rargids as $k => $v) {
        $rargids[$k] = trim($v);
        $rargids[$k].= $domindex;
    }
    $sattrid = "[";
    $sattrid.= strtolower("'" . implode("','", $rargids) . "'");
    $sattrid.= "]";
    $action->lay->Set("attrid", $sattrid);
    $action->lay->SetBlockData("SELECT", $tselect);
    $action->lay->SetBlockData("ATTRVAL", $tval);
}

function enumjschoice(&$action)
{
    $sorm = GetHttpVars("sorm", "single"); // single or multiple
    $notalone = "true";
    
    if (preg_match("/([a-z]*)-alone/", $sorm, $reg)) {
        $sorm = $reg[1];
        $notalone = "false";
    }
    $action->lay->set("notalone", $notalone);
}
/**
 * @param string $n
 * @param string $def
 * @param bool $whttpvars
 * @param Doc $doc
 * @param NormalAttribute $oa
 * @return array|bool|string
 */
function getFuncVar($n, $def = "", $whttpvars, &$doc, &$oa)
{
    if ($whttpvars) return GetHttpVars("_" . strtolower($n) , $def);
    else {
        $h = GetHttpVars(strtolower($n));
        if ($h) return $h;
        if (!$oa) return ($n);
        if (($oa->repeat) || $oa->inArray()) $r = $doc->getTValue($n);
        else $r = $doc->getValue($n);
        if ($r === "") return false;
        return $r;
    }
}
function getResPhpFunc(Doc & $doc, NormalAttribute & $oattr, &$rargids, &$tselect, &$tval, $whttpvars = true, $index = "")
{
    global $action;
    
    $phpfunc = $oattr->phpfunc;
    $phpfunc = str_replace(array(
        '\)',
        '\('
    ) , array(
        '&rparenthesis;',
        '&lparenthesis;'
    ) , $phpfunc);
    $oParse = new parseFamilyFunction();
    $strucFunc = $oParse->parse($phpfunc);
    if ($strucFunc->getError()) return $strucFunc->getError();
    
    if (!preg_match('/(.*)\((.*)\)\:(.*)/', $phpfunc, $reg)) {
        return sprintf(_("the pluggins function description '%s' is not conform for %s attribut") , $phpfunc, $oattr->id);
    }
    $callfunc = $reg[1];
    if (!function_exists($callfunc)) {
        if (!@file_exists("EXTERNALS/$oattr->phpfile")) {
            return sprintf(_("the external pluggin file %s cannot be read") , $oattr->phpfile);
        } else {
            include_once ("EXTERNALS/$oattr->phpfile");
        }
    }
    if (!function_exists($callfunc)) {
        return sprintf(_("function '%s' declared in %s is not found") , $callfunc, $oattr->id);
    }
    $rargids = explode(",", $reg[3]); // return args
    // change parameters familly
    $iarg = preg_replace('/\{([^\}]+)\}/e',
    //"/\{([a-zA-Z0-9_]+)\}/e",
    "getAttr('\\1')", $reg[2]);
    $argids = explode(",", $iarg); // input args
    $arg = array();
    foreach ($strucFunc->inputs as $k => $inpArg) {
        $v = str_replace(array(
            '&rparenthesis;',
            '&lparenthesis;'
        ) , array(
            ')',
            '('
        ) , $inpArg->name);
        if ($v != " ") $v = trim($v);
        
        $unser = @unserialize($v); // try unserial to see if it is object
        if ($unser != "") $arg[$k] = $unser;
        elseif ($inpArg->type == "string") $arg[$k] = $v;
        elseif ($v == "A") {
            global $action;
            $arg[$k] = & $action;
        } else if ($v == "D") $arg[$k] = $doc->dbaccess;
        else if ($v == "I") $arg[$k] = $doc->id;
        else if ($v == "WIID") $arg[$k] = getHttpVars("wiid");
        else if ($v == "K") $arg[$k] = $index;
        else if ($v == "T") $arg[$k] = & $doc;
        else if (($v[0] == "'") || ($v[0] == '"')) {
            $lc = substr($v, -1);
            if ($lc == $v[0]) $arg[$k] = mb_substr($v, 1, -1);
            else $arg[$k] = mb_substr($v, 1);
        } else if ($doc->getProperty($v) !== false) $arg[$k] = $doc->getProperty($v);
        else {
            // can be values or family parameter
            $a = $doc->GetAttribute($v);
            if ($index === "") {
                $ta = getFuncVar($v, $v, $whttpvars, $doc, $a);
                if ($ta === false) return false;
                
                if (is_array($ta)) {
                    unset($ta["-1"]); // suppress hidden row because not set yet
                    $arg[$k] = $ta;
                } else $arg[$k] = trim($ta);
            } else {
                if ($a && ($a->usefor == "Q")) {
                    if (($a->fieldSet->id == $oattr->fieldSet->id)) { // search with index
                        $ta = getFuncVar($v, $v, $whttpvars, $doc, $a);
                        if ($ta === false) return false;
                        $arg[$k] = trim($ta[$index]);
                    } else {
                        $arg[$k] = $doc->getParamValue($v);
                    }
                } else if ($a && $a->inArray()) {
                    if (($a->fieldSet->id == $oattr->fieldSet->id)) { // search with index
                        $ta = getFuncVar($v, $v, $whttpvars, $doc, $a);
                        if ($ta === false) return false;
                        $arg[$k] = trim($ta[$index]);
                    } else {
                        $ta = getFuncVar($v, $v, $whttpvars, $doc, $a);
                        if ($ta === false) return false;
                        
                        if (is_array($ta)) {
                            unset($ta["-1"]); // suppress hidden row because not set yet
                            $arg[$k] = $ta;
                        } else $arg[$k] = trim($ta);
                        $arg[$k] = $ta;
                    }
                } else {
                    $ta = getFuncVar($v, $v, $whttpvars, $doc, $a);
                    if ($ta === false) return false;
                    if (is_array($ta)) {
                        if ($index !== "") $arg[$k] = trim($ta[$index]);
                        else $arg[$k] = $ta;
                    } else $arg[$k] = trim($ta);
                }
            }
            if ($a && ($a->usefor == "Q")) {
                if (getFuncVar($v, false, $whttpvars, $doc, $a) === false) $arg[$k] = $doc->getParamValue($v);
            }
        }
    }
    try {
        
        $res = call_user_func_array($callfunc, $arg);
    }
    catch(Exception $e) {
        $res = $e->getMessage();
    }
    
    if (is_array($res) && (count($res) > 0)) {
        // addslahes for JS array
        reset($res);
        foreach ($res as $k => $v) {
            if (!is_array($v)) {
                $err = ErrorCode::getError("INH0001", $callfunc, $oattr->id);
                error_log($err);
                return $err;
            }
            foreach ($v as $k2 => $v2) {
                if (!seems_utf8($v2)) {
                    $err = ErrorCode::getError("INH0002", iconv('ISO-8859-1', "UTF-8//TRANSLIT", $v2) , $callfunc, $oattr->id);
                    error_log($err);
                    return $err;
                }
                // not for the title
                if ($k2 > 0) $res[$k][$k2] = addslashes(str_replace("\r", "", str_replace("\n", "\\n", $v2))); // because JS array
                else $res[$k][$k2] = str_replace(array(
                    "<script",
                    "/script>"
                ) , array(
                    "&lt;",
                    "&gt;"
                ) , $res[$k][$k2]);
            }
        }
        $tselect = array();
        $tval = array();
        reset($res);
        $ki = 0;
        while (list($k, $v) = each($res)) {
            $tselect[$k]["choice"] = $v[0];
            $tselect[$k]["cindex"] = $ki; // numeric index needed
            $tval[$k]["index"] = $ki;
            array_shift($v);
            
            $tval[$k]["attrv"] = "['" . implode("','", $v) . "']";
            $ki++;
        }
    } else {
        $res = BasicAttribute::encodeXml($res, true);
    }
    
    return $res;
}

function getAttr($aid)
{
    $r = GetParam($aid);
    if ($r == "") $r = getFamIdFromName(GetParam("FREEDOM_DB") , $aid);
    
    return $r;
}
?>
