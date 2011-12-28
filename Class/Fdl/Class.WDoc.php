<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Workflow Class Document
 *
 * @author Anakeen 2002
 * @version $Id: Class.WDoc.php,v 1.63 2009/01/08 17:47:07 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 */

include_once ('FDL/Class.Doc.php');
/**
 * WorkFlow Class
 */
class WDoc extends Doc
{
    /**
     * WDoc has its own special access depend on transition
     * by default the three access are always set
     *
     * @var array
     */
    var $acls = array(
        "view",
        "edit",
        "delete"
    );
    
    var $usefor = 'W';
    var $defDoctype = 'W';
    var $defClassname = 'WDoc';
    var $attrPrefix = "WF"; // prefix attribute
    
    /**
     * state's activities labels
     * @var array
     */
    var $stateactivity = array(); // label of states
    // --------------------------------------------------------------------
    //----------------------  TRANSITION DEFINITION --------------------
    var $transitions = array(); // set by childs classes
    var $cycle = array(); // set by childs classes
    var $autonext = array(); // set by childs classes
    var $firstState = ""; // first state in workflow
    var $viewnext = "list"; // view interface as select list may be (list|button)
    var $nosave = array(); // states where it is not permitted to save and stay (force next state)
    
    /**
     * document instance
     * @var Doc
     */
    public $doc = null;
    function __construct($dbaccess = '', $id = '', $res = '', $dbid = 0)
    {
        // first construct acl array
        $ka = POS_WF;
        if (is_array($this->transitions)) {
            foreach ($this->transitions as $k => $trans) {
                $this->dacls[$k] = array(
                    "pos" => $ka,
                    "description" => _($k)
                );
                $this->acls[] = $k;
                $ka++;
            }
        }
        if (isset($this->fromid)) $this->defProfFamId = $this->fromid; // it's a profil itself
        // don't use Doc constructor because it could call this constructor => infinitive loop
        DocCtrl::__construct($dbaccess, $id, $res, $dbid);
    }
    /**
     * affect document instance
     * @param Doc $doc
     */
    function set(Doc & $doc)
    {
        if ((!isset($this->doc)) || ($this->doc->id != $doc->id)) {
            $this->doc = & $doc;
            if (($doc->doctype != 'C') && ($doc->state == "")) {
                $doc->state = $this->getFirstState();
                $this->changeProfil($doc->state);
                $this->changeCv($doc->state);
            }
        }
    }
    function getFirstState()
    {
        return $this->firstState;
    }
    /**
     * change profil according to state
     * @param string $newstate new state of document
     */
    function changeProfil($newstate)
    {
        
        if ($newstate != "") {
            $profid = $this->getValue($this->_Aid("_ID", $newstate));
            if (!is_numeric($profid)) $profid = getIdFromName($this->dbaccess, $profid);
            if ($profid > 0) {
                // change only if new profil
                $err = $this->doc->setProfil($profid);
            }
        }
        return $err;
    }
    /**
     * change allocate user according to state
     * @param string $newstate new state of document
     */
    function changeAllocateUser($newstate)
    {
        $err = "";
        if ($newstate != "") {
            $auserref = trim($this->getValue($this->_Aid("_AFFECTREF", $newstate)));
            if ($auserref) {
                $uid = $this->getAllocatedUser($newstate);
                if ($uid) $wuid = $this->getDocValue($uid, "us_whatid");
                if ($wuid > 0) {
                    $lock = (trim($this->getValue($this->_Aid("_AFFECTLOCK", $newstate))) == "yes");
                    $err = $this->doc->allocate($wuid, "", false, $lock);
                    if ($err == "") {
                        $automail = (trim($this->getValue($this->_Aid("_AFFECTMAIL", $newstate))) == "yes");
                        if ($automail) {
                            include_once ("FDL/mailcard.php");
                            $to = trim($this->getDocValue($uid, "us_mail"));
                            if (!$to) addWarningMsg(sprintf(_("%s has no email address") , $this->getTitle($uid)));
                            else {
                                $subject = sprintf(_("allocation for %s document") , $this->doc->title);
                                $err = sendCard($action, $this->doc->id, $to, "", $subject, "", true, $commentaction, "", "", "htmlnotif");
                                if ($err != "") addWarningMsg($err);
                            }
                        }
                    }
                }
            } else $err = $this->doc->unallocate("", false);
        }
        return $err;
    }
    
