<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Document list class
 *
 * @author Anakeen
 * @version $Id:  $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * Format document list to be easily used in
 * @class FormatCollection
 * @code
 *      $s = new \SearchDoc(self::$dbaccess, $this->famName);
 $s->setObjectReturn();
 $dl = $s->search()->getDocumentList();
 $fc = new \FormatCollection();
 $fc->useCollection($dl);
 $fc->addProperty($fc::propName);
 $fc->addAttribute(('tst_x'));
 $fc->setNc($nc);
 $r = $fc->render();
 * @endcode
 */
class FormatCollection
{
    /**
     * @var DocumentList $dl
     */
    private $dl = null;
    public $debug = array();
    private $propsKeys = array();
    private $fmtProps = array(
        self::propId,
        self::title
    );
    private $fmtAttrs = array();
    private $ncAttribute = '';
    /**
     * @var int family icon size in pixel
     */
    public $familyIconSize = 24;
    /**
     * @var int relation icon size in pixel
     */
    public $relationIconSize = 14;
    /**
     * @var int mime type icon size in pixel
     */
    public $mimeTypeIconSize = 14;
    /**
     * @var int thumbnail width in pixel
     */
    public $imageThumbnailSize = 48;
    /**
     * @var string text in case of no access in relation target
     */
    public $relationNoAccessText = "";
    
    private $decimalSeparator = ',';
    
    private $dateStyle = DateAttributeValue::defaultStyle;
    
    private $stripHtmlTag = false;
    /**
     * @var closure
     */
    private $hookStatus = null;
    const title = "title";
    /**
     * name property
     */
    const propName = "name";
    /**
     * id property
     */
    const propId = "id";
    /**
     * icon property
     */
    const propIcon = "icon";
    /**
     * initid property
     */
    const propInitid = "initid";
    /**
     * url access to document
     */
    const propUrl = "url";
    /**
     * state property
     */
    const propState = "state";
    /**
     * revision date
     */
    const revdate = "revdate";
    /**
     * access date
     */
    const adate = "adate";
    /**
     * creation date
     */
    const cdate = "cdate";
    public function __construct($doc = null)
    {
        $this->propsKeys = array_keys(Doc::$infofields);
        if ($doc !== null) {
            $this->dl = array(
                $doc
            );
        }
    }
    /**
     * default value returned when attribute not found in document
     * @param $s
     * @return \FormatCollection
     */
    public function setNc($s)
    {
        $this->ncAttribute = $s;
        return $this;
    }
    /**
     * document list to format
     * @param DocumentList $l
     * @return FormatCollection
     */
    public function useCollection(DocumentList & $l)
    {
        $this->dl = $l;
        return $this;
    }
    /**
     * set decimal character character to use for double and money type
     * @param string $s a character to separate decimal part from integer part
     * @return FormatCollection
     */
    public function setDecimalSeparator($s)
    {
        $this->decimalSeparator = $s;
        return $this;
    }
    /**
     * display Value of htmltext content value without tags
     * @param bool $strip
     * @return FormatCollection
     */
    public function stripHtmlTags($strip = true)
    {
        $this->stripHtmlTag = $strip;
        return $this;
    }
    /**
     * set date style
     * possible values are :DateAttributeValue::defaultStyle,DateAttributeValue::frenchStyle,DateAttributeValue::isoWTStyle,DateAttributeValue::isoStyle
     * @param string $style
     * @throws Dcp\Fmtc\Exception
     */
    public function setDateStyle($style)
    {
        if (!in_array($style, array(
            DateAttributeValue::defaultStyle,
            DateAttributeValue::frenchStyle,
            DateAttributeValue::isoWTStyle,
            DateAttributeValue::isoStyle
        ))) {
            throw new \Dcp\Fmtc\Exception("FMTC0003", $style);
        }
        $this->dateStyle = $style;
    }
    /**
     * add a property to render
     * by default id and title are rendered
     * @param string $props
     * @throws \Dcp\Fmtc\Exception
     * @return FormatCollection
     */
    public function addProperty($props)
    {
        if ((!in_array($props, $this->propsKeys) && ($props != self::propUrl))) {
            throw new \Dcp\Fmtc\Exception("FMTC0001", $props);
        }
        $this->fmtProps[$props] = $props;
        return $this;
    }
    /**
     * add an attribute to render
     * by default no attributes are rendered
     * @param string $attrid
     * @return FormatCollection
     */
    public function addAttribute($attrid)
    {
        
        $this->fmtAttrs[$attrid] = $attrid;
        return $this;
    }
    /**
     * apply a callback on each document
     * if callback return false, the document is skipped from list
     * @param Closure $hookfunction
     * @return void
     */
    public function setHookAdvancedStatus($hookFunction)
    {
        $this->hookStatus = $hookFunction;
    }
    
