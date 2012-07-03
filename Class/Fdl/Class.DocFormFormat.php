<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Get Html form inputs for documents
 * @class DocFormFormat
 *
 */
class DocFormFormat
{
    /**
     * @var Doc
     */
    public $doc = null;
    private $index = - 1;
    /**
     * @var NormalAttribute
     */
    private $oattr = null;
    /**
     * @var bool use td tag has button separator
     */
    private $notd = false;
    /**
     * @var string current attribute id
     */
    private $attrid;
    /**
     * @var string current attribute HTML name (ie with _ prefix)
     */
    private $attrin;
    /**
     * @var string current attribute HTML id with index
     */
    private $attridk;
    /**
     * @var string current attribute visibility
     */
    private $visibility;
    /**
     * @var boolean the attribute is in the non visible duplicable line
     *
     */
    private $isInDuplicableTableLine = false;
    private $classname;
    /**
     * @var int current document id
     */
    private $docid;
    private $onChange;
    private $linkPrefix;
    /**
     * @var string mode option for  specific interface
     */
    private $iOptions;
    /**
     * @var string js disabled attribute
     */
    private $idisabled;
    /**
     * @var string add an javascript callback on input (like onblur or onmouseover)
     */
    private $jsEvents = '';
    
    public function __construct(Doc & $doc)
    {
        $this->setDoc($doc);
    }
    