    private function getAllocatedUser($newstate)
    {
        $auserref = trim($this->getValue($this->_Aid("_AFFECTREF", $newstate)));
        $type = trim($this->getValue($this->_Aid("_AFFECTTYPE", $newstate)));
        if (!$auserref) return false;
        $wuid = false;
        $aid = strtok($auserref, " ");
        switch ($type) {
            case 'F': // fixed address
                //	$wuid=$this->getDocValue($aid,"us_whatid");
                $uid = $aid;
                break;

            case 'PR': // docid parameter
                $uid = $this->doc->getparamValue($aid);
                //	if ($uid) $wuid=$this->getDocValue($uid,"us_whatid");
                break;

            case 'WPR': // workflow docid parameter
                $uid = $this->getparamValue($aid);
                //	if ($uid) $wuid=$this->getDocValue($uid,"us_whatid");
                break;

            case 'D': // user relations
                $uid = $this->doc->getRValue($aid);
                //	if ($uid)  $wuid=$this->getDocValue($docid,'us_whatid');
                break;

            case 'WD': // user relations
                $uid = $this->getRValue($aid);
                //	if ($uid) $wuid=$this->getDocValue($docid,'us_whatid');
                break;
        }
        return $uid;
    }
    /**
     * change cv according to state
     * @param string $newstate new state of document
     */
    function changeCv($newstate)
    {
        
        if ($newstate != "") {
            $cvid = ($this->getValue($this->_Aid("_CVID", $newstate)));
            if (!is_numeric($cvid)) $cvid = getIdFromName($this->dbaccess, $cvid);
            if ($cvid > 0) {
                // change only if set
                $this->doc->cvid = $cvid;
            } else {
                $fdoc = $this->doc->getFamDoc();
                $this->doc->cvid = $fdoc->ccvid;
            }
        }
    }
    