    protected function callHookStatus($s)
    {
        if ($this->hookStatus) {
            // call_user_func($function, $this->currentDoc);
            $h = $this->hookStatus;
            return $h($s);
        }
        return true;
    }
    /**
     * return formatted document list to be easily exported in other format
     * @throws \Dcp\Fmtc\Exception
     * @return array
     */
    public function render()
    {
        /**
         * @var Doc $doc
         */
        $r = array();
        $kdoc = 0;
        $countDoc = count($this->dl);
        foreach ($this->dl as $docid => $doc) {
            if ($kdoc % 10 == 0) $this->callHookStatus(sprintf(_("Doc Render %d/%d") , $kdoc, $countDoc));
            foreach ($this->fmtProps as $propName) {
                $r[$kdoc]["properties"][$propName] = $this->getPropInfo($propName, $doc);
            }
            
            foreach ($this->fmtAttrs as $attrid) {
                $oa = $doc->getAttribute($attrid);
                if ($oa) {
                    if (($oa->type == "array") || ($oa->type == "tab") || ($oa->type == "frame")) throw new \Dcp\Fmtc\Exception("FMTC0002", $attrid);
                    $mb0 = microtime(true);
                    $value = $doc->getRawValue($oa->id);
                    if ($value === '') {
                        if ($empty = $oa->getOption("showempty")) $r[$kdoc]["attributes"][$oa->id] = $empty;
                        else $r[$kdoc]["attributes"][$oa->id] = null;
                        
                        $this->debug["empty"][] = microtime(true) - $mb0;
                    } else {
                        $r[$kdoc]["attributes"][$oa->id] = $this->getInfo($oa, $value, $doc);
                        
                        $this->debug[$oa->type][] = microtime(true) - $mb0;
                    }
                } else {
                    $r[$kdoc]["attributes"][$attrid] = new UnknowAttributeValue($this->ncAttribute);
                }
            }
            
            $kdoc++;
        }
        return $r;
    }
    private function getPropInfo($propName, Doc $doc)
    {
        switch ($propName) {
            case self::title:
                return $doc->getTitle();
            case self::propIcon:
                return $doc->getIcon('', $this->familyIconSize);
            case self::propId:
                return intval($doc->id);
            case self::propInitid:
                return intval($doc->initid);
            case self::propState:
                return $this->getState($doc);
            case self::propUrl:
                return sprintf("?app=FDL&amp;action=OPENDOC&amp;mode=view&amp;id=%d", $doc->id);
            case self::revdate:
                return $this->getFormatDate(date("c", $doc->$propName));
            case self::cdate:
            case self::adate:
                return $this->getFormatDate($doc->$propName);
            default:
                return $doc->$propName;
        }
    }
    private function getFormatDate($v)
    {
        if ($this->dateStyle === DateAttributeValue::defaultStyle) return stringDateToLocaleDate($v);
        else if ($this->dateStyle === DateAttributeValue::isoStyle) return stringDateToIso($v, false, true);
        else if ($this->dateStyle === DateAttributeValue::isoWTStyle) return stringDateToIso($v, false, false);
        else if ($this->dateStyle === DateAttributeValue::frenchStyle) {
            if (getLcdate() == "iso") { // FR
                $ldate = stringDateToLocaleDate($v, '%d/%m/%Y %H:%M');
                if (strlen($v) < 11) return substr($ldate, 0, strlen($v));
                else return $ldate;
            }
        }
        return stringDateToLocaleDate($v);
    }
    private function getState(Doc $doc)
    {
        $s = new StatePropertyValue();
        if ($doc->state) {
            $s->reference = $doc->state;
            $s->stateLabel = _($doc->state);
            
            if ($doc->locked != - 1) {
                $s->activity = $doc->getStateActivity();
                if ($s->activity) $s->displayValue = $s->activity;
                else $s->displayValue = $s->stateLabel;
            } else {
                $s->displayValue = $s->stateLabel;
            }
            
            $s->color = $doc->getStateColor();
        }
        return $s;
    }
    /**
     * delete last null values
     * @param array $t
     * @return array
     */
    private static function rtrimNull(array $t)
    {
        $i = count($t) - 1;
        for ($k = $i; $k >= 0; $k--) {
            if ($t[$k] === null) unset($t[$k]);
            else break;
        }
        return $t;
    }
    public function getInfo(NormalAttribute $oa, $value, $doc = null)
    {
        $info = null;
        if ($oa->isMultiple()) {
            if ($oa->isMultipleInArray()) {
                // double level multiple
                $tv = Doc::rawValueToArray($value);
                if (count($tv) == 1 && $tv[0] == "\t") {
                    $tv[0] = '';
                }
                foreach ($tv as $k => $av) {
                    if ($av) {
                        if (is_array($av)) {
                            $tvv = $this->rtrimNull($av);
                        } else {
                            $tvv = explode('<BR>', $av); // second level multiple
                            
                        }
                        if (count($tvv) == 0) {
                            $info[$k] = array();
                        } else {
                            foreach ($tvv as $avv) {
                                $info[$k][] = $this->getSingleInfo($oa, $avv, $doc);
                            }
                        }
                    } else {
                        $info[$k] = array();
                    }
                }
            } else {
                // single level multiple
                $tv = Doc::rawValueToArray($value);
                if ($oa->inArray() && count($tv) == 1 && $tv[0] == "\t") {
                    $tv[0] = '';
                }
                foreach ($tv as $k => $av) {
                    $info[] = $this->getSingleInfo($oa, $av, $doc, $k);
                }
            }
            
            return $info;
        } else {
            
            return $this->getSingleInfo($oa, $value, $doc);
        }
    }
    
