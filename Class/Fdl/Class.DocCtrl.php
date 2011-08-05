<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Control Access Document
 *
 * @author Anakeen 2002
 * @version $Id: Class.DocCtrl.php,v 1.54 2008/08/05 15:16:58 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 */

include_once ("Class.DocLDAP.php");
include_once ("FDL/Class.DocPerm.php");
include_once ("FDL/Class.VGroup.php");

define("POS_INIT", 0);
define("POS_VIEW", 1);
define("POS_EDIT", 2);
define("POS_DEL", 3);
define("POS_SEND", 4);
// common part are 0-4 and 7-8
define("POS_OPEN", 5);
define("POS_EXEC", 5); // idem OPEN : alias
define("POS_CONT", 6); // view containt
define("POS_VACL", 7);
define("POS_MACL", 8);
define("POS_ULCK", 9);
define("POS_CONF", 10); // confidential
define("POS_FORUM", 11); // edit forum
define("POS_WASK", 12); // view all askes
// family profil
define("POS_CREATE", 5);
define("POS_ICREATE", 6);
//  11 undefined for the moment
define("POS_WF", 12); // begin of workflow privilege definition
// end of privilege is 31 : (coded on 32bits)

/**
 * Control Access Document Class
 * @package FDL
 *
 */
class DocCtrl extends DocLDAP
{
    var $isCacheble = false;
    // --------------------------------------------------------------------
    //---------------------- OBJECT CONTROL PERMISSION --------------------
    // access privilege definition
    var $dacls = array(
        "init" => array(
            "pos" => POS_INIT,
            "description" => "control initialized"
        ) ,
        
        "view" => array(
            "pos" => POS_VIEW, # N_("view document")
            "description" => "view document"
        ) , #  N_("view")
        "send" => array(
            "pos" => POS_SEND, # N_("send document")
            "description" => "send document"
        ) , # N_("send")
        "edit" => array(
            "pos" => POS_EDIT, # N_("edit document")
            "description" => "edit document"
        ) , #  N_("edit")
        "delete" => array(
            "pos" => POS_DEL, # N_("delete document")
            "description" => "delete document"
        ) , #  N_("delete")
        "open" => array(
            "pos" => POS_OPEN, # N_("open folder")
            "description" => "open folder"
        ) , #  N_("open")
        "execute" => array(
            "pos" => POS_EXEC, # N_("execute search")
            "description" => "execute search"
        ) , #  N_("execute")
        "modify" => array(
            "pos" => POS_CONT, # N_("modify folder")
            "description" => "modify folder"
        ) , #  N_("modify")
        "viewacl" => array(
            "pos" => POS_VACL, # N_("view acl")
            "description" => "view acl"
        ) , #  N_("viewacl")
        "modifyacl" => array(
            "pos" => POS_MACL, # N_("modify acl")
            "description" => "modify acl"
        ) , #  N_("modifyacl")
        "create" => array(
            "pos" => POS_CREATE, # N_("modify acl")
            "description" => "create doc"
        ) , #  N_("create doc")
        "unlock" => array(
            "pos" => POS_ULCK, # N_("unlock")
            "description" => "unlock unowner locked doc"
        ) , #  N_("unlock unowner locked doc")
        "icreate" => array(
            "pos" => POS_ICREATE, # N_("icreate")
            "description" => "create doc manually"
        ) , #  N_("create doc manually")
        "confidential" => array(
            "pos" => POS_CONF, # N_("confidential")
            "description" => "view confidential"
        ) , #  N_("view confidential")
        "forum" => array(
            "pos" => POS_FORUM, # N_("forum")
            "description" => "edit forum"
        ) , #  N_("edit forum")
        "wask" => array(
            "pos" => POS_WASK, # N_("wask")
            "description" => "view answers"
        ) #  N_("view answers")
        
    );
    // --------------------------------------------------------------------
    function __construct($dbaccess = '', $id = '', $res = '', $dbid = 0)
    {
        // --------------------------------------------------------------------
        global $action; // necessary to see information about user privilege
        if (isset($action)) {
            $this->userid = $action->parent->user->id;
        }
        if (!isset($this->attributes->attr)) $this->attributes->attr = array();
        parent::__construct($dbaccess, $id, $res, $dbid);
    }
    
    function isControlled()
    {
        return ($this->profid != 0);
    }
    