    private function _Aid($fix, $state)
    {
        return strtolower($this->attrPrefix . $fix . str_replace(":", "_", $state));
    }
    /**
     * get the profile id according to state
     * @param string $state
     * @return string
     */
    public function getStateProfil($state)
    {
        return $this->getValue($this->_Aid("_id", $state));
    }
    /**
     * get the attribute id for profile id according to state
     * @param string $state
     * @return string
     */
    public function getStateProfilAttribute($state)
    {
        return $this->_Aid("_id", $state);
    }
    /**
     * get the mask id according to state
     * @param string $state
     * @return string
     */
    public function getStateMask($state)
    {
        return $this->getValue($this->_Aid("_mskid", $state));
    }
    /**
     * get the view control id according to state
     * @param string $state
     * @return string
     */
    public function getStateViewControl($state)
    {
        return $this->getValue($this->_Aid("_cvid", $state));
    }
    /**
     * get the timers ids according to state
     * @param string $state
     * @return string
     */
    public function getStateTimers($state)
    {
        return $this->getValue($this->_Aid("_tmid", $state));
    }
    /**
     * get the mail templates ids according to state
     * @param string $state
     * @return array
     */
    public function getStateMailTemplate($state)
    {
        return $this->getTValue($this->_Aid("_mtid", $state));
    }
    /**
     * create of parameters attributes of workflow
     */
    function createProfileAttribute()
    {
        if ($this->doctype == 'C') $cid = $this->id;
        else $cid = $this->fromid;
        
        $ordered = 1000;
        // delete old attributes before
        $this->exec_query(sprintf("delete from docattr where docid=%d  and options ~ 'autocreated=yes'", intval($cid)));
        $this->getStates();
        foreach ($this->states as $k => $state) {
            // --------------------------
            // frame
            $aidframe = $this->_Aid("_FR", $state);
            $oattr = new DocAttr($this->dbaccess, array(
                $cid,
                $aidframe
            ));
            $oattr->docid = $cid;
            $oattr->visibility = "W";
            $oattr->type = "frame";
            $oattr->id = $aidframe;
            $oattr->frameid = "wf_tab_states";
            $oattr->labeltext = sprintf(_("parameters for %s step") , _($state));
            $oattr->link = "";
            $oattr->phpfunc = "";
            $oattr->options = "autocreated=yes";
            $oattr->ordered = $ordered++;
            if ($oattr->isAffected()) $oattr->Modify();
            else $oattr->Add();
            // --------------------------
            // profil id
            $aid = $this->_Aid("_", $state);
            $aidprofilid = $this->_Aid("_ID", $state); //strtolower($this->attrPrefix."_ID".strtoupper($state));
            $oattr = new DocAttr($this->dbaccess, array(
                $cid,
                $aidprofilid
            ));
            $oattr->docid = $cid;
            $oattr->visibility = "W";
            $oattr->type = 'docid("PROFIL")';
            $oattr->id = $aidprofilid;
            $oattr->labeltext = sprintf(_("%s profile") , _($state));
            $oattr->link = "";
            $oattr->frameid = $aidframe;
            $oattr->options = "autocreated=yes";
            
            $oattr->phpfile = "fdl.php";
            $oattr->phpfunc = "lprofil(D,CT,WF_FAMID):$aidprofilid,CT";
            $oattr->ordered = $ordered++;
            if ($oattr->isAffected()) $oattr->Modify();
            else $oattr->Add();
            // --------------------------
            // mask id
            $aid = $this->_Aid("_MSKID", $state);
            
            $oattr = new DocAttr($this->dbaccess, array(
                $cid,
                $aid
            ));
            $oattr->docid = $cid;
            $oattr->visibility = "W";
            $oattr->type = 'docid("MASK")';
            $oattr->id = $aid;
            $oattr->labeltext = sprintf(_("%s mask") , _($state));
            $oattr->link = "";
            $oattr->frameid = $aidframe;
            $oattr->phpfile = "fdl.php";
            $oattr->phpfunc = "lmask(D,CT,WF_FAMID):$aid,CT";
            $oattr->elink = '';
            $oattr->options = 'autocreated=yes|creation={autoclose:"yes",msk_famid:wf_famid,ba_title:"' . _($state) . '"}';
            $oattr->ordered = $ordered++;
            if ($oattr->isAffected()) $oattr->Modify();
            else $oattr->Add();
            // --------------------------
            // state color
            $aid = $this->_Aid("_COLOR", $state);
            $oattr = new DocAttr($this->dbaccess, array(
                $cid,
                $aid
            ));
            $oattr->docid = $cid;
            $oattr->visibility = "W";
            $oattr->type = "color";
            $oattr->link = "";
            $oattr->phpfile = "";
            $oattr->id = $aid;
            $oattr->frameid = $aidframe;
            $oattr->ordered = $ordered++;
            $oattr->phpfunc = "";
            $oattr->options = "autocreated=yes";
            $oattr->labeltext = sprintf(_("%s color") , _($state));
            if ($oattr->isAffected()) $oattr->Modify();
            else $oattr->Add();
            // --------------------------
            // CV link
            $aid = $this->_Aid("_CVID", $state);
            $oattr = new DocAttr($this->dbaccess, array(
                $cid,
                $aid
            ));
            $oattr->docid = $cid;
            $oattr->visibility = "W";
            $oattr->type = 'docid("CVDOC")';
            $oattr->link = "";
            $oattr->phpfile = "fdl.php";
            $oattr->phpfunc = "lcvdoc(D,CT,WF_FAMID):$aid,CT";
            $oattr->elink = '';
            $oattr->options = 'autocreated=yes|creation={autoclose:"yes",cv_famid:wf_famid,ba_title:"' . _($state) . '"}';
            $oattr->id = $aid;
            $oattr->frameid = $aidframe;
            $oattr->ordered = $ordered++;
            
            $oattr->labeltext = sprintf(_("%s cv") , _($state));
            if ($oattr->isAffected()) $oattr->Modify();
            else $oattr->Add();
            // --------------------------
            // Mail template link
            $aid = $this->_Aid("_MTID", $state);
            $oattr = new DocAttr($this->dbaccess, array(
                $cid,
                $aid
            ));
            $oattr->docid = $cid;
            $oattr->visibility = "W";
            $oattr->type = 'docid("MAILTEMPLATE")';
            $oattr->link = "";
            $oattr->phpfile = "fdl.php";
            $oattr->phpfunc = "lmailtemplatedoc(D,CT,WF_FAMID):$aid,CT";
            $oattr->id = $aid;
            $oattr->frameid = $aidframe;
            $oattr->options = "multiple=yes|autocreated=yes";
            
            $oattr->elink = '';
            $oattr->options = 'autocreated=yes|multiple=yes|creation={autoclose:"yes",tmail_family:wf_famid,tmail_workflow:fromid}';
            $oattr->ordered = $ordered++;
            $oattr->labeltext = sprintf(_("%s mail template") , _($state));
            if ($oattr->isAffected()) $oattr->Modify();
            else $oattr->Add();
            // --------------------------
            //  Timer link
            $aid = $this->_Aid("_TMID", $state);
            $oattr = new DocAttr($this->dbaccess, array(
                $cid,
                $aid
            ));
            $oattr->docid = $cid;
            $oattr->visibility = "W";
            $oattr->type = 'docid("TIMER")';
            $oattr->link = "";
            $oattr->phpfile = "fdl.php";
            $oattr->phpfunc = "ltimerdoc(D,CT,WF_FAMID):$aid,CT";
            $oattr->id = $aid;
            $oattr->elink = '';
            $oattr->options = 'autocreated=yes|creation={autoclose:"yes",tm_family:wf_famid,tm_workflow:fromid,tm_title:"' . _($state) . '"}';
            $oattr->frameid = $aidframe;
            $oattr->ordered = $ordered++;
            $oattr->labeltext = sprintf(_("%s timer") , _($state));
            if ($oattr->isAffected()) $oattr->Modify();
            else $oattr->Add();
            // --------------------------
            //  Ask link
            $aid = $this->_Aid("_ASKID", $state);
            $oattr = new DocAttr($this->dbaccess, array(
                $cid,
                $aid
            ));
            $oattr->docid = $cid;
            $oattr->visibility = "W";
            $oattr->type = 'docid("WASK")';
            $oattr->link = "";
            $oattr->phpfile = "";
            $oattr->phpfunc = "";
            $oattr->id = $aid;
            $oattr->elink = '';
            $oattr->options = 'multiple=yes|autocreated=yes|creation={autoclose:"yes"}';
            $oattr->frameid = $aidframe;
            $oattr->ordered = $ordered++;
            $oattr->labeltext = sprintf(_("%s wask") , _($state));
            if ($oattr->isAffected()) $oattr->Modify();
            else $oattr->Add();
            // --------------------------
            // Label action
            $aid = $this->_Aid("_ACTIVITYLABEL", $k);
            $oattr = new DocAttr($this->dbaccess, array(
                $cid,
                $aid
            ));
            $oattr->docid = $cid;
            
            if ($this->stateactivity[$k]) {
                $oattr->visibility = "S";
            } else $oattr->visibility = "W";
            $oattr->type = 'text';
            $oattr->link = "";
            $oattr->phpfile = "";
            $oattr->phpfunc = "";
            $oattr->id = $aid;
            $oattr->options = "autocreated=yes";
            $oattr->frameid = $aidframe;
            $oattr->ordered = $ordered++;
            
            $oattr->labeltext = sprintf(_("%s activity") , _($k));
            if ($oattr->isAffected()) $oattr->Modify();
            else $oattr->Add();
            // --------------------------
            //  Affected user link
            $aid = $this->_Aid("_T_AFFECT", $state);
            $afaid = $aid;
            $oattr = new DocAttr($this->dbaccess, array(
                $cid,
                $aid
            ));
            $oattr->docid = $cid;
            $oattr->visibility = "U";
            $oattr->type = 'array';
            $oattr->id = $aid;
            $oattr->frameid = $aidframe;
            $oattr->options = "vlabel=none|autocreated=yes";
            $oattr->ordered = $ordered++;
            $oattr->labeltext = sprintf(_("%s affectation") , _($state));
            if ($oattr->isAffected()) $oattr->Modify();
            else $oattr->Add();
            
            $aid = $this->_Aid("_AFFECTTYPE", $state);
            $aidtype = $aid;
            $oattr = new DocAttr($this->dbaccess, array(
                $cid,
                $aid
            ));
            $oattr->docid = $cid;
            $oattr->visibility = "W";
            $oattr->type = 'enum';
            $oattr->options = "autocreated=yes";
            $oattr->phpfunc = "F|" . _("Utilisateur fixe") . ",D|" . _("Attribut relation") . ",PR|" . _("Relation parametre") . ",WD|" . _("Relation cycle") . ",WPR|" . _("Parametre cycle");
            $oattr->id = $aid;
            $oattr->frameid = $afaid;
            $oattr->ordered = $ordered++;
            $oattr->labeltext = sprintf(_("%s affectation type") , _($state));
            if ($oattr->isAffected()) $oattr->Modify();
            else $oattr->Add();
            
            $aid = $this->_Aid("_AFFECTREF", $state);
            $oattr = new DocAttr($this->dbaccess, array(
                $cid,
                $aid
            ));
            $oattr->docid = $cid;
            $oattr->visibility = "W";
            $oattr->type = 'text';
            $oattr->link = "";
            $oattr->options = "cwidth=160px|autocreated=yes";
            $oattr->phpfile = "fdl.php";
            $oattr->phpfunc = "tpluser(D,$aidtype,WF_FAMID,FROMID,$aid):$aid";
            $oattr->id = $aid;
            $oattr->frameid = $afaid;
            $oattr->ordered = $ordered++;
            $oattr->labeltext = sprintf(_("%s affected user") , _($state));
            if ($oattr->isAffected()) $oattr->Modify();
            else $oattr->Add();
            
            $aid = $this->_Aid("_AFFECTLOCK", $state);
            $oattr = new DocAttr($this->dbaccess, array(
                $cid,
                $aid
            ));
            $oattr->docid = $cid;
            $oattr->visibility = "W";
            $oattr->type = 'enum';
            $oattr->link = "";
            $oattr->options = "eformat=bool|autocreated=yes";
            $oattr->phpfunc = "no|" . _("affect no lock") . ",yes|" . _("affect auto lock");
            $oattr->id = $aid;
            $oattr->frameid = $afaid;
            $oattr->ordered = $ordered++;
            $oattr->labeltext = sprintf(_("%s autolock") , _($state));
            if ($oattr->isAffected()) $oattr->Modify();
            else $oattr->Add();
            
            $aid = $this->_Aid("_AFFECTMAIL", $state);
            $oattr = new DocAttr($this->dbaccess, array(
                $cid,
                $aid
            ));
            $oattr->docid = $cid;
            $oattr->visibility = "W";
            $oattr->type = 'enum';
            $oattr->link = "";
            $oattr->options = "eformat=bool|autocreated=yes";
            $oattr->phpfunc = "no|" . _("affect no mail") . ",yes|" . _("affect auto mail");
            $oattr->id = $aid;
            $oattr->frameid = $afaid;
            $oattr->ordered = $ordered++;
            $oattr->labeltext = sprintf(_("%s automail") , _($state));
            if ($oattr->isAffected()) $oattr->Modify();
            else $oattr->Add();
        }
        
        foreach ($this->transitions as $k => $trans) {
            // --------------------------
            // frame
            $aidframe = $this->_Aid("_TRANS_FR", $k);
            $oattr = new DocAttr($this->dbaccess, array(
                $cid,
                $aidframe
            ));
            $oattr->docid = $cid;
            $oattr->visibility = "W";
            $oattr->type = "frame";
            $oattr->id = $aidframe;
            $oattr->frameid = "wf_tab_transitions";
            $oattr->labeltext = sprintf(_("parameters for %s transition") , _($k));
            $oattr->link = "";
            $oattr->phpfunc = "";
            $oattr->options = "autocreated=yes";
            $oattr->ordered = $ordered++;
            if ($oattr->isAffected()) $oattr->Modify();
            else $oattr->Add();
            // --------------------------
            // Mail template link
            $aid = $this->_Aid("_TRANS_MTID", $k);
            $oattr = new DocAttr($this->dbaccess, array(
                $cid,
                $aid
            ));
            $oattr->docid = $cid;
            $oattr->visibility = "W";
            $oattr->type = 'docid("MAILTEMPLATE")';
            $oattr->link = "";
            $oattr->phpfile = "fdl.php";
            $oattr->phpfunc = "lmailtemplatedoc(D,CT,WF_FAMID):$aid,CT";
            $oattr->elink = "";
            $oattr->id = $aid;
            $oattr->frameid = $aidframe;
            $oattr->ordered = $ordered++;
            $oattr->options = 'autocreated=yes|multiple=yes|creation={autoclose:"yes",tmail_family:wf_famid,tmail_workflow:fromid}';
            
            $oattr->labeltext = sprintf(_("%s mail template") , _($k));
            if ($oattr->isAffected()) $oattr->Modify();
            else $oattr->Add();
            // --------------------------
            // Timer link
            $aid = $this->_Aid("_TRANS_TMID", $k);
            $oattr = new DocAttr($this->dbaccess, array(
                $cid,
                $aid
            ));
            $oattr->docid = $cid;
            $oattr->visibility = "W";
            $oattr->type = 'docid("TIMER")';
            $oattr->link = "";
            $oattr->phpfile = "fdl.php";
            $oattr->phpfunc = "ltimerdoc(D,CT,WF_FAMID):$aid,CT";
            $oattr->elink = "";
            $oattr->options = 'autocreated=yes|creation={autoclose:"yes",tm_family:wf_famid,tm_workflow:fromid,tm_title:"' . _($state) . '"}';
            
            $oattr->id = $aid;
            $oattr->frameid = $aidframe;
            $oattr->ordered = $ordered++;
            $oattr->labeltext = sprintf(_("%s timer") , _($k));
            if ($oattr->isAffected()) $oattr->Modify();
            else $oattr->Add();
            // --------------------------
            // Persistent Attach Timer link
            $aid = $this->_Aid("_TRANS_PA_TMID", $k);
            $oattr = new DocAttr($this->dbaccess, array(
                $cid,
                $aid
            ));
            $oattr->docid = $cid;
            $oattr->visibility = "W";
            $oattr->type = 'docid("TIMER")';
            $oattr->link = "";
            $oattr->phpfile = "fdl.php";
            $oattr->phpfunc = "ltimerdoc(D,CT,WF_FAMID):$aid,CT";
            $oattr->elink = "";
            $oattr->options = 'multiple=yes|autocreated=yes|creation={autoclose:"yes",tm_family:wf_famid,tm_workflow:fromid,tm_title:"' . _($state) . '"}';
            
            $oattr->id = $aid;
            $oattr->frameid = $aidframe;
            $oattr->ordered = $ordered++;
            $oattr->labeltext = sprintf(_("%s persistent timer") , _($k));
            if ($oattr->isAffected()) $oattr->Modify();
            else $oattr->Add();
            // --------------------------
            // Persistent UnAttach Timer link
            $aid = $this->_Aid("_TRANS_PU_TMID", $k);
            $oattr = new DocAttr($this->dbaccess, array(
                $cid,
                $aid
            ));
            $oattr->docid = $cid;
            $oattr->visibility = "W";
            $oattr->type = 'docid("TIMER")';
            $oattr->link = "";
            $oattr->phpfile = "fdl.php";
            $oattr->phpfunc = "ltimerdoc(D,CT,WF_FAMID):$aid,CT";
            $oattr->elink = "";
            $oattr->id = $aid;
            $oattr->options = "multiple=yes|autocreated=yes";
            $oattr->frameid = $aidframe;
            $oattr->ordered = $ordered++;
            $oattr->labeltext = sprintf(_("%s unattach timer") , _($k));
            if ($oattr->isAffected()) $oattr->Modify();
            else $oattr->Add();
        }
        refreshPhpPgDoc($this->dbaccess, $cid);
    }
    /**
     * change state of a document
     * the method {@link set()} must be call before
     * @param string $newstate the next state
     * @param string $comment comment to be set in history (describe why change state)
     * @param bool $force is true when it is the second passage (without interactivity)
     * @param bool $withcontrol set to false if you want to not verify control permission ot transition
     * @param bool $wm1 set to false if you want to not apply m1 methods
     * @param bool $wm2 set to false if you want to not apply m2 methods
     * @param bool $need set to false if you want to not verify needed attribute are set
     * @return string error message, if no error empty string
     */
    function changeState($newstate, $addcomment = "", $force = false, $withcontrol = true, $wm1 = true, $wm2 = true, $wneed = true)
    {
        // if ($this->doc->state == $newstate) return ""; // no change => no action
        // search if possible change in concordance with transition array
        $foundFrom = false;
        $foundTo = false;
        reset($this->cycle);
        while (list($k, $trans) = each($this->cycle)) {
            if (($this->doc->state == $trans["e1"])) {
                // from state OK
                $foundFrom = true;
                if ($newstate == $trans["e2"]) {
                    $foundTo = true;
                    $tr = $this->transitions[$trans["t"]];
                    $tname = $trans["t"];
                }
            }
        }
        
        if ($this->userid != 1) { // admin can go to any states
            if (!$foundTo) return (sprintf(_("ChangeState :: the new state '%s' is not known or is not allowed from %s") , _($newstate) , _($this->doc->state)));
            if (!$foundFrom) return (sprintf(_("ChangeState :: the initial state '%s' is not known") , _($this->doc->state)));
        }
        // verify if completed doc
        if ($wneed) {
            $err = $this->doc->isCompleteNeeded();
            if ($err != "") return $err;
        }
        // verify if privilege granted
        if ($withcontrol) $err = $this->control($tname);
        if ($err != "") return $err;
        
        if ($wm1 && ($tr["m1"] != "")) {
            // apply first method (condition for the change)
            if (!method_exists($this, $tr["m1"])) return (sprintf(_("the method '%s' is not known for the object class %s") , $tr["m1"], get_class($this)));
            
            $err = call_user_func(array(
                $this,
                $tr["m1"]
            ) , $newstate, $this->doc->state, $addcomment);
            
            if ($err == "->") {
                if ($force) {
                    $err = ""; // it is the return of the report
                    SetHttpVar("redirect_app", ""); // override the redirect
                    SetHttpVar("redirect_act", "");
                } else {
                    if ($addcomment != "") $this->doc->AddComment($addcomment); // add comment now because it will be lost
                    return ""; //it is not a real error, but don't change state (reported)
                    
                }
            }
            if ($err != "") {
                $this->doc->unlock(true);
                return (sprintf(_("The change state to %s has been aborted.\n%s") , _($newstate) , $err));
            }
        }
        // change the state
        $oldstate = $this->doc->state == "" ? " " : $this->doc->state;
        $this->doc->state = $newstate;
        $this->changeProfil($newstate);
        $this->changeCv($newstate);
        $this->doc->disableEditControl();
        $err = $this->doc->Modify(); // don't control edit permission
        if ($err != "") return $err;
        
        $revcomment = sprintf(_("change state : %s to %s") , _($oldstate) , _($newstate));
        if ($addcomment != "") $this->doc->AddComment($addcomment);
        if (isset($tr["ask"])) {
            foreach ($tr["ask"] as $vpid) {
                $pv = $this->getValue($vpid);
                if ($pv != "") {
                    $oa = $this->getAttribute($vpid);
                    $revcomment.= "\n-" . $oa->getLabel() . ":" . $pv;
                }
            }
        }
        
        $err = $this->doc->AddRevision($revcomment);
        if ($err != "") {
            $this->doc->disableEditControl(); // restore old states
            $this->doc->state = $oldstate;
            $this->changeProfil($oldstate);
            $this->changeCv($oldstate);
            $err2 = $this->doc->Modify(); // don't control edit permission
            $this->doc->enableEditControl();
            
            return $err . $err2;
        }
        AddLogMsg(sprintf(_("%s new state %s") , $this->doc->title, _($newstate)));
        
        $this->doc->enableEditControl();
        // post action
        if ($wm2 && ($tr["m2"] != "")) {
            if (!method_exists($this, $tr["m2"])) return (sprintf(_("the method '%s' is not known for the object class %s") , $tr["m2"], get_class($this)));
            $err = call_user_func(array(
                $this,
                $tr["m2"]
            ) , $newstate, $oldstate, $addcomment);
            
            if ($err == "->") $err = ""; //it is not a real error
            if ($err != "") $err = sprintf(_("The change state to %s has been realized. But the following warning is appeared.\n%s") , _($newstate) , $err);
        }
        $this->doc->addLog("state", array(
            "id" => $this->id,
            "initid" => $this->initid,
            "revision" => $this->revision,
            "title" => $this->title,
            "state" => $this->state,
            "message" => $err
        ));
        $this->doc->disableEditControl();
        if (!$this->domainid) $this->doc->unlock(false, true);
        $this->workflowSendMailTemplate($newstate, $addcomment, $tname);
        $this->workflowAttachTimer($newstate, $tname);
        $err.= $this->changeAllocateUser($newstate);
        $this->doc->enableEditControl();
        return $err;
    }
    // --------------------------------------------------------------------
    function getFollowingStates()
    {
        // search if following states in concordance with transition array
        if ($this->doc->locked == - 1) return array(); // no next state for revised document
        if (($this->doc->locked > 0) && ($this->doc->locked != $this->doc->userid)) return array(); // no next state if locked by another person
        $fstate = array();
        if ($this->doc->state == "") $this->doc->state = $this->getFirstState();
        
        if ($this->userid == 1) return $this->getStates(); // only admin can go to any states from anystates
        reset($this->cycle);
        while (list($k, $tr) = each($this->cycle)) {
            if ($this->doc->state == $tr["e1"]) {
                // from state OK
                if ($this->control($tr["t"]) == "") $fstate[] = $tr["e2"];
            }
        }
        return $fstate;
    }
    
