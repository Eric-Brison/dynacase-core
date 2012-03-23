<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Specials methods for GROUP family
 *
 * @author Anakeen 2003
 * @version $Id: Method.DocGroup.php,v 1.15 2007/02/16 07:36:28 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage USERCARD
 */
/**
 * @begin-method-ignore
 * this part will be deleted when construct document class until end-method-ignore
 */
class _GROUP extends Dir
{
    /*
     * @end-method-ignore
    */
    /**
     * reconstruct mail group & recompute parent group
     *
     * @return string error message, if no error empty string
     * @see Doc::PostModify()
     */
    function PostModify()
    {
        
        $err = $this->SetGroupMail();
        $this->refreshParentGroup();
        return $err;
    }
    /**
     * recompute only parent group
     * @apiExpose
     *
     * @return string error message, if no error empty string
     */
    function RefreshGroup()
    {
        global $refreshedGrpId; // to avoid inifinitive loop recursion
        $err = "";
        if (!isset($refreshedGrpId[$this->id])) {
            $err = $this->SetGroupMail();
            $err.= $this->modify();
            $this->specPostInsert();
            $refreshedGrpId[$this->id] = true;
        }
        return $err;
    }
    /**
     * update groups table in USER database
     * @return string error message
     */
    function postInsertDoc($docid, $multiple)
    {
        $this->SetGroupMail();
        $this->refreshMembers();
        $this->specPostInsert();
    }
    /**
     * update groups table in USER database
     * @return string error message
     */
    function postMInsertDoc($tdocid)
    {
        $this->SetGroupMail();
        $this->refreshMembers();
        $this->specPostInsert();
    }
    /**
     * update groups table in USER database before suppress
     * @return string error message
     */
    function postUnlinkDoc($docid)
    {
        $this->SetGroupMail();
        $this->refreshMembers();
        $this->specPostInsert();
    }
    /**
     * special method for child classes
     * call after insert user in group
     * @return string error message
     */
    function specPostInsert()
    {;
    }
    /**
     * compute the mail of the group
     * concatenation of each user mail and group member mail
     *
     *
     * @return string error message, if no error empty string
     */
    function SetGroupMail($nomail = false)
    {
        
        $err = "";
        $gmail = " ";
        $tmail = array();
        
        if (!$nomail) $nomail = ($this->getValue("grp_hasmail") == "no");
        //------------------------------------------------------
        // first compute mail from users members
        $tiduser = $this->getTValue("GRP_IDUSER");
        $tuser = $this->getTValue("GRP_USER");
        if (count($tiduser) > 0) {
            if (!$nomail) {
                foreach ($tiduser as $k => $v) {
                    
                    $udoc = getTDoc($this->dbaccess, $v);
                    if ($udoc) {
                        
                        $mail = getv($udoc, "us_mail");
                        
                        if ($mail != "") $tmail[] = $mail;
                    } else {
                        if ($tuser[$k] != "") $err.= sprintf("%s does not exist", $tuser[$k]);
                    }
                }
                $gmail = implode(", ", array_unique($tmail));
            }
        }
        $nodetectmembers = ($this->getValue("grp_hasmembers") == "no");
        // add mail groups
        //------------------------------------------------------
        // second compute mail from groups members
        $tgmemberid = $tiduser; // affiliated members ids
        $tgmember = $tuser; // affiliated members
        $tiduser = $this->getTValue("GRP_IDGROUP");
        if (count($tiduser) > 0) {
            while (list($k, $v) = each($tiduser)) {
                $udoc = new_Doc($this->dbaccess, $v);
                if ($udoc && $udoc->isAlive()) {
                    if (!$nomail) {
                        $mail = $udoc->getValue("GRP_MAIL");
                        if ($mail != "") {
                            $tmail1 = explode(",", str_replace(" ", "", $mail));
                            $tmail = array_merge($tmail, $tmail1);
                        }
                    }
                    if (!$nodetectmembers) {
                        $tgmemberid = array_merge($tgmemberid, $udoc->getTValue("GRP_IDRUSER"));
                        $tgmember = array_merge($tgmember, $udoc->getTValue("GRP_RUSER"));
                    }
                }
            }
            
            $gmail = implode(", ", array_unique($tmail));
        }
        $tgmembers = array();
        reset($tgmemberid);
        while (list($k, $v) = each($tgmemberid)) {
            $tgmembers[$v] = $tgmember[$k];
        }
        
        if ($nodetectmembers) {
            $this->DeleteValue("GRP_IDRUSER");
            $this->DeleteValue("GRP_RUSER");
        } else {
            if (count($tgmembers) > 0) {
                $this->SetValue("GRP_IDRUSER", array_keys($tgmembers));
                $this->SetValue("GRP_RUSER", $tgmembers);
            } else {
                $this->DeleteValue("GRP_IDRUSER");
                $this->DeleteValue("GRP_RUSER");
            }
        }
        
        if (!$nomail) $this->SetValue("GRP_MAIL", $gmail);
        else if ($this->getValue("grp_hasmail") == "no") $this->deleteValue("GRP_MAIL");
        
        return $err;
    }
    /**
     * recompute parent group and its ascendant
     *
     * @return array/array parents group list refreshed
     * @see RefreshGroup()
     */
    function refreshParentGroup()
    {
        include_once ("FDL/freedom_util.php");
        include_once ("FDL/Lib.Dir.php");
        
        $sqlfilters[] = "in_textlist(grp_idgroup,{$this->id})";
        // $sqlfilters[]="fromid !=".getFamIdFromName($this->dbaccess,"IGROUP");
        $tgroup = getChildDoc($this->dbaccess, 0, "0", "ALL", $sqlfilters, 1, "LIST", getFamIdFromName($this->dbaccess, "GROUP"));
        
        $tpgroup = array();
        $tidpgroup = array();
        while (list($k, $v) = each($tgroup)) {
            $v->RefreshGroup();
            $tpgroup[] = $v->title;
            $tidpgroup[] = $v->id;
        }
        
        $this->SetValue("GRP_PGROUP", implode("\n", $tpgroup));
        $this->SetValue("GRP_IDPGROUP", implode("\n", $tidpgroup));
        return $tgroup;
    }
    /**
     * refresh members of the group from USER database
     */
    function refreshMembers()
    {
        include_once ("FDL/Lib.Dir.php");
        // 1)users
        $tu = getChildDoc($this->dbaccess, $this->initid, "0", "ALL", array() , 1, "TABLE", "USER");
        
        if (count($tu) > 0) {
            foreach ($tu as $k => $v) {
                $tmemid[] = $v["id"];
                $tmem[] = $v["title"];
            }
            $this->SetValue("GRP_USER", $tmem);
            $this->SetValue("GRP_IDUSER", $tmemid);
        } else {
            $this->DeleteValue("GRP_USER");
            $this->DeleteValue("GRP_IDUSER");
            $this->DeleteValue("GRP_GROUP");
            $this->DeleteValue("GRP_IDGROUP");
        }
        // 2)groups
        $tu = getChildDoc($this->dbaccess, $this->initid, "0", "ALL", array() , 1, "TABLE", "GROUP");
        $tmemid = array();
        $tmem = array();
        if (count($tu) > 0) {
            foreach ($tu as $k => $v) {
                $tmemid[] = $v["id"];
                $tmem[] = $v["title"];
            }
            $this->SetValue("GRP_GROUP", $tmem);
            $this->SetValue("GRP_IDGROUP", $tmemid);
        } else {
            $this->DeleteValue("GRP_GROUP");
            $this->DeleteValue("GRP_IDGROUP");
        }
        $err = $this->modify();
    }
    
    function refreshMailMembersOnChange()
    {
        // Recompute mail/members when the hasmail/hasmembers enum is changed
        if ($this->getOldValue('GRP_HASMAIL') !== false || $this->getOldValue('GRP_HASMEMBERS') !== false) {
            $err = $this->refreshGroup();
            if ($err != '') {
                return $err;
            }
        }
        return '';
    }
    /**
     * @begin-method-ignore
     * this part will be deleted when construct document class until end-method-ignore
     */
}
/*
 * @end-method-ignore
*/
?>