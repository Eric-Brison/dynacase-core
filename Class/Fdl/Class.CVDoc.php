<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 *  Control view Class Document
 *
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 */

include_once ('FDL/Class.Doc.php');
/**
 * Control view Class
 */
use \Dcp\AttributeIdentifiers\Cvdoc as MyAttributes;
class CVDoc extends Doc
{
    /**
     * CVDoc has its own special access depend on special views
     * by default the three access are always set
     *
     * @var array
     */
    var $acls = array(
        "view",
        "edit",
        "delete"
    );
    
    var $nbId = 0;
    
    var $usefor = 'SW';
    var $defDoctype = 'P';
    var $attrPrefix = "CVI"; // prefix attribute
    
    /**
     * @var Doc instance document
     */
    public $doc = null;
    /**
     * @var CVDoc profil document
     */
    private $pdoc = null;
    // --------------------------------------------------------------------
    function CVDoc($dbaccess = '', $id = '', $res = '', $dbid = 0)
    {
        // first construct acl array
        if (isset($this->fromid)) $this->defProfFamId = $this->fromid; // it's a profil itself
        // don't use Doc constructor because it could call this constructor => infinitive loop
        DocCtrl::__construct($dbaccess, $id, $res, $dbid);
        
        $this->setAcls();
    }
    
    function Complete()
    {
        $this->setAcls();
    }
    function setAcls()
    {
        $this->extendedAcls = array();
        $ti = $this->getMultipleRawValues("CV_IDVIEW");
        $tl = $this->getMultipleRawValues("CV_LVIEW");
        $tk = $this->getMultipleRawValues("CV_KVIEW");
        
        foreach ($tk as $k => $v) {
            if ($ti[$k] == "") $cvk = "CV$k";
            else $cvk = $ti[$k];
            $this->extendedAcls[$cvk] = array(
                "name" => $cvk,
                "description" => $tl[$k]
            );
            
            $this->acls[$cvk] = $cvk;
        }
    }
    
    function isIdValid($value)
    {
        $err = "";
        $sug = array();
        $this->nbId++;
        
        $dc = new DocCtrl($this->dbaccess);
        $originals = $dc->dacls;
        
        if (!preg_match('!^[0-9a-z_-]+$!i', $value)) {
            $err = sprintf(_("You must use only a-z, 0-9, _, - characters : \"%s\""), $value);
        } elseif (array_key_exists($value, $originals)) {
            $err = _("Impossible to name a view like a control acl");
        } else {
            $id_list = $this->getMultipleRawValues('CV_IDVIEW');
            $id_count = 0;
            foreach ($id_list as $id) {
                if ($id == $value) {
                    $id_count++;
                }
            }
            if ($id_count > 1) {
                $err = _("Impossible to have several identical names");
            }
        }
        return array(
            "err" => $err,
            "sug" => $sug
        );
    }
    
    function isLabelValid($value)
    {
        $err = '';
        $sug = array();
        if (strlen(trim($value)) == 0) {
            $err = _("Label must not be empty");
        }
        return array(
            'err' => $err,
            'sug' => $sug
        );
    }
    
    function getView($vid)
    {
        $ti = $this->getMultipleRawValues("CV_IDVIEW");
        foreach ($ti as $k => $v) {
            if ($v === $vid) {
                // found it
                $tl = $this->getMultipleRawValues(MyAttributes::cv_lview);
                $tz = $this->getMultipleRawValues(MyAttributes::cv_zview);
                $tk = $this->getMultipleRawValues(MyAttributes::cv_kview);
                $tm = $this->getMultipleRawValues(MyAttributes::cv_mskid);
                $tmenu = $this->getMultipleRawValues(MyAttributes::cv_menu);
                
                return array(
                    "CV_IDVIEW" => $v,
                    "CV_LVIEW" => $tl[$k],
                    "CV_ZVIEW" => $tz[$k],
                    "CV_KVIEW" => $tk[$k],
                    "CV_MSKID" => $tm[$k],
                    "CV_MENU" => $tmenu[$k]
                );
            }
        }
        return false;
    }
    /**
     * @param string $vid view identifier
     * @return string the locale label
     */
    public function getLocaleViewLabel($vid)
    {
        $key = $this->getPropertyValue("name") . "#label#" . $vid;
        $lkey = _($key);
        if ($lkey != $key) {
            return $lkey;
        }
        $view = $this->getView($vid);
        return isset($view["CV_LVIEW"]) ? $view["CV_LVIEW"] : sprintf(_("Unlabeled view (%s)") , $vid);
    }
    /**
     * @param string $vid view identifier
     * @return string the locale menu label
     */
    public function getLocaleViewMenu($vid)
    {
        $key = $this->getPropertyValue("name") . "#menu#" . $vid;
        $lkey = _($key);
        if ($lkey != $key) {
            return $lkey;
        }
        $view = $this->getView($vid);
        return isset($view["CV_MENU"]) ? $view["CV_MENU"] : sprintf(_("Unlabeled menu (%s)") , $vid);
    }
    