    function getStates()
    {
        if (!isset($this->states)) {
            $this->states = array();
            reset($this->cycle);
            while (list($k, $tr) = each($this->cycle)) {
                if ($tr["e1"] != "") $this->states[$tr["e1"]] = $tr["e1"];
                if ($tr["e2"] != "") $this->states[$tr["e2"]] = $tr["e2"];
            }
        }
        return $this->states;
    }
    /**
     * get associated color of a state
     * @param string $state the state
     * @return string the color (#RGB)
     */
    function getColor($state, $def = "")
    {
        //$acolor=$this->attrPrefix."_COLOR".($state);
        $acolor = $this->_Aid("_COLOR", $state);
        return $this->getValue($acolor, $def);
    }
    /**
     * get activity (localized language)
     * @param string $state the state
     * @return string the text of action
     */
    function getActivity($state, $def = "")
    {
        //$acolor=$this->attrPrefix."_ACTIVITYLABEL".($state);
        $acolor = $this->_Aid("_ACTIVITYLABEL", $state);
        $v = $this->getValue($acolor);
        if ($v) return _($v);
        return $def;
    }
    /**
     * get action (localized language)
     * @deprecated
     * @param string $state the state
     * @return string the text of action
     */
    function getAction($state, $def = "")
    {
        deprecatedFunction();
        return $this->getActivity($state, $def);
    }
    /**
     * get askes for a document
     * searcj all WASK document which current user can see for a specific state
     * @param string $state the state
     * @param bool $control set to false to not control ask access
     * @return string the text of action
     */
    function getDocumentWasks($state, $control = true)
    {
        $aask = $this->_Aid("_ASKID", $state);
        $vasks = $this->getTValue($aask);
        if ($control) {
            $cask = array();
            foreach ($vasks as $askid) {
                $ask = new_doc($this->dbaccess, $askid);
                $ask->set($this->doc);
                if ($ask->isAlive() && ($ask->control('answer') == "")) $cask[] = $ask->id;
            }
            return $cask;
        } else {
            return $vasks;
        }
    }
    /**
     * verify if askes are defined
     *
     * @return bool true if at least one ask is set in workflow
     */
    function hasWasks()
    {
        $states = $this->getStates();
        foreach ($states as $state) {
            $aask = $this->_Aid("_ASKID", $state);
            if ($this->getValue($aask)) return true;
        }
        return false;
    }
    /**
     * send associated mail of a state
     * @param string $state the state
     * @param string $comment reason of change state
     * @param array $tname transition name
     * @return Doc
     */
    function workflowSendMailTemplate($state, $comment = "", $tname = "")
    {
        $tmtid = $this->getTValue($this->_Aid("_TRANS_MTID", $tname));
        
        $tr = $this->transitions[$tname];
        if ($tmtid && (count($tmtid) > 0)) {
            foreach ($tmtid as $mtid) {
                $keys = array();
                $mt = new_doc($this->dbaccess, $mtid);
                if ($mt->isAlive()) {
                    $keys["WCOMMENT"] = nl2br($comment);
                    if (isset($tr["ask"])) {
                        foreach ($tr["ask"] as $vpid) {
                            $keys["V_" . strtoupper($vpid) ] = $this->getHtmlAttrValue($vpid);
                            $keys[strtoupper($vpid) ] = $this->getValue($vpid);
                        }
                    }
                    $err = $mt->sendDocument($this->doc, $keys);
                }
            }
        }
        
        $tmtid = $this->getTValue($this->_Aid("_MTID", $state));
        if ($tmtid && (count($tmtid) > 0)) {
            foreach ($tmtid as $mtid) {
                $keys = array();
                $mt = new_doc($this->dbaccess, $mtid);
                if ($mt->isAlive()) {
                    $keys["WCOMMENT"] = nl2br($comment);
                    if (isset($tr["ask"])) {
                        foreach ($tr["ask"] as $vpid) {
                            $keys["V_" . strtoupper($vpid) ] = $this->getHtmlAttrValue($vpid);
                            $keys[strtoupper($vpid) ] = $this->getValue($vpid);
                        }
                    }
                    $err = $mt->sendDocument($this->doc, $keys);
                }
            }
        }
        return $err;
    }
    /**
     * attach timer to a document
     * @param string $state the state
     * @param array $tname transition name
     * @return Doc
     */
    function workflowAttachTimer($state, $tname = "")
    {
        $mtid = $this->getValue($this->_Aid("_TRANS_TMID", $tname));
        
        $this->doc->unattachAllTimers($this);
        $tr = $this->transitions[$tname];
        if ($mtid) {
            $mt = new_doc($this->dbaccess, $mtid);
            if ($mt->isAlive()) {
                $err = $this->doc->attachTimer($mt, $this);
            }
        }
        // unattach persistent
        $tmtid = $this->getTValue($this->_Aid("_TRANS_PU_TMID", $tname));
        if ($tmtid && (count($tmtid) > 0)) {
            foreach ($tmtid as $mtid) {
                $mt = new_doc($this->dbaccess, $mtid);
                if ($mt->isAlive()) {
                    $err.= $this->doc->unattachTimer($mt);
                }
            }
        }
        
        $mtid = $this->getValue($this->_Aid("_TMID", $state));
        if ($mtid) {
            $mt = new_doc($this->dbaccess, $mtid);
            if ($mt->isAlive()) {
                $err.= $this->doc->attachTimer($mt, $this);
            }
        }
        // attach persistent
        $tmtid = $this->getTValue($this->_Aid("_TRANS_PA_TMID", $tname));
        if ($tmtid && (count($tmtid) > 0)) {
            foreach ($tmtid as $mtid) {
                $mt = new_doc($this->dbaccess, $mtid);
                if ($mt->isAlive()) {
                    $err.= $this->doc->attachTimer($mt);
                }
            }
        }
        return $err;
    }
    
