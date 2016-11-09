<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 *  Control view Class Document
 *
 * @author Anakeen
 * @package FDL
 */
/**
 */
namespace Dcp\Core;
/**
 * Control view Class
 */
use \Dcp\AttributeIdentifiers\Cvdoc as MyAttributes;
class CVDoc extends \Dcp\Family\Base
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
     * @var \Doc instance document
     */
    public $doc = null;
    /**
     * @var CVDoc profil document
     */
    private $pdoc = null;
    // --------------------------------------------------------------------
    function __construct($dbaccess = '', $id = '', $res = '', $dbid = 0)
    {
        // first construct acl array
        if (isset($this->fromid)) $this->defProfFamId = $this->fromid; // it's a profil itself
        // don't use Doc constructor because it could call this constructor => infinitive loop
        \DocCtrl::__construct($dbaccess, $id, $res, $dbid);
        
        $this->setAcls();
    }
    
    public function preConsultation()
    {
        $err = parent::preConsultation();
        $this->injectCss();
        
        $ids = $this->getMultipleRawValues(MyAttributes::cv_idview);
        $menus = $this->getMultipleRawValues(MyAttributes::cv_menu);
        $vLabel = $this->getMultipleRawValues(MyAttributes::cv_lview);
        foreach ($menus as $k => $menuId) {
            
            if ($menuId) {
                $menuLabel = $this->getLocaleViewMenu($ids[$k]);
                
                if ($menuLabel && $menuLabel !== $menuId) {
                    $this->setValue(MyAttributes::cv_menu, sprintf("%s (%s)", $menuId, $menuLabel) , $k);
                }
            }
            $label = $this->getLocaleViewLabel($ids[$k]);
            if ($vLabel[$k] && $vLabel[$k] !== $label) {
                $this->setValue(MyAttributes::cv_lview, sprintf("%s (%s)", $vLabel[$k], $label) , $k);
            }
        }
        
        return $err;
    }
    
    public function preEdition()
    {
        $err = parent::preEdition();
        $this->injectCss();
        return $err;
    }
    
    protected function injectCss()
    {
        global $action;
        $action->parent->addCssRef("FDL/Layout/cvdoc_array_view.css");
    }
    
    protected function postAffect(array $data, $more, $reset)
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
    
    function computeCreationViewLabel($idCreationView)
    {
        if ('' !== $idCreationView) {
            $viewIds = $this->getAttributeValue(MyAttributes::cv_idview);
            $viewLabels = $this->getAttributeValue(MyAttributes::cv_lview);
            $viewIndex = array_search($idCreationView, $viewIds);
            if (false !== $viewIndex) {
                return sprintf("%s (%s)", $viewLabels[$viewIndex], $idCreationView);
            } else {
                return ' ';
            }
        } else {
            return ' ';
        }
    }
    
    function isIdValid($value)
    {
        $err = "";
        $sug = array();
        $this->nbId++;
        
        $dc = new \DocCtrl($this->dbaccess);
        $originals = $dc->dacls;
        
        if (!preg_match('!^[0-9a-z_-]+$!i', $value)) {
            $err = sprintf(_("You must use only a-z, 0-9, _, - characters : \"%s\"") , $value);
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
    
    function isCreationViewValid($idCreationView, $labelCreationView, $idViews)
    {
        $err = '';
        if ('' !== $idCreationView) {
            if (!is_array($idViews) || !in_array($idCreationView, $idViews)) {
                $err = sprintf(___("creation view '%s' does not exists", "CVDOC") , $labelCreationView);
            }
        }
        return $err;
    }
    /**
     * Return view properties
     * @param $vid
     * @return array|false false if vid not found
     */
    function getView($vid)
    {
        $tv = $this->getArrayRawValues("cv_t_views");
        foreach ($tv as $v) {
            if ($v["cv_idview"] === $vid) {
                // found it
                foreach ($v as $k => $av) {
                    $v[strtoupper($k) ] = $av;
                }
                return $v;
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
    /**
     * get Views that can be displayed in a menu by example
     */
    public function getDisplayableViews()
    {
        $tv = $this->getArrayRawValues("cv_t_views");
        $cud = ($this->doc->CanEdit() == "");
        $displayableViews = array();
        foreach ($tv as $v) {
            $vid = $v[MyAttributes::cv_idview];
            $mode = $v[MyAttributes::cv_kview];
            if ($v[MyAttributes::cv_displayed] !== "no") {
                switch ($mode) {
                    case "VCONS":
                        if ($this->control($vid) == "") {
                            $displayableViews[] = $v;
                        }
                        break;

                    case "VEDIT":
                        if ($cud && $this->control($vid) == "") {
                            $displayableViews[] = $v;
                        }
                        break;
                }
            }
        }
        return $displayableViews;
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
        return \Doc::control($aclname, $strict);
    }
    /**
     * Special control in case of dynamic controlled profil
     *
     * @param string $aclname
     * @param bool   $strict
     *
     * @return string
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
                    $pdoc->acls = $this->acls;
                    $pdoc->extendedAcls = $this->extendedAcls;
                    $pdoc->setProfil($this->profid, $this->doc);
                    
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