    private function getSingleInfo(NormalAttribute $oa, $value, $doc = null, $index = - 1)
    {
        $info = null;
        switch ($oa->type) {
            case 'text':
                $info = new TextAttributeValue($oa, $value);
                break;

            case 'int':
                $info = new IntAttributeValue($oa, $value);
                break;

            case 'money':
            case 'double':
                $info = new DoubleAttributeValue($oa, $value, $this->decimalSeparator);
                break;

            case 'enum':
                $info = new EnumAttributeValue($oa, $value);
                break;

            case 'thesaurus':
                $info = new ThesaurusAttributeValue($oa, $value, $doc, $this->relationIconSize, $this->relationNoAccessText);
                break;

            case 'docid':
            case 'account':
                $info = new DocidAttributeValue($oa, $value, $doc, $this->relationIconSize, $this->relationNoAccessText);
                break;

            case 'file':
                $info = new FileAttributeValue($oa, $value, $doc, $index, $this->mimeTypeIconSize);
                break;

            case 'image':
                $info = new ImageAttributeValue($oa, $value, $doc, $index, $this->imageThumbnailSize);
                break;

            case 'timestamp':
            case 'date':
                $info = new DateAttributeValue($oa, $value, $this->dateStyle);
                break;

            case 'htmltext':
                $info = new HtmltextAttributeValue($oa, $value, $this->stripHtmlTag);
                break;

            default:
                $info = new StandardAttributeValue($oa, $value);
                break;
        }
        return $info;
    }
    /**
     * get some stat to estimate time cost
     * @return array
     */
    public function getDebug()
    {
        $average = $cost = $sum = array();
        foreach ($this->debug as $type => $time) {
            $average[$type] = sprintf("%0.3fus", array_sum($time) / count($time) * 1000000);
            $cost[$type] = sprintf("%0.3fms", array_sum($time) * 1000);
            $sum[$type] = sprintf("%d", count($time));
        }
        
        return array(
            "average" => $average,
            "cost" => $cost,
            "count" => $sum
        );
    }
    /**
     * @param array|stdClass $info
     * @param NormalAttribute $oAttr
     * @param int $index
     * @param array $configuration
     * @return string
     */
    public static function getDisplayValue($info, $oAttr, $index = - 1, $configuration = array())
    {
        $attrInArray = ($oAttr->inArray());
        $attrIsMultiple = ($oAttr->getOption('multiple') == 'yes');
        $sepRow = isset($configuration['multipleSeparator'][0]) ? $configuration['multipleSeparator'][0] : "\n";
        $sepMulti = isset($configuration['multipleSeparator'][1]) ? $configuration['multipleSeparator'][1] : ", ";
        $displayDocId = (isset($configuration['displayDocId']) && $configuration['displayDocId'] === true);
        
        if (is_array($info) && $index >= 0) {
            $info = array(
                $info[$index]
            );
        }
        
        $result = '';
        if (!$attrInArray) {
            if ($attrIsMultiple) {
                $multiList = array();
                foreach ($info as $data) {
                    $multiList[] = $displayDocId ? $data->value : $data->displayValue;
                }
                $result = join($sepMulti, $multiList);
            } else {
                $result = $displayDocId ? $info->value : $info->displayValue;
            }
        } else {
            $rowList = array();
            if ($attrIsMultiple) {
                foreach ($info as $multiData) {
                    $multiList = array();
                    foreach ($multiData as $data) {
                        $multiList[] = $displayDocId ? $data->value : $data->displayValue;
                    }
                    $rowList[] = join($sepMulti, $multiList);
                }
            } else {
                if (!is_array($info)) {
                    $info = array(
                        $info
                    );
                }
                foreach ($info as $data) {
                    $rowList[] = $displayDocId ? $data->value : $data->displayValue;
                }
            }
            $result = join($sepRow, $rowList);
        }
        return $result;
    }
}

