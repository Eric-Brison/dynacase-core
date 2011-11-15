<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Edition functions utilities
 *
 * @author Anakeen 2000
 * @version $Id: editutil.php,v 1.182 2009/01/14 12:33:31 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */
//
// ---------------------------------------------------------------
include_once ("FDL/Class.Doc.php");
include_once ("FDL/Class.DocAttr.php");
include_once ("VAULT/Class.VaultFile.php");
/**
 * Compose html code to insert input
 * @param Doc &$doc document to edit
 * @param DocAttribute &$attr attribute to edit
 * @param string $value value of the attribute
 * @param string $index in case of array : row of the array
 * @param string $jsevent add an javascript callback on input (like onblur or onmouseover)
 * @param string $notd not add cells in html input generated (by default inputs are in arrays)
 */
function getHtmlInput(&$doc, &$oattr, $value, $index = "", $jsevent = "", $notd = false)
{
    global $action;
    $docid = intval($doc->id);
    if ($docid == 0) $docid = intval($doc->fromid);
    $attrtype = $oattr->type;
    $usephpfunc = true;
    $alone = $oattr->isAlone; // set by method caller in special case to display alone
    $linkprefix = "";
    $attrid = $oattr->id;
    $attrin = '_' . $oattr->id; // for js name => for return values from client
    if ($index !== "") $attridk = $oattr->id . '_' . $index;
    else $attridk = $oattr->id . $index;
    if ($oattr->inArray()) {
        if ($index == - 1) {
            $attrin.= '[-1]';
            $attridk = $oattr->id . '__1x_';
        } else $attrin.= "[$index]";
    }
    if (isset($oattr->mvisibility)) $visibility = $oattr->mvisibility;
    else $visibility = $oattr->visibility;
    if ($visibility == "I") return ""; // not editable attribute
    $idisabled = " disabled readonly=1 title=\"" . _("read only") . "\" ";
    $input = "";
    
    if (!$notd) $classname = "class=\"fullresize\"";
    else $classname = "";
    
    if (($visibility == "H") || ($visibility == "R")) {
        $input = "<input  type=\"hidden\" name=\"" . $attrin . "\" value=\"" . chop(htmlentities(($value) , ENT_COMPAT, "UTF-8")) . "\"";
        $input.= " id=\"" . $attridk . "\" ";
        $input.= " > ";
        if (!$notd) $input.= '</td><td class="hiddenAttribute">';
        return $input;
    }
    
    $oc = "$jsevent onchange=\"document.isChanged=true\" "; // use in "pleaseSave" js function
    if ($docid == 0) {
        // case of specific interface
        $iopt = '&phpfile=' . $oattr->phpfile . '&phpfunc=' . $oattr->phpfunc . '&label=' . ($oattr->getLabel());
    } else $iopt = "";
    if (($oattr->type != "array") && ($oattr->type != "htmltext") && ($oattr->type != "docid")) {
        if ($visibility != "S") {
            if ($usephpfunc && ($oattr->phpfunc != "") && ($oattr->phpfile != "") && ($oattr->type != "enum") && ($oattr->type != "enumlist")) {
                if ($oattr->getOption("autosuggest", "yes") != "no") {
                    $autocomplete = " autocomplete=\"off\" autoinput=\"1\" onfocus=\"activeAuto(event," . $docid . ",this,'$iopt','$attrid','$index')\" ";
                    $oc.= $autocomplete;
                }
            }
        }
    }
    // output change with type
    switch ($attrtype) {
            //----------------------------------------
            
        case "image":
            if (preg_match(PREGEXPFILE, $value, $reg)) {
                $check = "";
                $originalname = "";
                $dbaccess = GetParam("FREEDOM_DB");
                $vf = newFreeVaultFile($dbaccess);
                $info = array();
                if ($vf->Show($reg[2], $info) == "") {
                    $vid = $reg[2];
                    $fname = "<A target=\"_self\" href=\"" . GetParam("CORE_BASEURL") . "app=FDL&action=EXPORTFILE&vid=$vid&docid=$docid&attrid=$attrid&index=$index\" title=\"{$info->name}\">";
                    // put image
                    $fname.= "<IMG  id=\"img_$attridk\" style=\"vertical-align:bottom;border:none;width:30px\" SRC=\"";
                    $fname.= GetParam("CORE_BASEURL") . "app=FDL&action=EXPORTFILE&width=30&vid=$vid&docid=" . $docid . "&attrid=" . $attrid . "&index=$index";
                    $fname.= "\">";
                    
                    $fname.= "</A>";
                    if ($oattr->getOption("preventfilechange") == "yes") {
                        include_once ("FDL/Lib.Vault.php");
                        $check = vault_uniqname($vid);
                        $originalname = "<input id=\"IFORI$attridk\" name=\"IFORI$attrin\" type=\"hidden\" orivalue=\"" . $doc->vault_filename($attrid, false, ($index ? $index : -1)) . "\">";
                    }
                } else $fname = _("error in filename");
            } else {
                if ($value) {
                    $fname.= "<img id=\"img_$attridk\" style=\"vertical-align:bottom;width:30px\" SRC=\"";
                    $fname.= $action->getImageUrl($value);
                    $fname.= "\">";
                } else {
                    
                    $fname = $action->GetIcon($oattr->getOption("defaultimage", "noimage.png") , _("no image") , 30);
                    $fname = str_replace("<img", '<img id="img_' . $attridk . '" style="vertical-align:bottom"', $fname);
                }
            }
            
            $input = $fname;
            
            $input = "<span id=\"IFERR" . $attridk . "\" class=\"Error\"></span><span class=\"FREEDOMText\">" . $fname . "</span><br/>";
            $input.= $originalname;
            // input
            $input.= "<input name=\"" . $attrin . "\" type=\"hidden\" value=\"" . $value . "\" id=\"" . $attridk . "\">";
            $input.= "<input type=\"hidden\" value=\"" . $value . "\" id=\"INIV" . $attridk . "\">";
            
            if (($visibility == "W") || ($visibility == "O")) {
                $input.= "<span><input onchange=\"document.isChanged=true;changeFile(this,'$attridk','$check')\" $classname accept=\"image/*\" size=15 type=\"file\" id=\"IF_$attridk\" name=\"_UPL" . $attrin . "\"";
                $input.= " ></span> ";
            }
            break;
            //----------------------------------------
            
        case "file":
            if (preg_match(PREGEXPFILE, $value, $reg)) {
                $check = "";
                $originalname = "";
                $dbaccess = $action->GetParam("FREEDOM_DB");
                $vf = newFreeVaultFile($dbaccess);
                if ($vf->Show($reg[2], $info) == "") {
                    $vid = $reg[2];
                    $DAV = getParam("FREEDAV_SERVEUR", false);
                    
                    if ($DAV) {
                        global $action;
                        $action->parent->AddJsRef($action->GetParam("CORE_PUBURL") . "/DAV/Layout/getsessionid.js");
                        $oc = sprintf(" onclick='this.href=getPrivateDavHref(\"%s\",\"%s\",\"%s\",this.getAttribute(\"filename\"))' filename=\"%s\"", $docid, $vid, $DAV, str_replace('"', '%22', $info->name));
                        //$oc="onclick=\"var sid=getsessionid('".$docid."','$vid');this.href='asdav://$DAV/freedav/vid-'+sid+'/'.$info->name."e";
                        $fname = "<A title=\"" . _("open file with your editor") . "\" href=\"#\" $oc><img style=\"border:none\" src=\"Images/davedit.png\">";
                    } else {
                        $fname = "<A target=\"_self\" title=\"" . _("download file") . "\" href=\"" . $action->GetParam("CORE_BASEURL") . "app=FDL&action=EXPORTFILE&vid=$vid&docid=$docid&attrid=$attrid&index=$index\">";
                    }
                    $fname.= $info->name;
                    $fname.= "</A>";
                    if ($oattr->getOption("preventfilechange") == "yes") {
                        include_once ("FDL/Lib.Vault.php");
                        $check = vault_uniqname($vid);
                        $originalname = "<input id=\"IFORI$attridk\" name=\"IFORI$attrin\" type=\"hidden\" orivalue=\"" . $doc->vault_filename($attrid, false, ($index ? $index : -1)) . "\">";
                    }
                } else $fname = _("error in filename");
            } else $fname = _("no filename");
            
            $input = "<span id=\"IFERR" . $attridk . "\" class=\"Error\"></span><span class=\"FREEDOMText\">" . $fname . "</span><br/>";
            $input.= $originalname;
            // input
            $input.= "<input name=\"" . $attrin . "\" type=\"hidden\" value=\"" . $value . "\" id=\"" . $attridk . "\">";
            $input.= "<input type=\"hidden\" value=\"" . $value . "\" id=\"INIV" . $attridk . "\">";
            $input.= "<span><input onchange=\"document.isChanged=true;changeFile(this,'$attridk','$check')\"  class=\"\" size=15 type=\"file\" id=\"IF_$attridk\" name=\"_UPL" . $attrin . "\" value=\"" . chop(htmlentities($value, ENT_COMPAT, "UTF-8")) . "\"";
            
            if (($visibility == "R") || ($visibility == "S")) $input.= $idisabled;
            $input.= " ></span> ";
            break;
            //----------------------------------------
            
        case "longtext":
        case "xml":
            $lh = $oattr->getOption("editheight", "2em");
            $elabel = $oattr->getOption("elabel");
            if ($elabel != "") $oc.= " title=\"$elabel\"";
            
            $input = "<textarea $oc wrap=\"virtual\"  onkeyup=\"textautovsize(event,this)\"  onclick=\"textautovsize(event,this)\" class=\"autoresize\" style=\"height:$lh\" name=\"" . $attrin . "\" ";
            $input.= " id=\"" . $attridk . "\" ";
            if (($visibility == "R") || ($visibility == "S")) $input.= $idisabled;
            $input.= " >" . str_replace(array(
                "[",
                "$"
            ) , array(
                "&#091;",
                "&#036;"
            ) , htmlentities((str_replace("<BR>", "\n", $value)) , ENT_COMPAT, "UTF-8")) . "</textarea>";
            
            break;
            //----------------------------------------
            
        case "htmltext":
            
            if (($visibility == "H") || ($visibility == "R")) {
                $input = "<textarea    name=\"$attrin\">$value</textarea>";
            } elseif ($visibility == "S") {
                // no input : just text
                if ($value == "") $value = '<br/>';
                $input = "<div class=\"static\" name=\"$attrin\">$value</div>";
            } else {
                $lay = new Layout("FDL/Layout/fckeditor.xml", $action);
                $lay->set("Value", str_replace(array(
                    "\n",
                    "\r",
                    "script>",
                    '&quot;',
                    '&lt;',
                    '&gt;'
                ) , array(
                    " ",
                    " ",
                    "pre>",
                    '&amp;quot;',
                    '&amp;lt;',
                    '&amp;gt;'
                ) , $value));
                $lay->set("label", ucFirst($oattr->getLabel()));
                $lay->set("need", $oattr->needed);
                $lay->set("height", $oattr->getOption("editheight", "150px"));
                $lay->set("toolbar", $oattr->getOption("toolbar", "Simple"));
                $lay->set("toolbarexpand", (strtolower($oattr->getOption("toolbarexpand")) == "no") ? "false" : "true");
                $lay->set("aid", $attridk);
                $lay->set("aname", $attrin);
                if (($visibility == "R") || ($visibility == "S")) $lay->set("disabled", $idisabled);
                else $lay->set("disabled", "");
                $input = $lay->gen();
            }
            
            break;
            //----------------------------------------
            
        case "idoc":
            
            $input.= getLayIdoc($doc, $oattr, $attridk, $attrin, $value);
            
            break;
            //----------------------------------------
            
        case "array":
            
            $lay = new Layout("FDL/Layout/editarray.xml", $action);
            $rn = $oattr->getOption("roweditzone");
            if ($rn) getZoneLayArray($lay, $doc, $oattr, $rn);
            else getLayArray($lay, $doc, $oattr);
            
            $input = $lay->gen();
            break;

        case "thesaurus":
            $multi = $oattr->getOption("multiple");
            if ($multi) {
                
                $lay = new Layout("THESAURUS/Layout/editmultiinputthconcept.xml");
                $lay->set("atitle", false);
                $lay->set("elabel", $oattr->getOption("elabel", _("Display available choices")));
                $top = array();
                if ($value) {
                    $thids = explode("<BR>", str_replace("\n", "<BR>", $value));
                    foreach ($thids as $kth => $vth) {
                        $th = new_doc($doc->dbaccess, trim($vth));
                        if ($th->isAlive()) {
                            $thtitle = $th->getLangTitle();
                            $top[] = array(
                                "ltitle" => substr($thtitle, 0, 100) ,
                                "ldocid" => $vth
                            );
                        }
                    }
                    $lay->setBlockData("options", $top);
                    $lay->set("size", count($top));
                } else $lay->set("size", 1); // may be up to zero
                $lay->set("empty", count($top) == 0);
            } else {
                $lay = new Layout("THESAURUS/Layout/editinputthconcept.xml");
                if ($value) {
                    $th = new_doc($doc->dbaccess, $value);
                    $thtitle = $th->getLangTitle();
                    $lay->set("atitle", $thtitle);
                } else $lay->set("atitle", false);
            }
            $lay->set("value", $value);
            $lay->set("aname", $attrin);
            $lay->set("aid", $attridk);
            $idth = $oattr->format;
            
            $thid = $doc->getValue($idth);
            if (!$thid) $thid = $idth; // direct logical name
            $lay->set("thesaurus", $thid);
            $notd = true; // autonome input
            $input = $lay->gen();
            break;
            //----------------------------------------
            
        case "doc":
            
            $lay = new Layout("FDL/Layout/editadoc.xml", $action);
            getLayAdoc($lay, $doc, $oattr, $value, $attrin, $index);
            
            if (($visibility == "R") || ($visibility == "S")) $lay->set("disabled", $idisabled);
            else $lay->set("disabled", "");
            $input = $lay->gen();
            break;
            //----------------------------------------
            
            
        case "docid":
            $famid = $oattr->format;
            if ($famid) {
                $needLatest = ($oattr->getOption("docrev", "latest") == "latest");
                // edit document relation
                $multi = $oattr->getOption("multiple");
                $input = "";
                $linkprefix = "ilink_";
                if ($multi == "yes") {
                    $lay = new Layout("FDL/Layout/editmdoc.xml", $action);
                    getLayMultiDoc($lay, $doc, $oattr, $value, $attrin, $index);
                    
                    $cible = "work";
                    if (($visibility == "R") || ($visibility == "S")) $lay->set("disabled", $idisabled);
                    else $lay->set("disabled", "");
                    $lay->set("cible", $cible);
                    
                    $input2 = $lay->gen();
                    $autocomplete = " autocomplete=\"off\" autoinput=\"1\" onfocus=\"activeAuto(event," . $docid . ",this,'$iopt','$attrid','$index')\" ";
                    $oc.= $autocomplete;
                    if (!$oattr->phpfile) {
                        $oattr->phpfile = "fdl.php";
                        $oattr->phpfunc = "zou";
                    }
                } else {
                    $input2 = "";
                    
                    if ($doc->usefor == "D") $input = "<input type=\"text\" title=\"" . _("real value to set") . "\" name=\"" . $attrin . "\"";
                    else $input = "<input type=\"hidden\"  name=\"" . $attrin . "\"";
                    $input.= " id=\"" . $attridk . "\" value=\"$value\">";
                    $cible = "";
                    if (!$oattr->phpfile) {
                        $oattr->phpfile = "fdl.php";
                        $oattr->phpfunc = "lfamily(D,$famid,${linkprefix}${attrid}):${cible}$attrid,${linkprefix}${attrid}";
                    } else {
                        $phpfunc = preg_replace('/([\s|,|:])CT([\s|,|\)]|$)/', '$1' . $linkprefix . $attrid . '$2', $oattr->phpfunc);
                        $phpfunc = str_replace("):$attrid,", "):${cible}${attrid},", $phpfunc);
                        $phpfunc = str_replace("):" . strtoupper($attrid) . ",", "):${cible}${attrid},", $phpfunc);
                        $oattr->phpfunc = $phpfunc;
                    }
                    if ($docid == 0) {
                        // case of specific interface
                        $iopt = '&phpfile=' . $oattr->phpfile . '&phpfunc=' . $oattr->phpfunc . '&label=' . ($oattr->getLabel());
                    }
                    $autocomplete = " autocomplete=\"off\" autoinput=\"1\" onfocus=\"activeAuto(event," . $docid . ",this,'$iopt','$attrid','$index')\" ";
                    $oc.= $autocomplete;
                    $textvalue = $doc->getTitle(trim($value) , '', $needLatest);
                }
                
                $famid = $oattr->format;
                $input.= "<input $classname $autocomplete $jsevent onchange=\"addmdocs('$attrin');document.isChanged=true\" type=\"text\" name=\"_${linkprefix}" . substr($attrin, 1) . "\"";
                if (($visibility == "R") || ($visibility == "S")) $input.= $idisabled;
                $input.= " id=\"${linkprefix}" . $attridk . "\" value=\"" . $textvalue . "\">";
                
                if (!$cible) {
                    $doc->addparamrefresh($attrid, $linkprefix . $attrid);
                } else {
                    $input = $input2 . $input;
                }
            } else {
                $input = "<input $oc $classname  type=\"text\" name=\"" . $attrin . "\" value=\"" . $value . "\"";
                $input.= " id=\"" . $attridk . "\" ";
                if (($visibility == "R") || ($visibility == "S")) $input.= $idisabled;
                $input.= " > ";
            }
            break;

        case "enum":
            if ($oattr->eformat == "") $oattr->eformat = $oattr->getOption("eformat");
            if (($oattr->repeat) && (!$oattr->inArray())) { // enumlist
                switch ($oattr->eformat) {
                    case "vcheck":
                        $lay = new Layout("FDL/Layout/editenumlistvcheck.xml", $action);
                        break;

                    case "hcheck":
                        $lay = new Layout("FDL/Layout/editenumlisthcheck.xml", $action);
                        break;

                    case "auto":
                        $lay = new Layout("FDL/Layout/editenumlistauto.xml", $action);
                        break;

                    default:
                        $lay = new Layout("FDL/Layout/editenumlist.xml", $action);
                }
            } else {
                $enuml = $oattr->getenumlabel();
                $lunset = current($enuml);
                if ($value == "") {
                    if (($oattr->eformat == 'bool') || ($oattr->getOption("eunset") != "yes")) $value = key($enuml);
                    else $value = " ";
                }
                switch ($oattr->eformat) {
                    case "vcheck":
                        $lay = new Layout("FDL/Layout/editenumvcheck.xml", $action);
                        break;

                    case "hcheck":
                        $lay = new Layout("FDL/Layout/editenumhcheck.xml", $action);
                        break;

                    case "auto":
                        $lay = new Layout("FDL/Layout/editenumauto.xml", $action);
                        $notd = true;
                        break;

                    case "bool":
                        $lay = new Layout("FDL/Layout/editenumbool.xml", $action);
                        
                        $lset = next($enuml);
                        $boolkeys = array_keys($enuml);
                        if ($value == key($enuml)) $lay->set("checkedyesno", "checked");
                        else $lay->set("checkedyesno", "");
                        $lay->set("tyesno", sprintf(_("set for %s, unset for %s") , $lset, $lunset));
                        $lay->set("val1", $boolkeys[0]);
                        $lay->set("val2", $boolkeys[1]);
                        break;

                    default:
                        $lay = new Layout("FDL/Layout/editenum.xml", $action);
                    }
                }
                
                getLayOptions($lay, $doc, $oattr, $value, $attrin, $index);
                $lay->set("msize", $oattr->getOption("mselectsize", 3));
                if (($visibility == "R") || ($visibility == "S")) $lay->set("disabled", $idisabled);
                else $lay->set("disabled", "");
                
                $lay->set("NOTD", $notd);
                $input = $lay->gen();
                break;
                //----------------------------------------
                
                
            case "color":
                $elabel = $oattr->getOption("elabel");
                if ($elabel != "") $eopt.= " title=\"$elabel\"";
                $input = "<input size=7  $eopt style=\"background-color:$value\" type=\"text\" name=\"" . $attrin . "\" value=\"" . chop(htmlentities($value, ENT_COMPAT, "UTF-8")) . "\"";
                $input.= " id=\"" . $attridk . "\" ";
                
                if (($visibility == "R") || ($visibility == "S")) $input.= $idisabled;
                else if ($doc->usefor != 'D') $input.= " disabled "; // always but default
                $input.= " class=\"color {pickerOnfocus:false,pickerClosable:true,pickerCloseText:'" . _("Close") . "',hash:true,required:false}\" ";
                
                $input.= " >&nbsp;";
                if (!(($visibility == "R") || ($visibility == "S"))) {
                    $input.= "<input id=\"ic_$attridk\" type=\"button\" value=\"&#133;\"" . " title=\"" . _("color picker") . "\" onclick=\"jscolor.init(); document.getElementById('$attridk').color.showPicker()\"" . ">";
                }
                break;
                //----------------------------------------
                
                
            case "date":
                $lay = new Layout("FDL/Layout/editdate.xml", $action);
                getLayDate($lay, $doc, $oattr, $value, $attrin, $index);
                
                $lay->set("disabled", "");
                if (($visibility == "R") || ($visibility == "S")) {
                    $lay->set("disabled", $idisabled);
                } else if ($doc->usefor != 'D') $lay->set("disabled", "disabled");
                
                if (!(($visibility == "R") || ($visibility == "S"))) {
                    $lay->setBlockData("VIEWCALSEL", array(
                        array(
                            "zou"
                        )
                    ));
                }
                if (($doc->usefor != 'D') && ($oattr->usefor != 'Q')) $lay->setBlockData("CONTROLCAL", array(
                    array(
                        "zou"
                    )
                ));
                $input = trim($lay->gen());
                break;
                //----------------------------------------
                
                
            case "timestamp":
                $lay = new Layout("FDL/Layout/edittimestamp.xml", $action);
                getLayDate($lay, $doc, $oattr, $value, $attrin, $index);
                
                $lay->set("readonly", false);
                $lay->set("disabled", "");
                if (($visibility == "R") || ($visibility == "S")) {
                    $lay->set("disabled", $idisabled);
                    $lay->set("readonly", true);
                } else if ($doc->usefor != 'D') $lay->set("disabled", "disabled");
                
                $input = $lay->gen();
                break;
                //----------------------------------------
                
                
            case "time":
                $isDisabled = "";
                if (($visibility == "R") || ($visibility == "S")) $isDisabled = $idisabled;
                list($hh, $mm, $ss) = explode(":", $value);
                $input = "<input $isDisabled size=2 maxlength=2 onchange=\"chtime('$attridk')\" type=\"text\"  value=\"" . $hh . "\" id=\"hh" . $attridk . "\">:";
                
                $input.= "<input $isDisabled size=2 maxlength=2 onchange=\"chtime('$attridk')\" type=\"text\"  value=\"" . $mm . "\"id=\"mm" . $attridk . "\">";
                
                $input.= "<input  type=\"hidden\" onchange=\"displayTime(this)\" name=\"" . $attrin . "\" id=\"" . $attridk . "\" value=\"" . $value . "\">";
                
                break;
                //----------------------------------------
                
            case "password":
                // don't see the value
                $eopt = "$classname ";
                $esize = $oattr->getOption("esize");
                if ($esize > 0) $eopt = "size=$esize";
                $input = "<input $oc $eopt type=\"password\" name=\"" . $attrin . "\" value=\"" . "\"";
                $input.= " id=\"" . $attridk . "\" ";
                
                if (($visibility == "R") || ($visibility == "S")) $input.= $idisabled;
                
                $input.= " > ";
                break;
                //----------------------------------------
                
            case "option":
                
                $lay = new Layout("FDL/Layout/editdocoption.xml", $action);
                getLayDocOption($lay, $doc, $oattr, $value, $attrin, $index);
                if (($visibility == "R") || ($visibility == "S")) $lay->set("disabled", $idisabled);
                else $lay->set("disabled", "");
                $input = $lay->gen();
                break;
                //----------------------------------------
                
            default:
                
                if (($oattr->repeat) && (!$oattr->inArray())) { // textlist
                    $input = "<textarea $oc $classname rows=2 name=\"" . $attrin . "\" ";
                    $input.= " id=\"" . $attridk . "\" ";
                    if (($visibility == "R") || ($visibility == "S")) $input.= $idisabled;
                    $input.= " >\n" . htmlentities((str_replace("<BR>", "\n", $value)) , ENT_COMPAT, "UTF-8") . "</textarea>";
                } else {
                    $hvalue = str_replace(array(
                        "[",
                        "$"
                    ) , array(
                        "&#091;",
                        "&#036;"
                    ) , chop(htmlentities(($value) , ENT_COMPAT, "UTF-8")));
                    
                    if ($oattr->eformat != "") {
                        // input help with selector
                        $lay = new Layout("FDL/Layout/edittextlist.xml", $action);
                        if (getLayTextOptions($lay, $doc, $oattr, $value, $attrin, $index)) {
                            if (($visibility == "R") || ($visibility == "S")) $lay->set("disabled", $idisabled);
                            else $lay->set("disabled", "");
                            $lay->set("adisabled", $idisabled);
                            $lay->set("oc", $jsevent);
                            
                            if ($oattr->eformat == "hlist") $lay->set("atype", "hidden");
                            else $lay->set("atype", "text");
                            $input = $lay->gen();
                            $usephpfunc = false; // disabled default input help
                            
                        } else {
                            $oattr->eformat = ""; // restore default display
                            
                        }
                    }
                    if ($oattr->eformat == "") {
                        //Common representation
                        $eopt = "$classname ";
                        $esize = $oattr->getOption("esize");
                        if ($esize > 0) $eopt = "size=$esize";
                        $elabel = $oattr->getOption("elabel");
                        if ($elabel != "") $eopt.= " title=\"$elabel\"";
                        $ecolor = $oattr->getOption("color");
                        $estyle = ""; // css style
                        if ($ecolor != "") $estyle = "color:$ecolor;";
                        $ealign = $oattr->getOption("align");
                        if ($ealign != "") $estyle.= "text-align:$ealign";
                        if ($estyle) $estyle = "style=\"$estyle\"";
                        
                        $input = "<input $oc $eopt $estyle type=\"text\" name=\"" . $attrin . "\" value=\"" . $hvalue . "\"";
                        $input.= " id=\"" . $attridk . "\" ";
                        if (($visibility == "R") || ($visibility == "S")) $input.= $idisabled;
                        $input.= " > ";
                    }
                }
                break;
            }
            
            if (($oattr->type != "array")) {
                if ($visibility != "S") {
                    if ($usephpfunc && ($oattr->phpfunc != "") && ($oattr->phpfile != "") && ($oattr->type != "enum") && ($oattr->type != "enumlist")) {
                        $phpfunc = $oattr->phpfunc;
                        
                        $linkprefixCT = "ilink_";
                        $phpfunc = preg_replace('/([\s|,|:])CT\[([^]]+)\]/e', "'\\1'.$linkprefixCT.strtolower('\\2')", $phpfunc);
                        // capture title
                        //if (isUTF8($oattr->getLabel())) $oattr->labelText=utf8_decode($oattr->getLabel());
                        $ititle = sprintf(_("choose inputs for %s") , ($oattr->getLabel()));
                        if ($oattr->getOption("ititle") != "") $ititle = str_replace("\"", "'", $oattr->getOption("ititle"));
                        
                        if ($phpfunc[0] == "[") {
                            if (preg_match('/\[(.*)\](.*)/', $phpfunc, $reg)) {
                                $phpfunc = $reg[2];
                                $ititle = addslashes($reg[1]);
                            }
                        }
                        if (!$notd) $input.= "</td><td class=\"editbutton\">";
                        if (preg_match("/list/", $attrtype, $reg)) $ctype = "multiple";
                        else $ctype = "single";
                        
                        if ($alone) $ctype.= "-alone";
                        /*$input.="<input id=\"ic2_$attridk\" type=\"button\" value=\"&#133;\"".
                        " title=\"".$ititle."\"".
                        " onclick=\"sendEnumChoice(event,".$docid.
                        ",this,'$attridk','$ctype','$iopt')\">";*/
                        if (preg_match('/[A-Z_\-0-9]+:[A-Z_\-0-9]+\(/i', $phpfunc)) {
                            $mheight = $oattr->getOption('mheight', 30);
                            $mwidth = $oattr->getOption('mwidth', 290);
                            $input.= "<input id=\"ic_${linkprefix}$attridk\" type=\"button\" value=\"Z\"" . " title=\"" . $ititle . "\"" . " onclick=\"sendSpecialChoice(event,'${linkprefix}${attridk}'," . $docid . ",'$attrid','$index','$mheight','$mwidth')\">";
                        } else {
                            $ib = "<input id=\"ic_${linkprefix}$attridk\" type=\"button\" value=\"&#133;\"" . " title=\"" . $ititle . "\"" . " onclick=\"sendAutoChoice(event," . $docid . ",this,'${linkprefix}${attridk}','$iopt','$attrid','$index')\">";
                            $input.= $ib;
                        }
                        // clear button
                        if (($oattr->type == "docid") && ($oattr->getOption("multiple") == "yes")) {
                            $ib = "<input id=\"ix_$attridk\" type=\"button\" value=\"&times;\"" . " title=\"" . _("clear selected inputs") . "\" disabled " . " onclick=\"clearDocIdInputs('$attridk','mdocid_isel_$attridk',this)\">";
                            //$input.="</td><td>";
                            $input.= $ib;
                        } elseif (preg_match('/(.*)\((.*)\)\:(.*)/', $phpfunc, $reg)) {
                            if ($alone && $oattr->type != "docid") {
                                $arg = array(
                                    $oattr->id
                                );
                            } else {
                                $argids = explode(",", $reg[3]); // output args
                                $arg = array();
                                $outsideArg = array();
                                foreach ($argids as $k => $v) {
                                    $linkprefix = "ilink_";
                                    $isILink = false;
                                    $attrId = $argids[$k];
                                    if (substr($attrId, 0, strlen($linkprefix)) == $linkprefix) {
                                        $attrId = substr($attrId, strlen($linkprefix));
                                        $isILink = true;
                                    }
                                    $docAttr = $doc->getAttribute($attrId);
                                    if (is_object($docAttr) && !$docAttr->inArray()) {
                                        $targid = trim(strtolower($attrId));
                                        if ($isILink) {
                                            $targid = $linkprefix . $targid;
                                        }
                                        $outsideArg[] = $targid;
                                    } else {
                                        $targid = strtolower(trim($attrId));
                                        if ($isILink) {
                                            $targid = $linkprefix . $targid;
                                        }
                                        if (strlen($attrId) > 1) $arg[$targid] = $targid;
                                    }
                                }
                            }
                            if (count($arg) > 0 || count($outsideArg) > 0) {
                                if (count($arg) == 0) {
                                    $jarg = "'" . implode("','", $outsideArg) . "'";
                                } else {
                                    $jarg = "'" . implode("','", $arg) . "'";
                                    $jOutsideArg = "";
                                    if (count($outsideArg) > 0) {
                                        $jOutsideArg = "'" . implode("','", $outsideArg) . "'";
                                    }
                                    if (!empty($jOutsideArg)) {
                                        $jOutsideArg = ",[$jOutsideArg]";
                                    }
                                }
                                
                                $input.= "<input id=\"ix_$attridk\" type=\"button\" value=\"&times;\"" . " title=\"" . _("clear inputs") . "\"" . " onclick=\"clearInputs([$jarg],'$index','$attridk' $jOutsideArg)\">";
                            }
                        }
                    } else if (($oattr->type == "date") || ($oattr->type == "timestamp")) {
                        $input.= "<input id=\"ix_$attridk\" type=\"button\" value=\"&times;\"" . " title=\"" . _("clear inputs") . "\"" . " onclick=\"clearInputs(['$attrid'],'$index')\">";
                        if (!$notd) $input.= "</td><td class=\"nowrap\">";
                    } else if ($oattr->type == "color") {
                        $input.= "<input id=\"ix_$attridk\" type=\"button\" value=\"&times;\"" . " title=\"" . _("clear inputs") . "\"" . " onclick=\"clearInputs(['$attrid'],'$index')\">";
                        $input.= "</td><td class=\"nowrap\">";
                    } else if ($oattr->type == "time") {
                        $input.= "<input id=\"ix_$attridk\" type=\"button\" value=\"&times;\"" . " title=\"" . _("clear inputs") . "\"" . " onclick=\"clearTime('$attridk')\">";
                        if (!$notd) $input.= "</td><td class=\"nowrap\">";
                    } else if (($oattr->type == "file") || ($oattr->type == "image")) {
                        $input.= "<input id=\"ix_$attridk\" type=\"button\" style=\"vertical-align:baseline\" value=\"&times;\"" . " title=\"" . _("clear file") . "\"" . " title1=\"" . _("clear file") . "\"" . " value1=\"&times;\"" . " title2=\"" . _("restore original file") . "\"" . " value2=\"&minus;\"" . " onclick=\"clearFile(this,'$attridk')\">";
                        if (!$notd) $input.= "</td><td class=\"nowrap\">";
                    } else {
                        if (!$notd) $input.= "</td><td class=\"nowrap\">";
                    }
                } else {
                    if (!$notd) $input.= "</td><td class=\"nowrap\">";
                }
                
                $input.= addDocidCreate($oattr, $doc, $attridk, $value, $index);
                if ($oattr->elink != "" && (!$alone)) {
                    if (substr($oattr->elink, 0, 3) == "JS:") {
                        // javascript action
                        $url = elinkEncode($doc, $attridk, substr($oattr->elink, 3) , $index, $ititle, $isymbol);
                        
                        $jsfunc = $url;
                    } else {
                        $url = elinkEncode($doc, $attridk, $oattr->elink, $index, $ititle, $isymbol);
                        
                        $target = $oattr->getOption("eltarget", $attrid);
                        
                        $jsfunc = "subwindow(300,500,'$target','$url');";
                    }
                    
                    if ($oattr->getOption("elsymbol") != "") $isymbol = $oattr->getOption("elsymbol");
                    if ($oattr->getOption("eltitle") != "") $ititle = str_replace("\"", "'", $oattr->getOption("eltitle"));
                    $input.= "<input type=\"button\" value=\"$isymbol\"" . " title=\"" . $ititle . "\"" . " onclick=\"$jsfunc;";
                    
                    $input.= "\">";
                }
                if (GetHttpVars("viewconstraint") == "Y") { // set in modcard
                    if (($oattr->phpconstraint != "") && ($index != "__1x_")) {
                        $res = $doc->verifyConstraint($oattr->id, ($index == "") ? -1 : $index);
                        if (($res["err"] == "") && (count($res["sug"]) == 0)) $color = 'mediumaquamarine';
                        if (($res["err"] == "") && (count($res["sug"]) > 0)) $color = 'orange';
                        if (($res["err"] != "")) $color = 'tomato';
                        
                        $input.= "<input style=\"background-color:$color;\"type=\"button\" class=\"constraint\" id=\"co_$attridk\" value=\"C\"" . " onclick=\"vconstraint(this," . $doc->fromid . ",'$attrid');\">";
                    }
                }
            } elseif ($oattr->type == "htmltext") {
                if (!$notd) $input.= "</td><td class=\"nowrap\">";
            }
            
            return $input;
        }
        
        function elinkEncode(&$doc, $attrik, $link, $index, &$ititle = "", &$isymbol = "")
        {
            
            $linkprefix = "ilink_";
            $ititle = _("add inputs");
            $isymbol = '+';
            
            $urllink = "";
            if ($link[0] == "[") {
                if (preg_match('/\[(.*)\|(.*)\](.*)/', $link, $reg)) {
                    $link = $reg[3];
                    $ititle = $reg[1];
                    $isymbol = $reg[2];
                }
            }
            
            for ($i = 0; $i < strlen($link); $i++) {
                switch ($link[$i]) {
                    case '%':
                        $i++; // skip end '%'
                        $sattrid = "";
                        while (($link[$i] != "%") && ($i <= strlen($link))) {
                            $sattrid.= $link[$i];
                            $i++;
                        }
                        
                        switch ($sattrid) {
                            case "B": // baseurl
                                $urllink.= GetParam("CORE_BASEURL");
                                break;

                            case "S": // standurl
                                $urllink.= GetParam("CORE_STANDURL");
                                break;

                            case "K":
                                $urllink.= $index;
                                break;

                            case "I":
                                $urllink.= $doc->id;
                                break;

                            case "F":
                                $urllink.= $doc->fromid;
                                break;

                            case "A":
                                $urllink.= $attrik;
                                break;

                            case "CT":
                                $urllink.= "'+elinkvalue('${linkprefix}${attrik}')+'";
                                break;

                            default:
                                $prop = $doc->getProperty($sattrid);
                                if ($prop !== false) {
                                    $urllink.= $prop;
                                } else {
                                    $sattrid = strtolower($sattrid);
                                    
                                    $attr = $doc->getAttribute($sattrid);
                                    if (!$attr) {
                                        global $action;
                                        $action->exitError(sprintf(_("elinkEncode::attribute not found %s in %s : %s") , $sattrid, $attrik, $link));
                                    }
                                    if ($attr->inArray()) $sattrid.= '_' . $index;
                                    //print "attr=$sattrid";
                                    $urllink.= "'+elinkvalue('$sattrid')+'";
                                }
                            }
                            break;

                        case "{":
                            $i++;
                            
                            $sattrid = "";
                            while ($link[$i] != '}') {
                                $sattrid.= $link[$i];
                                $i++;
                            }
                            //	  print "attr=$sattrid";
                            $ovalue = GetParam($sattrid, getFamIdFromName(GetParam("FREEDOM_DB") , $sattrid));
                            
                            $urllink.= $ovalue;
                            
                            break;

                        default:
                            $urllink.= $link[$i];
                    }
            }
            
            return ($urllink);
        }
        
        function getLayArray(&$lay, &$doc, &$oattr, $row = - 1)
        {
            global $action;
            
            $attrid = $oattr->id;
            $ta = $doc->attributes->getArrayElements($attrid);
            
            $height = $oattr->getOption("height", false);
            $lay->set("tableheight", $height);
            $tableStyle = $oattr->getOption("tstyle", '');
            $lay->set("tableStyle", $tableStyle);
            $lay->set("thspan", "2");
            
            $talabel = array();
            $tilabel = array();
            $tvattr = array();
            
            $max = - 1;
            $max0 = - 1;
            
            foreach ($ta as $k => $v) { // detect uncompleted rows
                $t = $doc->getTValue($k);
                $c = count($t);
                if ($c > $max) {
                    if ($max0 < 0) $max0 = $c;
                    $max = $c;
                }
            }
            
            if ($max > $max0) {
                foreach ($ta as $k => $v) { // fill uncompleted rows
                    $t = $doc->getTValue($k);
                    $c = count($t);
                    if ($c < $max) {
                        $t = array_pad($t, $max, "");
                        $doc->setValue($k, $t);
                    }
                }
            }
            // get default values
            $ddoc = createDoc($doc->dbaccess, $doc->fromid == 0 ? $doc->id : $doc->fromid, false);
            $ddoc->setDefaultValues($ddoc->getFamDoc()->getDefValues() , true, true);
            
            $tad = $ddoc->attributes->getArrayElements($attrid);
            $tval = array();
            $nbcolattr = 0; // number of column
            $autoWidthAttr = false; //is there at least one attribute displayed with width auto?
            foreach ($ta as $k => $v) {
                if ($v->mvisibility == "R") {
                    $v->mvisibility = "H"; // don't see read attribute
                    $ta[$k]->mvisibility = "H";
                }
                $visible = ($v->mvisibility != "H");
                $width = $v->getOption("cwidth", "auto");
                $talabel[] = array(
                    "alabel" => (!$visible) ? "" : $v->getLabel() ,
                    "elabel" => $v->getOption("elabel") ,
                    "astyle" => $v->getOption("cellheadstyle") ,
                    "ahclass" => (!$visible) ? "hiddenAttribute" : "visibleAttribute"
                );
                $tilabel[] = array(
                    "ilabel" => getHtmlInput($doc, $v, $ddoc->getValue($tad[$k]->id) , '__1x_') ,
                    "ihw" => (!$visible) ? "0px" : $width,
                    "bgcolor" => $v->getOption("bgcolor", "inherit") ,
                    "tdstyle" => $v->getOption("cellbodystyle") ,
                    "cellatype" => $v->type,
                    "cellattrid" => $v->id,
                    "cellmultiple" => ($v->getOption("multiple") == "yes") ? "true" : "false",
                    "ihclass" => (!$visible) ? "hiddenAttribute" : "visibleAttribute"
                );
                
                if ($visible) $nbcolattr++;
                $tval[$k] = $doc->getTValue($k);
                $nbitem = count($tval[$k]);
                
                if (($visible) && ($width == "auto")) {
                    $autoWidthAttr = true;
                }
                
                if ($nbitem == 0) {
                    // add first range
                    if ($oattr->format != "empty" && $oattr->getOption("empty") != "yes") {
                        $tval[$k] = array(
                            0 => ""
                        );
                        $nbitem = 1;
                    }
                }
                //Dead code?
                //("bvalue_" or "ivalue" could not be found in editarray.xml neither in this function.
                // and $tivalue is redefined as empty array in code below.
                /*
                $tivalue=array();
                for ($i=0;$i<$nbitem;$i++) {
                $tivalue[]=array("ivalue"=>$tval[$k][$i]);
                }
                $lay->setBlockData("bvalue_$k",$tivalue);
                */
            }
            //No more required (works even in IE6 which is no more supported)
            /*
            if ($action->read("navigator") == "EXPLORER") {
            // compute col width explicitly
            if ($nbcolattr> 0) {
            $aw=sprintf("%d%%",100/$nbcolattr);
            
            foreach ($talabel as $ka => $va) {
            if ($va["ahw"]=="auto") {
            $talabel[$ka]["ahw"]=$aw;
            $tilabel[$ka]["ihw"]=$aw;
            }
            }
            }
            }
            */
            $pindex = '';
            if (($row >= 0) && ($oattr->mvisibility == "W" || $oattr->mvisibility == "O" || $oattr->mvisibility == "U")) {
                $oattr->mvisibility = "U";
                $pindex = 's';
            }
            //Compute table width with some compatibility rules
            $tableWidth = $oattr->getOption("twidth", '100%'); //compatibility
            //but if all columns are fixed, you probably want an 'auto' layout...
            if ((!$autoWidthAttr) && ($tableWidth != 'auto')) {
                //TODO: should write something in the log
                $tableWidth = 'auto';
            }
            $lay->set("tableWidth", $tableWidth);
            
            $lay->setBlockData("TATTR", $talabel);
            $lay->setBlockData("IATTR", $tilabel);
            $lay->set("attrid", $attrid);
            if (($oattr->getOption("vlabel") == "") || ($oattr->getOption("vlabel") == "up")) $lay->set("caption", $oattr->getLabel());
            else $lay->set("caption", "");
            $lay->set("footspan", count($ta) * 2);
            
            reset($tval);
            $nbitem = count(current($tval));
            
            $tvattr = array();
            for ($k = 0; $k < $nbitem; $k++) {
                if (($row >= 0) && ($k != $row)) continue;
                $tvattr[] = array(
                    "bevalue" => "bevalue_$k",
                    "index" => $k
                );
                reset($ta);
                $tivalue = array();
                $ika = 0;
                foreach ($ta as $ka => $va) {
                    
                    $visible = ($va->mvisibility != "H");
                    
                    $tivalue[] = array(
                        "eivalue" => getHtmlInput($doc, $va, $tval[$ka][$k], $pindex . $k) ,
                        "bgcolor" => $va->getOption("bgcolor", "inherit") ,
                        "cellatype" => $va->type,
                        "cellattrid" => $va->id,
                        "cellmultiple" => ($va->getOption("multiple") == "yes") ? "true" : "false",
                        "tdstyle" => $va->getOption("cellbodystyle") ,
                        "vhw" => (!$visible) ? "0px" : $va->getOption("cwidth", "auto") ,
                        "eiclass" => (!$visible) ? "hiddenAttribute" : "visibleAttribute"
                    );
                    $ika++;
                }
                $lay->setBlockData("bevalue_$k", $tivalue);
            }
            $lay->set("addfunc", false);
            if (($oattr->phpfunc != "") && ($oattr->phpfile != "")) {
                if (preg_match('/[A-Z_\-0-9]+:[A-Z_\-0-9]+\(/i', $oattr->phpfunc)) {
                    $mheight = $oattr->getOption('mheight', 30);
                    $mwidth = $oattr->getOption('mwidth', 290);
                    $lay->set("addtitle", $oattr->getOption("ltitle", _("Modify table")));
                    $lay->set("addsymbol", $oattr->getOption("lsymbol"));
                    $lay->set("addfunc", "sendSpecialChoice(event,'{$oattr->id}'," . ($doc->id ? $doc->id : $doc->fromid) . ",'" . $oattr->id . "','" . $row . "','" . $mheight . "','" . $mwidth . "')");
                }
            }
            
            $lay->set("useadd", ($oattr->getOption("userowadd") != "no"));
            $lay->set("readonly", ($oattr->mvisibility == 'U'));
            if (count($tvattr) > 0) $lay->setBlockData("EATTR", $tvattr);
        }
        
        function getZoneLayArray(&$lay, &$doc, &$oattr, $zone)
        {
            global $action;
            
            $height = $oattr->getOption("height", false);
            $lay->set("tableheight", $height);
            $lay->set("readonly", ($oattr->mvisibility == 'U'));
            $lay->set("thspan", "1");
            
            if (($zone != "") && preg_match("/([A-Z_-]+):([^:]+):{0,1}[A-Z]{0,1}/", $zone, $reg)) {
                $attrid = $oattr->id;
                $ta = $doc->attributes->getArrayElements($attrid);
                
                $dxml = new DomDocument();
                $rowlayfile = getLayoutFile($reg[1], ($reg[2]));
                if (!@$dxml->load($rowlayfile)) {
                    AddwarningMsg(sprintf(_("cannot open %s layout file") , DEFAULT_PUBDIR . "/$rowlayfile"));
                    return;
                }
                $theads = $dxml->getElementsByTagName('table-head');
                if ($theads->length > 0) {
                    $thead = $theads->item(0);
                    $theadcells = $thead->getElementsByTagName('cell');
                    $talabel = array();
                    for ($i = 0; $i < $theadcells->length; $i++) {
                        $th = xt_innerXML($theadcells->item($i));
                        $thstyle = $theadcells->item($i)->getAttribute("style");
                        
                        $talabel[] = array(
                            "alabel" => $th,
                            "ahw" => "auto",
                            "astyle" => $thstyle,
                            "ahclass" => "",
                            "ahvis" => "visible"
                        );
                    }
                    $lay->setBlockData("TATTR", $talabel);
                }
                
                $tbodies = $dxml->getElementsByTagName('table-body');
                if ($tbodies->length > 0) {
                    $tbody = $tbodies->item(0);
                    $tbodycells = $tbody->getElementsByTagName('cell');
                    for ($i = 0; $i < $tbodycells->length; $i++) {
                        $tr[] = xt_innerXML($tbodycells->item($i));
                        $tcellstyle[] = $tbodycells->item($i)->getAttribute("style");
                    }
                }
                
                $nbitem = 0;
                
                foreach ($ta as $k => $v) {
                    $tval[$k] = $doc->getTValue($k);
                    $nbitem = max($nbitem, count($tval[$k]));
                    $lay->set("L_" . strtoupper($v->id) , ucfirst($v->getLabel()));
                }
                
                $lay->set("attrid", $attrid);
                $lay->set("caption", $oattr->getLabel());
                $lay->set("footspan", count($ta) * 2);
                $lay->set("eiclass", '');
                $lay->set("tableWidth", $oattr->getOption("twidth", '100%'));
                $lay->set("tableStyle", $oattr->getOption("tstyle", ''));
                // get default values
                $fdoc = $doc->getFamDoc();
                $defval = $fdoc->getDefValues();
                
                $tvattr = array();
                for ($k = 0; $k < $nbitem; $k++) {
                    $tvattr[] = array(
                        "bevalue" => "bevalue_$k"
                    );
                    $tivalue = array();
                    
                    foreach ($tr as $kd => $td) {
                        $val = preg_replace('/\[([^\]]*)\]/e', "rowattrReplace(\$doc,'\\1',$k)", $td);
                        $tivalue[] = array(
                            "eivalue" => $val,
                            "ehvis" => "visible",
                            "tdstyle" => $tcellstyle[$kd],
                            "bgcolor" => "inherit",
                            "vhw" => "auto"
                        );
                    }
                    $lay->setBlockData("bevalue_$k", $tivalue);
                }
                
                foreach ($tr as $kd => $td) {
                    $dval = preg_replace('/\[([^\]]*)\]/e', "rowattrReplace(\$doc,'\\1','__1x_',\$defval)", $td);
                    $tilabel[] = array(
                        "ilabel" => $dval,
                        "ihw" => "auto",
                        "tdstyle" => $tcellstyle[$kd],
                        "bgcolor" => "inherit",
                        "ihvis" => "visible"
                    );
                }
                $lay->set("addfunc", false);
                if (($oattr->phpfunc != "") && ($oattr->phpfile != "")) {
                    if (preg_match('/[A-Z_\-0-9]+:[A-Z_\-0-9]+\(/i', $oattr->phpfunc)) {
                        $row = '';
                        $mheight = $oattr->getOption('mheight', 30);
                        $mwidth = $oattr->getOption('mwidth', 290);
                        $docid = $doc->id ? $doc->id : $doc->fromid;
                        $lay->set("addtitle", $oattr->getOption("ltitle", _("Modify table")));
                        $lay->set("addsymbol", $oattr->getOption("lsymbol"));
                        $lay->set("addfunc", "sendSpecialChoice(event,'{$oattr->id}',$docid,'{$oattr->id}','$row','$mheight','$mwidth')");
                    }
                }
                $lay->setBlockData("IATTR", $tilabel);
                $lay->set("readonly", ($oattr->mvisibility == 'U'));
                $lay->set("useadd", ($oattr->getOption("userowadd") != "no"));
                if (count($tvattr) > 0) $lay->setBlockData("EATTR", $tvattr);
            }
        }
        
        function rowattrReplace(&$doc, $s, $index, &$defval = null)
        {
            if (substr($s, 0, 2) == "L_") return "[$s]";
            if (substr($s, 0, 2) == "V_") {
                $s = substr($s, 2);
                if ($index != - 1) $value = $doc->getTValue($s, "", $index);
                else $value = $defval[strtolower($s) ];
                $oattr = $doc->getAttribute($s);
                if (!$oattr) return sprintf(_("unknow attribute %s") , $s);
                $v = getHtmlInput($doc, $oattr, $value, $index, "", true);
            } else {
                $sl = strtolower($s);
                if (!isset($doc->$sl)) return "[$s]";
                if ($index == - 1) $v = $doc->getValue($sl);
                else $v = $doc->getTValue($sl, "", $index);
            }
            return $v;
        }
        /**
         * generate HTML for inline document (not virtual)
         *
         * @param Layout $lay template of html input
         * @param Doc $doc current document in edition
         * @param DocAttribute $oattr current attribute for input
         * @param string $value value of the attribute to display (generaly the value comes from current document)
         * @param string $aname input HTML name (generaly it is '_'+$oattr->id)
         * @param int $index current row number if it is in array ("" if it is not in array)
         */
        function getLayAdoc(&$lay, &$doc, &$oattr, $value, $aname, $index)
        {
            $idocid = $oattr->format . $index;
            $lay->set("name", $aname);
            $lay->set("id", $oattr->id . $index);
            $lay->set("idocid", strtolower($idocid));
            $lay->set("value", $value);
        }
        /**
         * generate HTML for multiple docid
         *
         * @param Layout $lay template of html input
         * @param Doc $doc current document in edition
         * @param DocAttribute $oattr current attribute for input
         * @param string $value value of the attribute to display (generaly the value comes from current document)
         * @param string $aname input HTML name (generaly it is '_'+$oattr->id)
         * @param int $index current row number if it is in array ("" if it is not in array)
         */
        function getLayMultiDoc(&$lay, &$doc, &$oattr, $value, $aname, $index)
        {
            if ($index !== "") $idocid = $oattr->id . '_' . $index;
            else $idocid = $oattr->id;
            $needLatest = ($oattr->getOption("docrev", "latest") == "latest");
            
            $lay->set("name", $aname);
            $lay->set("aid", $idocid);
            $lay->set("value", $value);
            $lay->set("docid", ($doc->id == 0) ? $doc->fromid : $doc->id);
            $value = str_replace("\n", "<BR>", $value);
            $topt = array();
            $lay->set("size", 1);
            if ($value != "") {
                $tval = explode("<BR>", $value);
                foreach ($tval as $k => $v) {
                    $topt[] = array(
                        "ltitle" => $doc->getTitle($v, '', $needLatest) ,
                        "ldocid" => $v
                    );
                }
                $lay->set("size", min(count($topt) , 6));
            }
            $lay->setBlockData("options", $topt);
        }
        /**
         * generate HTML for date attribute
         *
         * @param Layout $lay template of html input
         * @param Doc $doc current document in edition
         * @param DocAttribute $oattr current attribute for input
         * @param string $value value of the attribute to display (generaly the value comes from current document)
         * @param string $aname input HTML name (generaly it is '_'+$oattr->id)
         * @param int $index current row number if it is in array ("" if it is not in array)
         */
        function getLayDate(&$lay, &$doc, &$oattr, $value, $aname, $index)
        {
            if ($index !== "") $idocid = $oattr->format . '_' . $index;
            else $idocid = $oattr->format;
            $lay->set("name", $aname);
            
            $localeconfig = getLocaleConfig();
            if ($localeconfig != false) {
                $lay->set("dateformat", $localeconfig['dateFormat']);
                $lay->set("datetimeformat", $localeconfig['dateTimeFormat']);
                $value = FrenchDateToLocaleDate($value);
            } else {
                $lay->set("dateformat", '');
                $lay->set("datetimeformat", '');
            }
            
            if ($index !== "") $lay->set("id", $oattr->id . '_' . $index);
            else $lay->set("id", $oattr->id);
            $lay->set("idocid", strtolower($idocid));
            $lay->set("value", $value);
        }
        /**
         * generate HTML for enum attributes
         *
         * @param Layout $lay template of html input
         * @param Doc $doc current document in edition
         * @param DocAttribute $oattr current attribute for input
         * @param string $value value of the attribute to display (generaly the value comes from current document)
         * @param string $aname input HTML name (generaly it is '_'+$oattr->id)
         * @param int $index current row number if it is in array ("" if it is not in array)
         */
        function getLayOptions(&$lay, &$doc, &$oattr, $value, $aname, $index)
        {
            if ($index !== "") $idocid = $oattr->format . '_' . $index;
            else $idocid = $oattr->format;
            $lay->set("name", $aname);
            if ($index !== "") $idx = $oattr->id . '_' . $index;
            else $idx = $oattr->id;
            
            $lay->set("id", $idx);
            $lay->set("idi", $oattr->id);
            $etype = $oattr->getOption("etype");
            $eformat = $oattr->getOption("eformat");
            $multiple = $oattr->getOption("multiple");
            if (($eformat == "auto") && ($multiple != "yes")) $doc->addParamRefresh($oattr->id, "li_" . $oattr->id);
            
            $lay->set("isopen", ($etype == "open"));
            $lay->set("isfreeselected", false);
            $lay->set("isfree", ($etype == "free"));
            $tvalue = $doc->_val2array($value);
            
            $lay->set("lvalue", $value);
            $enuml = $oattr->getenumlabel();
            
            $enumk = array_keys($enuml);
            if (($etype == "free") && ($eformat != "auto")) {
                $enuml['...'] = _("Other...");
            }
            
            if (($eformat == "") && ($value == " ") && ($oattr->getOption("eunset") == "yes")) {
                $enuml[' '] = _("Do choice");
            }
            
            $ki = 0;
            $noselect = true;
            $topt = array();
            
            foreach ($enuml as $k => $v) {
                if (in_array($k, $tvalue)) {
                    $topt[$k]["selected"] = "selected";
                    $topt[$k]["checked"] = "checked";
                    $lay->set("lvalue", $v);
                    $noselect = false;
                } else {
                    if ($eformat != "auto") {
                        $topt[$k]["selected"] = "";
                        $topt[$k]["checked"] = "nochecked";
                    }
                }
                if (($eformat != "auto") || ($topt[$k]["selected"] == "selected")) {
                    if ($k == "...") $topt[$k]["optid"] = $idx . '___';
                    else $topt[$k]["optid"] = $idx . '_' . $ki;
                    $topt[$k]["fvalue"] = $v;
                    $topt[$k]["kvalue"] = $k;
                    $topt[$k]["ki"] = $ki;
                    $topt[$k]["other"] = false;
                }
                $ki++;
            }
            if (($eformat == "auto") && ($multiple == "yes")) $lay->set("isopen", false); // set by typing
            if ($noselect && ($etype == "free")) {
                if ((trim($value) != "") && ($value != "\t")) {
                    if ($eformat != "auto") {
                        $topt['...']["fvalue"] = "";
                        $topt['...']["kvalue"] = "";
                        $topt['...']["selected"] = "selected";
                        $topt['...']["checked"] = "checked";
                    }
                    $lay->set("isfreeselected", true);
                    $lay->set("lvalue", $lay->get("lvalue") . ' ' . _("(Other)"));
                    if (!$eformat) {
                        if ($multiple != "yes") {
                            $topt['.sel.'] = $topt['...'];
                            $topt['.sel.']["fvalue"] = $value . ' ' . _("(Other input)");
                            $topt['.sel.']["kvalue"] = $value;
                        }
                        $topt['...']["selected"] = "";
                        $topt['...']["checked"] = "";
                        $lay->set("isfreeselected", false);
                    }
                }
                //    $lay->set("isfree",true);
                
            }
            
            if ($multiple && ($etype == "free")) {
                $lay->set("freevalue", "");
                if ($eformat != "auto") $topt['...']["other"] = true;
                foreach ($tvalue as $kv) {
                    if (trim($kv) && (!in_array($kv, $enumk))) {
                        if (($eformat == "auto") || ($eformat == "")) {
                            $topt[$kv]["fvalue"] = $kv . ' ' . _("(Other)");
                            $topt[$kv]["kvalue"] = $kv;
                            $topt[$kv]["ki"] = $ki++;
                            $topt[$kv]["selected"] = "selected";
                        } else {
                            if ($eformat) {
                                $topt['...']["selected"] = "selected";
                                $topt['...']["checked"] = "checked";
                                $lay->set("isfreeselected", true);
                                $lay->set("freevalue", $kv);
                            }
                        }
                    }
                }
            }
            $lay->setBlockData("OPTIONS", $topt);
            $lay->set("value", $value);
            $lay->set("docid", ($doc->id == 0) ? $doc->fromid : $doc->id);
            $lay->set("index", $index);
        }
        /**
         * generate HTML for option attributes
         *
         * @param Layout $lay template of html input
         * @param Doc $doc current document in edition
         * @param DocAttribute $oattr current attribute for input
         * @param string $value value of the attribute to display (generaly the value comes from current document)
         * @param string $aname input HTML name (generaly it is '_'+$oattr->id)
         * @param int $index current row number if it is in array ("" if it is not in array)
         */
        function getLayDocOption(&$lay, &$doc, &$oattr, $value, $aname, $index)
        {
            if ($index !== "") $idocid = $oattr->format . '_' . $index;
            else $idocid = $oattr->format;
            $lay->set("name", $aname);
            $idx = $oattr->id . $index;
            $lay->set("id", $idx);
            $lay->set("didx", $index);
            $lay->set("di", trim(strtolower($oattr->format)));
            if ($index !== "") $lay->set("said", $doc->getTValue($oattr->format, "", $index));
            else $lay->set("said", $doc->getValue($oattr->format));
            
            $lay->set("value", $value);
            $lay->set("uuvalue", urlencode($value));
        }
        /**
         * generate HTML for text attributes with help function
         *
         * @param Layout $lay template of html input
         * @param Doc $doc current document in edition
         * @param DocAttribute $oattr current attribute for input
         * @param string $value value of the attribute to display (generaly the value comes from current document)
         * @param string $aname input HTML name (generaly it is '_'+$oattr->id)
         * @param int $index current row number if it is in array ("" if it is not in array)
         */
        function getLayTextOptions(&$lay, &$doc, &$oattr, $value, $aname, $index)
        {
            include_once ("FDL/enum_choice.php");
            if ($index !== "") $idocid = $oattr->format . '_' . $index;
            else $idocid = $oattr->format;
            $lay->set("name", $aname);
            $idx = $oattr->id . $index;
            $lay->set("id", $idx);
            
            $res = getResPhpFunc($doc, $oattr, $rargids, $tselect, $tval, false);
            
            if ($res === false) return false; // one or more attribute are not set
            $sattrid = "[";
            if (is_array($rargids)) $sattrid.= strtolower("'" . implode("','", $rargids) . "'");
            $sattrid.= "]";
            $lay->Set("attrid", $sattrid);
            
            if (is_array($tselect)) {
                foreach ($tselect as $k => $v) {
                    if ($v["choice"] == $value) $tselect[$k]["selected"] = "selected";
                    else $tselect[$k]["selected"] = "";
                }
                $lay->SetBlockData("SELECTENUM", $tselect);
            }
            
            $lay->SetBlockData("ATTRVAL", $tval);
            
            $lay->set("value", $value);
            return true;
        }
        /**
         * generate HTML for idoc attribute
         *
         * @param DocAttribute $oattr current attribute for input
         * @param string $value value of the attribute to display (generaly the value comes from current document)
         * @return String the formated output
         */
        function getLayIdoc(&$doc, &$oattr, $attridk, $attrin, $value, $zone = "")
        {
            
            $idocfamid = $oattr->format;
            if ($value != "") {
                $temp = base64_decode($value);
                $entete = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\" standalone=\"yes\" ?>";
                $xml = $entete;
                $xml.= $temp;
                $title = recup_argument_from_xml($xml, "title"); //in freedom_util.php
                
            } else {
                $famname = $doc->getTitle($idocfamid);
                $title = sprintf(_("create new %s") , $famname);
            }
            $input = "<INPUT id=\"_" . $attridk . "\" TYPE=\"hidden\"  name=$attrin value=\"" . $value . " \"><a id='iti_$attridk' " . " oncontextmenu=\"viewidoc_in_popdoc(event,'$attridk','_$attridk','$idocfamid')\"" . " onclick=\"editidoc('_$attridk','_$attridk','$idocfamid','$zone');\">$title</a> ";
            return $input;
        }
        /**
         * add different js files needed in edition mode
         */
        function editmode(&$action)
        {
            $action->parent->AddJsRef(sprintf("%sapp=FDL&action=ALLEDITJS&wv=%s", $action->GetParam("CORE_SSTANDURL") , $action->GetParam("WVERSION")));
            $action->parent->AddCssRef(sprintf("%sapp=FDL&action=ALLEDITCSS&wv=%s", $action->GetParam("CORE_SSTANDURL") , $action->GetParam("WVERSION")));
        }
        /**
         * add button to create/modify document relation
         *
         * @param DocAttribute $oattr
         */
        function addDocIdCreate(BasicAttribute & $oattr, Doc & $doc, $attridk, $value, $index)
        {
            if ($oattr->type == "docid") {
                $creation = $oattr->getOption("creation");
                if ($creation && ($creation != "no")) {
                    
                    $reldoc = new_doc($doc->dbaccess, $oattr->format);
                    if ($reldoc->control('icreate') != "") return '';
                    
                    $urlcreate = '';
                    if ($creation != "yes") {
                        $create = str_replace('"', '&quote;', $creation);
                        $create = str_replace(array(
                            '{',
                            '}',
                            ':',
                            ','
                        ) , array(
                            '{"',
                            '"}',
                            '":"',
                            '","'
                        ) , $create);
                        
                        $jscreate = json_decode($create);
                        if ($jscreate === null) {
                            addWarningMsg(sprintf("creation option syntax error:%s [%s] ", $oattr->id, $creation));
                        } else {
                            foreach ($jscreate as $k => $v) {
                                $kl = trim(strtolower($k));
                                $v = str_replace('&quote;', '"', $v);
                                
                                if ($v[0] == '"') {
                                    $urlcreate.= sprintf("&%s=%s", $kl, urlencode(trim($v, '"')));
                                } else {
                                    $urlcreate.= sprintf("&%s=%s", $kl, elinkencode($doc, $attridk, "%$v%", $index));
                                }
                            }
                        }
                    }
                    $esymbol = '&nbsp;';
                    if (!$attridk) $attridk = $oattr->id;
                    $ectitle = sprintf(_("create a %s document") , $reldoc->getTitle());
                    
                    $emtitle = sprintf(_("modify document"));
                    
                    $jsfunc = sprintf("editRelation('%s',elinkvalue('%s'),'%s','%s')", $oattr->format, $attridk, $attridk, ($urlcreate));
                    $input = sprintf("<input id=\"icr_%s\" class=\"%s\" type=\"button\" value=\"%s\" titleedit=\"%s\" titleview=\"%s\" onclick=\"%s\">", $attridk, "add-doc", $esymbol, $ectitle, $emtitle, $jsfunc);
                    return $input;
                }
            }
            return '';
        }
?>