    function unsetControl()
    {
        if ($this->id == $this->profid) {
            // inhibated all doc references this profil
            if ($this->doctype == 'P') $this->exec_query("update doc set profid=-profid where profid=" . $this->id . " and locked != -1;");
        }
        $this->profid = "0";
        $err = $this->modify(true, array(
            "profid"
        ));
    }
    /**
     * Unset all Acl for document (for everybody)
     *
     */
    function removeControl()
    {
        if ($this->id == $this->profid) {
            // inhibated all doc references this profil
            $this->exec_query("delete from docperm where docid=" . $this->id);
        }
        $this->modify();
    }
    /**
     * activate access specific control
     * @param bool $userctrl if true add all acls for current user
     */
    function setControl($userctrl = true)
    {
        if ($userctrl) {
            $perm = new DocPerm($this->dbaccess, array(
                $this->id,
                $this->userid
            ));
            $perm->docid = $this->id;
            $perm->userid = $this->userid;
            $perm->upacl = - 2; // all privileges
            $perm->unacl = 0;
            $perm->cacl = 0;
            if (!$perm->IsAffected()) {
                // add all privileges to current user
                $perm->Add();
            } else {
                $perm->Modify();
            }
        }
        // reactivation of doc with its profil
        if ($this->doctype == 'P') $this->exec_query("update doc set profid=-profid where profid=-" . $this->id . " and locked != -1;");
        
        $this->profid = $this->id;
        $err = $this->modify(true, array(
            "profid"
        ) , true);
        return $err;
    }
    /**
     * set profil for document
     *
     * @param int profid identificator for profil document
     */
    function setProfil($profid, $fromdocidvalues = 0)
    {
        
        if (!is_numeric($profid)) $profid = getIdFromName($this->dbaccess, $profid);
        if (empty($profid)) {
            $profid = 0;
        }
        $this->profid = $profid;
        if (($profid > 0) && ($profid != $this->id)) {
            // make sure that the profil is activated
            $pdoc = new_Doc($this->dbaccess, $profid);
            if ($pdoc->getValue("DPDOC_FAMID") > 0) {
                // dynamic profil
                $this->dprofid = $profid;
                $this->computeDProfil($this->dprofid, $fromdocidvalues);
                unset($this->uperm); // force recompute privileges
                
            } else {
                $this->dprofid = 0;
            }
            if ($pdoc->profid == 0) $this->profid = - $profid; // inhibition
            
        } elseif (($profid > 0) && ($profid == $this->id)) {
            $this->dprofid = 0;
        }
        if ($this->id > 0) {
            $err = $this->modify(true, array(
                "profid",
                "dprofid"
            ) , true);
        }
    }
    /**
     * reset right for dynamic profil
     *
     * @param int dprofid identificator for dynamic profil document
     */
    function computeDProfil($dprofid = 0, $fromdocidvalues = 0)
    {
        if ($this->id == 0) return;
        if ($dprofid == 0) $dprofid = $this->dprofid;
        if ($dprofid <= 0) return;
        
        $pdoc = new_Doc($this->dbaccess, $dprofid);
        $pfamid = $pdoc->getValue("DPDOC_FAMID");
        if ($pfamid > 0) {
            if ($this->profid != $this->id) {
                $this->profid = $this->id; //private profil
                $this->modify(true, array(
                    "profid"
                ));
            }
            
            $query = new QueryDb($this->dbaccess, "DocPerm");
            $query->AddQuery("docid=" . $pdoc->id);
            $tacl = $query->Query(0, 0, "TABLE");
            if (!is_array($tacl)) {
                //	print "err $tacl";
                $tacl = array();
            }
            $tgnum = array(); // list of virtual user/group
            foreach ($tacl as $v) {
                if ($v["userid"] >= STARTIDVGROUP) {
                    $tgnum[] = $v["userid"];
                }
            }
            if (count($tgnum) > 0) {
                $query = new QueryDb($this->dbaccess, "VGroup");
                $query->AddQuery(GetSqlCond($tgnum, "num", true));
                $tg = $query->Query(0, 0, "TABLE");
                if ($query->nb > 0) {
                    foreach ($tg as $vg) {
                        $tnum[$vg["num"]] = $vg["id"];
                    }
                }
            }
            $this->exec_query("delete from docperm where docid=" . $this->id);
            if ($fromdocidvalues == 0) $fromdocidvalues = & $this;
            foreach ($tacl as $v) {
                
                if ($v["userid"] < STARTIDVGROUP) {
                    $tuid = array(
                        $v["userid"]
                    );
                } else {
                    $tuid = array();
                    $aid = $tnum[$v["userid"]];
                    $duid = $fromdocidvalues->getValue($aid);
                    if ($duid == "") $duid = $fromdocidvalues->getParamValue($aid);
                    if ($duid != "") {
                        $duid = str_replace("<BR>", "\n", $duid); // docid multiple
                        $tduid = $this->_val2array($duid);
                        foreach ($tduid as $duid) {
                            if ($duid > 0) {
                                $docu = getTDoc($fromdocidvalues->dbaccess, intval($duid)); // not for idoc list for the moment
                                $tuid[] = $docu["us_whatid"];
                                //print "<br>$aid:$duid:".$docu["us_whatid"];
                                
                            }
                        }
                    }
                }
                foreach ($tuid as $ku => $uid) {
                    // add right in case of multiple use of the same user : possible in dynamic profile
                    $vupacl[$uid] = (intval($vupacl[$uid]) | intval($v["upacl"]));
                    $vunacl[$uid] = (intval($vunacl[$uid]) | intval($v["unacl"]));
                    
                    if ($uid > 0) {
                        $perm = new DocPerm($this->dbaccess, array(
                            $this->id,
                            $uid
                        ));
                        $perm->cacl = "0";
                        $perm->upacl = $vupacl[$uid];
                        $perm->unacl = $vunacl[$uid];
                        //   print "<BR>set perm $uid : ".$this->id."/".$perm->upacl;
                        if ($perm->isAffected()) $err = $perm->modify();
                        else $err = $perm->Add();
                    }
                }
            }
            if ($perm) {
                // reinit computed
                $err = $perm->resetComputed();
            }
        }
        unset($this->uperm); // force recompute privileges
        return $err;
    }
    /**
     * modify control for a specific user
     *
     * @param int uid user identificator
     * @param string $aclname name of the acl (edit, view,...)
     * @param bool $deletecontrol set true if want delete a control
     * @param bool $negativecontrol set true if want add a negative control (explicit no permission)
     * @return string error message (empty if no errors)
     */
    function modifyControl($uid, $aclname, $deletecontrol = false, $negativecontrol = false)
    {
        
        if (!isset($this->dacls[$aclname])) {
            return sprintf(_("unknow privilege %s") , $aclname);
        }
        $pos = $this->dacls[$aclname]["pos"];
        
        if (!is_numeric($uid)) {
            $uiid = getIdFromName($this->dbaccess, $uid);
            if ($uiid) {
                $udoc = new_Doc($this->dbaccess, $uiid);
                if ($udoc->isAlive()) $uid = $udoc->getValue("us_whatid");
            }
        }
        
        if (!is_numeric($uid)) {
            // logical name
            $vg = new VGroup($this->dbaccess, strtolower($uid));
            if (!$vg->isAffected()) {
                // try to add
                $ddoc = new_Doc($this->dbaccess, $this->getValue("dpdoc_famid"));
                $oa = $ddoc->getAttribute($uid);
                if ($oa->type == "docid") {
                    $vg->id = $oa->id;
                    $vg->Add();
                    $uid = $vg->num;
                } else $err = sprintf(_("unknow virtual user identificateur %s") , $uid);
            } else {
                $uid = $vg->num;
            }
        }
        
        if ($uid > 0) {
            $perm = new DocPerm($this->dbaccess, array(
                $this->id,
                $uid
            ));
            if ($deletecontrol) {
                if ($negativecontrol) $perm->UnsetControlN($pos);
                else $perm->UnsetControlP($pos);
            } else {
                if ($negativecontrol) $perm->SetControlN($pos);
                else $perm->SetControlP($pos);
            }
            if ($perm->isAffected()) $err = $perm->modify();
            else {
                $err = $perm->Add();
            }
        }
        return $err;
    }
    /**
     * add control for a specific user
     *
     * @param int uid user identificator
     * @param string $aclname name of the acl (edit, view,...)
     * @param bool $negativecontrol set true if want add a negative control (explicit no permission)
     * @return string error message (empty if no errors)
     */
    function addControl($uid, $aclname, $negativecontrol = false)
    {
        return $this->ModifyControl($uid, $aclname, false, $negativecontrol);
    }
    /**
     * suppress control for a specific user
     *
     * is not a negative control
     * @param int uid user identificator
     * @param string $aclname name of the acl (edit, view,...)
     * @param bool $negativecontrol set true if want suppress a negative control
     * @return string error message (empty if no errors)
     */
    function delControl($uid, $aclname, $negativecontrol = false)
    {
        return $this->ModifyControl($uid, $aclname, true, $negativecontrol);
    }
    /**
     * set control view for document
     *
     * @param int cvid identificator for control view document
     */
    function setCvid($cvid)
    {
        if (!is_numeric($cvid)) $cvid = getIdFromName($this->dbaccess, $cvid);
        $this->cvid = $cvid;
    }
    /**
     * use to know if current user has access privilege
     *
     * @param int $docid profil identificator
     * @param string $aclname name of the acl (edit, view,...)
     * @return string if empty access granted else error message
     */
    function controlId($docid, $aclname)
    {
        if ($this->profid == $docid) {
            if (!isset($this->uperm)) {
                $perm = new DocPerm($this->dbaccess, array(
                    $docid,
                    $this->userid
                ));
                if ($perm->IsAffected()) $this->uperm = $perm->uperm;
                else $this->uperm = $perm->getUperm($docid, $this->userid);
            }
            return $this->ControlUp($this->uperm, $aclname);
        } else {
            $perm = new DocPerm($this->dbaccess, array(
                $docid,
                $this->userid
            ));
            if ($perm->isAffected()) $uperm = $perm->uperm;
            else $uperm = $perm->getUperm($docid, $this->userid);
            return $this->ControlUp($uperm, $aclname);
        }
    }
    /**
     * use to know if current user has access privilege
     *
     * @param int $docid profil identificator
     * @param int $uid user identificator
     * @param string $aclname name of the acl (edit, view,...)
     * @return string if empty access granted else error message
     */
    function controlUserId($docid, $uid, $aclname)
    {
        $perm = new DocPerm($this->dbaccess, array(
            $docid,
            $uid
        ));
        
        if ($perm->isAffected()) $uperm = $perm->uperm;
        else $uperm = $perm->getUperm($docid, $uid);
        
        return $this->controlUp($uperm, $aclname);
    }
    /**
     * use to know if permission has access privilege
     *
     * @param int $uperm permission mask
     * @param string $aclname name of the acl (edit, view,...)
     * @return string if empty access granted else error message
     */
    function controlUp($uperm, $aclname)
    {
        if (isset($this->dacls[$aclname])) {
            return (($uperm & (1 << ($this->dacls[$aclname]["pos"]))) != 0) ? "" : sprintf(_("no privilege %s for %s |%d]") , $aclname, $this->title, $this->id);
        } else {
            return sprintf(_("unknow privilege %s") , $aclname);
        }
    }
    /**
     * return all users which has a control for a document
     * @param string $aclname the name of acl to search
     * @return array of the system users informations
     */
    function getUsersForAcl($aclname)
    {
        if (isset($this->dacls[$aclname])) {
            $pos = $this->dacls[$aclname]["pos"];
            
            if ($this->getValue("DPDOC_FAMID") > 0) {
                if ($this->doc) {
                    if (!isset($this->pdoc)) {
                        $pdoc = createTmpDoc($this->dbaccess, $this->fromid);
                        $err = $pdoc->Add();
                        if ($err != "") return "getUsersForAcl:" . $err; // can't create profil
                        $pdoc->setProfil($this->profid, $this->doc);
                        $this->pdoc = & $pdoc;
                    } else {
                        $pdoc = $this->pdoc;
                    }
                }
            }
        }
        if ($pdoc) $pdocid = $pdoc->id;
        else $pdocid = $this->profid;
        
        $query = new QueryDb($this->dbaccess, "DocPerm");
        $query->AddQuery("docid=" . $pdocid);
        $query->AddQuery(sprintf("(upacl & %d) != 0", 1 << $pos));
        $tperm = $query->Query(0, 0, "TABLE");
        $u = new User("");
        $ru = array();
        if ($query->nb > 0) {
            foreach ($tperm as $perm) {
                $u->select($perm["userid"]);
                if ($u->login) {
                    if ($u->isgroup == 'Y') {
                        $ru+= $u->GetRUsersList($u->id);
                    } else {
                        $ru[$u->id] = $u->getValues();
                    }
                }
            }
            
            foreach ($ru as $k => $v) { // delete groups
                if ($v["isgroup"] == "Y") unset($ru[$k]);
            }
        }
        return $ru;
    }
    /**
     * apply computeDProfil in all documents with this profile
     * @return void
     */
    public function recomputeProfiledDocument()
    {
        if ($this->getAttribute("dpdoc_famid")) {
            
            include_once ("FDL/Class.SearchDoc.php");
            if ($this->getValue("dpdoc_famid") > 0) {
                // dynamic profil
                // recompute associated documents
                $s = new SearchDoc($this->dbaccess);
                $s->addFilter("dprofid = %d", $this->id);
                $s->setObjectReturn();
                $s->search();
                while ($doc = $s->nextDoc()) {
                    $doc->computeDProfil();
                }
                // in case of change profil status (static -> dynamic)
                $s = new SearchDoc($this->dbaccess);
                $s->addFilter("profid = %d", $this->id);
                $s->setObjectReturn();
                $s->search();
                while ($doc = $s->nextDoc()) {
                    $doc->setProfil($this->id);
                }
            } else {
                // static profil
                // revert to static profiling
                $s = new SearchDoc($this->dbaccess);
                $s->addFilter("dprofid = %d", $this->id);
                $s->setObjectReturn();
                $s->search();
                while ($doc = $s->nextDoc()) {
                    $doc->setProfil($this->id);
                }
            }
        }
    }
    //   // --------------------------------------------------------------------
    //   function ControlUserId ($userid,$aclname) {
    //     // --------------------------------------------------------------------
    //     if (isset($this->dacls[$aclname])) {
    //       $perm = new DocPerm($this->dbaccess, array($this->id,$userid));
    //       if ($perm -> IsAffected()) $uperm = $perm->uperm;
    //       else $uperm = $perm->getUperm($this->id,$userid);
    //       return (($uperm & (1 << ($this->dacls[$aclname]["pos"] ))) != 0)?"":sprintf(_("no privilege %s"),$aclname);
    //     } else {
    //       return sprintf(_("unknow privilege %s"),$aclname);
    //     }
    //   }
    static public function parseMail($Email)
    {
        $sug = array(); // suggestions
        $err = "";
        
        if ($Email != "") {
            if ($Email[0] == "<") {
                $sug[] = _("<it's a message>");
            } else {
                if (preg_match("/^[_\.0-9\/'?$&\+~`%|*a-z=^{}-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,6}$/i", $Email)) {
                    return true;
                }
                $err = _("the email syntax  is like : john.doe@anywhere.org");
                if (!preg_match("/@/", $Email)) {
                    $err = _("the email must containt the @ character");
                }
            }
        }
        return array(
            "err" => $err,
            "sug" => $sug
        );
    }
    /** 
     * return true if the date is in the future (one day after at less)
     * @param string date date JJ/MM/AAAA
     */
    static public function isFutureDate($date)
    {
        
        $err = "";
        $sug = array(); // suggestions
        if ($date != "") {
            if (!preg_match("|^[0-9]{2}/[0-9]{2}/[0-9]{4}|", $date)) {
                $err = _("the date syntax must be like : DD/MM/AAAA");
            } else {
                
                list($dd, $mm, $yy) = explode("/", $date);
                $yy = intval($yy);
                $mm = intval($mm);
                $dd = intval($dd);
                $ti = mktime(0, 0, 0, $mm, $dd + 1, $yy);
                if ($ti < time()) {
                    $err = sprintf(_("the date %s is in the past: today is %s") , date("d/m/Y", mktime(0, 0, 0, $mm, $dd, $yy)) , date("d/m/Y", time()));
                    $sug[] = date("d/m/Y", time());
                }
            }
        }
        return array(
            "err" => $err,
            "sug" => $sug
        );
    }
    /** 
     * verify if a document title and its link are for the same document
     * @param string document title use for verification
     * @param string document identificator use for verification
     */
    public function isDocLinked($title, $docid)
    {
        
        $err = "";
        $sug = array(); // suggestions
        if (trim($title) != "") {
            if (trim($docid) == "") $err = _("need to select the document with the list");
            else {
                $d = new_doc($this->dbaccess, $docid);
                if (!$d->isAlive()) $err = sprintf(_("the document id [%s] for this attribute is not valid") , $docid);
                else if ($d->title != $title) $err = sprintf(_("the title of document [%s] is not conform to original [%s]") , $title, $d->title);
            }
            if ($err) {
                $sug[] = _("clic to the ... button to link document correctly");
            }
        }
        return array(
            "err" => $err,
            "sug" => $sug
        );
    }
    /** 
     * verify if a link of document is alive
     * @param string document title use for verification
     * @param string document identificator use for verification
     */
    public function isValidLink($title, $docid)
    {
        $err = "";
        $sug = array();
        if (trim($title) != "") {
            if (trim($docid) == "") {
                $err = _("the document id is empty");
            } else {
                $d = new_doc($this->dbaccess, $docid);
                if (!$d->isAlive()) $err = sprintf(_("the document id [%s] for this attribute is not valid") , $docid);
            }
            if ($err) {
                $sug[] = _("clic to the [...] button to link document correctly");
            }
        } else {
            if (trim($docid) != "") $err = _("the document title is empty");
        }
        return array(
            "err" => $err,
            "sug" => $sug
        );
    }
    /** 
     * return true it is a number
     * use for constraint
     * @param float $x the number to test
     * @param float $min the minimum of the number (null to indicate no limit)
     * @param float $max the maximum of the number (null to indicate no limit)
     * @return array err if cannot match range
     */
    static public function isFloat($x, $min = null, $max = null)
    {
        $err = "";
        if ($x === "" || $x == '-') return "";
        if (!is_numeric($x)) $err = sprintf(_("[%s] must be a number") , $x);
        if (($min !== null) && ($x < $min)) $err = sprintf(_("[%s] must be greater than %s") , $x, $min);
        if (($max !== null) && ($x > $max)) $err = sprintf(_("[%s] must be lower than %s") , $x, $max);
        return $err;
    }
    /** 
     * return true it is a integer
     * use for constraint
     * @param float $x the number to test
     * @param float $min the minimum of the number (null to indicate no limit)
     * @param float $max the maximum of the number (null to indicate no limit)
     * @return array err if cannot match range
     */
    static public function isInteger($x, $min = null, $max = null)
    {
        $err = "";
        if ($x === "") return "";
        $err = DocCtrl::isFloat($x, $min, $max);
        if ($err == "") {
            if (intval($x) != floatval($x)) $err = sprintf(_("[%s] must be a integer") , $x);
        }
        
        return $err;
    }
    /** 
     * return true it is a number
     * @param string date date JJ/MM/AAAA
     */
    static public function isString($x, $p)
    {
        $err = "";
        if ($x === "") return "";
        if (!preg_match("/^$p$/", $x)) $err = sprintf(_("[%s] must match /%s/") , $x, $p);
        return array(
            "err" => $err
        );
    }
    /** 
     * return MENU_ACTIVE if user can execute the specified action
     * @param string $appname application name
     * @param string $actname action name
     * @return int
     */
    static public function canExecute($appname, $actname)
    {
        global $action;
        
        $err = $action->canExecute($actname, $appname);
        
        if ($err == "") return MENU_ACTIVE;
        return MENU_INVISIBLE;
    }
    /** 
     * return MENU_ACTIVE if user can edit document
     * @param string $appname application name
     * @param string $actname action name
     * @return int
     */
    public function canEditMenu()
    {
        
        $err = $this->canEdit();
        
        if ($err == "") return MENU_ACTIVE;
        return MENU_INVISIBLE;
    }
    /**
     * return MENU_ACTIVE if user can view or modify access in a profil document
     * @param string $acl acl name
     * @return int
     */
    public function controlAclAccess($acl)
    {
        //    if ($this->profid != $this->id) return MENU_INVISIBLE;
        $err = $this->control($acl);
        
        if ($err == "") return MENU_ACTIVE;
        return MENU_INVISIBLE;
    }
    /**
     * return MENU_ACTIVE if profil is actvate
     * @return int
     */
    function controlActifProfil()
    {
        $m = $this->controlAclAccess('modifyacl');
        if ($m == MENU_ACTIVE) $m = $this->profilIsActivate("true");
        return $m;
    }
    /**
     * return MENU_ACTIVE if user can view or modify access in a profil document
     * @param string $acl acl name
     * @return int
     */
    public function profilIsActivate($yes = true)
    {
        $err = $this->control('modifyacl');
        if ($err != "") return MENU_INVISIBLE;
        $err = $this->control('edit');
        if ($err != "") return MENU_INVISIBLE;
        
        $r = ($this->profid == $this->id);
        if ($yes == 'false') $r = !$r;
        
        if ($r) return MENU_ACTIVE;
        return MENU_INVISIBLE;
    }
}
?>
