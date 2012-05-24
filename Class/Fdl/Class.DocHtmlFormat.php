<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Get Html Value for document
 * @class DocHtmlFormat
 *
 */
class DocHtmlFormat
{
    /**
     * @var Doc
     */
    public $doc = null;
    private $index = - 1;
    private $target = '_self';
    /**
     * @var NormalAttribute
     */
    private $oattr = null;
    private $attrid = '';
    /**
     * format set in type
     * @var string
     */
    private $cFormat = '';
    private $htmlLink = true;
    private $useEntities = true;
    private $abstractMode = false;
    /**
     * @var bool to send once vault error
     */
    private $vaultErrorSent = false;
    
    public function __construct(Doc & $doc)
    {
        $this->setDoc($doc);
    }
    
    public function setDoc(Doc & $doc)
    {
        $this->doc = $doc;
    }
    /**
     * @param NormalAttribute $oattr
     * @param string $value raw value
     * @param string $target
     * @param bool $htmlLink
     * @param int $index
     * @param bool $useEntities
     * @param bool $abstractMode
     * @return string the HTML formated value
     */
    public function getHtmlValue($oattr, $value, $target = "_self", $htmlLink = true, $index = - 1, $useEntities = true, $abstractMode = false)
    {
        global $action;
        
        $this->oattr = $oattr;
        $this->target = $target;
        $this->index = $index;
        $this->cFormat = $this->oattr->format;
        $atype = $this->oattr->type;
        $this->htmlLink = $htmlLink;
        $this->useEntities = $useEntities;
        $this->abstractMode = $abstractMode;
        
        if (($this->oattr->repeat) && ($this->index <= 0)) {
            $tvalues = explode("\n", $value);
        } else {
            $tvalues[$this->index] = $value;
        }
        $this->attrid = $this->oattr->id;
        $thtmlval = array();
        foreach ($tvalues as $kvalue => $avalue) {
            
            switch ($atype) {
                case "idoc":
                    $htmlval = $this->formatIDoc($kvalue, $avalue);
                    break;

                case "image":
                    $htmlval = $this->formatImage($kvalue, $avalue);
                    break;

                case "file":
                    
                    $htmlval = $this->formatFile($kvalue, $avalue);
                    break;

                case "longtext":
                case "xml":
                    
                    $htmlval = $this->formatLongtext($kvalue, $avalue);
                    break;

                case "password":
                    
                    $htmlval = $this->formatPassword($kvalue, $avalue);
                    break;

                case "enum":
                    
                    $htmlval = $this->formatEnum($kvalue, $avalue);
                    break;

                case "array":
                    
                    $htmlval = $this->formatArray($kvalue, $avalue);
                    break;

                case "doc":
                    $htmlval = $this->formatDoc($kvalue, $avalue);
                    break;

                case "account":
                    $htmlval = $this->formatAccount($kvalue, $avalue);
                    break;

                case "docid":
                    $htmlval = $this->formatDocid($kvalue, $avalue);
                    break;

                case "thesaurus":
                    
                    $htmlval = $this->formatThesaurus($kvalue, $avalue);
                    break;

                case "option":
                    
                    $htmlval = $this->formatOption($kvalue, $avalue);
                    break;

                case 'money':
                    
                    $htmlval = $this->formatMoney($kvalue, $avalue);
                    break;

                case 'htmltext':
                    
                    $htmlval = $this->formatHtmltext($kvalue, $avalue);
                    break;

                case 'date':
                    
                    $htmlval = $this->formatDate($kvalue, $avalue);
                    break;

                case 'time':
                    
                    $htmlval = $this->formatTime($kvalue, $avalue);
                    break;

                case 'timestamp':
                    
                    $htmlval = $this->formatTimeStamp($kvalue, $avalue);
                    
                    break;

                case 'ifile':
                    
                    $htmlval = $this->formatIfile($kvalue, $avalue);
                    break;

                case 'color':
                    $htmlval = $this->formatColor($kvalue, $avalue);
                    break;

                default:
                    $htmlval = $this->formatDefault($kvalue, $avalue);
                    
                    break;
            }
            
            $abegin = $aend = '';
            if ($htmlval == '' && $this->oattr->getOption('showempty')) {
                $htmlval = $this->oattr->getOption('showempty');
            } else if (($this->cFormat != "") && ($atype != "doc") && ($atype != "array") && ($atype != "option")) {
                //printf($htmlval);
                $htmlval = sprintf($this->cFormat, $htmlval);
            }
            // add link if needed
            if ($this->htmlLink && ($this->oattr->link != "")) {
                $ititle = "";
                $hlink = $this->oattr->link;
                if ($hlink[0] == "[") {
                    if (preg_match('/\[(.*)\](.*)/', $hlink, $reg)) {
                        $hlink = $reg[2];
                        $ititle = str_replace("\"", "'", $reg[1]);
                    }
                }
                if ($ulink = $this->doc->urlWhatEncode($hlink, $kvalue)) {
                    if ($this->target == "ext") {
                        if (preg_match("/FDL_CARD.*id=([0-9]+)/", $ulink, $reg)) {
                            $abegin = $this->doc->getDocAnchor($reg[1], $this->target, true, $htmlval);
                            $htmlval = '';
                            $aend = "";
                        } else if (true || preg_match("/^http:/", $ulink, $reg)) {
                            $ec = getSessionValue("ext:targetUrl");
                            
                            if ($ec) {
                                $ec = str_replace("%V%", $ulink, $ec);
                                $ec = str_replace("%L%", $this->oattr->getLabel() , $ec);
                                $ecu = str_replace("'", "\\'", $this->doc->urlWhatEncode($ec));
                                $abegin = "<a  onclick='parent.$ecu'>";
                            } else {
                                $ltarget = $this->oattr->getOption("ltarget");
                                $abegin = "<a target=\"$ltarget\"  href=\"$ulink\">";
                            }
                            
                            $aend = "</a>";
                        }
                    } else if ($this->target == "mail") {
                        $scheme = "";
                        if (preg_match("/^([[:alpha:]]*):(.*)/", $ulink, $reg)) {
                            $scheme = $reg[1];
                        }
                        $abegin = "<a target=\"$this->target\"  href=\"";
                        if ($scheme == "") $abegin.= $action->GetParam("CORE_URLINDEX", ($action->GetParam("CORE_ABSURL") . "/")) . $ulink;
                        else $abegin.= $ulink;
                        $abegin.= "\">";
                        $aend = "</a>";
                    } else {
                        $ltarget = $this->oattr->getOption("ltarget");
                        if ($ltarget != "") $this->target = $ltarget;
                        $ltitle = $this->oattr->getOption("ltitle");
                        if ($ltitle != "") $ititle = str_replace("\"", "'", $ltitle);
                        $abegin = "<a target=\"$this->target\" title=\"$ititle\" onmousedown=\"document.noselect=true;\" href=\"";
                        $abegin.= $ulink . "\" ";;
                        if ($this->htmlLink > 1) {
                            $scheme = "";
                            if (preg_match("/^([[:alpha:]]*):(.*)/", $ulink, $reg)) {
                                $scheme = $reg[1];
                            }
                            if (($scheme == "") || ($scheme == "http")) {
                                if ($scheme == "") $ulink.= "&ulink=1";
                                $abegin.= " oncontextmenu=\"popdoc(event,'$ulink');return false;\" ";
                            }
                        }
                        $abegin.= ">";
                        $aend = "</a>";
                    }
                } else {
                    $abegin = "";
                    $aend = "";
                }
            } else {
                $abegin = "";
                $aend = "";
            }
            
            $thtmlval[$kvalue] = $abegin . $htmlval . $aend;
        }
        
        return implode("<BR>", $thtmlval);
    }
    /**
     * format Default attribute
     * @param $kvalue
     * @param $avalue
     * @return string HTML value
     */
    public function formatDefault($kvalue, $avalue)
    {
        if ($this->useEntities) $avalue = htmlentities(($avalue) , ENT_COMPAT, "UTF-8");
        else $avalue = ($avalue);
        $htmlval = str_replace(array(
            "[",
            "$"
        ) , array(
            "&#091;",
            "&#036;"
        ) , $avalue);
        return $htmlval;
    }
    /**
     * format iDoc attribute
     * @param $kvalue
     * @param $avalue
     * @return string HTML value
     */
    public function formatIDoc($kvalue, $avalue)
    {
        
        $idocfamid = $this->oattr->format;
        $value = $avalue;
        if ($value != "") {
            // printf("la ");
            $temp = base64_decode($value);
            $entete = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\" ?>";
            $xml = $entete;
            $xml.= $temp;
            $title = recup_argument_from_xml($xml, "title"); //in freedom_util.php
            
        }
        $this->attrid = $this->attrid . $this->index;
        $htmlval = "<form style=\"display:inline\"><INPUT id=\"_" . $this->attrid . "\" TYPE=\"hidden\"  name=\"_" . $this->attrid . "\" value=\"" . $value . " \">";
        $htmlval.= "<a onclick=\"subwindow(400,400,'_$this->attrid','');viewidoc('_$this->attrid','$idocfamid')\" ";
        $title = $this->doc->getHTMLTitle();
        $htmlval.= "oncontextmenu=\"viewidoc_in_popdoc(event,'$this->attrid','_$this->attrid','$idocfamid');return false\">$title</a>";
        $htmlval.= "</form>";
        return $htmlval;
    }
    /**
     * format Image attribute
     * @param $kvalue
     * @param $avalue
     * @return string HTML value
     */
    public function formatImage($kvalue, $avalue)
    {
        
        global $action;
        if ($this->target == "mail") {
            $htmlval = "cid:" . $this->oattr->id;
            if ($this->index >= 0) $htmlval.= "+$this->index";
        }
        if ($this->target == "te") {
            $htmlval = "file://" . $this->doc->vault_filename($this->oattr->id, true, $kvalue);
        } else {
            $vid = "";
            if (preg_match(PREGEXPFILE, $avalue, $reg)) {
                $vid = $reg[2];
                $fileInfo = new VaultFileInfo();
                $vf = newFreeVaultFile($this->doc->dbaccess);
                if ($vf->Show($reg[2], $fileInfo) == "") {
                    if (!file_exists($fileInfo->path)) {
                        if (!$vf->storage->fs->isAvailable()) {
                            if (!$this->vaultErrorSent) addWarningMsg(sprintf(_("cannot access to vault file system")));
                            $this->vaultErrorSent = true;
                        } else {
                            addWarningMsg(sprintf(_("file %s not found") , $fileInfo->name));
                        }
                    }
                }
                if (($this->oattr->repeat) && ($this->index <= 0)) $idx = $kvalue;
                else $idx = $this->index;
                $inline = $this->oattr->getOption("inline");
                if ($inline == "yes") $opt = "&inline=yes";
                else $opt = "";
                $htmlval = $action->GetParam("CORE_BASEURL") . "app=FDL" . "&action=EXPORTFILE$opt&cache=no&vid=$vid&docid=" . $this->doc->id . "&attrid=" . $this->oattr->id . "&index=$idx"; // upload name
                
            } else {
                $htmlval = $action->GetImageUrl($avalue);
            }
        }
        return $htmlval;
    }
    /**
     * format File attribute
     * @param $kvalue
     * @param $avalue
     * @return string HTML value
     */
    public function formatFile($kvalue, $avalue)
    {
        static $vf = null;
        
        if (!$vf) $vf = newFreeVaultFile($this->doc->dbaccess);
        $vid = "";
        $fileInfo = false;
        $mime = '';
        $fname = _("no file");
        $htmlval = '';
        if (preg_match(PREGEXPFILE, $avalue, $reg)) {
            // reg[1] is mime type
            $vid = $reg[2];
            $mime = $reg[1];
            include_once ("FDL/Lib.Dir.php");
            
            $fileInfo = new VaultFileInfo();
            if ($vf->Show($reg[2], $fileInfo) == "") {
                $fname = $fileInfo->name;
                if (!file_exists($fileInfo->path)) {
                    if (!$vf->storage->fs->isAvailable()) {
                        if (!$this->vaultErrorSent) addWarningMsg(sprintf(_("Cannot access to vault file system")));
                        $this->vaultErrorSent = true;
                    } else {
                        addWarningMsg(sprintf(_("file %s not found") , $fileInfo->name));
                    }
                    
                    $fname.= ' ' . _("(file not found)");
                }
            } else $htmlval = _("vault file error");
        } else {
            if ($this->oattr->getOption('showempty')) {
                $htmlval = $this->oattr->getOption('showempty');
            } else {
                $htmlval = _("no filename");
            }
        }
        
        if ($this->target == "mail") {
            $htmlval = "<a target=\"_blank\" href=\"";
            $htmlval.= "cid:" . $this->oattr->id;
            if ($this->index >= 0) $htmlval.= "+$this->index";
            $htmlval.= "\">" . $fname . "</a>";
        } else {
            if ($fileInfo) {
                if ($fileInfo->teng_state < 0 || $fileInfo->teng_state > 1) {
                    $htmlval = "";
                    include_once ("WHAT/Class.TEClient.php");
                    switch (intval($fileInfo->teng_state)) {
                        case TransformationEngine::error_convert: // convert fail
                            $textval = _("file conversion failed");
                            break;

                        case TransformationEngine::error_noengine: // no compatible engine
                            $textval = _("file conversion not supported");
                            break;

                        case TransformationEngine::error_connect: // no compatible engine
                            $textval = _("cannot contact server");
                            break;

                        case TransformationEngine::status_waiting: // waiting
                            $textval = _("waiting conversion file");
                            break;

                        case TransformationEngine::status_inprogress: // in progress
                            $textval = _("generating file");
                            break;

                        default:
                            $textval = sprintf(_("unknown file state %s") , $fileInfo->teng_state);
                    }
                    if ($this->htmlLink) {
                        //$errconvert=trim(file_get_contents($info->path));
                        //$errconvert=sprintf('<p>%s</p>',str_replace(array("'","\r","\n"),array("&rsquo;",""),nl2br(htmlspecialchars($errconvert,ENT_COMPAT,"UTF-8"))));
                        if ($fileInfo->teng_state > 1) $waiting = "<img class=\"mime\" src=\"Images/loading.gif\">";
                        else $waiting = "<img class=\"mime\" needresize=1 src=\"Images/bullet_error.png\">";;
                        $htmlval = sprintf('<a _href_="%s" vid="%d" onclick="popdoc(event,this.getAttribute(\'_href_\')+\'&inline=yes\',\'%s\')">%s %s</a>', $this->doc->getFileLink($this->oattr->id, $this->index) , $fileInfo->id_file, str_replace("'", "&rsquo;", _("file status")) , $waiting, $textval);
                        if ($fileInfo->teng_state < 0) {
                            $htmlval.= sprintf('<a href="?app=FDL&action=FDL_METHOD&id=%d&method=resetConvertVaultFile(\'%s,%s)"><img class="mime" title="%s" src="%s"></a>', $this->doc->id, $this->oattr->id, $this->index, _("retry file conversion") , "Images/arrow_refresh.png");
                        }
                    } else {
                        $htmlval = $textval;
                    }
                } elseif ($this->htmlLink) {
                    
                    $mimeicon = getIconMimeFile($fileInfo->mime_s == "" ? $mime : $fileInfo->mime_s);
                    if (($this->oattr->repeat) && ($this->index <= 0)) $idx = $kvalue;
                    else $idx = $this->index;
                    $standardview = true;
                    $infopdf = false;
                    $viewfiletype = $this->oattr->getOption("viewfiletype");
                    $imageview = false;
                    $pages = 0;
                    if ($viewfiletype == "image" || $viewfiletype == "pdf") {
                        global $action;
                        $waiting = false;
                        if (substr($fileInfo->mime_s, 0, 5) == "image") {
                            $imageview = true;
                            $viewfiletype = 'png';
                            $pages = 1;
                        } elseif (substr($fileInfo->mime_s, 0, 4) == "text") {
                            $imageview = true;
                            $viewfiletype = 'embed';
                            $pages = 1;
                        } else {
                            $infopdf = new VaultFileInfo();
                            $err = $vf->Show($vid, $infopdf, 'pdf');
                            if ($err == "") {
                                if ($infopdf->teng_state == TransformationEngine::status_done || $infopdf->teng_state == TransformationEngine::status_waiting || $infopdf->teng_state == TransformationEngine::status_inprogress) {
                                    $imageview = true;
                                    if ($viewfiletype == 'image') $viewfiletype = 'png';
                                    else if ($viewfiletype == 'pdf') $viewfiletype = 'embed';
                                    
                                    $pages = getPdfNumberOfPages($infopdf->path);
                                    if ($infopdf->teng_state == TransformationEngine::status_waiting || $infopdf->teng_state == TransformationEngine::status_inprogress) $waiting = true;
                                }
                            }
                        }
                        
                        if ($imageview && (!$this->abstractMode)) {
                            $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/widgetFile.js");
                            $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/detectPdfPlugin.js");
                            $lay = new Layout("FDL/Layout/viewfileimage.xml", $action);
                            $lay->set("docid", $this->doc->id);
                            $lay->set("waiting", ($waiting ? 'true' : 'false'));
                            $lay->set("attrid", $this->oattr->id);
                            $lay->set("index", $idx);
                            $lay->set("viewtype", $viewfiletype);
                            $lay->set("mimeicon", $mimeicon);
                            $lay->set("vid", ($infopdf ? $infopdf->id_file : $vid));
                            $lay->set("filetitle", $fname);
                            $lay->set("height", $this->oattr->getOption('viewfileheight', '300px'));
                            $lay->set("filelink", $this->doc->getFileLink($this->oattr->id, $idx, false, false));
                            
                            $lay->set("pdflink", '');
                            if ($pdfattr = $this->oattr->getOption('pdffile')) {
                                //$infopdf=$this->doc->vault_properties($this->doc->getAttribute($pdfattr));
                                if (!preg_match('/^(text|image)/', $fileInfo->mime_s)) {
                                    //$pdfidx=($idx <0)?0:$idx;
                                    if ($waiting || preg_match('/(pdf)/', $infopdf->mime_s)) {
                                        $lay->set("pdflink", $this->doc->getFileLink($pdfattr, $idx, false, false));
                                    }
                                }
                            }
                            $lay->set("pages", $pages); // todo
                            $htmlval = $lay->gen();
                            $standardview = false;
                        }
                    }
                    if ($standardview) {
                        global $action;
                        $size = round($fileInfo->size / 1024) . _("AbbrKbyte");
                        $utarget = ($action->Read("navigator", "") == "NETSCAPE") ? "_self" : "_blank";
                        $opt = "";
                        $inline = $this->oattr->getOption("inline");
                        if ($inline == "yes") $opt = "&inline=yes";
                        $htmlval = "<a onmousedown=\"document.noselect=true;\" title=\"$size\" target=\"$utarget\" type=\"$mime\" href=\"" . $this->doc->getFileLink($this->oattr->id, $idx, false, ($inline == "yes") , $avalue) . "\">";
                        if ($mimeicon) $htmlval.= "<img class=\"mime\" needresize=1  src=\"Images/$mimeicon\">&nbsp;";
                        $htmlval.= $fname . "</a>";
                    }
                } else {
                    $htmlval = $fileInfo->name;
                }
            }
        }
        return $htmlval;
    }
    /**
     * format Longtext attribute
     * @param $kvalue
     * @param $avalue
     * @return string HTML value
     */
    public function formatLongtext($kvalue, $avalue)
    {
        if ($this->useEntities) $bvalue = nl2br(htmlentities((str_replace("<BR>", "\n", $avalue)) , ENT_COMPAT, "UTF-8"));
        else $bvalue = (str_replace("<BR>", "\n", $avalue));
        $shtmllink = $this->htmlLink ? "true" : "false";
        $bvalue = preg_replace("/(\[|&#x5B;)ADOC ([^\]]*)\]/e", "\$this->doc->getDocAnchor('\\2',\"$this->target\",$shtmllink)", $bvalue);
        $htmlval = str_replace(array(
            "[",
            "$"
        ) , array(
            "&#091;",
            "&#036;"
        ) , $bvalue);
        return $htmlval;
    }
    /**
     * format Password attribute
     * @param $kvalue
     * @param $avalue
     * @return string HTML value
     */
    public function formatPassword($kvalue, $avalue)
    {
        $htmlval = preg_replace("/./", "*", htmlentities(($avalue) , ENT_COMPAT, "UTF-8"));
        return $htmlval;
    }
    /**
     * format Enum attribute
     * @param $kvalue
     * @param $avalue
     * @return string HTML value
     */
    public function formatEnum($kvalue, $avalue)
    {
        $enumlabel = $this->oattr->getEnumlabel();
        $colors = $this->oattr->getOption("boolcolor");
        if ($colors != "") {
            if (isset($enumlabel[$avalue])) {
                reset($enumlabel);
                $tcolor = explode(",", $colors);
                if (current($enumlabel) == $enumlabel[$avalue]) {
                    $color = $tcolor[0];
                    $htmlval = sprintf('<pre style="background-color:%s;display:inline">&nbsp;-&nbsp;</pre>', $color);
                } else {
                    $color = $tcolor[1];
                    $htmlval = sprintf('<pre style="background-color:%s;display:inline">&nbsp;&bull;&nbsp;</pre>', $color);
                }
            } else $htmlval = $avalue;
        } else {
            if (isset($enumlabel[$avalue])) $htmlval = $enumlabel[$avalue];
            else $htmlval = $avalue;
        }
        return $htmlval;
    }
    /**
     * format Array attribute
     * @param $kvalue
     * @param $avalue
     * @return string HTML value
     */
    public function formatArray($kvalue, $avalue)
    {
        
        global $action;
        $htmlval = '';
        if (count($this->doc->getAValues($this->oattr->id)) == 0 && $this->oattr->getOption('showempty')) {
            $htmlval = $this->oattr->getOption('showempty');
            return $htmlval;
        }
        $viewzone = $this->oattr->getOption("rowviewzone");
        $sort = $this->oattr->getOption("sorttable");
        if ($sort == "yes") {
            $action->parent->AddJsRef($action->GetParam("CORE_PUBURL") . "/FREEDOM/Layout/sorttable.js");
        }
        
        $lay = new Layout("FDL/Layout/viewdocarray.xml", $action);
        $lay->set("issort", ($sort == "yes"));
        if (!method_exists($this->doc->attributes, "getArrayElements")) {
            return $htmlval;
        }
        $height = $this->oattr->getOption("height", false);
        $lay->set("tableheight", $height);
        $lay->set("caption", $this->oattr->getLabel());
        $lay->set("aid", $this->oattr->id);
        
        if (($viewzone != "") && preg_match("/([A-Z_-]+):([^:]+):{0,1}[A-Z]{0,1}/", $viewzone, $reg)) {
            // detect special row zone
            $dxml = new DomDocument();
            $rowlayfile = getLayoutFile($reg[1], ($reg[2]));
            if (!file_exists($rowlayfile)) {
                $htmlval = sprintf(_("cannot open %s layout file : %s") , $rowlayfile);
                AddwarningMsg(sprintf(_("cannot open %s layout file : %s") , $rowlayfile));
                return $htmlval;
            }
            if (!@$dxml->load($rowlayfile)) {
                AddwarningMsg(sprintf(_("cannot load xml template : %s") , print_r(libxml_get_last_error() , true)));
                $htmlval = sprintf(_("cannot load xml layout file : %s") , $rowlayfile);
                return $htmlval;
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
                     * @var DOMElement $item
                     */
                    $item = $theadcells->item($i);
                    $th = xt_innerXML($item);
                    $thstyle = $item->getAttribute("style");
                    $thclass = $item->getAttribute("class");
                    if ($thstyle != "") $thstyle = "style=\"$thstyle\"";
                    if ($thclass) $thstyle.= ' class="' . $thclass . '"';
                    $talabel[] = array(
                        "alabel" => $th,
                        "astyle" => $thstyle,
                        "cwidth" => "auto"
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
                     * @var DOMElement $item
                     */
                    $item = $tbodycells->item($i);
                    $tr[] = xt_innerXML($item);
                    $tcellstyle[] = $item->getAttribute("style");
                    $tcellclass[] = $item->getAttribute("class");
                }
            }
            $ta = $this->doc->attributes->getArrayElements($this->oattr->id);
            $nbitem = 0;
            $tval = array();
            foreach ($ta as $k => $v) {
                $tval[$k] = $this->doc->getTValue($k);
                $nbitem = max($nbitem, count($tval[$k]));
                $lay->set("L_" . strtoupper($v->id) , ucfirst($v->getLabel()));
            }
            // view values
            $tvattr = array();
            for ($k = 0; $k < $nbitem; $k++) {
                $tvattr[] = array(
                    "bevalue" => "bevalue_$k"
                );
                reset($ta);
                $tivalue = array();
                
                foreach ($tr as $kd => $vd) {
                    
                    $hval = preg_replace('/\[([^\]]*)\]/e', "\$this->rowattrReplace('\\1',$k)", $vd);
                    $tivalue[] = array(
                        "evalue" => $hval,
                        "color" => "inherit",
                        "tdstyle" => $tcellstyle[$kd],
                        "tdclass" => $tcellclass[$kd],
                        "bgcolor" => "inherit",
                        "align" => "inherit"
                    );
                }
                $lay->setBlockData("bevalue_$k", $tivalue);
            }
            $lay->setBlockData("EATTR", $tvattr);
            $caption = '';
            if ($this->oattr->getOption("vlabel") == "up") {
                $caption = $this->oattr->getLabel();
            }
            
            if ($nbitem > 10) $caption.= " ($nbitem)";
            $lay->set("caption", $caption);
            $htmlval = $lay->gen();
        } else {
            $ta = $this->doc->attributes->getArrayElements($this->oattr->id);
            $talabel = array();
            $tvattr = array();
            
            $emptyarray = true;
            $nbitem = 0;
            
            $tval = array();
            foreach ($ta as $k => $v) {
                if (($v->mvisibility == "H") || ($v->mvisibility == "I") || ($v->mvisibility == "O")) continue;
                $talabel[] = array(
                    "alabel" => ucfirst($v->getLabel()) ,
                    "astyle" => $v->getOption("cellheadstyle") ,
                    "cwidth" => $v->getOption("cwidth", "auto")
                );
                $tval[$k] = $this->doc->getTValue($k);
                $nbitem = max($nbitem, count($tval[$k]));
                if ($emptyarray && ($this->doc->getValue($k) != "")) $emptyarray = false;
            }
            if (!$emptyarray) {
                if ($this->oattr->getOption("vlabel") == "up") {
                    $caption = $this->oattr->getLabel();
                    if ($nbitem > 10) $caption.= " ($nbitem)";
                } else {
                    $caption = "";
                    if ($nbitem > 10) {
                        if (count($talabel) > 0) {
                            $talabel[0]["alabel"].= " ($nbitem)";
                        }
                    }
                }
                
                $lay->setBlockData("TATTR", $talabel);
                $lay->set("caption", $caption);
                $tvattr = array();
                for ($k = 0; $k < $nbitem; $k++) {
                    $tvattr[] = array(
                        "bevalue" => "bevalue_$k"
                    );
                    $tivalue = array();
                    /**
                     * @var NormalAttribute $va
                     */
                    foreach ($ta as $ka => $va) {
                        if (($va->mvisibility == "H") || ($va->mvisibility == "I") || ($va->mvisibility == "O")) continue;
                        $hval = $this->doc->getHtmlValue($va, $tval[$ka][$k], $this->target, $this->htmlLink, $k);
                        if ($va->type == "image") {
                            $iwidth = $va->getOption("iwidth", "80px");
                            if ($tval[$ka][$k] == "") $hval = "";
                            else if ($va->link == "") {
                                if (strstr($hval, '?')) $optwidth = "&width=" . intval($iwidth);
                                else $optwidth = '';
                                $hval = "<a  href=\"$hval\"><img border='0' width=\"$iwidth\" src=\"" . $hval . $optwidth . "\"></a>";
                            } else {
                                $hval = preg_replace("/>(.+)</", ">&nbsp;<img class=\"button\" width=\"$iwidth\" src=\"\\1\">&nbsp;<", $hval);
                            }
                        }
                        $tivalue[] = array(
                            "evalue" => $hval,
                            "tdstyle" => $va->getOption("cellbodystyle") ,
                            "color" => $va->getOption("color", "inherit") ,
                            "bgcolor" => $va->getOption("bgcolor", "inherit") ,
                            "tdclass" => $va->getOption("className", '') ,
                            "align" => $va->getOption("align", "inherit")
                        );
                    }
                    $lay->setBlockData("bevalue_$k", $tivalue);
                }
                $lay->setBlockData("EATTR", $tvattr);
                
                $htmlval = $lay->gen();
            } else {
                $htmlval = "";
            }
        }
        return $htmlval;
    }
    /**
     * format Doc attribute
     * @param $kvalue
     * @param $avalue
     * @return string HTML value
     */
    public function formatDoc($kvalue, $avalue)
    {
        $htmlval = "";
        if ($avalue != "") {
            if ($kvalue > - 1) $idocid = $this->doc->getTValue($this->cFormat, "", $kvalue);
            else $idocid = $this->doc->getValue($this->cFormat);
            
            if ($idocid > 0) {
                //$lay = new Layout("FDL/Layout/viewadoc.xml", $action);
                //$lay->set("id",$idocid);
                $idoc = new_Doc($this->doc->dbaccess, $idocid);
                $htmlval = $idoc->viewDoc("FDL:VIEWTHUMBCARD:T", "finfo");
                //$htmlval =$lay->gen();
                
            }
        }
        return $htmlval;
    }
    /**
     * format Account attribute
     * @param $kvalue
     * @param $avalue
     * @return string HTML value
     */
    public function formatAccount($kvalue, $avalue)
    {
        if (!$this->oattr->format) $this->oattr->format = "x";
        return $this->formatDocid($kvalue, $avalue);
    }
    /**
     * format Docid attribute
     * @param $kvalue
     * @param $avalue
     * @return string HTML value
     */
    public function formatDocid($kvalue, $avalue)
    {
        if ($this->oattr->format != "") {
            
            $this->cFormat = "";
            $multiple = ($this->oattr->getOption("multiple") == "yes");
            $dtarget = $this->target;
            if ($this->target != "mail") {
                $ltarget = $this->oattr->getOption("ltarget");
                if ($ltarget != "") $dtarget = $ltarget;
            }
            if ($multiple) {
                $avalue = str_replace("\n", "<BR>", $avalue);
                $tval = explode("<BR>", $avalue);
                $thval = array();
                foreach ($tval as $kv => $vv) {
                    if (trim($vv) == "") $thval[] = $vv;
                    elseif ($this->oattr->link != "") {
                        $link = preg_replace("/%" . $this->oattr->id . "%/i", $vv, $this->oattr->link);
                        $link = $this->doc->urlWhatEncode($this->oattr->link, $kvalue);
                        if ($link) $thval[] = '<a target="' . $dtarget . '" href="' . $link . '">' . $this->doc->getHTMLTitle($vv) . '</a>';
                        else $thval[] = $this->doc->getHTMLTitle($vv);
                    } else $thval[] = $this->doc->getDocAnchor(trim($vv) , $dtarget, $this->htmlLink, false, true, $this->oattr->getOption("docrev") , true);
                }
                if ($this->oattr->link) $this->htmlLink = false;
                $htmlval = implode("<br/>", $thval);
            } else {
                if ($avalue == "") $htmlval = $avalue;
                elseif ($this->oattr->link != "") $htmlval = $this->doc->getHTMLTitle($avalue);
                else $htmlval = $this->doc->getDocAnchor(trim($avalue) , $dtarget, $this->htmlLink, false, true, $this->oattr->getOption("docrev") , true);
            }
        } else $htmlval = $avalue;
        return $htmlval;
    }
    /**
     * format Image attribute
     * @param $kvalue
     * @param $avalue
     * @return string HTML value
     */
    public function formatThesaurus($kvalue, $avalue)
    {
        $this->cFormat = "";
        $multiple = ($this->oattr->getOption("multiple") == "yes");
        if ($multiple) {
            $avalue = str_replace("\n", "<BR>", $avalue);
            $tval = explode("<BR>", $avalue);
            $thval = array();
            foreach ($tval as $kv => $vv) {
                if (trim($vv) == "") $thval[] = $vv;
                else {
                    $thc = new_doc($this->doc->dbaccess, trim($vv));
                    if ($thc->isAlive()) $thval[] = $this->doc->getDocAnchor(trim($vv) , $this->target, $this->htmlLink, $thc->getSpecTitle());
                    else $thval[] = "th error $vv";
                }
            }
            $htmlval = implode("<br/>", $thval);
        } else {
            if ($avalue == "") $htmlval = $avalue;
            else {
                $thc = new_doc($this->doc->dbaccess, $avalue);
                if ($thc->isAlive()) $htmlval = $this->doc->getDocAnchor(trim($avalue) , $this->target, $this->htmlLink, $thc->getSpecTitle());
                else $htmlval = "th error $avalue";
            }
        }
        return $htmlval;
    }
    /**
     * format Option attribute
     * @param $kvalue
     * @param $avalue
     * @return string HTML value
     */
    public function formatOption($kvalue, $avalue)
    {
        global $action;
        $lay = new Layout("FDL/Layout/viewdocoption.xml", $action);
        $htmlval = "";
        
        if ($kvalue > - 1) $di = $this->doc->getTValue($this->oattr->format, "", $kvalue);
        else $di = $this->doc->getValue($this->oattr->format);
        if ($di > 0) {
            $lay->set("said", $di);
            $lay->set("uuvalue", urlencode($avalue));
            
            $htmlval = $lay->gen();
        }
        return $htmlval;
    }
    /**
     * format Image attribute
     * @param $kvalue
     * @param $avalue
     */
    public function formatMoney($kvalue, $avalue)
    {
        if ($avalue == '' && $this->oattr->getOption('showempty')) {
            $htmlval = $this->oattr->getOption('showempty');
        } else {
            $htmlval = money_format('%!.2n', doubleval($avalue));
            $htmlval = str_replace(" ", "&nbsp;", $htmlval); // need to replace space by non breaking spaces
            
        }
        return $htmlval;
    }
    /**
     * format HTML attribute
     *
     * @param $kvalue
     * @param $avalue
     *
     * @return string
     */
    public function formatHtmltext($kvalue, $avalue)
    {
        if ($avalue == '' && $this->oattr->getOption('showempty')) {
            $avalue = $this->oattr->getOption('showempty');
        }
        $shtmllink = $this->htmlLink ? "true" : "false";
        $avalue = preg_replace("/(\[|&#x5B;)ADOC ([^\]]*)\]/e", "\$this->doc->getDocAnchor('\\2',\"$this->target\",$shtmllink)", $avalue);
        if (stripos($avalue, "data-initid") !== false) {
            $doc = new DOMDocument();
            
            $doc->loadHTML(mb_convert_encoding($avalue, 'HTML-ENTITIES', 'UTF-8'));
            
            $aElements = $doc->getElementsByTagName("a");
            
            foreach ($aElements as $currentA) {
                /* @var $currentA DOMElement */
                if ($currentA->hasAttribute("data-initid")) {
                    $newA = $this->doc->getDocAnchor($currentA->getAttribute("data-initid") , $this->target, $shtmllink, false, true, $currentA->getAttribute("data-docrev"));
                    $newAFragment = $doc->createDocumentFragment();
                    $newAFragment->appendXML($newA);
                    $currentA->parentNode->replaceChild($newAFragment, $currentA);
                }
            }
            
            $avalue = $doc->saveHTML();
        }
        $htmlval = '<div class="htmltext">' . $avalue . '</div>';
        return $htmlval;
    }
    /**
     * format Image attribute
     * @param $kvalue
     * @param $avalue
     */
    public function formatDate($kvalue, $avalue)
    {
        if (($this->cFormat != "") && (trim($avalue) != "")) {
            if ($avalue) $htmlval = strftime($this->cFormat, stringDateToUnixTs($avalue));
            else $htmlval = $avalue;
        } elseif (trim($avalue) == "") {
            $htmlval = "";
        } else {
            $htmlval = stringDateToLocaleDate($avalue);
        }
        $this->cFormat = "";
        return $htmlval;
    }
    /**
     * format Image attribute
     * @param $kvalue
     * @param $avalue
     */
    public function formatTime($kvalue, $avalue)
    {
        if (($this->cFormat != "") && (trim($avalue) != "")) {
            if ($avalue) $htmlval = strftime($this->cFormat, strtotime($avalue));
            else $htmlval = $avalue;
        } else {
            $htmlval = substr($avalue, 0, 5); // do not display second
            
        }
        $this->cFormat = "";
        return $htmlval;
    }
    /**
     * format Image attribute
     * @param $kvalue
     * @param $avalue
     */
    public function formatTimestamp($kvalue, $avalue)
    {
        if (($this->cFormat != "") && (trim($avalue) != "")) {
            if ($avalue) $htmlval = strftime($this->cFormat, stringDateToUnixTs($avalue));
            else $htmlval = $avalue;
        } elseif (trim($avalue) == "") {
            $htmlval = "";
        } else {
            $htmlval = stringDateToLocaleDate($avalue);
        }
        $this->cFormat = "";
        return $htmlval;
    }
    /**
     * format Image attribute
     * @param $kvalue
     * @param $avalue
     */
    public function formatIfile($kvalue, $avalue)
    {
        global $action;
        $lay = new Layout("FDL/Layout/viewifile.xml", $action);
        $lay->set("aid", $this->oattr->id);
        $lay->set("id", $this->doc->id);
        $lay->set("iheight", $this->oattr->getOption("height", "200px"));
        $htmlval = $lay->gen();
        return $htmlval;
    }
    /**
     * format Image attribute
     * @param $kvalue
     * @param $avalue
     */
    public function formatColor($kvalue, $avalue)
    {
        $htmlval = sprintf("<span style=\"background-color:%s\">%s</span>", $avalue, $avalue);
        return $htmlval;
    }
    private function rowattrReplace($s, $index)
    {
        if (substr($s, 0, 2) == "L_") return "[$s]";
        if (substr($s, 0, 2) == "V_") {
            $sl = substr(strtolower($s) , 2);
            $vis = $this->doc->getAttribute($sl)->mvisibility;
            
            if (($vis == "H") || ($vis == "I") || ($vis == "O")) $v = "";
            else $v = $this->doc->GetHtmlAttrValue($sl, "_self", 2, $index);
        } else {
            $sl = strtolower($s);
            if (!isset($this->doc->$sl)) return "[$s]";
            $v = $this->doc->getTValue($sl, "", $index);
        }
        return $v;
    }
}
?>