class StandardAttributeValue
{
    
    public $value;
    public $displayValue;
    
    public function __construct(NormalAttribute $oa, $v)
    {
        $this->value = $v;
        $this->displayValue = $v;
    }
}
class UnknowAttributeValue
{
    public $value;
    public $displayValue;
    
    public function __construct($v)
    {
        $this->value = $v;
        $this->displayValue = $v;
    }
}

class StatePropertyValue
{
    public $reference;
    public $color;
    public $activity;
    public $stateLabel;
    public $displayValue;
}

class FormatAttributeValue extends StandardAttributeValue
{
    public function __construct(NormalAttribute $oa, $v)
    {
        
        $this->value = $v;
        if ($oa->format) $this->displayValue = sprintf($oa->format, $v);
        else $this->displayValue = $v;
    }
}

class TextAttributeValue extends FormatAttributeValue
{
}

class IntAttributeValue extends FormatAttributeValue
{
    public function __construct(NormalAttribute $oa, $v)
    {
        parent::__construct($oa, $v);
        $this->value = intval($v);
    }
}

class DateAttributeValue extends StandardAttributeValue
{
    const defaultStyle = 'D';
    const isoStyle = 'I';
    const isoWTStyle = 'U';
    const frenchStyle = 'F';
    public function __construct(NormalAttribute $oa, $v, $dateStyle = self::defaultStyle)
    {
        parent::__construct($oa, $v);
        if ($oa->format != "") {
            $this->displayValue = strftime($oa->format, stringDateToUnixTs($v));
        } else {
            if ($dateStyle === self::defaultStyle) $this->displayValue = stringDateToLocaleDate($v);
            else if ($dateStyle === self::isoStyle) $this->displayValue = stringDateToIso($v, false, true);
            else if ($dateStyle === self::isoWTStyle) $this->displayValue = stringDateToIso($v, false, false);
            else if ($dateStyle === self::frenchStyle) {
                if (getLcdate() == "iso") { // FR
                    $ldate = stringDateToLocaleDate($v, '%d/%m/%Y %H:%M');
                    if (strlen($v) < 11) $this->displayValue = substr($ldate, 0, strlen($v));
                    else $this->displayValue = $ldate;
                }
            } else $this->displayValue = stringDateToLocaleDate($v);
        }
    }
}

class HtmltextAttributeValue extends StandardAttributeValue
{
    const defaultStyle = 'D';
    const isoStyle = 'I';
    const isoWTStyle = 'U';
    const frenchStyle = 'F';
    public function __construct(NormalAttribute $oa, $v, $stripHtmlTag = false)
    {
        parent::__construct($oa, $v);
        if ($stripHtmlTag) {
            $this->displayValue = html_entity_decode(strip_tags($this->displayValue) , ENT_NOQUOTES, 'UTF-8');
        }
    }
}
class DoubleAttributeValue extends FormatAttributeValue
{
    
    public function __construct(NormalAttribute $oa, $v, $decimalSeparator = ',')
    {
        parent::__construct($oa, $v);
        $lang = getParam("CORE_LANG");
        if ($lang == "fr_FR") {
            if (is_array($this->displayValue)) {
                foreach ($this->displayValue as $k => $v) {
                    $this->displayValue[$k] = str_replace('.', $decimalSeparator, $v);
                }
            } else {
                $this->displayValue = str_replace('.', $decimalSeparator, $this->displayValue);
            }
        }
        if (is_array($this->value)) {
            /** @noinspection PhpWrongForeachArgumentTypeInspection */
            foreach ($this->value as $k => $v) {
                $this->value[$k] = doubleval($v);
            }
        } else {
            $this->value = doubleval($this->value);
        }
    }
}