    public function setDoc(Doc & $doc)
    {
        $this->doc = $doc;
    }
    /**
     * add TD HTML tag between input and button
     * @param bool $usetd
     */
    public function useTd($usetd)
    {
        $this->notd = (!$usetd);
    }
    /**
     * add some js handler un input like onmouseover="alert('foo')"
     * @param string $jsEvent
     */
    public function setJsEvents($jsEvent)
    {
        $this->jsEvents = $jsEvent;
    }
    /**
     * Compose html code to insert input
     * @param NormalAttribute &$oattr attribute to edit
     * @param string $value value of the attribute
     * @param string $index in case of array : row of the array
     * @return mixed|string
     */
    function getHtmlInput(&$oattr, $value, $index = "")
    {
        global $action;
        $this->oattr = $oattr;
        $this->index = $index;
        $docid = intval($this->doc->id);
        if ($docid == 0) $docid = intval($this->doc->fromid);
        $attrtype = $this->oattr->type;
        $usephpfunc = true;
        $alone = $this->oattr->isAlone; // set by method caller in special case to display alone
        $this->linkPrefix = "";
        $attrid = $this->oattr->id;
        $attrin = '_' . $this->oattr->id; // for js name => for return values from client
        if ($this->index !== "") $attridk = $this->oattr->id . '_' . $this->index;
        else $attridk = $this->oattr->id . $this->index;
        if ($this->oattr->inArray()) {
            if ($this->index == - 1) {
                $attrin.= '[-1]';
                $attridk = $this->oattr->id . '__1x_';
                $this->isInDuplicableTableLine = true;
            } else $attrin.= "[{$this->index}]";
        }
        if (isset($this->oattr->mvisibility)) $this->visibility = $this->oattr->mvisibility;
        else $this->visibility = $this->oattr->visibility;
        if ($this->visibility == "I") return ""; // not editable attribute
        $this->idisabled = " disabled readonly=1 title=\"" . _("read only") . "\" ";
        $input = "";
        
        if (!$this->notd) $classname = "class=\"fullresize\"";
        else $classname = "";
        if ($this->isInDuplicableTableLine == false) {
            $this->isInDuplicableTableLine = ($this->index == '__1x_');
        }
        $this->attrid = $this->oattr->id;
        $this->docid = $docid;
        $this->attrin = $attrin;
        $this->attridk = $attridk;
        $this->classname = $classname;
        if (($this->visibility == "H") || ($this->visibility == "R")) {
            $input = "<input  type=\"hidden\" name=\"" . $attrin . "\" value=\"" . chop(htmlentities(($value) , ENT_COMPAT, "UTF-8")) . "\"";
            $input.= " id=\"" . $attridk . "\" ";
            $input.= " > ";
            if (!$this->notd) $input.= '</td><td class="hiddenAttribute">';
            return $input;
        }
        
        $this->onChange = $this->jsEvents . " onchange=\"document.isChanged=true\" "; // use in "pleaseSave" js function
        if ($docid == 0) {
            // case of specific interface
            $this->iOptions = str_replace('\"', '&quot;', '&phpfile=' . $this->oattr->phpfile . '&phpfunc=' . $this->oattr->phpfunc . '&label=' . ($this->oattr->getLabel()));
        } else $this->iOptions = "";
        if (($this->oattr->type != "array") && ($this->oattr->type != "htmltext") && ($this->oattr->type != "docid")) {
            if ($this->visibility != "S") {
                if ($usephpfunc && ($this->oattr->phpfunc != "") && ($this->oattr->phpfile != "") && ($this->oattr->type != "enum") && ($this->oattr->type != "enumlist")) {
                    if ($this->oattr->getOption("autosuggest", "yes") != "no") {
                        $autocomplete = " autocomplete=\"off\" autoinput=\"1\" onfocus=\"activeAuto(event," . $docid . ",this,'{$this->iOptions}','$attrid','{$this->index}')\" ";
                        $this->onChange.= $autocomplete;
                    }
                }
            }
        }
        // output change with type
        switch ($attrtype) {
                //----------------------------------------
                
            case "image":
                $input = $this->formatImage($value);
                break;

            case "file":
                $input = $this->formatFile($value);
                break;

            case "longtext":
            case "xml":
                $input = $this->formatLongText($value);
                break;

            case "htmltext":
                $input = $this->formatHtmlText($value);
                break;

            case "idoc":
                $input.= $this->getLayIdoc($this->doc, $this->oattr, $attridk, $attrin, $value);
                break;

            case "array":
                $input = $this->formatArray($value);
                break;

            case "thesaurus":
                $input = $this->formatThesaurus($value);
                break;

            case "doc":
                $input = $this->formatDoc($value);
                break;

            case "docid":
                $input = $this->formatDocid($value);
                break;

            case "account":
                $input = $this->formatAccount($value);
                break;

            case "enum":
                $input = $this->formatEnum($value);
                break;

            case "color":
                $input = $this->formatColor($value);
                break;

            case "date":
                $input = $this->formatDate($value);
                break;

            case "timestamp":
                $input = $this->formatTimestamp($value);
                break;

            case "time":
                $input = $this->formatTime($value);
                break;

            case "password":
                $input = $this->formatPassword($value);
                break;

            case "option":
                $input = $this->formatOption($value);
                break;

            default:
                
                if (($this->oattr->repeat) && (!$this->oattr->inArray())) { // textlist
                    $input = "<textarea {$this->onChange} $classname rows=2 name=\"" . $attrin . "\" ";
                    $input.= " id=\"" . $attridk . "\" ";
                    if (($this->visibility == "R") || ($this->visibility == "S")) $input.= $this->idisabled;
                    $input.= " >\n" . htmlentities((str_replace("<BR>", "\n", $value)) , ENT_COMPAT, "UTF-8") . "</textarea>";
                } else {
                    $hvalue = str_replace(array(
                        "[",
                        "$"
                    ) , array(
                        "&#091;",
                        "&#036;"
                    ) , chop(htmlentities(($value) , ENT_COMPAT, "UTF-8")));
                    
                    if ($this->oattr->eformat != "") {
                        // input help with selector
                        $lay = new Layout("FDL/Layout/edittextlist.xml", $action);
                        if ($this->getLayTextOptions($lay, $this->doc, $this->oattr, $value, $attrin, $this->index)) {
                            if (($this->visibility == "R") || ($this->visibility == "S")) $lay->set("disabled", $this->idisabled);
                            else $lay->set("disabled", "");
                            $lay->set("adisabled", $this->idisabled);
                            $lay->set("oc", $this->jsEvents);
                            
                            if ($this->oattr->eformat == "hlist") $lay->set("atype", "hidden");
                            else $lay->set("atype", "text");
                            $input = $lay->gen();
                            $usephpfunc = false; // disabled default input help
                            
                        } else {
                            $this->oattr->eformat = ""; // restore default display
                            
                        }
                    }
                    if ($this->oattr->eformat == "") {
                        //Common representation
                        $eopt = "$classname ";
                        $esize = $this->oattr->getOption("esize");
                        if ($esize > 0) $eopt = "size=$esize";
                        $elabel = $this->oattr->getOption("elabel");
                        if ($elabel != "") $eopt.= " title=\"$elabel\"";
                        $ecolor = $this->oattr->getOption("color");
                        $estyle = ""; // css style
                        if ($ecolor != "") $estyle = "color:$ecolor;";
                        $ealign = $this->oattr->getOption("align");
                        if ($ealign != "") $estyle.= "text-align:$ealign";
                        if ($estyle) $estyle = "style=\"$estyle\"";
                        
                        $input = "<input {$this->onChange} $eopt $estyle type=\"text\" name=\"" . $attrin . "\" value=\"" . $hvalue . "\"";
                        $input.= " id=\"" . $attridk . "\" ";
                        if (($this->visibility == "R") || ($this->visibility == "S")) $input.= $this->idisabled;
                        $input.= " > ";
                    }
                }
                break;
            }
            
            if (($this->oattr->type != "array")) {
                if ($this->visibility != "S") {
                    if ($usephpfunc && ($this->oattr->phpfunc != "") && ($this->oattr->phpfile != "") && ($this->oattr->type != "enum") && ($this->oattr->type != "enumlist")) {
                        $phpfunc = $this->oattr->phpfunc;
                        
                        $linkPrefixCT = "ilink_";
                        $phpfunc = preg_replace('/([\s|,|:|\(])CT\[([^]]+)\]/e', "'\\1'.$linkPrefixCT.strtolower('\\2')", $phpfunc);
                        // capture title
                        //if (isUTF8($oattr->getLabel())) $oattr->labelText=utf8_decode($oattr->getLabel());
                        $ititle = sprintf(_("choose inputs for %s") , ($this->oattr->getLabel()));
                        if ($this->oattr->getOption("ititle") != "") $ititle = str_replace("\"", "'", $this->oattr->getOption("ititle"));
                        
                        if ($phpfunc[0] == "[") {
                            if (preg_match('/\[(.*)\](.*)/', $phpfunc, $reg)) {
                                $phpfunc = $reg[2];
                                $ititle = addslashes($reg[1]);
                            }
                        }
                        if (!$this->notd) $input.= "</td><td class=\"editbutton\">";
                        if (preg_match("/list/", $attrtype, $reg)) $ctype = "multiple";
                        else $ctype = "single";
                        
                        if ($alone) $ctype.= "-alone";
                        /*$input.="<input id=\"ic2_$attridk\" type=\"button\" value=\"&#133;\"".
                        " title=\"".$ititle."\"".
                        " onclick=\"sendEnumChoice(event,".$docid.
                        ",this,'$attridk','$ctype','$this->iOptions')\">";*/
                        if (preg_match('/[A-Z_\-0-9]+:[A-Z_\-0-9]+\(/i', $phpfunc)) {
                            $mheight = $this->oattr->getOption('mheight', 30);
                            $mwidth = $this->oattr->getOption('mwidth', 290);
                            $input.= "<input id=\"ic_{$this->linkPrefix}$attridk\" type=\"button\" value=\"Z\"" . " title=\"" . $ititle . "\"" . " onclick=\"sendSpecialChoice(event,'{$this->linkPrefix}${attridk}'," . $docid . ",'$attrid','{$this->index}','$mheight','$mwidth')\">";
                        } else {
                            $ib = "<input id=\"ic_{$this->linkPrefix}$attridk\" type=\"button\" value=\"&#133;\"" . " title=\"" . $ititle . "\"" . " onclick=\"sendAutoChoice(event," . $docid . ",this,'{$this->linkPrefix}${attridk}','{$this->iOptions}','$attrid','{$this->index}')\">";
                            $input.= $ib;
                        }
                        // clear button
                        if (($this->oattr->type == "docid" || $this->oattr->type == "account") && ($this->oattr->getOption("multiple") == "yes")) {
                            $ib = "<input id=\"ix_$attridk\" type=\"button\" value=\"&times;\"" . " title=\"" . _("clear selected inputs") . "\" disabled " . " onclick=\"clearDocIdInputs('$attridk','mdocid_isel_$attridk',this)\">";
                            //$input.="</td><td>";
                            $input.= $ib;
                        } elseif (preg_match('/(.*)\((.*)\)\:(.*)/', $phpfunc, $reg)) {
                            
                            $outsideArg = array();
                            if ($alone && $this->oattr->type != "docid") {
                                $arg = array(
                                    $this->oattr->id
                                );
                            } else {
                                $argids = explode(",", $reg[3]); // output args
                                $arg = array();
                                foreach ($argids as $k => $v) {
                                    $this->linkPrefix = "ilink_";
                                    $isILink = false;
                                    $attrId = trim($v);
                                    if (substr($attrId, 0, strlen($this->linkPrefix)) == $this->linkPrefix) {
                                        $attrId = substr($attrId, strlen($this->linkPrefix));
                                        $isILink = true;
                                    }
                                    $docAttr = $this->doc->getAttribute($attrId);
                                    if (is_object($docAttr) && !$docAttr->inArray()) {
                                        $targid = trim(strtolower($attrId));
                                        if ($isILink) {
                                            $targid = $this->linkPrefix . $targid;
                                        }
                                        $outsideArg[] = $targid;
                                    } else {
                                        $targid = strtolower(trim($attrId));
                                        if ($isILink) {
                                            $targid = $this->linkPrefix . $targid;
                                        }
                                        if (strlen($attrId) > 1) $arg[$targid] = $targid;
                                    }
                                }
                            }
                            if (count($arg) > 0 || count($outsideArg) > 0) {
                                
                                $jOutsideArg = "";
                                if (count($arg) == 0) {
                                    $jarg = "'" . implode("','", $outsideArg) . "'";
                                } else {
                                    $jarg = "'" . implode("','", $arg) . "'";
                                    if (count($outsideArg) > 0) {
                                        $jOutsideArg = "'" . implode("','", $outsideArg) . "'";
                                    }
                                    if (!empty($jOutsideArg)) {
                                        $jOutsideArg = ",[$jOutsideArg]";
                                    }
                                }
                                
                                $input.= "<input id=\"ix_$attridk\" type=\"button\" value=\"&times;\"" . " title=\"" . _("clear inputs") . "\"" . " onclick=\"clearInputs([$jarg],'{$this->index}','$attridk' $jOutsideArg)\">";
                            }
                        }
                    } else if (($this->oattr->type == "date") || ($this->oattr->type == "timestamp")) {
                        $input.= "<input id=\"ix_$attridk\" type=\"button\" value=\"&times;\"" . " title=\"" . _("clear inputs") . "\"" . " onclick=\"clearInputs(['$attrid'],'{$this->index}')\">";
                        if (!$this->notd) $input.= "</td><td class=\"nowrap\">";
                    } else if ($this->oattr->type == "color") {
                        $input.= "<input id=\"ix_$attridk\" type=\"button\" value=\"&times;\"" . " title=\"" . _("clear inputs") . "\"" . " onclick=\"clearInputs(['$attrid'],'{$this->index}')\">";
                        $input.= "</td><td class=\"nowrap\">";
                    } else if ($this->oattr->type == "time") {
                        $input.= "<input id=\"ix_$attridk\" type=\"button\" value=\"&times;\"" . " title=\"" . _("clear inputs") . "\"" . " onclick=\"clearTime('$attridk')\">";
                        if (!$this->notd) $input.= "</td><td class=\"nowrap\">";
                    } else if (($this->oattr->type == "file") || ($this->oattr->type == "image")) {
                        $input.= "<input id=\"ix_$attridk\" type=\"button\" style=\"vertical-align:baseline\" value=\"&times;\"" . " title=\"" . _("clear file") . "\"" . " title1=\"" . _("clear file") . "\"" . " value1=\"&times;\"" . " title2=\"" . _("restore original file") . "\"" . " value2=\"&minus;\"" . " onclick=\"clearFile(this,'$attridk')\">";
                        if (!$this->notd) $input.= "</td><td class=\"nowrap\">";
                    } else {
                        if (!$this->notd) $input.= "</td><td class=\"nowrap\">";
                    }
                } else {
                    if (!$this->notd) $input.= "</td><td class=\"nowrap\">";
                }
                
                $input.= $this->addDocidCreate($this->oattr, $this->doc, $attridk, $value, $this->index);
                if ($this->oattr->elink != "" && (!$alone)) {
                    if (substr($this->oattr->elink, 0, 3) == "JS:") {
                        // javascript action
                        $url = $this->elinkEncode($this->doc, $attridk, substr($this->oattr->elink, 3) , $this->index, $ititle, $isymbol);
                        
                        $jsfunc = $url;
                    } else {
                        $url = $this->elinkEncode($this->doc, $attridk, $this->oattr->elink, $this->index, $ititle, $isymbol);
                        
                        $target = $this->oattr->getOption("eltarget", $attrid);
                        
                        $jsfunc = "subwindow(300,500,'$target','$url');";
                    }
                    
                    if ($this->oattr->getOption("elsymbol") != "") $isymbol = $this->oattr->getOption("elsymbol");
                    if ($this->oattr->getOption("eltitle") != "") $ititle = str_replace("\"", "'", $this->oattr->getOption("eltitle"));
                    $input.= "<input type=\"button\" value=\"$isymbol\"" . " title=\"" . $ititle . "\"" . " onclick=\"$jsfunc;";
                    
                    $input.= "\">";
                }
                if (GetHttpVars("viewconstraint") == "Y") { // set in modcard
                    if (($this->oattr->phpconstraint != "") && ($this->index != "__1x_")) {
                        $color = '';
                        $res = $this->doc->verifyConstraint($this->oattr->id, ($this->index == "") ? -1 : $this->index);
                        if (($res["err"] == "") && (count($res["sug"]) == 0)) $color = 'mediumaquamarine';
                        if (($res["err"] == "") && (count($res["sug"]) > 0)) $color = 'orange';
                        if (($res["err"] != "")) $color = 'tomato';
                        
                        $input.= "<input style=\"background-color:$color;\"type=\"button\" class=\"constraint\" id=\"co_$attridk\" value=\"C\"" . " onclick=\"vconstraint(this," . $this->doc->fromid . ",'$attrid');\">";
                    }
                }
            } elseif ($this->oattr->type == "htmltext") {
                if (!$this->notd) $input.= "</td><td class=\"nowrap\">";
            }
            
            return $input;
        }
        
