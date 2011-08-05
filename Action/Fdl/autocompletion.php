<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: autocompletion.php,v 1.22 2009/01/13 14:17:36 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage
 */
/**
 */

include_once ("FDL/enum_choice.php");

function autocompletion(&$action)
{
    // list of choice to be insert in attribute values
    $docid = GetHttpVars("docid"); // document being edition
    if (!$docid) $docid = GetHttpVars("classid", 0); // in case of docid is null
    $attrid = GetHttpVars("attrid", 0); // attribute need to enum
    $sorm = GetHttpVars("sorm", "single"); // single or multiple
    $index = GetHttpVars("index", ""); // index of the attributes for arrays
    $domindex = GetHttpVars("domindex", ""); // index in dom of the attributes for arrays
    $enum = GetHttpVars("enum"); // special case when it is an enum
    $skey = GetHttpVars("skey"); // use only when enum (filter key)
    header('Content-type: text/xml; charset=utf-8');
    
    $action->lay->setEncoding("utf-8");
    if ($enum != "") $attrid = $enum;
    
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $docid = intval($docid);
    $doc = new_Doc($dbaccess, $docid);
    $action->lay->Set("count", 0);
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
        if (!$oattr) $err = sprintf(_("unknown attribute %s") , $attrid);
    }
    if ($err == "") {
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
        
        $linkprefix = "ilink_"; // in coherence with editutil.php
        $action->lay->set("ititle", $ititle);
        // Utf8_decode_POST(); // because default is iso8859-1
        if ($enum != "") {
            //if (seems_utf8($skey)) $skey=utf8_decode($skey);
            $canitem = (!$oattr->existEnum(trim($skey)));
            if (trim($skey) == '' && strpos($oattr->phpfunc, "linkenum") !== false) {
                $oattr->getEnum();
            }
            $eval = $oattr->phpfunc;
            
            $oattr->phpfile = "fdl.php";
            $eval = str_replace(array(
                '\,',
                '\.'
            ) , array(
                '&comma;',
                '&point;'
            ) , $eval);
            $oattr->phpfunc = sprintf("lenumvalues('%s,'%s):li_%s,%s", str_replace(array(
                ',',
                '(',
                ')'
            ) , array(
                '---',
                '&lpar;',
                '&rpar;'
            ) , $eval) , str_replace(array(
                ')',
                '(',
                ','
            ) , array(
                '&rpar;',
                '&lpar;',
                '&comma;'
            ) , $skey) , $oattr->id, $oattr->id);
        } elseif ($oattr->type == "docid") {
            $aname = $oattr->id;
            $famid = $oattr->format;
            $multi = $oattr->getOption("multiple");
            $cible = ($multi == "yes") ? "mdocid_work" : "";
            if (!$oattr->phpfile) {
                $filter = array(); //no filter by default
                $sfilter = '';
                $idid = "initid"; //if there's no docrev option (or it's present but not fixed), use initid to have the latest.
                $docrev = $oattr->getOption("docrev");
                if ($docrev == "fixed") {
                    $idid = "id";
                } elseif ($docrev != "latest") {
                    //if $docrev is neither fixed nor latest it should be state=...
                    //if not, we'll just ignore the option
                    $matches = array();
                    if (preg_match("/^state\(([a-zA-Z0-9_:-]+)\)/", $docrev, $matches)) {
                        $filter[] = "state='" . pg_escape_string($matches[1]) . "'";
                    }
                }
                //make $filter safe to pass in a string for getResPhpFunc.
                if (count($filter) == 0) $sfilter = serialize($filter);
                $oattr->phpfunc = "lfamily(D,$famid,${linkprefix}${aname},0,$sfilter,'$idid):${cible}${aname},${linkprefix}${aname}";
                $oattr->phpfile = "fdl.php";
            } else {
                //getDeclaration(D,ACOM_ENT_ID,ILINK_ACOM_DEPOTDECL):ACOM_DEPOTDECL,ILINK_ACOM_DEPOTDECL
                $phpfunc = preg_replace('/([\s|,|:])CT([\s|,|\)]|$)/', '$1' . $linkprefix . $aname . '$2', $oattr->phpfunc);
                $phpfunc = str_replace("):$aname,", "):${cible}${aname},", $phpfunc);
                $phpfunc = str_replace("):" . strtoupper($aname) . ",", "):${cible}${aname},", $phpfunc);
                $oattr->phpfunc = $phpfunc;
            }
        }
        $oattr->phpfunc = preg_replace('/([\s|,|:])CT\[([^]]+)\]/e', "'\\1'.$linkprefix.strtolower('\\2')", $oattr->phpfunc);
        
        $res = getResPhpFunc($doc, $oattr, $rargids, $tselect, $tval, true, $index);
        if (!is_array($res)) {
            if ($res == "") $res = sprintf(_("error in calling function %s\n%s") , $oattr->phpfunc, $res);
            $err = $res;
        }
        if ($err == "") {
            if (count($res) == 0) {
                $err = sprintf(_("no match for %s") , $oattr->getLabel());
                if ($enum) {
                    if (!$canitem) {
                        $err = sprintf(_("existing key item %s") , $skey);
                    } else {
                        if ($oattr->getOption("etype") == "open") {
                            $res = array(
                                array(
                                    sprintf(_("new item %s") , $skey) ,
                                    $skey . ' ' . _("(new item)") ,
                                    $skey
                                )
                            );
                            $err = "";
                        } elseif ($oattr->getOption("etype") == "free") {
                            $res = array(
                                array(
                                    sprintf(_("free item %s") , $skey) ,
                                    $skey . ' ' . _("(free item)") ,
                                    $skey
                                )
                            );
                            $err = "";
                        } else {
                            $err = sprintf(_("unknow item %s") , $skey);
                        }
                    }
                }
            } else {
                if ($enum && (trim($skey) != "")) {
                    foreach ($res as $kr => $kv) { // verify existed key
                        if (($kv[1] == trim($skey)) || ($kv[2] == trim($skey))) $canitem = false;
                    }
                    if ($canitem) {
                        if ($oattr->getOption("etype") == "free") {
                            $res[] = array(
                                sprintf(_("free item %s") , $skey) ,
                                $skey . ' ' . _("(free item)") ,
                                $skey
                            );
                            $err = "";
                        } elseif ($oattr->getOption("etype") == "open") {
                            $res[] = array(
                                sprintf(_("new item %s") , $skey) ,
                                $skey . ' ' . _("(new item)") ,
                                $skey
                            );
                            $err = "";
                        }
                    }
                }
            }
            
            if ($err == "") {
                // add  index for return args
                while (list($k, $v) = each($rargids)) {
                    $targids[]["attrid"] = trim(strtolower($rargids[$k] . $domindex));
                }
                
                $action->lay->SetBlockData("cibles", $targids);
                $topt = array();
                foreach ($res as $k => $v) {
                    $topt[$k]["choice"] = $v[0];
                    $topt[$k]["cindex"] = $k;
                    unset($v[0]);
                    $topt[$k]["values"] = '<val><![CDATA[' . stripslashes(implode("]]></val><val><![CDATA[", $v)) . ']]></val>';
                }
                
                $action->lay->SetBlockData("SELECT", $topt);
                $action->lay->Set("count", count($tselect));
            }
        }
    }
    
    $action->lay->Set("autowarning", $err);
    $action->lay->action = null; // don't want parameters - conflict with possible parameters
    
}

function Utf8_decode_POST()
{
    
    global $_POST, $ZONE_ARGS;
    
    foreach ($_POST as $k => $v) {
        if (is_array($v)) {
            foreach ($v as $kv => $vv) $ZONE_ARGS[$k][$kv] = utf8_decode($vv);
        } else {
            $ZONE_ARGS[$k] = utf8_decode($v);
        }
    }
}
?>
