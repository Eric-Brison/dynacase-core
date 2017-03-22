<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Document list class
 *
 * @author Anakeen
 * @version $Id:  $
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
    const noAccessText = "N.C.";
    /**
     * @var DocumentList $dl
     */
    protected $dl = null;
    public $debug = array();
    protected $propsKeys = array();
    protected $fmtProps = array(
        self::propId,
        self::title
    );
    protected $fmtAttrs = array();
    protected $ncAttribute = '';
    
    protected $noAccessText = self::noAccessText;
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
    /**
     * @var bool if true set showempty option in displayValue when value is empty
     */
    public $useShowEmptyOption = true;
    
    protected $attributeGrants = array();
    
    protected $decimalSeparator = ',';
    
    protected $dateStyle = DateAttributeValue::defaultStyle;
    
    protected $propDateStyle = null;
    
    protected $stripHtmlTag = false;
    
    protected $longtextMultipleBrToCr = "\n";
    /**
     * Verify attribute visibility "I"
     * @var bool
     */
    protected $verifyAttributeAccess = true;
    /**
     * @var closure
     */
    protected $hookStatus = null;
    /**
     * @var bool
     */
    protected $singleDocument = false;
    /**
     * @var closure
     */
    protected $renderAttributeHook = null;
    /**
     * @var closure
     */
    protected $renderDocumentHook = null;
    /**
     * @var closure
     */
    protected $renderPropertyHook = null;
    
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
     * locked property
     */
    const propLocked = "locked";
    /**
     * initid property
     */
    const propInitid = "initid";
    /**
     * revision property
     */
    const propRevision = "revision";
    /**
     * url access to document
     */
    const propUrl = "url";
    /**
     * family information
     */
    const propFamily = "family";
    /**
     * Last access date
     */
    const propLastAccessDate = "lastAccessDate";
    /**
     * Last modification date
     */
    const propLastModificationDate = "lastModificationDate";
    /**
     * Some informations about revision
     */
    const propRevisionData = "revisionData";
    /**
     * View Controller information
     */
    const propViewController = "viewController";
    /**
     * Workflow information
     */
    const propWorkflow = "workflow";
    /**
     * allocated information
     */
    const propAffected = "affected";
    /**
     * status information : alive, deleted, fixed
     */
    const propStatus = "status";
    /**
     * note information
     */
    const propNote = "note";
    /**
     * usefor information
     */
    const propUsage = "usage";
    /**
     * doctype information
     */
    const propType = "type";
    /**
     * Applictaion Tags list
     * @see \Doc::addAtag()
     */
    const propTags = "tags";
    /**
     * Security information (lock, profil)
     */
    const propSecurity = "security";
    /**
     * Creation date (of revision 0)
     */
    const propCreationDate = "creationDate";
    /**
     * Creation user (of revision 0)
     */
    const propCreatedBy = "createdBy";
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
        $this->propsKeys = self::getAvailableProperties();
        if ($doc !== null) {
            $this->dl = array(
                $doc
            );
            $this->singleDocument = true;
        }
    }
    
    public static function getAvailableProperties()
    {
        $keys = array_keys(Doc::$infofields);
        $keys[] = self::propFamily;
        $keys[] = self::propLastAccessDate;
        $keys[] = self::propLastModificationDate;
        $keys[] = self::propCreationDate;
        $keys[] = self::propCreatedBy;
        $keys[] = self::propRevisionData;
        $keys[] = self::propViewController;
        $keys[] = self::propWorkflow;
        $keys[] = self::propTags;
        $keys[] = self::propSecurity;
        $keys[] = self::propAffected;
        $keys[] = self::propStatus;
        $keys[] = self::propNote;
        $keys[] = self::propUsage;
        $keys[] = self::propType;
        return $keys;
    }
    /**
     * @param string $propDateStyle
     * @return $this
     * @throws \Dcp\Fmtc\Exception
     */
    public function setPropDateStyle($propDateStyle)
    {
        if (!in_array($propDateStyle, array(
            DateAttributeValue::defaultStyle,
            DateAttributeValue::frenchStyle,
            DateAttributeValue::isoWTStyle,
            DateAttributeValue::isoStyle
        ))) {
            throw new \Dcp\Fmtc\Exception("FMTC0003", $propDateStyle);
        }
        $this->propDateStyle = $propDateStyle;
        return $this;
    }
    /**
     * If false, attribute with "I" visibility are  returned
     * @param boolean $verifyAttributeAccess
     */
    public function setVerifyAttributeAccess($verifyAttributeAccess)
    {
        $this->verifyAttributeAccess = $verifyAttributeAccess;
    }
    /**
     * Use when cannot access attribut value
     * Due to visibility "I"
     * @param string $noAccessText
     */
    public function setNoAccessText($noAccessText)
    {
        $this->noAccessText = $noAccessText;
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
     * @return $this
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
        return $this;
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
     * @param Closure $hookFunction
     * @return $this
     */
    public function setHookAdvancedStatus($hookFunction)
    {
        $this->hookStatus = $hookFunction;
        return $this;
    }
    /**
     * apply a callback on each returned value
     * to modify render
     * @param Closure $hookFunction
     * @return $this
     */
    public function setAttributeRenderHook($hookFunction)
    {
        $this->renderAttributeHook = $hookFunction;
        return $this;
    }
    /**
     * apply a callback on each document returned
     * to modify render
     * @param Closure $hookFunction
     * @return $this
     */
    public function setDocumentRenderHook($hookFunction)
    {
        $this->renderDocumentHook = $hookFunction;
        return $this;
    }
    /**
     * apply a callback on each returned property
     * to modify render value
     * @param Closure $hookFunction
     * @return $this
     */
    public function setPropertyRenderHook($hookFunction)
    {
        $this->renderPropertyHook = $hookFunction;
        return $this;
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
     * @param StandardAttributeValue|null $info
     * @param BasicAttribute|null $oa
     * @param Doc $doc
     * @return StandardAttributeValue
     */
    protected function callAttributeRenderHook($info, $oa, \Doc $doc)
    {
        if ($this->renderAttributeHook) {
            $h = $this->renderAttributeHook;
            return $h($info, $oa, $doc);
        }
        return $info;
    }
    /**
     * @param array $info
     * @param Doc $doc
     * @return StandardAttributeValue
     */
    protected function callDocumentRenderHook(array $info, \Doc $doc)
    {
        if ($this->renderDocumentHook) {
            $h = $this->renderDocumentHook;
            return $h($info, $doc);
        }
        return $info;
    }
    /**
     * @param StandardAttributeValue|string|null $info
     * @param string $propId
     * @param Doc $doc
     * @return StandardAttributeValue
     */
    protected function callPropertyRenderHook($info, $propId, \Doc $doc)
    {
        if ($this->renderPropertyHook) {
            $h = $this->renderPropertyHook;
            return $h($info, $propId, $doc);
        }
        return $info;
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
        \Dcp\VerifyAttributeAccess::clearCache();
        foreach ($this->dl as $docid => $doc) {
            if ($kdoc % 10 == 0) $this->callHookStatus(sprintf(_("Doc Render %d/%d") , $kdoc, $countDoc));
            $renderDoc = array();
            foreach ($this->fmtProps as $propName) {
                $renderDoc["properties"][$propName] = $this->callPropertyRenderHook($this->getPropInfo($propName, $doc) , $propName, $doc);
            }
            
            foreach ($this->fmtAttrs as $attrid) {
                $oa = $doc->getAttribute($attrid);
                if ($oa) {
                    if (($oa->type == "array") || ($oa->type == "tab") || ($oa->type == "frame")) throw new \Dcp\Fmtc\Exception("FMTC0002", $attrid);
                    
                    $value = $doc->getRawValue($oa->id);
                    if ($value === '') {
                        if ($this->verifyAttributeAccess === true && !\Dcp\VerifyAttributeAccess::isAttributeAccessGranted($doc, $oa)) {
                            $attributeInfo = new noAccessAttributeValue($this->noAccessText);
                        } else {
                            if ($this->useShowEmptyOption && $empty = $oa->getOption("showempty")) {
                                $attributeInfo = new StandardAttributeValue($oa, null);
                                $attributeInfo->displayValue = $empty;
                            } else {
                                $attributeInfo = null;
                            }
                        }
                    } else {
                        $attributeInfo = $this->getInfo($oa, $value, $doc);
                    }
                    $renderDoc["attributes"][$oa->id] = $this->callAttributeRenderHook($attributeInfo, $oa, $doc);
                } else {
                    $renderDoc["attributes"][$attrid] = $this->callAttributeRenderHook(new UnknowAttributeValue($this->ncAttribute) , null, $doc);
                }
            }
            
            $r[$kdoc] = $this->callDocumentRenderHook($renderDoc, $doc);
            
            $kdoc++;
        }
        return $r;
    }
    protected function getPropInfo($propName, Doc $doc)
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
            case self::propRevision:
                return intval($doc->revision);
            case self::propLocked:
                return intval($doc->locked);
            case self::propState:
                return $this->getState($doc);
            case self::propUrl:
                return sprintf("?app=FDL&amp;action=OPENDOC&amp;mode=view&amp;id=%d", $doc->id);
            case self::revdate:
                return $this->getFormatDate(date("Y-m-d H:i:s", intval($doc->$propName)) , $this->propDateStyle);
            case self::cdate:
            case self::adate:
                return $this->getFormatDate($doc->$propName, $this->propDateStyle);
            case self::propFamily:
                return $this->getFamilyInfo($doc);
            case self::propLastAccessDate:
                return $this->getFormatDate($doc->adate, $this->propDateStyle);
            case self::propLastModificationDate:
                return $this->getFormatDate(date("Y-m-d H:i:s", $doc->revdate) , $this->propDateStyle);
            case self::propCreationDate:
                if ($doc->revision == 0) {
                    return $this->getFormatDate($doc->cdate, $this->propDateStyle);
                } else {
                    $sql = sprintf("select cdate from docread where initid=%d and revision = 0", $doc->initid);
                    simpleQuery($doc->dbaccess, $sql, $cdate, true, true);
                    return $this->getFormatDate($cdate, $this->propDateStyle);
                }
            case self::propCreatedBy:
                return $this->getCreatedByData($doc);
            case self::propRevisionData:
                return $this->getRevisionData($doc);
            case self::propViewController:
                return $this->getViewControllerData($doc);
            case self::propWorkflow:
                return $this->getWorkflowData($doc);
            case self::propTags:
                return $this->getApplicationTagsData($doc);
            case self::propSecurity:
                return $this->getSecurityData($doc);
            case self::propAffected:
                return $this->getAllocatedData($doc);
            case self::propStatus:
                return $this->getStatusData($doc);
            case self::propNote:
                return $this->getNoteData($doc);
            case self::propUsage:
                return $this->getUsageData($doc);
            case self::propType:
                return $this->getTypeData($doc);
            default:
                return $doc->$propName;
        }
    }
    
    protected function getCreatedByData(\Doc $doc)
    {
        if ($doc->revision == 0) {
            $ownerId = $doc->owner;
        } else {
            $sql = sprintf("select owner from docread where initid=%d and revision = 0", $doc->initid);
            simpleQuery($doc->dbaccess, $sql, $ownerId, true, true);
        }
        return $this->getAccountData(abs($ownerId) , $doc);
    }
    
    protected function getStatusData(\Doc $doc)
    {
        if ($doc->doctype == "Z") {
            return "deleted";
        } elseif ($doc->locked == - 1) {
            return "fixed";
        } else {
            return "alive";
        }
    }
    
    protected function getUsageData(\Doc $doc)
    {
        if (strstr($doc->usefor, "S")) {
            return "system";
        } else {
            return "normal";
        }
    }
    protected function getTypeData(\Doc $doc)
    {
        switch ($doc->defDoctype) {
            case 'F':
                return "document";
            case 'D':
                return "folder";
            case "S":
                return "search";
            case "C":
                return "family";
            case "P":
                return "profil";
            case "W":
                return "workflow";
            default:
                return $doc->defDoctype;
        }
    }
    
    protected function getNoteData(\Doc $doc)
    {
        if ($doc->postitid > 0) {
            $note = new_doc($doc->dbaccess, $doc->postitid);
            return array(
                "id" => intval($note->initid) ,
                "title" => $note->getTitle() ,
                "icon" => $note->getIcon("", $this->familyIconSize)
            );
        } else {
            return array(
                "id" => 0,
                "title" => ""
            );
        }
    }
    protected function getWorkflowData(\Doc $doc)
    {
        if ($doc->wid > 0) {
            $workflow = new_doc($doc->dbaccess, $doc->wid);
            return array(
                "id" => intval($workflow->initid) ,
                "title" => $workflow->getTitle() ,
                "icon" => $workflow->getIcon("", $this->familyIconSize)
            );
        } else {
            return array(
                "id" => 0,
                "title" => ""
            );
        }
    }
    protected function getApplicationTagsData(\Doc $doc)
    {
        if ($doc->atags) {
            return explode("\n", $doc->atags);
        } else {
            return array();
        }
    }
    
    protected function getAllocatedData(\Doc $doc)
    {
        if ($doc->allocated > 0) {
            return $this->getAccountData($doc->allocated, $doc);
        } else {
            return array(
                "id" => 0,
                "title" => ""
            );
        }
    }
    
    protected function getAccountData($accountId, \Doc $doc)
    {
        $sql = sprintf("select initid, icon, title from doc128 where us_whatid='%d' and locked != -1", $accountId);
        simpleQuery("", $sql, $result, false, true);
        if ($result) {
            return array(
                "id" => intval($result["initid"]) ,
                "title" => $result["title"],
                "icon" => $doc->getIcon($result["icon"], $this->familyIconSize)
            );
        } else {
            return array(
                "id" => 0,
                "title" => "",
                "icon" => ""
            );
        }
    }
    protected function getSecurityData(\Doc $doc)
    {
        $info = array();
        if ($doc->locked) {
            if ($doc->locked == - 1) {
                $info["lock"] = array(
                    "id" => - 1,
                    "temporary" => false
                );
            } else {
                $info["lock"] = array(
                    "lockedBy" => $this->getAccountData(abs($doc->locked) , $doc) ,
                    "temporary" => ($doc->locked < - 1)
                );
            }
        } else {
            $info["lock"] = array(
                "id" => 0
            );
        }
        $info["readOnly"] = ($doc->canEdit() != "");
        $info["fixed"] = ($doc->locked == - 1);
        if ($doc->profid != 0) {
            
            if ($doc->profid == $doc->id) {
                $info["profil"] = array(
                    "id" => intval($doc->initid) ,
                    "icon" => $doc->getIcon("", $this->familyIconSize) ,
                    "private" => true,
                    "activated" => true,
                    "type" => "private",
                    "title" => $doc->getTitle()
                );
                if ($doc->dprofid > 0) {
                    $profil = new_doc($doc->dbaccess, $doc->dprofid);
                    $info["profil"]["reference"] = array(
                        "id" => intval($profil->initid) ,
                        "icon" => $profil->getIcon("", $this->familyIconSize) ,
                        "activated" => ($profil->id == $profil->profid) ,
                        "title" => $profil->getTitle()
                    );
                    $info["profil"]["type"] = "dynamic";
                }
            } else {
                $profil = new_doc($doc->dbaccess, abs($doc->profid));
                $info["profil"] = array(
                    "id" => intval($profil->initid) ,
                    "icon" => $profil->getIcon("", $this->familyIconSize) ,
                    "type" => "linked",
                    "activated" => ($profil->id == $profil->profid) ,
                    "title" => $profil->getTitle()
                );
            }
        } else {
            $info["profil"] = array(
                "id" => 0,
                "title" => ""
            );
        }
        
        $info["confidentiality"] = ($doc->confidential > 0) ? "private" : "public";
        return $info;
    }
    
    protected function getViewControllerData(\Doc $doc)
    {
        if ($doc->cvid > 0) {
            $cv = new_doc($doc->dbaccess, $doc->cvid);
            return array(
                "id" => intval($cv->initid) ,
                
                "title" => $cv->getTitle() ,
                "icon" => $cv->getIcon("", $this->familyIconSize)
            );
        } else {
            return array(
                "id" => 0,
                "title" => ""
            );
        }
    }
    protected function getRevisionData(\Doc $doc)
    {
        return array(
            "isModified" => ($doc->lmodify == "Y") ,
            "id" => intval($doc->id) ,
            "number" => intval($doc->revision) ,
            "createdBy" => $this->getAccountData(abs($doc->owner) , $doc)
        );
    }
    
    protected function getFamilyInfo(\Doc $doc)
    {
        $family = $doc->getFamilyDocument();
        return array(
            "title" => $family->getTitle() ,
            "name" => $family->name,
            "id" => intval($family->id) ,
            "icon" => $family->getIcon("", $this->familyIconSize)
        );
    }
    protected function getFormatDate($v, $dateStyle = '')
    {
        if (!$dateStyle) {
            $dateStyle = $this->dateStyle;
        }
        if ($dateStyle === DateAttributeValue::defaultStyle) return stringDateToLocaleDate($v);
        else if ($dateStyle === DateAttributeValue::isoStyle) return stringDateToIso($v, false, true);
        else if ($dateStyle === DateAttributeValue::isoWTStyle) return stringDateToIso($v, false, false);
        else if ($dateStyle === DateAttributeValue::frenchStyle) {
            
            $ldate = stringDateToLocaleDate($v, '%d/%m/%Y %H:%M');
            if (strlen($v) < 11) return substr($ldate, 0, strlen($v));
            else return $ldate;
        }
        return stringDateToLocaleDate($v);
    }
    protected function getState(Doc $doc)
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
    protected static function rtrimNull(array $t)
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
                    if ($av !== '') {
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
    
    protected function getSingleInfo(NormalAttribute $oa, $value, $doc = null, $index = - 1)
    {
        $info = null;
        
        if ($this->verifyAttributeAccess === true && !\Dcp\VerifyAttributeAccess::isAttributeAccessGranted($doc, $oa)) {
            $info = new noAccessAttributeValue($this->noAccessText);
        } else {
            
            switch ($oa->type) {
                case 'text':
                    $info = new TextAttributeValue($oa, $value);
                    break;

                case 'longtext':
                    $info = new LongtextAttributeValue($oa, $value, $this->longtextMultipleBrToCr);
                    break;

                case 'int':
                    $info = new IntAttributeValue($oa, $value);
                    break;

                case 'money':
                    $info = new MoneyAttributeValue($oa, $value);
                    break;

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
        }
        return $info;
    }
    /**
     * @param string $longtextMultipleBrToCr
     */
    public function setLongtextMultipleBrToCr($longtextMultipleBrToCr)
    {
        $this->longtextMultipleBrToCr = $longtextMultipleBrToCr;
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
        $displayDocId = (isset($configuration['displayDocId']) && $configuration['displayDocId'] === true) && (!isset($info->visible));
        
        if (is_array($info) && $index >= 0) {
            $info = array(
                $info[$index]
            );
        }
        if ($displayDocId && is_array($info) && count($info) > 0) {
            $displayDocId = (!isset($info[0]->visible));
        }
        
        if (!$attrInArray) {
            if ($attrIsMultiple) {
                $multiList = array();
                if (empty($info)) {
                    $info = array();
                }
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
                if (empty($info)) {
                    $info = array();
                }
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
    /**
     * @param NormalAttribute $oa
     * @param $v
     */
    public function __construct($oa, $v)
    {
        $this->value = ($v === '') ? null : $v;
        $this->displayValue = $v;
    }
}
class UnknowAttributeValue extends StandardAttributeValue
{
    /**
     * noAccessAttributeValue constructor.
     * @param string $v
     */
    public function __construct($v)
    {
        $this->value = ($v === '') ? null : $v;
        $this->displayValue = $v;
    }
}
class noAccessAttributeValue extends StandardAttributeValue
{
    public $visible = true;
    /**
     * noAccessAttributeValue constructor.
     * @param string $v
     */
    public function __construct($v)
    {
        $this->value = '';
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
        $this->value = ($v === '') ? null : $v;
        if ($oa->format) $this->displayValue = sprintf($oa->format, $v);
        else $this->displayValue = $v;
    }
}

class TextAttributeValue extends FormatAttributeValue
{
}

class LongtextAttributeValue extends FormatAttributeValue
{
    public function __construct(NormalAttribute $oa, $v, $multipleLongtextCr = "\n")
    {
        if ($oa->inArray()) {
            $v = str_replace("<BR>", $multipleLongtextCr, $v);
        }
        parent::__construct($oa, $v);
    }
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
    /**
     * ISO with T : YYYY-MM-DDTHH:MM:SS
     */
    const isoStyle = 'I';
    /**
     * ISO without T : YYYY-MM-DD HH:MM:SS
     */
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
                
                $ldate = stringDateToLocaleDate($v, '%d/%m/%Y %H:%M');
                if (strlen($v) < 11) $this->displayValue = substr($ldate, 0, strlen($v));
                else $this->displayValue = $ldate;
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

class MoneyAttributeValue extends FormatAttributeValue
{
    
    public function __construct(NormalAttribute $oa, $v)
    {
        parent::__construct($oa, $v);
        
        $lang = getParam("CORE_LANG");
        if ($lang == "fr_FR") {
        }
        if (is_array($this->displayValue)) {
            foreach ($this->displayValue as $k => $dv) {
                $this->displayValue[$k] = money_format('%!.2n', doubleval($dv));
                if ($oa->format) {
                    $this->displayValue[$k] = sprintf($oa->format, $this->displayValue[$k]);
                }
            }
        } else {
            $this->displayValue = money_format('%!.2n', doubleval($v));
            if ($oa->format) {
                $this->displayValue = sprintf($oa->format, $this->displayValue);
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
    public $exists = true;
    public function __construct(NormalAttribute $oa, $v)
    {
        $this->value = ($v === '') ? null : $v;
        if ($v !== null && $v !== '') {
            $this->displayValue = $oa->getEnumLabel($v);
            $this->exists = $oa->existEnum($v, false);
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
        
        $this->value = ($v === '') ? null : $v;
        if ($v) {
            $finfo = $doc->getFileInfo($v, "", "object");
            if ($finfo) {
                $this->size = $finfo->size;
                $this->creationDate = $finfo->cdate;
                $this->fileName = $finfo->name;
                $this->mime = $finfo->mime_s;
                $this->displayValue = $this->fileName;
                
                $iconFile = getIconMimeFile($this->mime);
                if ($iconFile) $this->icon = $doc->getIcon($iconFile, $iconMimeSize);
                $this->url = $doc->getFileLink($oa->id, $index, false, true, $v, $finfo);
            }
        }
    }
}
class ImageAttributeValue extends FileAttributeValue
{
    public $thumbnail = '';
    public function __construct(NormalAttribute $oa, $v, Doc $doc, $index, $thumbnailSize = 48)
    {
        parent::__construct($oa, $v, $doc, $index);
        $fileLink = $doc->getFileLink($oa->id, $index, false, true, $v);
        if ($fileLink) {
            if ($thumbnailSize > 0) {
                $this->thumbnail = sprintf('%s&width=%d', $fileLink, $thumbnailSize);
            } else {
                $this->thumbnail = $fileLink;
            }
        } elseif ($v) {
            global $action;
            $localImage = $action->parent->getImageLink($v);
            if ($localImage) {
                $this->displayValue = basename($v);
                $this->url = $localImage;
                if ($thumbnailSize > 0) {
                    $this->thumbnail = $action->parent->getImageLink($v, null, $thumbnailSize);
                } else {
                    $this->thumbnail = $localImage;
                }
            }
        }
    }
}
class DocidAttributeValue extends StandardAttributeValue
{
    public $familyRelation;
    
    public $url;
    public $icon = null;
    public $revision = - 1;
    public $initid;
    public $fromid;
    protected $visible = true;
    
    public function __construct(NormalAttribute $oa, $v, Doc & $doc, $iconsize = 24, $relationNoAccessText = '')
    {
        $this->familyRelation = $oa->format;
        $this->value = ($v === '') ? null : $v;
        $info = array();
        $docRevOption = $oa->getOption("docrev", "latest");
        $this->displayValue = DocTitle::getRelationTitle($v, $docRevOption == "latest", $doc, $docRevOption, $info);
        if ($this->displayValue !== false) {
            if ($v !== '' && $v !== null) {
                if ($iconsize > 0) {
                    if (!empty($info["icon"])) {
                        $this->icon = $doc->getIcon($info["icon"], $iconsize, $info["initid"]);
                    } else {
                        $this->icon = $doc->getIcon("doc.png", $iconsize);
                    }
                }
                $this->url = $this->getDocUrl($v, $docRevOption);
                if ($docRevOption === "fixed") {
                    $this->revision = intval($info["revision"]);
                } else if (preg_match('/^state\(([^\)]+)\)/', $docRevOption, $matches)) {
                    $this->revision = array(
                        "state" => $matches[1]
                    );
                }
                if (isset($info["initid"])) {
                    $this->initid = intval($info["initid"]);
                }
                if (isset($info["fromid"])) {
                    $this->fromid = intval($info["fromid"]);
                }
            }
        } else {
            $this->visible = false;
            if ($relationNoAccessText) $this->displayValue = $relationNoAccessText;
            else $this->displayValue = $oa->getOption("noaccesstext", _("information access deny"));
        }
    }
    
    protected function getDocUrl($v, $docrev)
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
                $rawValue = getTDoc("", $this->value);
                self::$thcDoc->affect($rawValue);
                $this->displayValue = self::$thcDoc->getTitle();
                // set local cache
                self::$thcDocTitle[$this->value] = $this->displayValue;
            }
        }
    }
}