        private function formatImage($value)
        {
            $originalname = '';
            
            $check = "";
            if (preg_match(PREGEXPFILE, $value, $reg)) {
                $originalname = "";
                $dbaccess = GetParam("FREEDOM_DB");
                $vf = newFreeVaultFile($dbaccess);
                /**
                 * @var vaultFileInfo $info
                 */
                $info = null;
                if ($vf->Show($reg[2], $info) == "") {
                    $vid = $reg[2];
                    $fname = "<A target=\"_self\" href=\"" . GetParam("CORE_BASEURL") . "app=FDL&action=EXPORTFILE&vid=$vid&docid=$this->docid&attrid={$this->attrid}&index={$this->index}\" title=\"{$info->name}\">";
                    // put image
                    $fname.= "<IMG  id=\"img_{$this->attridk}\" style=\"vertical-align:bottom;border:none;width:30px\" SRC=\"";
                    $fname.= GetParam("CORE_BASEURL") . "app=FDL&action=EXPORTFILE&width=30&vid=$vid&docid=" . $this->docid . "&attrid=" . $this->attrid . "&index={$this->index}";
                    $fname.= "\">";
                    
                    $fname.= "</A>";
                    if ($this->oattr->getOption("preventfilechange") == "yes") {
                        include_once ("FDL/Lib.Vault.php");
                        $check = vault_uniqname($vid);
                        $originalname = "<input id=\"IFORI{$this->attridk}\" name=\"IFORI{$this->attrin}\" type=\"hidden\" orivalue=\"" . $this->doc->vault_filename($this->attrid, false, ($this->index ? $this->index : -1)) . "\">";
                    }
                } else $fname = _("error in filename");
            } else {
                global $action;
                if ($value) {
                    $fname = "<img id=\"img_{$this->attridk}\" style=\"vertical-align:bottom;width:30px\" SRC=\"";
                    $fname.= $action->getImageUrl($value);
                    $fname.= "\">";
                } else {
                    
                    $fname = $action->GetIcon($this->oattr->getOption("defaultimage", "noimage.png") , _("no image") , 30);
                    $fname = str_replace("<img", '<img id="img_' . $this->attridk . '" style="vertical-align:bottom"', $fname);
                }
            }
            
            $input = "<span id=\"IFERR" . $this->attridk . "\" class=\"Error\"></span><span class=\"FREEDOMText\">" . $fname . "</span><br/>";
            $input.= $originalname;
            // input
            $input.= "<input name=\"" . $this->attrin . "\" type=\"hidden\" value=\"" . $value . "\" id=\"" . $this->attridk . "\">";
            $input.= "<input type=\"hidden\" value=\"" . $value . "\" id=\"INIV" . $this->attridk . "\">";
            
            if (($this->visibility == "W") || ($this->visibility == "O")) {
                $input.= "<span><input onchange=\"document.isChanged=true;changeFile(this,'$this->attridk','$check')\" $this->classname accept=\"image/*\" size=15 type=\"file\" id=\"IF_{$this->attridk}\" name=\"_UPL" . $this->attrin . "\"";
                $input.= " ></span> ";
            }
            return $input;
        }
        
