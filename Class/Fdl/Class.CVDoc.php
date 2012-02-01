<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 *  Control view Class Document
 *
 * @author Anakeen 2003
 * @version $Id: Class.CVDoc.php,v 1.7 2006/04/03 14:56:26 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 */

include_once ('FDL/Class.Doc.php');
/**
 * Control view Class
 */
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
    
    var $usefor = 'W';
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
    
    function setAcls()
    {
        $ti = $this->getTValue("CV_IDVIEW");
        $tl = $this->getTValue("CV_LVIEW");
        $tz = $this->getTValue("CV_ZVIEW");
        $tk = $this->getTValue("CV_KVIEW");
        $tm = $this->getTValue("CV_MSKID");
        
        $ka = POS_WF;
        while (list($k, $v) = each($tk)) {
            if ($ti[$k] == "") $cvk = "CV$k";
            else $cvk = $ti[$k];
            $this->dacls[$cvk] = array(
                "pos" => $ka,
                "description" => $tl[$k]
            );
            $this->acls[] = $cvk;
            $ka++;
        }
    }
    
    function isIdValid($value)
    {
        $err = "";
        $sug = array();
        $this->nbId++;
        
        $dc = new DocCtrl($this->dbaccess);
        $originals = $dc->dacls;
        
        if ($this->nbId > 20) {
            $err = _("Maximum 20 views by control");
        } elseif (!preg_match('!^[0-9a-z_-]+$!i', $value)) {
            $err = _("You must use only a-z, 0-9, _, - caracters");
        } elseif (array_key_exists($value, $originals)) {
            $err = _("Impossible to name a view like a control acl");
        } else {
            $id_list = $this->getTValue('CV_IDVIEW');
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
    
    function getView($vid)
    {
        $ti = $this->getTValue("CV_IDVIEW");
        foreach ($ti as $k => $v) {
            if ($v == $vid) {
                // found it
                $tl = $this->getTValue("CV_LVIEW");
                $tz = $this->getTValue("CV_ZVIEW");
                $tk = $this->getTValue("CV_KVIEW");
                $tm = $this->getTValue("CV_MSKID");
                
                return array(
                    "CV_IDVIEW" => $v,
                    "CV_LVIEW" => $tl[$k],
                    "CV_ZVIEW" => $tz[$k],
                    "CV_KVIEW" => $tk[$k],
                    "CV_MSKID" => $tm[$k]
                );
            }
        }
        return false;
    }
    
    function getViews()
    {
        $ti = $this->getTValue("CV_IDVIEW");
        $tv = array();
        foreach ($ti as $k => $v) {
            
            $tv[$v] = $this->getView($v);
        }
        return $tv;
    }
    
    function postModify()
    {
        
        $ti = $this->getTValue("CV_IDVIEW");
        foreach ($ti as $k => $v) {
            if ($v == "") $ti[$k] = "CV$k";
        }
        $this->setValue("CV_IDVIEW", $ti);
    }
    
    function DocControl($aclname)
    {
        return Doc::Control($aclname);
    }
    /**
     * Special control in case of dynamic controlled profil
     */
    function Control($aclname)
    {
        
        $err = $this->DocControl($aclname);
        if ($err == "") return $err; // normal case
        if ($this->getValue("DPDOC_FAMID") > 0) {
            if ($this->doc) {
                // special control for dynamic users
                if ($this->pdoc === null) {
                    $pdoc = createDoc($this->dbaccess, $this->fromid, false);
                    $pdoc->doctype = "T"; // temporary
                    //	$pdoc->setValue("DPDOC_FAMID",$this->getValue("DPDOC_FAMID"));
                    $err = $pdoc->Add();
                    if ($err != "") return "CVDoc::Control:" . $err; // can't create profil
                    $pdoc->setProfil($this->profid, $this->doc);
                    $pdoc->dacls = $this->dacls;
                    
                    $this->pdoc = & $pdoc;
                }
                $err = $this->pdoc->DocControl($aclname);
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
                $vidcreate = $this->getValue("cv_idcview");
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
                $tv = $this->getAValues("cv_t_views");
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