    function getViews()
    {
        $ti = $this->getMultipleRawValues("CV_IDVIEW");
        $tv = array();
        foreach ($ti as $k => $v) {
            
            $tv[$v] = $this->getView($v);
        }
        return $tv;
    }
    
    function preImport(array $extra = array())
    {
        return $this->verifyAllConstraints();
    }
    
    function postStore()
    {
        
        $ti = $this->getMultipleRawValues("CV_IDVIEW");
        foreach ($ti as $k => $v) {
            if ($v == "") $ti[$k] = "CV$k";
        }
        $this->setValue("CV_IDVIEW", $ti);
    }
    
    function docControl($aclname, $strict = false)
    {
        return Doc::control($aclname, $strict);
    }
    /**
     * Special control in case of dynamic controlled profil
     */
    function control($aclname, $strict = false)
    {
        
        $err = $this->docControl($aclname, $strict);
        if ($err == "") return $err; // normal case
        if ($this->getRawValue("DPDOC_FAMID") > 0) {
            if ($this->doc) {
                // special control for dynamic users
                if ($this->pdoc === null) {
                    $pdoc = createDoc($this->dbaccess, $this->fromid, false);
                    $pdoc->doctype = "T"; // temporary
                    //	$pdoc->setValue("DPDOC_FAMID",$this->getRawValue("DPDOC_FAMID"));
                    $err = $pdoc->Add();
                    if ($err != "") return "CVDoc::Control:" . $err; // can't create profil
                    $pdoc->setProfil($this->profid, $this->doc);
                    
                    $pdoc->acls = $this->acls;
                    $pdoc->extendedAcls = $this->extendedAcls;
                    
                    $this->pdoc = & $pdoc;
                }
                
                $err = $this->pdoc->docControl($aclname, $strict);
            }
        }
        return $err;
    }
    
    function Set(&$doc)
    {
        if (!isset($this->doc)) {
            $this->doc = & $doc;
        }
    }
    /**
     * retrieve first compatible view
     * @param bool $edition if true edition view else consultation view
     * @return string view definition "cv_idview", "cv_mskid"
     */
    function getPrimaryView($edition = false)
    {
        $view = '';
        if ($this->doc) {
            if ($edition && (!$this->doc->id)) {
                $vidcreate = $this->getRawValue("cv_idcview");
                if ($vidcreate) {
                    //	   control must be done by the caller
                    $viewU = $this->getView($vidcreate); // use it first if exist
                    $view = array();
                    foreach ($viewU as $k => $v) $view[strtolower($k) ] = $v;
                }
            }
            
            if (!$view) {
                $type = ($edition) ? "VEDIT" : "VCONS";
                // search preferred view
                $tv = $this->getArrayRawValues("cv_t_views");
                // sort
                usort($tv, "cmp_cvorder3");
                foreach ($tv as $k => $v) {
                    if ($v["cv_order"] > 0) {
                        if ($v["cv_kview"] == $type) {
                            $err = $this->control($v["cv_idview"]); // control special view
                            if ($err == "") {
                                $view = $v;
                                break;
                            }
                        }
                    }
                }
            }
        }
        return $view;
    }
}
?>
