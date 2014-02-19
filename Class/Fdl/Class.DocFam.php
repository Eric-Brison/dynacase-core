<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Family Document Class
 *
 * @author Anakeen
 * @version $Id: Class.DocFam.php,v 1.31 2008/09/16 16:09:59 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 */
include_once ('FDL/Class.PFam.php');
/**
 * @class DocFam
 * @method  createProfileAttribute
 */
class DocFam extends PFam
{
    
    var $dbtable = "family.families";
    
    var $sqlcreate = "
create table family.families (cprofid int ,
                     dfldid int, 
                     cfldid int, 
                     ccvid int, 
                     ddocid int,
                     methods text,
                     defaultvalues json,
                     schar char,
                     parametervalues json,
                     genversion float,
                     maxrev int,
                     usedocread int,
                     tagable text,
                     configuration text) inherits (family.documents);
create unique index idx_idfam on family.families(id);";
    var $sqltcreate = array();
    
    var $defDoctype = 'C';
    
    var $defaultview = "FDL:VIEWFAMCARD";
    
    var $attr;
    public $specialmenu = "FDL:POPUPFAMDETAIL";
    public $addfields = array(
        "dfldid",
        "cfldid",
        "ccvid",
        "cprofid",
        "ddocid",
        "methods",
        "defaultvalues",
        "parametervalues",
        "genversion",
        "usedocread",
        "schar",
        "maxrev",
        "tagable",
        "configuration"
    );
    public $genversion;
    public $dfldid;
    public $cfldid;
    public $ccvid;
    public $cprofid;
    public $ddocid;
    public $methods;
    public $defaultvalues;
    public $parametervalues;
    public $schar;
    public $maxrev;
    public $configuration;
    public $tagable;
    private $_configuration;
    private $_xtdefaultvalues; // dynamic used by ::getParams()
    private $_xtparametervalues; // dynamic used by ::getDefValues()
    private $defaultSortProperties = array(
        'owner' => array(
            'sort' => 'no',
        ) ,
        'title' => array(
            'sort' => 'asc',
        ) ,
        'revision' => array(
            'sort' => 'no',
        ) ,
        'initid' => array(
            'sort' => 'desc',
        ) ,
        'revdate' => array(
            'sort' => 'desc',
        ) ,
        'state' => array(
            'sort' => 'asc',
        )
    );
    
    function __construct($dbaccess = '', $id = '', $res = '', $dbid = 0, $include = true)
    {
        
        foreach ($this->addfields as $f) $this->fields[$f] = $f;
        // specials characteristics R : revised on each modification
        parent::__construct($dbaccess, $id, $res, $dbid);
        $this->doctype = 'C';
        if ($include && ($this->id > 0) && ($this->isAffected())) {
            try {
                if (file_exists(sprintf("%s/%s", DEFAULT_PUBDIR, getFamilyFileName($this->name)))) {
                    include_once (getFamilyFileName($this->name));
                    $adoc = "ADoc" . $this->id;
                    $this->attributes = new $adoc();
                    uasort($this->attributes->attr, "tordered");
                }
            }
            catch(Exception $e) {
                throw new Dcp\Exception(sprintf("cannot access attribute definition for %s (#%s) family", $this->name, $this->id));
            }
        }
    }
    
    function complete()
    {
        $this->_xtdefaultvalues = null;
        $this->_xtparametervalues = null;
    }
    function preDocDelete()
    {
        return _("cannot delete family");
    }
    /**
     * return i18n title for family
     * based on name
     * @return string
     */
    function getCustomTitle()
    {
        $r = $this->name . '#title';
        $i = _($r);
        if ($i != $r) return $i;
        return $this->title;
    }
    
    static function getLangTitle($values)
    {
        $r = $values["name"] . '#title';
        $i = _($r);
        if ($i != $r) return $i;
        return $values["title"];
    }
    
    function postStore()
    {
        include_once ("FDL/Lib.Attr.php");
        return refreshPhpPgDoc($this->dbaccess, $this->id);
    }
    
