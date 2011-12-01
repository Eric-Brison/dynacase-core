<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Family Document Class
 *
 * @author Anakeen 2000
 * @version $Id: Class.DocFam.php,v 1.31 2008/09/16 16:09:59 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 */
include_once ('FDL/Class.PFam.php');

class DocFam extends PFam
{
    
    var $dbtable = "docfam";
    
    var $sqlcreate = "
create table docfam (cprofid int , 
                     dfldid int, 
                     cfldid int, 
                     ccvid int, 
                     ddocid int,
                     methods text,
                     defval text,
                     schar char,
                     param text,
                     genversion float,
                     maxrev int,
                     usedocread int) inherits (doc);
create unique index idx_idfam on docfam(id);";
    
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
        "defval",
        "param",
        "genversion",
        "usedocread",
        "schar",
        "maxrev"
    );
    public $genversion;
    public $dfldid;
    public $cfldid;
    public $ccvid;
    public $cprofid;
    public $ddocid;
    public $methods;
    public $defval;
    public $param;
    public $schar;
    public $maxrev;
    
    function __construct($dbaccess = '', $id = '', $res = '', $dbid = 0, $include = true)
    {
        
        foreach ($this->addfields as $f) $this->fields[$f] = $f;
        // specials characteristics R : revised on each modification
        parent::__construct($dbaccess, $id, $res, $dbid);
        
        $this->doctype = 'C';
        if ($include && ($this->id > 0) && ($this->isAffected())) {
            $adoc = "Doc" . $this->id;
            $GEN = getGen($dbaccess);
            if (@include_once ("FDL$GEN/Class.$adoc.php")) {
                $adoc = "ADoc" . $this->id;
                $this->attributes = new $adoc();
                uasort($this->attributes->attr, "tordered");
            } else {
                throw new Exception(sprintf("cannot access attribute definition for %s family", $this->id));
            }
        }
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
    function getSpecTitle()
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
    function PostModify()
    {
        include_once ("FDL/Lib.Attr.php");
        return refreshPhpPgDoc($this->dbaccess, $this->id);
    }
    function preCreated()
    {
        $cdoc = $this->getFamDoc();
        if ($cdoc->isAlive()) {
            if (!$this->ccvid) $this->ccvid = $cdoc->ccvid;
            if (!$this->cprofid) $this->cprofid = $cdoc->cprofid;
            if (!$this->defval) $this->defval = $cdoc->defval;
            if (!$this->schar) $this->schar = $cdoc->schar;
            if (!$this->usefor) $this->usefor = $cdoc->usefor;
        }
    }
    /**
     * update attributes of workflow if needed
     */
    function postImport()
    {
        if ($this->usefor == 'W') {
            $w = createDoc($this->dbaccess, $this->id);
            if ($w) {
                if (method_exists($w, "createProfileAttribute")) {
                    $w->createProfileAttribute();
                }
            }
        }
    }
    // -----------------------------------
    function viewfamcard($target = "_self", $ulink = true, $abstract = false)
    {
        // -----------------------------------
        global $action;
        
        $this->lay->set("modifyacl", ($this->control("modifyacl") == ""));
        $this->lay->set("canInitProfil", $action->HasPermission("FREEDOM_ADMIN", "FREEDOM"));
        
        foreach ($this->fields as $k => $v) {
            
            $this->lay->set("$v", $this->$v ? $this->$v : false);
            switch ($v) {
                case cprofid:
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

                case cfldid:
                    if ($this->$v > 0) {
                        $tdoc = new_Doc($this->dbaccess, $this->$v);
                        $this->lay->set("cfldtitle", $tdoc->title);
                        $this->lay->set("cflddisplay", "");
                    } else {
                        $this->lay->set("cflddisplay", "none");
                    }
                    break;

                case dfldid:
                    if ($this->$v > 0) {
                        $tdoc = new_Doc($this->dbaccess, $this->$v);
                        $this->lay->set("dfldtitle", $tdoc->title);
                        $this->lay->set("dflddisplay", "");
                    } else {
                        $this->lay->set("dflddisplay", "none");
                    }
                    break;

                case wid:
                    if ($this->$v > 0) {
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
                        foreach ($tstates as $k => $v) {
                            $tstates[$k]["states"] = implode(", ", $v["states"]);
                        }
                        $this->lay->setBlockData("pstate", $tstates);
                        $this->lay->setBlockData("nopstate", $tnoprofilstates);
                    } else {
                        $this->lay->set("wdisplay", false);
                    }
                    break;

                case ccvid:
                    if ($this->$v > 0) {
                        $tdoc = new_Doc($this->dbaccess, $this->$v);
                        $this->lay->set("cvtitle", $tdoc->title);
                        $this->lay->set("cvdisplay", "");
                    } else {
                        $this->lay->set("cvdisplay", "none");
                    }
                    break;

                case forumid:
                    $this->lay->set("forum", ($this->forumid == "" ? _("disable forum") : _("enable forum")));
                    break;

                case maxrev:
                    if (!$this->maxrev) {
                        if ($this->schar == 'S') $this->lay->set("maxrevision", _("no revisable"));
                        else $this->lay->set("maxrevision", _("unlimited revisions"));
                    } else $this->lay->set("maxrevision", $this->maxrev);
                    
                    break;
                }
            }
        }
        //~~~~~~~~~~~~~~~~~~~~~~~~~ PARAMETERS ~~~~~~~~~~~~~~~~~~~~~~~~
        
        /**
         * return family parameter
         *
         * @param string $idp parameter identificator
         * @param string $def default value if parameter not found or if it is null
         * @return string parameter value
         */
        final public function getParamValue($idp, $def = "")
        {
            return $this->getXValue("param", $idp, $def);
        }
        /**
         * return all family parameter
         *
         * @return array string parameter value
         */
        function getParams()
        {
            return $this->getXValues("param");
        }
        /**
         * return the value of an list parameter document
         *
         * the parameter must be in an array or of a type '*list' like enumlist or textlist
         * @param string $idAttr identificator of list parameter
         * @param string $def default value returned if parameter not found or if is empty
         * @return array the list of parameter values
         */
        function getParamTValue($idAttr, $def = "", $index = - 1)
        {
            $t = $this->_val2array($this->getParamValue("$idAttr", $def));
            if ($index == - 1) return $t;
            if (isset($t[$index])) return $t[$index];
            else return $def;
        }
        /**
         * set family parameter value
         *
         * @param string $idp parameter identificator
         * @param string $val value of the parameter
         */
        function setParam($idp, $val)
        {
            $this->setChanged();
            if (is_array($val)) $val = $this->_array2val($val);
            return $this->setXValue("param", $idp, $val);
        }
        //~~~~~~~~~~~~~~~~~~~~~~~~~ DEFAULT VALUES  ~~~~~~~~~~~~~~~~~~~~~~~~
        
        /**
         * return family default value
         *
         * @param string $idp parameter identificator
         * @param string $def default value if parameter not found or if it is null
         * @return string default value
         */
        function getDefValue($idp, $def = "")
        {
            return $this->getXValue("defval", $idp, $def);
        }
        /**
         * return all family default values
         *
         * @return array string default value
         */
        function getDefValues()
        {
            return $this->getXValues("defval");
        }
        /**
         * set family default value
         *
         * @param string $idp parameter identificator
         * @param string $val value of the default
         */
        function setDefValue($idp, $val)
        {
            return $this->setXValue("defval", $idp, $val);
        }
        //~~~~~~~~~~~~~~~~~~~~~~~~~ X VALUES  ~~~~~~~~~~~~~~~~~~~~~~~~
        
        /**
         * return family default value
         *
         * @param string $idp parameter identificator
         * @param string $def default value if parameter not found or if it is null
         * @return string default value
         */
        function getXValue($X, $idp, $def = "")
        {
            $tval = "t$X";
            if (!isset($this->$tval)) $this->getXValues($X);
            
            $tval2 = $this->$tval;
            $v = $tval2[strtolower($idp) ];
            if ($v != "") return $v;
            return $def;
        }
        /**
         * return all family default values
         *
         * @return array string default value
         */
        function getXValues($X)
        {
            $tval = "t$X";
            $defval = $this->$X;
            
            $tdefattr = explode("][", substr($defval, 1, strlen($defval) - 2));
            $this->$tval = array();
            
            $txval = array();
            foreach ($tdefattr as $k => $v) {
                
                $aid = substr($v, 0, strpos($v, '|'));
                $dval = substr(strstr($v, '|') , 1);
                
                $txval[$aid] = $dval;
            }
            $this->$tval = $txval;
            
            return $this->$tval;
        }
        /**
         * set family default value
         *
         * @param string $idp parameter identificator
         * @param string $val value of the default
         */
        function setXValue($X, $idp, $val)
        {
            $tval = "t$X";
            if (is_array($val)) $val = $this->_array2val($val);
            
            if (!isset($this->$tval)) $this->getXValues($X);
            $txval = $this->$tval;
            $txval[strtolower($idp) ] = $val;
            $this->$tval = $txval;
            
            $tdefattr = array();
            foreach ($txval as $k => $v) {
                if ($k && ($v !== '')) $tdefattr[] = "$k|$v";
            }
            
            $this->$X = "[" . implode("][", $tdefattr) . "]";
        }
        
        final public function UpdateVaultIndex()
        {
            $dvi = new DocVaultIndex($this->dbaccess);
            $err = $dvi->DeleteDoc($this->id);
            
            $fa = $this->getParamAttributes();
            
            $tvid = array();
            
            foreach ($fa as $aid => $oattr) {
                if ($oattr->inArray()) {
                    $ta = $this->_val2array($this->getParamValue($aid));
                } else {
                    $ta = array(
                        $this->getParamValue($aid)
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
                $err = $vf->Retrieve($vid, $info);
                if ($err == "") $err = $vf->Save($filename, false, $vid);
                unlink($filename);
                return $err;
            }
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
                        
                        return $o;
                    } else {
                        $properties = $dxml->getElementsByTagName('property');
                        foreach ($properties as $prop) {
                            $name = $prop->getAttribute('name');
                            $value = $prop->nodeValue;
                            $o->properties[$name] = $value;
                        }
                        $views = $dxml->getElementsByTagName('view');
                        foreach ($views as $view) {
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
    }
?>