    function changeStateOfDocid($docid, $newstate, $comment = "")
    {
        $cmd = new_Doc($this->dbaccess, $docid);
        $cmdid = $cmd->latestId(); // get the latest
        $cmd = new_Doc($this->dbaccess, $cmdid);
        
        if ($cmd->wid > 0) {
            $wdoc = new_Doc($this->dbaccess, $cmd->wid);
            
            if (!$wdoc) $err = sprintf(_("cannot change state of document #%d to %s") , $cmd->wid, $newstate);
            if ($err != "") return $err;
            $wdoc->Set($cmd);
            $err = $wdoc->ChangeState($newstate, sprintf(_("automaticaly by change state of %s\n%s") , $this->doc->title, $comment));
            if ($err != "") return $err;
        }
    }
    /**
     * get transition array for the transition between $to and $from states
     * @param string $to first state
     * @param string $from next state
     * @return array transition array (false if not found)
     */
    function getTransition($from, $to)
    {
        foreach ($this->cycle as $v) {
            if (($v["e1"] == $from) && ($v["e2"] == $to)) {
                $t = $this->transitions[$v["t"]];
                $t["id"] = $v["t"];
                return $t;
            }
        }
        return false;
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
        $err = Doc::Control($aclname);
        if ($err == "") return $err; // normal case
        if ($this->getValue("DPDOC_FAMID") > 0) {
            // special control for dynamic users
            if (!isset($this->pdoc)) {
                $pdoc = createDoc($this->dbaccess, $this->fromid, false);
                $pdoc->doctype = "T"; // temporary
                //	$pdoc->setValue("DPDOC_FAMID",$this->getValue("DPDOC_FAMID"));
                $err = $pdoc->Add();
                if ($err != "") return "WDoc::Control:" . $err; // can't create profil
                $pdoc->setProfil($this->profid, $this->doc);
                
                $this->pdoc = & $pdoc;
            }
            $err = $this->pdoc->DocControl($aclname);
        }
        return $err;
    }
    /**
     * affect action label
     */
    function postModify()
    {
        foreach ($this->stateactivity as $k => $v) {
            $this->setValue($this->_Aid("_ACTIVITYLABEL", $k) , $v);
        }
        $this->getStates();
        foreach ($this->states as $k => $state) {
            $allo = trim($this->getValue($this->_Aid("_AFFECTREF", $state)));
            if (!$allo) $this->removeArrayRow($this->_Aid("_T_AFFECT", $state) , 0);
        }
        
        if ($this->isChanged()) $this->modify();
    }
    /**
     * get value of instanced document
     * @param string $attrid attribute identificator
     * @return string return the value, false if attribute not exist or document not set
     */
    function getInstanceValue($attrid, $def = false)
    {
        if ($this->doc) {
            return $this->doc->getValue($attrid, $def);
        }
        return $def;
    }
}
?>
