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
    
    const title = "title";
    
    const propName = "name";
    const propId = "id";
    const propIcon = "icon";
    const propInitid = "initid";
    const propUrl = "url";
    const propState = "state";
    public function __construct()
    {
        $this->propsKeys = array_keys(Doc::$infofields);
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
     * add a property to render
     * by default id and title are rendered
     * @param string $props
     * @throws Exception
     * @return FormatCollection
     */
    public function addProperty($props)
    {
        if ((!in_array($props, $this->propsKeys) && ($props != self::propUrl))) {
            throw new Exception(ErrorCode::getError("FMTC0001", $props));
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
     * return formatted document list to be easily exported in other format
     * @throws Exception
     * @return array
     */
    public function render()
    {
        /**
         * @var Doc $doc
         */
        $r = array();
        $kdoc = 0;
        foreach ($this->dl as $docid => $doc) {
            foreach ($this->fmtProps as $propName) {
                $r[$kdoc]["properties"][$propName] = $this->getPropInfo($propName, $doc);
            }
            
            foreach ($this->fmtAttrs as $attrid) {
                $oa = $doc->getAttribute($attrid);
                if ($oa) {
                    if (($oa->type == "array") || ($oa->type == "tab") || ($oa->type == "frame")) throw new Exception(ErrorCode::getError("FMTC0002", $attrid));
                    $mb0 = microtime(true);
                    $value = $doc->getValue($oa->id);
                    if ($value === '') {
                        $r[$kdoc]["attributes"][$oa->id] = null;
                        
                        $this->debug["empty"][] = microtime(true) - $mb0;
                    } else {
                        $r[$kdoc]["attributes"][$oa->id] = $this->getInfo($oa, $value, $doc);
                        
                        $this->debug[$oa->type][] = microtime(true) - $mb0;
                    }
                } else {
                    $r[$kdoc]["attributes"][$attrid] = new unknowAttributeValue($this->ncAttribute);
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
                return $doc->getIcon('', 24);
            case self::propId:
                return intval($doc->id);
            case self::propInitid:
                return intval($doc->initid);
            case self::propState:
                return $this->getState($doc);
            case self::propUrl:
                return sprintf("?app=FDL&amp;action=OPENDOC&amp;mode=view&amp;id=%d", $doc->id);
            default:
                return $doc->$propName;
        }
    }
    
    private function getState(Doc $doc)
    {
        $s = new statePropertyValue();
        if ($doc->state) {
            $s->state = $doc->state;
            $s->stateLabel = _($doc->state);
            $s->activity = $doc->getStateActivity();
            
            $s->color = $doc->getStateColor();
        }
        return $s;
    }
    private function getInfo(NormalAttribute $oa, $value, $doc = null)
    {
        $info = null;
        if ($oa->isMultiple()) {
            if ($oa->inArray() && $oa->getOption("multiple") == "yes") {
                // double level multiple
                $tv = Doc::_val2array($value);
                foreach ($tv as $k => $av) {
                    $tvv = explode('<BR>', $av); // second level multiple
                    foreach ($tvv as $avv) {
                        $info[$k][] = $this->getSingleInfo($oa, $avv, $doc);
                    }
                }
            } else {
                // single level multiple
                $tv = Doc::_val2array($value);
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
                $info = new textAttributeValue($oa, $value);
                break;

            case 'int':
                $info = new intAttributeValue($oa, $value);
                break;

            case 'double':
                $info = new doubleAttributeValue($oa, $value);
                break;

            case 'enum':
                $info = new enumAttributeValue($oa, $value);
                break;

            case 'docid':
                $info = new docidAttributeValue($oa, $value, $doc);
                break;

            case 'file':
                $info = new fileAttributeValue($oa, $value, $doc, $index);
                break;

            case 'image':
                $info = new imageAttributeValue($oa, $value, $doc, $index);
                break;

            default:
                $info = new standardAttributeValue($oa, $value);
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
}

class standardAttributeValue
{
    public $value;
    public $displayValue;
    
    public function __construct(NormalAttribute $oa, $v)
    {
        $this->value = $v;
        $this->displayValue = $v;
    }
}
class unknowAttributeValue
{
    public $value;
    public $displayValue;
    
    public function __construct($v)
    {
        $this->value = $v;
        $this->displayValue = $v;
    }
}

class statePropertyValue
{
    public $state;
    public $color;
    public $activity;
    public $stateLabel;
}

class formatAttributeValue extends standardAttributeValue
{
    public function __construct(NormalAttribute $oa, $v)
    {
        
        $this->value = $v;
        if ($oa->format) $this->displayValue = sprintf($oa->format, $v);
        else $this->displayValue = $v;
    }
}

class textAttributeValue extends formatAttributeValue
{
}

class intAttributeValue extends formatAttributeValue
{
    public function __construct(NormalAttribute $oa, $v)
    {
        parent::__construct($oa, $v);
        $this->value = intval($v);
    }
}

class doubleAttributeValue extends formatAttributeValue
{
    public function __construct(NormalAttribute $oa, $v)
    {
        parent::__construct($oa, $v);
        $lang = getParam("CORE_LANG");
        if ($lang == "fr_FR") {
            if (is_array($this->displayValue)) {
                foreach ($this->displayValue as $k => $v) {
                    $this->displayValue[$k] = str_replace('.', ',', $v);
                }
            } else {
                $this->displayValue = str_replace('.', ',', $this->displayValue);
            }
        }
        if (is_array($this->value)) {
            foreach ($this->displayValue as $k => $v) {
                $this->value[$k] = doubleval($v);
            }
        } else {
            $this->value = doubleval($this->value);
        }
    }
}

class enumAttributeValue extends standardAttributeValue
{
    public function __construct(NormalAttribute $oa, $v)
    {
        
        $this->value = $v;
        $this->displayValue = $oa->getEnumLabel($v);
    }
}

class fileAttributeValue extends standardAttributeValue
{
    public $size = 0;
    public $creationDate = '';
    public $fileName = '';
    public $url = '';
    public $mime = '';
    public $icon = '';
    public function __construct(NormalAttribute $oa, $v, Doc $doc, $index)
    {
        
        $this->value = $v;
        $finfo = $doc->getFileInfo($v);
        $this->size = $finfo["size"];
        $this->creationDate = $finfo["cdate"];
        $this->fileName = $finfo["name"];
        $this->mime = $finfo["mime_s"];
        $this->displayValue = $this->fileName;
        
        $iconFile = getIconMimeFile($this->mime);
        if ($iconFile) $this->icon = "Images/" . $iconFile;
        $this->url = $doc->getFileLink($oa->id, $index);
    }
}
class imageAttributeValue extends fileAttributeValue
{
    public $thumbnail = '';
    const thumbnailSize = 48;
    public function __construct(NormalAttribute $oa, $v, Doc $doc, $index)
    {
        parent::__construct($oa, $v, $doc, $index);
        $this->thumbnail = $doc->getFileLink($oa->id, $index, false, true) . sprintf('&width=%d', self::thumbnailSize);
    }
}
class docidAttributeValue extends standardAttributeValue
{
    public $familyRelation;
    
    public $url;
    public $icon;
    /**
     * @var NormalAttribute
     */
    private $oa;
    
    public function __construct(NormalAttribute $oa, $v, Doc & $doc)
    {
        $this->familyRelation = $oa->format;
        $this->oa = $oa;
        
        $hasTitle = $oa->getOption("doctitle");
        if ($hasTitle == "auto") $hasTitle = $oa->id . '_title';
        
        $this->value = $v;
        $prop = getDocProperties($v, $oa->getOption("docrev", "latest") == "latest", array(
            "title",
            "icon"
        ));
        $this->displayValue = getDocTitle($v, $oa->getOption("docrev", "latest") == "latest");
        // print_r($prop);
        $this->displayValue = $prop["title"];
        if ($prop["icon"]) $this->icon = $doc->getIcon($prop["icon"], 24);
        $this->url = $this->getDocUrl($v);
        
        $this->oa = null;
    }
    
    private function getDocUrl($v)
    {
        if (!$v) return '';
        $docrev = $this->oa->getOption("docrev");
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
?>