        private function formatFile($value)
        {
            $check = "";
            $originalname = "";
            if (preg_match(PREGEXPFILE, $value, $reg)) {
                $dbaccess = getDbAccess();
                $vf = newFreeVaultFile($dbaccess);
                if ($vf->Show($reg[2], $info) == "") {
                    $vid = $reg[2];
                    $DAV = getParam("FREEDAV_SERVEUR", false);
                    
                    global $action;
                    if ($DAV) {
                        $action->parent->AddJsRef($action->GetParam("CORE_PUBURL") . "/DAV/Layout/getsessionid.js");
                        $parms = $action->parent->GetAllParam();
                        if (isset($parms['CORE_ABSURL']) && isset($parms['ISIE']) && $parms['ISIE'] && preg_match('/^https:/i', $parms['CORE_ABSURL'])) {
                            $this->onChange = sprintf(" onclick='asdavLaunch(getPrivateDavHref(\"%s\", \"%s\", \"%s\", this.getAttribute(\"filename\")))' filename=\"%s\"", $this->docid, $vid, $DAV, str_replace('"', '%22', $info->name));
                        } else {
                            $this->onChange = sprintf(" onclick='this.href=getPrivateDavHref(\"%s\",\"%s\",\"%s\",this.getAttribute(\"filename\"))' filename=\"%s\"", $this->docid, $vid, $DAV, str_replace('"', '%22', $info->name));
                        }
                        //$this->onChange="onclick=\"var sid=getsessionid('".$docid."','$vid');this.href='asdav://$DAV/freedav/vid-'+sid+'/'.$info->name."e";
                        $fname = "<A title=\"" . _("open file with your editor") . "\" href=\"#\" {$this->onChange}><img style=\"border:none\" src=\"Images/davedit.png\">";
                    } else {
                        $fname = "<A target=\"_self\" title=\"" . _("download file") . "\" href=\"" . $action->GetParam("CORE_BASEURL") . "app=FDL&action=EXPORTFILE&vid=$vid&docid={$this->docid}&attrid={$this->attrid}&index={$this->index}\">";
                    }
                    $fname.= $info->name;
                    $fname.= "</A>";
                    if ($this->oattr->getOption("preventfilechange") == "yes") {
                        include_once ("FDL/Lib.Vault.php");
                        $check = vault_uniqname($vid);
                        $originalname = "<input id=\"IFORI{$this->attridk}\" name=\"IFORI{$this->attrin}\" type=\"hidden\" orivalue=\"" . $this->doc->vault_filename($this->attrid, false, ($this->index ? $this->index : -1)) . "\">";
                    }
                } else $fname = _("error in filename");
            } else $fname = _("no filename");
            
            $input = "<span id=\"IFERR" . $this->attridk . "\" class=\"Error\"></span><span class=\"FREEDOMText\">" . $fname . "</span><br/>";
            $input.= $originalname;
            // input
            $input.= "<input name=\"" . $this->attrin . "\" type=\"hidden\" value=\"" . $value . "\" id=\"" . $this->attridk . "\">";
            $input.= "<input type=\"hidden\" value=\"" . $value . "\" id=\"INIV" . $this->attridk . "\">";
            $input.= "<span><input onchange=\"document.isChanged=true;changeFile(this,'$this->attridk','$check')\"  class=\"\" size=15 type=\"file\" id=\"IF_{$this->attridk}\" name=\"_UPL" . $this->attrin . "\" value=\"" . chop(htmlentities($value, ENT_COMPAT, "UTF-8")) . "\"";
            
            if (($this->visibility == "R") || ($this->visibility == "S")) $input.= $this->idisabled;
            $input.= " ></span> ";
            return $input;
        }
        /**
         * HTML input for Longtext attribute
         * @param string $value the row value of input
         * @return string HTML input fragment
         */
        private function formatLongText($value)
        {
            $lh = $this->oattr->getOption("editheight", "2em");
            $elabel = $this->oattr->getOption("elabel");
            if ($elabel != "") $this->onChange.= " title=\"$elabel\"";
            
            $input = "<textarea {$this->onChange} wrap=\"virtual\"  onkeyup=\"textautovsize(event,this)\"  onclick=\"textautovsize(event,this)\" class=\"autoresize\" style=\"height:$lh\" name=\"" . $this->attrin . "\" ";
            $input.= " id=\"" . $this->attridk . "\" ";
            if (($this->visibility == "R") || ($this->visibility == "S")) $input.= $this->idisabled;
            $input.= " >" . str_replace(array(
                "[",
                "$"
            ) , array(
                "&#091;",
                "&#036;"
            ) , htmlentities((str_replace("<BR>", "\n", $value)) , ENT_COMPAT, "UTF-8")) . "</textarea>";
            return $input;
        }
        /**
         * HTML input for Htmltext attribute
         * @param string $value the row value of input
         * @return string HTML input fragment
         */
        private function formatHtmlText($value)
        {
            if (($this->visibility == "H") || ($this->visibility == "R")) {
                $input = "<textarea    name=\"{$this->attrin}\">$value</textarea>";
            } elseif ($this->visibility == "S") {
                // no input : just text
                if ($value == "") $value = '<br/>';
                $input = "<div class=\"static\" name=\"{$this->attrin}\">$value</div>";
            } else {
                global $action;
                $lay = new Layout("FDL/Layout/ckeditor.xml", $action);
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
                $lay->set("isInDuplicableTableLine", $this->isInDuplicableTableLine ? "TRUE" : "");
                $lay->set("label", ucFirst($this->oattr->getLabel()));
                $lay->set("need", $this->oattr->needed);
                $jsonconf = $this->oattr->getOption("jsonconf");
                if (!$jsonconf) {
                    $conf = array(
                        "height" => $this->oattr->getOption("editheight", "150px") ,
                        "toolbar" => $this->oattr->getOption("toolbar", "Simple") ,
                        "toolbarStartupExpanded" => (strtolower($this->oattr->getOption("toolbarexpand")) == "no") ? false : true
                    );
                    
                    $jsonconf = json_encode($conf);
                }
                if ($this->oattr->getOption("doclink")) {
                    $conf = json_decode($jsonconf, true);
                    $conf["doclink"] = json_decode($this->oattr->getOption("doclink") , true);
                    $jsonconf = json_encode($conf);
                }
                
                $lay->set("jsonconf", $jsonconf);
                
                $lay->set("height", $this->oattr->getOption("editheight", "150px"));
                $lay->set("toolbar", $this->oattr->getOption("toolbar", "Simple"));
                $lay->set("toolbarexpand", (strtolower($this->oattr->getOption("toolbarexpand")) == "no") ? "false" : "true");
                $lay->set("aid", $this->attridk);
                $lay->set("aname", $this->attrin);
                if (($this->visibility == "R") || ($this->visibility == "S")) $lay->set("disabled", $this->idisabled);
                else $lay->set("disabled", "");
                $input = $lay->gen();
            }
            return $input;
        }
        /**
         * HTML input for array attribute
         * @param string $value the row value of input
         * @return string HTML input fragment
         */
        private function formatArray($value)
        {
            global $action;
            $lay = new Layout("FDL/Layout/editarray.xml", $action);
            $rn = $this->oattr->getOption("roweditzone");
            if ($rn) $this->getZoneLayArray($lay, $this->doc, $this->oattr, $rn);
            else $this->getLayArray($lay, $this->doc, $this->oattr);
            
            $input = $lay->gen();
            return $input;
        }
        /**
         * HTML input for Thesaurus attribute
         * @param string $value the row value of input
         * @return string HTML input fragment
         */
        private function formatThesaurus($value)
        {
            
            $multi = $this->oattr->getOption("multiple");
            if ($multi) {
                
                $lay = new Layout("THESAURUS/Layout/editmultiinputthconcept.xml");
                $lay->set("atitle", false);
                $lay->set("elabel", $this->oattr->getOption("elabel", _("Display available choices")));
                $top = array();
                if ($value) {
                    $thids = explode("<BR>", str_replace("\n", "<BR>", $value));
                    foreach ($thids as $kth => $vth) {
                        $th = new_doc($this->doc->dbaccess, trim($vth));
                        if ($th->isAlive()) {
                            $thtitle = $th->getSpecTitle();
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
                    $th = new_doc($this->doc->dbaccess, $value);
                    $thtitle = $th->getSpecTitle();
                    $lay->set("atitle", $thtitle);
                } else $lay->set("atitle", false);
            }
            $lay->set("value", $value);
            $lay->set("aname", $this->attrin);
            $lay->set("aid", $this->attridk);
            $idth = $this->oattr->format;
            
            $thid = $this->doc->getValue($idth);
            if (!$thid) $thid = $idth; // direct logical name
            $lay->set("thesaurus", $thid);
            $this->notd = true; // autonome input
            $input = $lay->gen();
            return $input;
        }
        /**
         * HTML input for Doc attribute
         * @param string $value the row value of input
         * @return string HTML input fragment
         */
        private function formatDoc($value)
        {
            global $action;
            $lay = new Layout("FDL/Layout/editadoc.xml", $action);
            $this->getLayAdoc($lay, $this->doc, $this->oattr, $value, $this->attrin, $this->index);
            
            if (($this->visibility == "R") || ($this->visibility == "S")) $lay->set("disabled", $this->idisabled);
            else $lay->set("disabled", "");
            $input = $lay->gen();
            return $input;
        }
        /**
         * HTML input for Account attribute
         * @param string $value the row value of input
         * @return string HTML input fragment
         */
        private function formatAccount($value)
        {
            if (!$this->oattr->format) {
                $match = $this->oattr->getOption("match");
                switch ($match) {
                    case 'user':
                        $this->oattr->format = 'IUSER';
                        break;

                    case 'role':
                        $this->oattr->format = 'ROLE';
                        break;

                    case 'group':
                        $this->oattr->format = 'IGROUP';
                        break;

                    case 'all':
                        $this->oattr->format = 'IUSER|IGROUP|ROLE';
                        break;

                    default:
                        $this->oattr->format = 'IUSER';
                        break;
                }
                $this->oattr->format = 'IUSER';
            }
            
            if (!$this->oattr->phpfile) {
                // it is already set in Lib.Attr.php when create family class
                // use fdlGetAccounts phpfunc
                
            }
            return $this->formatDocid($value);
        }
        /**
         * HTML input for Docid attribute
         * @param string $value the row value of input
         * @return string HTML input fragment
         */
        private function formatDocid($value)
        {
            global $action;
            $famid = $this->oattr->format;
            $textvalue = '';
            if ($famid) {
                $needLatest = ($this->oattr->getOption("docrev", "latest") == "latest");
                // edit document relation
                $multi = $this->oattr->getOption("multiple");
                $input = "";
                $this->linkPrefix = "ilink_";
                if ($multi == "yes") {
                    $lay = new Layout("FDL/Layout/editmdoc.xml", $action);
                    $this->getLayMultiDoc($lay, $this->doc, $this->oattr, $value, $this->attrin, $this->index);
                    
                    $cible = "mdocid_work";
                    if (($this->visibility == "R") || ($this->visibility == "S")) $lay->set("disabled", $this->idisabled);
                    else $lay->set("disabled", "");
                    $lay->set("cible", $cible);
                    
                    $input2 = $lay->gen();
                } else {
                    $input2 = "";
                    
                    if ($this->doc->usefor == "D") $input = "<input type=\"text\" title=\"" . _("real value to set") . "\" name=\"" . $this->attrin . "\"";
                    else $input = "<input type=\"hidden\"  name=\"" . $this->attrin . "\"";
                    $input.= " id=\"" . $this->attridk . "\" value=\"$value\">";
                    $cible = "";
                    $textvalue = $this->doc->getHtmlTitle(trim($value) , '', $needLatest);
                }
                if (!$this->oattr->phpfile) {
                    $this->oattr->phpfile = "fdl.php";
                    $this->oattr->phpfunc = "lfamily(D,$famid,{$this->linkPrefix}{$this->attrid}):${cible}{$this->attrid},{$this->linkPrefix}{$this->attrid}";
                } else {
                    $phpfunc = preg_replace('/([\s|,|:|\(])CT([\s|,|\)]|$)/', '$1' . $this->linkPrefix . $this->attrid . '$2', $this->oattr->phpfunc);
                    $phpfunc = str_replace("):{$this->attrid},", "):${cible}{$this->attrid},", $phpfunc);
                    $phpfunc = str_replace("):" . strtoupper($this->attrid) . ",", "):${cible}{$this->attrid},", $phpfunc);
                    $this->oattr->phpfunc = $phpfunc;
                }
                if ($this->docid == 0) {
                    // case of specific interface
                    $this->iOptions = str_replace('\"', '&quot;', '&phpfile=' . $this->oattr->phpfile . '&phpfunc=' . $this->oattr->phpfunc . '&label=' . ($this->oattr->getLabel()));
                }
                $autocomplete = " autocomplete=\"off\" autoinput=\"1\" onfocus=\"activeAuto(event," . $this->docid . ",this,'{$this->iOptions}','{$this->attrid}','{$this->index}')\" ";
                $this->onChange.= $autocomplete;
                
                $famid = $this->oattr->format;
                $input.= "<input {$this->classname} $autocomplete {$this->jsEvents} onchange=\"addmdocs('{$this->attrin}');document.isChanged=true\" type=\"text\" name=\"_{$this->linkPrefix}" . substr($this->attrin, 1) . "\"";
                if (($this->visibility == "R") || ($this->visibility == "S")) $input.= $this->idisabled;
                $input.= " id=\"{$this->linkPrefix}" . $this->attridk . "\" value=\"" . str_replace('"', '&quot;', $textvalue) . "\">";
                
                if (!$cible) {
                    $this->doc->addparamrefresh($this->attrid, $this->linkPrefix . $this->attrid);
                } else {
                    $input = $input2 . $input;
                }
            } else {
                $input = "<input {$this->onChange} {$this->classname}  type=\"text\" name=\"" . $this->attrin . "\" value=\"" . $value . "\"";
                $input.= " id=\"" . $this->attridk . "\" ";
                if (($this->visibility == "R") || ($this->visibility == "S")) $input.= $this->idisabled;
                $input.= " > ";
            }
            return $input;
        }
        /**
         * HTML input for Enum attribute
         * @param string $value the row value of input
         * @return string HTML input fragment
         */
        private function formatEnum($value)
        {
            global $action;
            if ($this->oattr->eformat == "") $this->oattr->eformat = $this->oattr->getOption("eformat");
            if (($this->oattr->repeat) && (!$this->oattr->inArray())) { // enumlist
                switch ($this->oattr->eformat) {
                    case "vcheck":
                        $lay = new Layout("FDL/Layout/editenumlistvcheck.xml", $action);
                        break;

                    case "hcheck":
                        $lay = new Layout("FDL/Layout/editenumlisthcheck.xml", $action);
                        break;

                    case "auto":
                        $lay = new Layout("FDL/Layout/editenumlistauto.xml", $action);
                        $this->doc->AddParamRefresh($this->oattr->id, "ic_" . $this->oattr->id);
                        break;

                    default:
                        $lay = new Layout("FDL/Layout/editenumlist.xml", $action);
                }
            } else {
                $enuml = $this->oattr->getenumlabel();
                $lunset = current($enuml);
                if ($value == "") {
                    if (($this->oattr->eformat == 'bool') || ($this->oattr->getOption("eunset") != "yes")) $value = key($enuml);
                    else $value = " ";
                }
                switch ($this->oattr->eformat) {
                    case "vcheck":
                        $lay = new Layout("FDL/Layout/editenumvcheck.xml", $action);
                        break;

                    case "hcheck":
                        $lay = new Layout("FDL/Layout/editenumhcheck.xml", $action);
                        break;

                    case "auto":
                        $lay = new Layout("FDL/Layout/editenumauto.xml", $action);
                        $this->notd = true;
                        $this->doc->AddParamRefresh($this->oattr->id, "ic_" . $this->oattr->id);
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
                
                $this->getLayOptions($lay, $this->doc, $this->oattr, $value, $this->attrin, $this->index);
                $lay->set("msize", $this->oattr->getOption("mselectsize", 3));
                if (($this->visibility == "R") || ($this->visibility == "S")) $lay->set("disabled", $this->idisabled);
                else $lay->set("disabled", "");
                
                $lay->set("NOTD", $this->notd);
                $input = $lay->gen();
                return $input;
            }
            /**
             * HTML input for Color attribute
             * @param string $value the row value of input
             * @return string HTML input fragment
             */
            private function formatColor($value)
            {
                
                $eopt = '';
                $elabel = $this->oattr->getOption("elabel");
                if ($elabel != "") $eopt.= " title=\"$elabel\"";
                $input = "<input size=7  $eopt style=\"background-color:$value\" type=\"text\" name=\"" . $this->attrin . "\" value=\"" . chop(htmlentities($value, ENT_COMPAT, "UTF-8")) . "\"";
                $input.= " id=\"" . $this->attridk . "\" ";
                
                if (($this->visibility == "R") || ($this->visibility == "S")) $input.= $this->idisabled;
                else if ($this->doc->usefor != 'D') $input.= " disabled "; // always but default
                $input.= " class=\"color {pickerOnfocus:false,pickerClosable:true,pickerCloseText:'" . _("Close") . "',hash:true,required:false}\" ";
                
                $input.= " >&nbsp;";
                if (!(($this->visibility == "R") || ($this->visibility == "S"))) {
                    $input.= "<input id=\"ic_{$this->attridk}\" type=\"button\" value=\"&#133;\"" . " title=\"" . _("color picker") . "\" onclick=\"jscolor.init(); document.getElementById('{$this->attridk}').color.showPicker()\"" . ">";
                }
                return $input;
            }
            /**
             * HTML input for Enum attribute
             * @param string $value the row value of input
             * @return string HTML input fragment
             */
            private function formatDate($value)
            {
                global $action;
                $lay = new Layout("FDL/Layout/editdate.xml", $action);
                $this->getLayDate($lay, $this->doc, $this->oattr, $value, $this->attrin, $this->index);
                
                $lay->set("disabled", "");
                if (($this->visibility == "R") || ($this->visibility == "S")) {
                    $lay->set("disabled", $this->idisabled);
                } else if ($this->doc->usefor != 'D') $lay->set("disabled", "disabled");
                
                if (!(($this->visibility == "R") || ($this->visibility == "S"))) {
                    $lay->setBlockData("VIEWCALSEL", array(
                        array(
                            "zou"
                        )
                    ));
                }
                if (($this->doc->usefor != 'D') && ($this->doc->usefor != 'Q')) $lay->setBlockData("CONTROLCAL", array(
                    array(
                        "zou"
                    )
                ));
                $input = trim($lay->gen());
                return $input;
            }
            /**
             * HTML input for Timestamp attribute
             * @param string $value the row value of input
             * @return string HTML input fragment
             */
            private function formatTimestamp($value)
            {
                global $action;
                $lay = new Layout("FDL/Layout/edittimestamp.xml", $action);
                $this->getLayDate($lay, $this->doc, $this->oattr, $value, $this->attrin, $this->index);
                
                $lay->set("readonly", false);
                $lay->set("disabled", "");
                if (($this->visibility == "R") || ($this->visibility == "S")) {
                    $lay->set("disabled", $this->idisabled);
                    $lay->set("readonly", true);
                } else if ($this->doc->usefor != 'D') $lay->set("disabled", "disabled");
                
                $input = $lay->gen();
                return $input;
            }
            /**
             * HTML input for Time attribute
             * @param string $value the row value of input
             * @return string HTML input fragment
             */
            private function formatTime($value)
            {
                $isDisabled = "";
                if (($this->visibility == "R") || ($this->visibility == "S")) $isDisabled = $this->idisabled;
                list($hh, $mm, $ss) = explode(":", $value);
                $input = "<input $isDisabled size=2 maxlength=2 onchange=\"chtime('{$this->attridk}')\" type=\"text\"  value=\"" . $hh . "\" id=\"hh" . $this->attridk . "\">:";
                
                $input.= "<input $isDisabled size=2 maxlength=2 onchange=\"chtime('{$this->attridk}')\" type=\"text\"  value=\"" . $mm . "\"id=\"mm" . $this->attridk . "\">";
                
                $input.= "<input  type=\"hidden\" onchange=\"displayTime(this)\" name=\"" . $this->attrin . "\" id=\"" . $this->attridk . "\" value=\"" . $value . "\">";
                return $input;
            }
            /**
             * HTML input for Password attribute
             * @param string $value the row value of input
             * @return string HTML input fragment
             */
            private function formatPassword($value)
            {
                // don't see the value
                $eopt = $this->classname . ' ';
                $esize = $this->oattr->getOption("esize");
                if ($esize > 0) $eopt = "size=$esize";
                $input = "<input {$this->onChange} $eopt type=\"password\" name=\"" . $this->attrin . "\" value=\"" . "\"";
                $input.= " id=\"" . $this->attridk . "\" ";
                
                if (($this->visibility == "R") || ($this->visibility == "S")) $input.= $this->idisabled;
                
                $input.= " > ";
                return $input;
            }
            /**
             * HTML input for Password attribute
             * @param string $value the row value of input
             * @deprecated
             * @return string HTML input fragment
             */
            private function formatOption($value)
            {
                global $action;
                $lay = new Layout("FDL/Layout/editdocoption.xml", $action);
                $this->getLayDocOption($lay, $this->doc, $this->oattr, $value, $this->attrin, $this->index);
                if (($this->visibility == "R") || ($this->visibility == "S")) $lay->set("disabled", $this->idisabled);
                else $lay->set("disabled", "");
                $input = $lay->gen();
                return $input;
            }
            /**
             * @param Doc $doc
             * @param string $attrik
             * @param string $link
             * @param int $index
             * @param string $ititle
             * @param string $isymbol
             * @return string
             */
            public function elinkEncode(&$doc, $attrik, $link, $index, &$ititle = "", &$isymbol = "")
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
            /**
             * @param Layout $lay
             * @param Doc $doc
             * @param NormalAttribute $oattr
             * @param int $row
             */
            public function getLayArray(&$lay, &$doc, &$oattr, $row = - 1)
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
                // get inline help
                $help = $doc->getHelpPage();
                
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
                        "aid" => $v->id,
                        "alabel" => (!$visible) ? "" : $v->getLabel() ,
                        "elabel" => $v->getOption("elabel") ,
                        "astyle" => $v->getOption("cellheadstyle") ,
                        "ahclass" => (!$visible) ? "hiddenAttribute" : "visibleAttribute",
                        "aehelp" => ($help->isAlive()) ? $help->getAttributeHelpUrl($v->id) : false,
                        "aehelpid" => ($help->isAlive()) ? $help->id : false
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
                $lay->set("ehelp", ($help->isAlive()) ? $help->getAttributeHelpUrl($attrid) : false);
                $lay->set("ehelpid", ($help->isAlive()) ? $help->id : false);
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
            /**
             * @param layout $lay
             * @param Doc $doc
             * @param NormalAttribute $oattr
             * @param string $zone
             * @return mixed
             */
            private function getZoneLayArray(&$lay, &$doc, &$oattr, $zone)
            {
                global $action;
                
                $height = $oattr->getOption("height", false);
                $lay->set("tableheight", $height);
                $lay->set("readonly", ($oattr->mvisibility == 'U'));
                $lay->set("thspan", "1");
                $lay->set("aehelp", false);
                
                if (($zone != "") && preg_match("/([A-Z_-]+):([^:]+):{0,1}[A-Z]{0,1}/", $zone, $reg)) {
                    $attrid = $oattr->id;
                    $ta = $doc->attributes->getArrayElements($attrid);
                    
                    $dxml = new DomDocument();
                    $rowlayfile = getLayoutFile($reg[1], ($reg[2]));
                    if (!file_exists($rowlayfile)) {
                        $lay->template = sprintf(_("cannot open %s layout file") , $rowlayfile);
                        AddwarningMsg(sprintf(_("cannot open %s layout file") , $rowlayfile));
                        return;
                    }
                    if (!@$dxml->load($rowlayfile)) {
                        AddwarningMsg(sprintf(_("cannot load xml template : %s") , print_r(libxml_get_last_error() , true)));
                        $lay->template = sprintf(_("cannot load xml %s layout file") , $rowlayfile);
                        return;
                    }
                    $theads = $dxml->getElementsByTagName('table-head');
                    if ($theads->length > 0) {
                        /**
                         * @var DOMElement $thead
                         */
                        $thead = $theads->item(0);
                        $theadcells = $thead->getElementsByTagName('cell');
                        $talabel = array();
                        for ($i = 0; $i < $theadcells->length; $i++) {
                            /**
                             * @var DOMElement $iti
                             */
                            $iti = $theadcells->item($i);
                            $th = xt_innerXML($iti);
                            $thstyle = $iti->getAttribute("style");
                            $thclass = $iti->getAttribute("class");
                            
                            $talabel[] = array(
                                "alabel" => $th,
                                "ahw" => "auto",
                                "astyle" => $thstyle,
                                "ahclass" => $thclass,
                                "ahvis" => "visible"
                            );
                        }
                        $lay->setBlockData("TATTR", $talabel);
                    }
                    
                    $tbodies = $dxml->getElementsByTagName('table-body');
                    $tr = $tcellstyle = $tcellclass = array();
                    if ($tbodies->length > 0) {
                        /**
                         * @var DOMElement $tbody
                         */
                        $tbody = $tbodies->item(0);
                        $tbodycells = $tbody->getElementsByTagName('cell');
                        for ($i = 0; $i < $tbodycells->length; $i++) {
                            /**
                             * @var DOMElement $iti
                             */
                            $iti = $tbodycells->item($i);
                            $tr[] = xt_innerXML($iti);
                            $tcellstyle[] = $iti->getAttribute("style");
                            $tcellclass[] = $iti->getAttribute("class");
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
                    if ($doc->doctype == 'C') {
                        /**
                         * @var DocFam $doc
                         */
                        $defval = $doc->getDefValues();
                    } else {
                        
                        $fdoc = $doc->getFamDoc();
                        $defval = $fdoc->getDefValues();
                    }
                    
                    $tvattr = array();
                    for ($k = 0; $k < $nbitem; $k++) {
                        $tvattr[] = array(
                            "bevalue" => "bevalue_$k",
                            "index" => $k,
                            "cellattrid" => '',
                            "cellmultiple" => '',
                            "cellatype" => ''
                        );
                        $tivalue = array();
                        
                        foreach ($tr as $kd => $td) {
                            $val = preg_replace('/\[([^\]]*)\]/e', "\$this->rowattrReplace(\$doc,'\\1',$k)", $td);
                            $tivalue[] = array(
                                "eivalue" => $val,
                                "ehvis" => "visible",
                                "tdstyle" => $tcellstyle[$kd],
                                "eiclass" => $tcellclass[$kd],
                                "bgcolor" => "inherit",
                                "vhw" => "auto"
                            );
                        }
                        $lay->setBlockData("bevalue_$k", $tivalue);
                    }
                    $tilabel = array();
                    foreach ($tr as $kd => $td) {
                        $dval = preg_replace('/\[([^\]]*)\]/e', "\$this->rowattrReplace(\$doc,'\\1','__1x_',\$defval)", $td);
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
                    
                    if ($oattr->getOption("vlabel", "up") == "up") $lay->set("caption", $oattr->getLabel());
                    else $lay->set("caption", "");
                } else {
                    addWarningMsg(sprintf(_("roweditzone syntax %s is invalid") , $zone));
                }
            }
            /**
             * @param Doc $doc
             * @param string $s
             * @param int $index
             * @param string $defval
             * @return array|mixed|string
             */
            private function rowattrReplace(&$doc, $s, $index, &$defval = null)
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
                    $v = str_replace('"', '&quot;', $v);
                }
                return $v;
            }
            /**
             * generate HTML for inline document (not virtual)
             *
             * @param Layout $lay template of html input
             * @param Doc $doc current document in edition
             * @param NormalAttribute $oattr current attribute for input
             * @param string $value value of the attribute to display (generaly the value comes from current document)
             * @param string $aname input HTML name (generaly it is '_'+$oattr->id)
             * @param int $index current row number if it is in array ("" if it is not in array)
             */
            private function getLayAdoc(&$lay, &$doc, &$oattr, $value, $aname, $index)
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
             * @param NormalAttribute $oattr current attribute for input
             * @param string $value value of the attribute to display (generaly the value comes from current document)
             * @param string $aname input HTML name (generaly it is '_'+$oattr->id)
             * @param int $index current row number if it is in array ("" if it is not in array)
             */
            private function getLayMultiDoc(&$lay, &$doc, &$oattr, $value, $aname, $index)
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
             * @param NormalAttribute $oattr current attribute for input
             * @param string $value value of the attribute to display (generaly the value comes from current document)
             * @param string $aname input HTML name (generaly it is '_'+$oattr->id)
             * @param int $index current row number if it is in array ("" if it is not in array)
             */
            private function getLayDate(&$lay, &$doc, &$oattr, $value, $aname, $index)
            {
                if ($index !== "") $idocid = $oattr->format . '_' . $index;
                else $idocid = $oattr->format;
                $lay->set("name", $aname);
                
                $localeconfig = getLocaleConfig();
                if ($localeconfig != false) {
                    $lay->set("dateformat", $localeconfig['dateFormat']);
                    $lay->set("datetimeformat", $localeconfig['dateTimeFormat']);
                    $value = stringDateToLocaleDate($value);
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
             * @param NormalAttribute $oattr current attribute for input
             * @param string $value value of the attribute to display (generaly the value comes from current document)
             * @param string $aname input HTML name (generaly it is '_'+$oattr->id)
             * @param int $index current row number if it is in array ("" if it is not in array)
             */
            private function getLayOptions(&$lay, &$doc, &$oattr, $value, $aname, $index)
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
                $esort = $oattr->getOption("esort", "none");
                if (($eformat == "auto") && ($multiple != "yes")) $doc->addParamRefresh($oattr->id, "li_" . $oattr->id);
                
                $lay->set("isopen", ($etype == "open"));
                $lay->set("isfreeselected", false);
                $lay->set("isfree", ($etype == "free"));
                $tvalue = $doc->_val2array($value);
                
                $lay->set("lvalue", $value);
                $enuml = $oattr->getenumlabel();
                if ($esort == 'key' || $esort == 'label') {
                    $enuml = $this->sortEnumMap($enuml, $esort);
                }
                
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
                    $found = false;
                    foreach ($tvalue as $valKey) {
                        if ((string)$k === $valKey) $found = true;
                    }
                    if ($found) {
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
             * @param NormalAttribute $oattr current attribute for input
             * @param string $value value of the attribute to display (generaly the value comes from current document)
             * @param string $aname input HTML name (generaly it is '_'+$oattr->id)
             * @param int $index current row number if it is in array ("" if it is not in array)
             */
            private function getLayDocOption(&$lay, &$doc, &$oattr, $value, $aname, $index)
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
             * @param NormalAttribute $oattr current attribute for input
             * @param string $value value of the attribute to display (generaly the value comes from current document)
             * @param string $aname input HTML name (generaly it is '_'+$oattr->id)
             * @param int $index current row number if it is in array ("" if it is not in array)
             * @return bool
             */
            private function getLayTextOptions(&$lay, &$doc, &$oattr, $value, $aname, $index)
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
             * @param Doc $doc
             * @param NormalAttribute $oattr current attribute for input
             * @param string $attridk id suffix of the generated HTML tag
             * @param string $attrin name of the generated HTML tag
             * @param string $value value of the attribute to display (generaly the value comes from current document)
             * @param string $zone
             * @return String the formated output
             */
            private function getLayIdoc(&$doc, &$oattr, $attridk, $attrin, $value, $zone = "")
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
             * add button to create/modify document relation
             *
             * @param \BasicAttribute|\NormalAttribute $oattr
             * @param \Doc $doc
             * @param $attridk id suffix of the <input/> tag
             * @param string $value
             * @param integer $index
             * @return string
             */
            private function addDocIdCreate(BasicAttribute & $oattr, Doc & $doc, $attridk, $value, $index)
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
                                        $urlcreate.= sprintf("&%s=%s", $kl, $this->elinkencode($doc, $attridk, "%$v%", $index));
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
            /**
             * Sort an enum's (key => label) mapping structure by 'key' or 'label'.
             *
             * @param array $enumMap an enum mapping structure as returned by NormalAttribute::getEnumLabel() method
             * @param string $sortBy 'key' to sort by key, 'label' to sort by label
             * @return array the sorted enum's mapping structure
             */
            function sortEnumMap($enumMap, $sortBy)
            {
                global $action;
                
                switch ($sortBy) {
                    case 'key':
                        uksort($enumMap, function ($a, $b)
                        {
                            return strcmp($a, $b);
                        });
                        break;

                    case 'label':
                        $collator = new Collator($action->GetParam('CORE_LANG', 'fr_FR'));
                        uasort($enumMap, function ($a, $b) use ($collator)
                        {
                            /**
                             * @var Collator $collator
                             */
                            return $collator->compare($a, $b);
                        });
                        break;
                }
                return $enumMap;
            }
        }
?>