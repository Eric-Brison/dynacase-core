<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: autocompletion.php,v 1.22 2009/01/13 14:17:36 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/enum_choice.php");

function autocompletion(Action & $action)
{
    // list of choice to be insert in attribute values
    $docid = GetHttpVars("docid"); // document being edition
    if (!$docid) {
        $docid = GetHttpVars("classid", 0);
    } // in case of docid is null
    $attrid = GetHttpVars("attrid", 0); // attribute need to enum
    $sorm = GetHttpVars("sorm", "single"); // single or multiple
    $index = GetHttpVars("index", ""); // index of the attributes for arrays
    $domindex = GetHttpVars("domindex", ""); // index in dom of the attributes for arrays
    $enum = GetHttpVars("enum"); // special case when it is an enum
    $skey = GetHttpVars("skey"); // use only when enum (filter key)
    $acId = GetHttpVars("acid", ""); // autocompletion Id
    $defaultphpfunc = (getHttpVars("defaultphpfunc", "no") === "yes");
    if (!empty($_SERVER["HTTP_HOST"])) {
        header('Content-type: text/xml; charset=utf-8');
    }
    
    if ($enum != "") {
        $attrid = $enum;
    }
    $err = '';
    $canitem = false;
    $dbaccess = $action->dbaccess;
    
    $doc = new_Doc($dbaccess, $docid);
    $docid = $doc->id;
    $action->lay->Set("count", 0);
    $oattr = false;
    if ($docid == 0) {
        try {
            if ($acId == '') {
                throw new Exception(sprintf(_("Missing or empty 'acid'.")));
            }
            $ac = $action->Read(sprintf('autocompletion.%s', $acId));
            if ($ac === '' || $ac === '--') {
                throw new Exception(sprintf(_("Missing value for 'autocompletion.%s'.") , $acId));
            }
            if (!isset($ac['phpfile'])) {
                throw new Exception(sprintf(_("Missing 'phpfile' in 'autocompletion.%s'.") , $acId));
            }
            if (!isset($ac['phpfunc'])) {
                throw new Exception(sprintf(_("Missing 'phpfunc' in 'autocompletion.%s'.") , $acId));
            }
            $label = isset($ac['label']) ? $ac['label'] : _("no label");
            $phpfile = $ac['phpfile'];
            $phpfunc = $ac['phpfunc'];
            // specific interface
            $index = "";
            $format = "";
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
            $eformat = "";
            $options = "";
            $oattr = new NormalAttribute($attrid, $doc->id, $label, "text", $format, $repeat, $order, $link, $visibility, $needed, $isInTitle, $isInAbstract, $fieldSet, $phpfile, $phpfunc, $elink, $phpconstraint, $usefor, $eformat, $options);
        }
        catch(Exception $e) {
            $err = $e->getMessage();
        }
    } else {
        $oattr = $doc->GetAttribute($attrid);
    }
    if (!$oattr) {
        $err = sprintf(_("unknown attribute %s") , $attrid);
    } else {
        if ($defaultphpfunc) {
            resetAttrDefaultPhpFunc($oattr);
        }
    }
    
    $xmlDocument = new DOMDocument();
    $xmlRoot = $xmlDocument->createElement("status");
    $xmlDocument->appendChild($xmlRoot);
    
    if ($err == "") {
        $notalone = "true";
        
        if (preg_match("/([a-z]*)-alone/", $sorm, $reg)) {
            $notalone = "false";
        }
        $action->lay->set("notalone", $notalone);
        
        $action->parent->AddJsRef($action->GetParam("CORE_STANDURL") . "app=FDL&action=ENUMCHOICEJS");
        $phpfunc = $oattr->phpfunc;
        // capture title
        $ititle = "";
        
        if ((!empty($phpfunc)) && $phpfunc[0] == "[") {
            if (preg_match('/\[(.*)\](.*)/', $phpfunc, $reg)) {
                $oattr->phpfunc = $reg[2];
                
                $ititle = addslashes($reg[1]);
            }
        }
        
        $linkprefix = "ilink_"; // in coherence with editutil.php
        $action->lay->set("ititle", $ititle);
        // Utf8_decode_POST(); // because default is iso8859-1
        if ($enum != "") {
            // Correct multiple call error
            $oattr = clone $oattr;
            //if (seems_utf8($skey)) $skey=utf8_decode($skey);
            $canitem = (!$oattr->existEnum(trim($skey)));
            if (trim($skey) == '' && strpos($oattr->phpfunc, "linkenum") !== false) {
                $oattr->getEnum();
            }
            
            $oattr->phpfile = "fdl.php";
            $oattr->phpfunc = sprintf("fdlGetEnumValues('%s,'%s,'%s):li_%s,%s", $oattr->docid, $oattr->id, str_replace(array(
                ')',
                '(',
                ','
            ) , array(
                '&rpar;',
                '&lpar;',
                '&comma;'
            ) , $skey) , $oattr->id, $oattr->id);
        } elseif ($oattr->type == "docid" || $oattr->type == "account") {
            $aname = $oattr->id;
            $famid = $oattr->format;
            $multi = $oattr->getOption("multiple");
            $cible = ($multi == "yes") ? "mdocid_work" : "";
            if (!$oattr->phpfile) {
                $filter = array(); //no filter by default
                $sfilter = '""';
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
                $oattr->phpfunc = "lfamily(D,'$famid',${linkprefix}${aname},0,$sfilter,'$idid):${cible}${aname},${linkprefix}${aname}";
                $oattr->phpfile = "fdl.php";
            } else {
                //getDeclaration(D,ACOM_ENT_ID,ILINK_ACOM_DEPOTDECL):ACOM_DEPOTDECL,ILINK_ACOM_DEPOTDECL
                $phpfunc = preg_replace('/([\s|,|:|\(])CT([\s|,|\)]|$)/', '$1' . $linkprefix . $aname . '$2', $oattr->phpfunc);
                $phpfunc = str_replace("):$aname,", "):${cible}${aname},", $phpfunc);
                $phpfunc = str_replace("):" . strtoupper($aname) . ",", "):${cible}${aname},", $phpfunc);
                $oattr->phpfunc = $phpfunc;
            }
        }
        $oattr->phpfunc = preg_replace_callback('/([\s|,|:|\(])CT\[([^]]+)\]/', function ($matches) use ($linkprefix)
        {
            return $matches[1] . $linkprefix . strtolower($matches[2]);
        }
        , $oattr->phpfunc);
        
        $res = getResPhpFunc($doc, $oattr, $rargids, $tselect, $tval, true, $index);
        
        if (!is_array($res)) {
            if ($res == "") {
                $res = sprintf(_("error in calling function %s\n%s") , $oattr->phpfunc, $res);
            }
            $err = $res;
        }
        if ($err == "") {
            if (count($res) == 0) {
                $err = sprintf(_("no match for %s") , $oattr->getLabel());
                if ($enum) {
                    if (!$canitem) {
                        $err = sprintf(_("existing key item %s") , $skey);
                    } else {
                        if ($oattr->getOption("etype") == "free" || $action->getArgument("usefor") == "D") {
                            $res = array(
                                array(
                                    sprintf(_("free item %s") , $skey) ,
                                    $skey . ' ' . _("(free item)") ,
                                    $skey
                                )
                            );
                            $err = "";
                        } elseif ($oattr->getOption("etype") == "open") {
                            $res = array(
                                array(
                                    sprintf(_("new item %s") , $skey) ,
                                    $skey . ' ' . _("(new item)") ,
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
                    foreach ($res as $kv) { // verify existed key
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
                $xmlCibles = $xmlDocument->createElement("cibles");
                // add  index for return args only if the element is not in a array
                foreach ($rargids as $k => $noUsed) {
                    $linkprefix = "ilink_";
                    $isILink = false;
                    $attrId = $rargids[$k];
                    if (substr($attrId, 0, strlen($linkprefix)) == $linkprefix) {
                        $attrId = substr($attrId, strlen($linkprefix));
                        $isILink = true;
                    }
                    $docAttr = $doc->getAttribute($attrId);
                    if (is_object($docAttr) && !$docAttr->inArray()) {
                        $targid = trim(strtolower($attrId));
                    } else {
                        $targid = trim(strtolower($attrId . $domindex));
                    }
                    if ($isILink) {
                        $targid = $linkprefix . $targid;
                    }
                    $xmlCible = $xmlDocument->createElement("cible", $targid);
                    $xmlCibles->appendChild($xmlCible);
                }
                $xmlRoot->appendChild($xmlCibles);
                
                foreach ($res as $k => $v) {
                    
                    $xmlOption = $xmlDocument->createElement("option");
                    $xmlOption->setAttribute("value", $k);
                    //title
                    $title = array_shift($v);
                    $xmlOptionTitle = $xmlDocument->createElement("title");
                    $xmlOptionTitleCData = $xmlDocument->createCDATASection($title);
                    $xmlOptionTitle->appendChild($xmlOptionTitleCData);
                    
                    $xmlOption->appendChild($xmlOptionTitle);
                    //values
                    $xmlOptionValues = $xmlDocument->createElement("values");
                    foreach ($v as $value) {
                        $xmlOptionCurrentValue = $xmlDocument->createElement("val");
                        $xmlOptionCurrentValueCData = $xmlDocument->createCDATASection($value);
                        $xmlOptionCurrentValue->appendChild($xmlOptionCurrentValueCData);
                        $xmlOptionValues->appendChild($xmlOptionCurrentValue);
                    }
                    
                    $xmlOption->appendChild($xmlOptionValues);
                    
                    $xmlRoot->appendChild($xmlOption);
                }
                
                $xmlRoot->setAttribute("count", count($tselect));
            }
        }
    }
    
    if ($err) {
        $xmlRoot->setAttribute("warning", $err);
    }
    
    $action->lay->noparse = true;
    $action->lay->template = $xmlDocument->saveXML();
    
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

function resetAttrDefaultPhpFunc(NormalAttribute & $oattr)
{
    if ($oattr->type == 'account') {
        /* Setup default fdlGetAccounts() call */
        $oattr->phpfile = 'fdl.php';
        $options = $oattr->options;
        if ($oattr->format) {
            if ($options) {
                $options.= '|';
            }
            $options.= sprintf("family=%s", $oattr->format);
        }
        $oattr->phpfunc = sprintf('fdlGetAccounts(CT,15,"%s"):%s,CT', str_replace('"', '\\"', $options) , $oattr->id);
    } else if ($oattr->type == 'docid') {
        /* Let autocompletion() setup a default lfamily() call */
        $oattr->phpfile = '';
        $oattr->phpfunc = '';
    }
}