class EnumAttributeValue extends StandardAttributeValue
{
    public function __construct(NormalAttribute $oa, $v)
    {
        $this->value = $v;
        if ($v) {
            $this->displayValue = $oa->getEnumLabel($v);
        }
    }
}

class FileAttributeValue extends StandardAttributeValue
{
    public $size = 0;
    public $creationDate = '';
    public $fileName = '';
    public $url = '';
    public $mime = '';
    public $icon = '';
    public function __construct(NormalAttribute $oa, $v, Doc $doc, $index, $iconMimeSize = 24)
    {
        
        $this->value = $v;
        if ($v) {
            $finfo = $doc->getFileInfo($v);
            $this->size = $finfo["size"];
            $this->creationDate = $finfo["cdate"];
            $this->fileName = $finfo["name"];
            $this->mime = $finfo["mime_s"];
            $this->displayValue = $this->fileName;
            
            $iconFile = getIconMimeFile($this->mime);
            if ($iconFile) $this->icon = $doc->getIcon($iconFile, $iconMimeSize);
            $this->url = $doc->getFileLink($oa->id, $index);
        }
    }
}
class ImageAttributeValue extends FileAttributeValue
{
    public $thumbnail = '';
    public function __construct(NormalAttribute $oa, $v, Doc $doc, $index, $thumbnailSize = 48)
    {
        parent::__construct($oa, $v, $doc, $index);
        $this->thumbnail = $doc->getFileLink($oa->id, $index, false, true) . sprintf('&width=%d', $thumbnailSize);
    }
}
class DocidAttributeValue extends StandardAttributeValue
{
    public $familyRelation;
    
    public $url;
    public $icon = null;
    protected $visible = true;
    
    public function __construct(NormalAttribute $oa, $v, Doc & $doc, $iconsize = 24, $relationNoAccessText = '')
    {
        $this->familyRelation = $oa->format;
        $this->value = $v;
        $this->displayValue = DocTitle::getRelationTitle($v, $oa->getOption("docrev", "latest") == "latest", $doc);
        if ($this->displayValue !== false) {
            if ($v !== '' && $v !== null) {
                if ($iconsize > 0) {
                    $prop = getDocProperties($v, $oa->getOption("docrev", "latest") == "latest", array(
                        
                        "icon"
                    ));
                    if (!$prop["icon"]) $prop["icon"] = "doc.png";
                    $this->icon = $doc->getIcon($prop["icon"], $iconsize);
                }
                $this->url = $this->getDocUrl($v, $oa->getOption("docrev"));
            }
        } else {
            $this->visible = false;
            if ($relationNoAccessText) $this->displayValue = $relationNoAccessText;
            else $this->displayValue = $oa->getOption("noaccesstext", _("information access deny"));
        }
    }
    
    private function getDocUrl($v, $docrev)
    {
        if (!$v) return '';
        $ul = "?app=FDL&amp;action=OPENDOC&amp;mode=view&amp;id=" . $v;
        
        if ($docrev == "latest" || $docrev == "" || !$docrev) $ul.= "&amp;latest=Y";
        elseif ($docrev != "fixed") {
            // validate that docrev looks like state(xxx)
            if (preg_match('/^state\(([a-zA-Z0-9_:-]+)\)/', $docrev, $matches)) {
                $ul.= "&amp;state=" . $matches[1];
            }
        }
        return $ul;
    }
}
class ThesaurusAttributeValue extends DocidAttributeValue
{
    static $thcDoc = null;
    static $thcDocTitle = array();
    public function __construct(NormalAttribute $oa, $v, Doc & $doc, $iconsize = 24, $relationNoAccessText = '')
    {
        parent::__construct($oa, $v, $doc, $iconsize, $relationNoAccessText);
        if ($this->visible) {
            if (isset(self::$thcDocTitle[$this->value])) {
                // use local cache
                $this->displayValue = self::$thcDocTitle[$this->value];
            } else {
                if (self::$thcDoc === null) {
                    self::$thcDoc = createTmpDoc("", "THCONCEPT");
                }
                $rawValue = getTDoc('', $this->value);
                self::$thcDoc->affect($rawValue);
                $this->displayValue = self::$thcDoc->getTitle();
                // set local cache
                self::$thcDocTitle[$this->value] = $this->displayValue;
            }
        }
    }
}