    function preCreated()
    {
        $cdoc = $this->getFamilyDocument();
        if ($cdoc->isAlive()) {
            if (!$this->ccvid) $this->ccvid = $cdoc->ccvid;
            if (!$this->cprofid) $this->cprofid = $cdoc->cprofid;
            if (!$this->defaultvalues) $this->defaultvalues = $cdoc->defaultvalues;
            if (!$this->schar) $this->schar = $cdoc->schar;
            if (!$this->usefor) $this->usefor = $cdoc->usefor;
            if (!$this->tagable) $this->tagable = $cdoc->tagable;
        }
        if ($this->name) {
            // destroy old file if exists
            $filePath = getFamilyFileName($this->name);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }
    /**
     * update attributes of workflow if needed
     */
    function postImport(array $extra = array())
    {
        $err = '';
        if (strstr($this->usefor, 'W')) {
            $classw = $this->classname;
            if ($classw) {
                $w = new $classw();
                if ($w) {
                    if (is_a($w, "WDoc")) {
                        /**
                         * @var WDoc $w
                         */
                        $err = $w->createProfileAttribute($this->id);
                    }
                }
            } else {
                $err = "workflow need class";
            }
        }
        return $err;
    }
    /**
     * @templateController default values controller
     * @param string $target
     * @param bool $ulink
     * @param bool $abstract
     */
    function viewDefaultValues($target = "_self", $ulink = true, $abstract = false)
    {
        $d = createDoc($this->dbaccess, $this->id, false, true, false);
        $defValues = $this->getDefValues();
        $ownDefValues = $this->explodeX($this->defaultvalues);
        $ownParValues = $this->explodeX($this->parametervalues);
        $tDefVal = $tDefPar = array();
        
        $tp = $this->getParamAttributes();
        $pPowns = $this->getOwnParams();
        foreach ($tp as $aid => & $oa) {
            if ($oa->type == "array") continue;
            $tDefPar[$aid] = array(
                "aid" => $aid,
                "alabel" => $oa->getLabel() ,
                "defown" => isset($pPowns[$aid]) ? $pPowns[$aid] : null,
                "definh" => ($this->fromid) ? $this->getFamilyDocument()->getParameterRawValue($aid) : '',
                "defresult" => $this->getHtmlValue($oa, $d->getFamilyParameterValue($aid))
            );
        }
        $parent = null;
        if ($this->fromid > 0) {
            $parent = $this->getFamilyDocument();
        }
        foreach ($defValues as $aid => $dv) {
            $oa = $d->getAttribute($aid);
            $value = $d->getRawValue($aid);
            $ownValue = isset($ownDefValues[$aid]) ? $ownDefValues[$aid] : null;
            
            if ($oa) {
                $oa->setVisibility('R');
                $label = $oa->getLabel();
                if ($oa->usefor == 'Q') {
                    $value = $d->getFamilyParameterValue($aid);
                    if (!empty($ownParValues[$aid])) {
                        $ownValue = $ownParValues[$aid];
                    } else {
                        if ($ownValue) $ownValue.= ' <em>(' . _("default value") . ")</em>";
                    }
                }
            } else {
                $label = '-';
            }
            $inhValue = '';
            if ($parent) {
                if ($oa->usefor == 'Q') {
                    
                    $inhValue = $parent->getParameterRawValue($aid);
                } else {
                    $inhValue = $parent->getDefValue($aid);
                }
            }
            $t = array(
                "aid" => $aid,
                "alabel" => $label,
                "defown" => $ownValue,
                "definh" => $inhValue,
                "defresult" => $this->getHtmlValue($oa, $value)
            );
            if ($oa && $oa->usefor == 'Q') {
                
                $tDefPar[$aid] = $t;
            } else {
                $tDefVal[$aid] = $t;
            }
        }
        $this->lay->set("hasAncestor", $this->fromid > 0);
        $this->lay->set("docid", $this->id);
        $this->lay->SetBlockData("DEFVAL", $tDefVal);
        $this->lay->SetBlockData("DEFPAR", $tDefPar);
        $this->lay->Set("NOVAL", count($tDefVal) == 0);
        $this->lay->Set("NOPAR", count($tDefPar) == 0);
        $this->lay->Set("canEdit", $this->canEdit() == "");
    }
    /**
     * @templateController special default view for families
     * @param string $target
     * @param bool $ulink
     * @param bool $abstract
     */
    function viewfamcard($target = "_self", $ulink = true, $abstract = false)
    {
        // -----------------------------------
        
        /** @var Action $action */
        global $action;
        //Checking if document has acls
        simpleQuery($this->dbaccess, "SELECT count(*) FROM docperm WHERE docid=" . $this->id, $nb_acl, true, true);
        $this->lay->set("hasAcl", ($nb_acl != "0"));
        
        $this->lay->set("modifyacl", ($this->control("modifyacl") == ""));
        $this->lay->set("canInitProfil", $action->HasPermission("FREEDOM_ADMIN", "FREEDOM"));
        
        foreach ($this->fields as $k => $v) {
            
            $this->lay->set("$v", $this->$v ? $this->$v : false);
            switch ($v) {
                case 'cprofid':
                    if ($this->$v > 0) {
                        $tdoc = new_Doc($this->dbaccess, $this->$v);
                        
                        $this->lay->set("cmodifyacl", ($tdoc->control("modifyacl") == ""));
                        
                        $this->lay->set("cproftitle", $tdoc->title);
                        $this->lay->set("cprofdisplay", "");
                        $hascontrol = ($this->controlUserId($this->$v, $this->userid, "modifyacl") == "");
                        $this->lay->set("ca_" . $v, $hascontrol);
                    } else {
                        $this->lay->set("cprofdisplay", "none");
                    }
                    break;

                case 'cfldid':
                    if ($this->$v > 0) {
                        $tdoc = new_Doc($this->dbaccess, $this->$v);
                        $this->lay->set("cfldtitle", $tdoc->title);
                        $this->lay->set("cflddisplay", "");
                    } else {
                        $this->lay->set("cflddisplay", "none");
                    }
                    break;

                case 'dfldid':
                    if ($this->$v > 0) {
                        $tdoc = new_Doc($this->dbaccess, $this->$v);
                        $this->lay->set("dfldtitle", $tdoc->title);
                        $this->lay->set("dflddisplay", "");
                    } else {
                        $this->lay->set("dflddisplay", "none");
                    }
                    break;

                case 'wid':
                    if ($this->$v > 0) {
                        /**
                         * @var WDoc $tdoc
                         */
                        $tdoc = new_Doc($this->dbaccess, $this->$v);
                        $this->lay->set("wtitle", $tdoc->title);
                        $this->lay->set("wdisplay", true);
                        $this->lay->set("wactif", ($tdoc->profid > 0));
                        $hascontrol = ($tdoc->control("modifyacl") == "");
                        $this->lay->set("wcontrol", $hascontrol);
                        $this->lay->set("wedit", ($tdoc->control("edit") == ""));
                        $states = $tdoc->getStates();
                        $tstates = array();
                        $tnoprofilstates = array();
                        foreach ($states as $st) {
                            $pid = $tdoc->getStateProfil($st);
                            if ($pid) {
                                $pdoc = new_doc($this->dbaccess, $pid);
                                $tstates[$pid]["smodifyacl"] = ($pdoc->control("modifyacl") == "");
                                $tstates[$pid]["sactif"] = $pdoc->profid;
                                $tstates[$pid]["pstateid"] = $pid;
                                $tstates[$pid]["states"][] = _($st);
                            } else {
                                $tnoprofilstates[_($st) ] = array(
                                    "pstateattrid" => $tdoc->getStateProfilAttribute($st) ,
                                    "states" => _($st)
                                );
                            }
                        }
                        
                        $this->lay->set("noprofilstate", implode(", ", array_keys($tnoprofilstates)));
                        foreach ($tstates as $ka => $va) {
                            $tstates[$ka]["states"] = implode(", ", $va["states"]);
                        }
                        $this->lay->setBlockData("pstate", $tstates);
                        $this->lay->setBlockData("nopstate", $tnoprofilstates);
                    } else {
                        $this->lay->set("wdisplay", false);
                    }
                    break;

                case 'ccvid':
                    if ($this->$v > 0) {
                        $tdoc = new_Doc($this->dbaccess, $this->$v);
                        $this->lay->set("cvtitle", $tdoc->title);
                        $this->lay->set("cvdisplay", "");
                    } else {
                        $this->lay->set("cvdisplay", "none");
                    }
                    break;

                case 'maxrev':
                    if (!$this->maxrev) {
                        if ($this->schar == 'S') {
                            $this->lay->set("maxrevision", _("no revisable"));
                        } else {
                            $this->lay->set("maxrevision", _("unlimited revisions"));
                        }
                    } else {
                        $this->lay->set("maxrevision", $this->maxrev);
                    }
                    
                    break;
            }
        }
    }
    //~~~~~~~~~~~~~~~~~~~~~~~~~ PARAMETERS ~~~~~~~~~~~~~~~~~~~~~~~~
    
    /**
     * return family parameter
     *
     * @deprecated use {@link Doc::getParameterRawValue} instead
     * @see Doc::getParameterRawValue
     * @param string $idp parameter identifier
     * @param string $def default value if parameter not found or if it is null
     * @return string parameter value
     */
    final public function getParamValue($idp, $def = "")
    {
        deprecatedFunction();
        return $this->getParameterRawValue($idp, $def);
    }
    /**
     * return family parameter
     *
     * @param string $idp parameter identifier
     * @param string $def default value if parameter not found or if it is null
     * @return string parameter value
     */
    final public function getParameterRawValue($idp, $def = "")
    {
        
        $pValue = $this->getXValue("parametervalues", $idp);
        if ($pValue === '') {
            $defsys = $this->getDefValue($idp);
            if ($defsys !== '') {
                return $defsys;
            }
            return $def;
        }
        return $pValue;
    }
    /**
     * use in Doc::getParameterFamilyValue
     * @param string $idp
     * @param string $def
     * @see Doc::getParameterFamilyValue_
     * @return bool|string
     */
    protected function getParameterFamilyRawValue($idp, $def)
    {
        
        return $this->getParameterRawValue($idp, $def);
    }
    /**
     * return all family parameter - seach in parents if parameter value is null
     *
     * @return array string parameter value
     */
    function getParams()
    {
        return $this->getXValues("parametervalues");
    }
    /**
     * return own family parameters values - no serach in parent families
     * @return array string parameter value
     */
    function getOwnParams()
    {
        return $this->explodeX($this->parametervalues);
    }
    /**
     * return the value of an list parameter document
     *
     * the parameter must be in an array or of a type '*list' like enumlist or textlist
     * @param string $idAttr identifier of list parameter
     * @param string $def default value returned if parameter not found or if is empty
     * @param int $index rank in case of multiple value
     * @return array the list of parameter values
     */
    public function getParamTValue($idAttr, $def = "", $index = - 1)
    {
        $t = $this->rawValueToArray($this->getParameterRawValue("$idAttr", $def));
        if ($index == - 1) return $t;
        if (isset($t[$index])) return $t[$index];
        else return $def;
    }
    /**
     * set family parameter value
     *
     * @param string $idp parameter identifier
     * @param string $val value of the parameter
     * @param bool $check set to false when construct family
     * @return string error message
     */
    public function setParam($idp, $val, $check = true)
    {
        $this->setChanged();
        $idp = strtolower($idp);
        
        $oa = null;
        if ($check) {
            $oa = $this->getAttribute($idp); // never use getAttribute if not check
            if (!$oa) {
                return ErrorCode::getError('DOC0120', $idp, $this->getTitle() , $this->name);
            }
        }
        
        if (is_array($val)) {
            if ($oa && $oa->type == 'htmltext') {
                $val = $this->arrayToRawValue($val, "\r");
            } else {
                $val = $this->arrayToRawValue($val);
            }
        }
        if (!empty($val) && $oa && ($oa->type == "date" || $oa->type == "timestamp")) {
            $err = $this->convertDateToiso($oa, $val);
            if ($err) return $err;
        }
        
        $err = '';
        if ($this->isComplete()) $err = $this->checkSyntax($idp, $val);
        if (!$err) $this->setXValue("parametervalues", strtolower($idp) , $val);
        return $err;
    }
    
    private function convertDateToiso(BasicAttribute $oa, &$val)
    {
        $localeconfig = getLocaleConfig();
        if ($localeconfig !== false) {
            if ($oa->type == "date" || $oa->type == "timestamp") {
                if ($oa->type == "date") {
                    $dateFormat = $localeconfig['dateFormat'];
                } else {
                    $dateFormat = $localeconfig['dateTimeFormat'];
                }
                
                $tDates = explode("\n", $val);
                foreach ($tDates as $k => $date) {
                    $tDates[$k] = stringDateToIso($date, $dateFormat);
                }
                $val = implode("\n", $tDates);
            } else {
                return sprintf(_("local config for date not found"));
            }
        }
        return '';
    }
    /**
     * Verify is family is under construction
     * @return bool
     */
    private function isComplete()
    {
        return ($this->attributes && $this->attributes->attr);
    }
    /**
     * @param string $aid attribute identifier
     * @param string $val value to test
     * @return string error message
     */
    private function checkSyntax($aid, $val)
    {
        /**
         * @var NormalAttribute $oa
         */
        $oa = $this->getAttribute($aid);
        if (!$oa) return ''; // cannot test in this case
        $err = '';
        $type = $oa->type;
        if ($oa->isMultiple()) {
            $val = explode("\n", $val);
        }
        
        if (is_array($val)) $vals = $val;
        else $vals[] = $val;
        foreach ($vals as $ka => $av) {
            switch ($type) {
                case 'money':
                case 'double':
                    if (!empty($av) && (!is_numeric($av))) {
                        $err = sprintf(_("value [%s] is not a number") , $av);
                    }
                    break;

                case 'int':
                    if (!empty($av)) {
                        if ((!is_numeric($av))) $err = sprintf(_("value [%s] is not a number") , $av);
                        if (!$err && (!ctype_digit($av))) $err = sprintf(_("value [%s] is not a integer") , $av);
                    }
                    break;
                }
                if (!$err) {
                    // verifiy constraint
                    if ($oa->phpconstraint) {
                        $map[$aid] = $av;
                        $err = $this->applyMethod($oa->phpconstraint, null, $oa->isMultiple() ? $ka : -1, array() , $map);
                    }
                }
            }
            return $err;
    }
    //~~~~~~~~~~~~~~~~~~~~~~~~~ DEFAULT VALUES  ~~~~~~~~~~~~~~~~~~~~~~~~
    
    /**
     * return family default value
     *
     * @param string $idp parameter identifier
     * @param string $def default value if parameter not found or if it is null
     * @return string default value
     */
    public function getDefValue($idp, $def = "")
    {
        $x = $this->getXValue("defaultvalues", $idp, $def);
        
        return $x;
    }
    /**
     * return all family default values
     * search in parents families if value is null
     *
     * @return array string default value
     */
    public function getDefValues()
    {
        return $this->getXValues("defaultvalues");
    }
    /**
     * return own default value not inherit default
     *
     * @return array string default value
     */
    function getOwnDefValues()
    {
        return $this->explodeX($this->defaultvalues);
    }
    /**
     * set family default value
     *
     * @param string $idp parameter identifier
     * @param string $val value of the default
     * @return string error message
     */
    function setDefValue($idp, $val, $check = true)
    {
        $idp = strtolower($idp);
        $err = '';
        $oa = null;
        if ($check) {
            $oa = $this->getAttribute($idp);
            if (!$oa) {
                return ErrorCode::getError('DOC0123', $idp, $this->getTitle() , $this->name);
            }
        }
        if (!empty($val) && $oa && ($oa->type == "date" || $oa->type == "timestamp")) {
            $err = $this->convertDateToiso($oa, $val);
        }
        $this->setXValue("defaultvalues", $idp, $val);
        return $err;
    }
    //~~~~~~~~~~~~~~~~~~~~~~~~~ X VALUES  ~~~~~~~~~~~~~~~~~~~~~~~~
    
    /**
     * return family default value
     *
     * @param string $idp parameter identifier
     * @param string $def default value if parameter not found or if it is null
     * @return string default value
     */
    function getXValue($X, $idp, $def = "")
    {
        $tval = "_xt$X";
        if (!isset($this->$tval)) $this->getXValues($X);
        
        $tval2 = $this->$tval;
        $v = isset($tval2[strtolower($idp) ]) ? $tval2[strtolower($idp) ] : '';
        if ($v == "-") return $def;
        if ($v != "") return $v;
        return $def;
    }
    /**
     * explode param or defval string
     * @param $sx
     * @return array
     */
    private function explodeX($sx)
    {
        $x = json_decode($sx, true);
        return empty($x) ? array() : $x;
    }
    /**
     * return all family default values
     *
     * @return array string default value
     */
    function getXValues($X)
    {
        $Xval = "_xt$X";
        $defval = $this->$X;
        
        if ($this->$Xval) return $this->$Xval;
        
        $XS[$this->id] = $defval;
        $this->$Xval = array();
        $inhIds = array();
        if ($this->attributes !== null && isset($this->attributes->fromids) && is_array($this->attributes->fromids)) {
            $sql = sprintf("select id,%s from %s where id in (%s)", pg_escape_string($X) , $this->dbtable, implode(',', $this->attributes->fromids));
            simpleQuery($this->dbaccess, $sql, $rx, false, false);
            foreach ($rx as $r) {
                $XS[$r["id"]] = $r[$X];
            }
            $inhIds = array_values($this->attributes->fromids);
        }
        if (!in_array($this->id, $inhIds)) $inhIds[] = $this->id;
        
        $txval = array();
        
        foreach ($inhIds as $famId) {
            $txvalh = $this->explodeX($XS[$famId]);
            foreach ($txvalh as $aid => $dval) {
                $txval[$aid] = ($dval == '-') ? null : $dval;
            }
        }
        if ($this->isComplete()) {
            uksort($txval, array(
                $this,
                "compareXOrder"
            ));
        }
        $this->$Xval = $txval;
        
        return $this->$Xval;
    }
    
    public function compareXOrder($a1, $a2)
    {
        $oa1 = $this->getAttribute($a1);
        $oa2 = $this->getAttribute($a2);
        if ($oa1 && $oa2) {
            if ($oa1->ordered > $oa2->ordered) return 1;
            else if ($oa1->ordered < $oa2->ordered) return -1;
        }
        return 0;
    }
    /**
     * set family default value
     *
     * @param $X
     * @param string $idp parameter identifier
     * @param string $val value of the default
     * @return void
     */
    function setXValue($X, $idp, $val)
    {
        $tval = "_xt$X";
        if (is_array($val)) $val = $this->arrayToRawValue($val);
        
        $txval = $this->explodeX($this->$X);
        
        $txval[strtolower($idp) ] = $val;
        $this->$tval = $txval;
        
        $tdefattr = array();
        foreach ($txval as $k => $v) {
            if ($k && ($v !== '')) $tdefattr[$k] = $v;
        }
        
        $this->$tval = null;
        $this->$X = json_encode($tdefattr);
        $this->hasChanged = true;
    }
    
    final public function UpdateVaultIndex()
    {
        $dvi = new DocVaultIndex($this->dbaccess);
        $err = $dvi->DeleteDoc($this->id);
        
        $fa = $this->getParamAttributes();
        
        $tvid = array();
        
        foreach ($fa as $aid => $oattr) {
            if ($oattr->inArray()) {
                $ta = $this->rawValueToArray($this->getParameterRawValue($aid));
            } else {
                $ta = array(
                    $this->getParameterRawValue($aid)
                );
            }
            foreach ($ta as $k => $v) {
                $vid = "";
                if (preg_match(PREGEXPFILE, $v, $reg)) {
                    $vid = $reg[2];
                    $tvid[$vid] = $vid;
                }
            }
        }
        
        foreach ($tvid as $k => $vid) {
            $dvi->docid = $this->id;
            $dvi->vaultid = $vid;
            $dvi->Add();
        }
    }
    
    function saveVaultFile($vid, $stream)
    {
        if (is_resource($stream) && get_resource_type($stream) == "stream") {
            $ext = "nop";
            $filename = uniqid(getTmpDir() . "/_fdl") . ".$ext";
            $tmpstream = fopen($filename, "w");
            while (!feof($stream)) {
                if (false === fwrite($tmpstream, fread($stream, 4096))) {
                    $err = "403 Forbidden";
                    break;
                }
            }
            fclose($tmpstream);
            $vf = newFreeVaultFile($this->dbaccess);
            $info = null;
            $err = $vf->Retrieve($vid, $info);
            if ($err == "") $err = $vf->Save($filename, false, $vid);
            unlink($filename);
            return $err;
        }
        return '';
    }
    /**
     * read xml configuration file
     */
    function getConfiguration()
    {
        if (!$this->_configuration) {
            if ($this->name) {
                $dxml = new DomDocument();
                $famfile = DEFAULT_PUBDIR . sprintf("/families/%s.fam", $this->name);
                if (!@$dxml->load($famfile)) {
                    return null;
                } else {
                    /** @var stdClass $o */
                    $o = null;
                    $properties = $dxml->getElementsByTagName('property');
                    foreach ($properties as $prop) {
                        /** @var DOMElement $prop */
                        $name = $prop->getAttribute('name');
                        $value = $prop->nodeValue;
                        $o->properties[$name] = $value;
                    }
                    $views = $dxml->getElementsByTagName('view');
                    foreach ($views as $view) {
                        /** @var DOMElement $view */
                        $name = $view->getAttribute('name');
                        foreach ($view->attributes as $a) {
                            $o->views[$name][$a->name] = $a->value;
                        }
                    }
                }
                $this->_configuration = $o;
            }
        }
        return $this->_configuration;
    }
    /**
     * @return string
     */
    function getXmlSchema()
    {
        $lay = new Layout(getLayoutFile("FDL", "family_schema.xml"));
        $lay->set("famname", strtolower($this->name));
        $lay->set("famtitle", strtolower($this->getTitle()));
        $lay->set("includefdlxsd", file_get_contents(getLayoutFile("FDL", "fdl.xsd")));
        
        $level1 = array();
        $la = $this->getAttributes();
        $tax = array();
        
        foreach ($la as $k => $v) {
            if ((!$v) || ($v->getOption("autotitle") == "yes") || ($v->usefor == 'Q')) unset($la[$k]);
        }
        foreach ($la as $k => $v) {
            if (($v->id != "FIELD_HIDDENS") && ($v->type == 'frame' || $v->type == "tab") && ((!$v->fieldSet) || $v->fieldSet->id == "FIELD_HIDDENS")) {
                $level1[] = array(
                    "level1name" => $k
                );
                $tax[] = array(
                    "tax" => $v->getXmlSchema($la)
                );
            } else {
                // if ($v)  $tax[]=array("tax"=>$v->getXmlSchema());
                
            }
        };
        
        $lay->setBlockData("ATTR", $tax);
        $lay->setBlockData("LEVEL1", $level1);
        return ($lay->gen());
    }
    /*
        private function loadDefaultSortProperties() {
        $confStore = new ConfigurationStore();
        foreach ($this->defaultSortProperties as $propName => $pValues) {
            foreach ($pValues as $pName => $pValue) {
                $confStore->add('sortProperties', $propName, $pName, $pValue);
            }
        }
        $conf = $confStore->getText();
        if ($conf === false) {
            return false;
        }
        $this->configuration = $conf;
        error_log(__METHOD__." ".sprintf("conf = [%s]", $conf));
        return $this;
        }
    */
    /**
     * Reset properties configuration
     * @return \DocFam
     */
    public function resetPropertiesParameters()
    {
        $this->configuration = '';
        return $this;
    }
    /**
     * Get a property's parameter's value
     * @param string $propName The property's name
     * @param string $pName The parameter's name
     * @return bool|string boolean false on error, string containing the parameter's value
     */
    public function getPropertyParameter($propName, $pName)
    {
        $propName = strtolower($propName);
        
        $confStore = new ConfigurationStore();
        if ($confStore->load($this->configuration) === false) {
            return false;
        }
        
        $class = CheckProp::getParameterClassMap($pName);
        $pValue = $confStore->get($class, $propName, $pName);
        if ($pValue === false) {
            return false;
        }
        
        return $pValue;
    }
    /**
     * Set a parameter's value on a property
     *
     * Note: The value is set on the object but not saved in the
     * database, so it's your responsibility to call modify() if you
     * want to make the change persistent.
     *
     * @param string $propName The property's name
     * @param string $pName The parameter's name
     * @param string $pValue The parameter's value
     * @return bool boolean false on error, or boolean true on success
     */
    public function setPropertyParameter($propName, $pName, $pValue)
    {
        $propName = strtolower($propName);
        
        $confStore = new ConfigurationStore();
        if ($confStore->load($this->configuration) === false) {
            return false;
        }
        
        $class = CheckProp::getParameterClassMap($pName);
        $confStore->add($class, $propName, $pName, $pValue);
        
        $conf = $confStore->getText();
        if ($conf === false) {
            return false;
        }
        
        $this->configuration = $conf;
        return true;
    }
    /**
     * Get sortable properties.
     * @return array properties' Names with their set of parameters
     */
    public function getSortProperties()
    {
        $res = array();
        /*
         * Lookup default parameters
        */
        foreach ($this->defaultSortProperties as $propName => $params) {
            if (isset($params['sort']) && $params['sort'] != 'no') {
                $res[$propName] = $params;
            }
        }
        $confStore = new ConfigurationStore();
        if ($confStore->load($this->configuration) === false) {
            return $res;
        }
        /*
         * Lookup custom parameters
        */
        $props = $confStore->get('sortProperties', null, 'sort');
        if ($props === null) {
            return $res;
        }
        foreach ($props as $propName => $params) {
            if (isset($params['sort']) && $params['sort'] != 'no') {
                $res[$propName] = $params;
            }
        }
        
        return $res;
    }
}
