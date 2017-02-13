<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Specials methods for GROUP family
 *
 */
namespace Dcp\Core;
class AccountCollection extends \Dcp\Family\Dir
{
    /*
     * @end-method-ignore
    */
    /**
     * reconstruct mail group & recompute parent group
     *
     * @return string error message, if no error empty string
     */
    function postStore()
    {
        
        $err = $this->SetGroupMail();
        $this->refreshParentGroup();
        return $err;
    }
    /**
     * @deprecated use postStore() instead
     * @return string
     */
    public function postModify()
    {
        deprecatedFunction();
        return self::postStore();
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
    function postInsertDocument($docid, $multiple = false)
    {
        if ($multiple == false) {
            $this->SetGroupMail();
            $this->refreshMembers();
            $this->specPostInsert();
        }
    }
    /**
     * update groups table in USER database
     * @return string error message
     */
    function postInsertMultipleDocuments($tdocid)
    {
        $this->SetGroupMail();
        $this->refreshMembers();
        $this->specPostInsert();
    }
    /**
     * update groups table in USER database before suppress
     * @return string error message
     */
    function postRemoveDocument($docid, $multiple = false)
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
        
        if (!$nomail) $nomail = ($this->getRawValue("grp_hasmail") == "no");
        if (!$nomail) {
            
            $s = new \SearchDoc($this->dbaccess);
            $s->useCollection($this->initid);
            $r = $s->search();
            foreach ($r as $account) {
                $mail = $account["us_mail"];
                if (!$mail) $account["grp_mail"];
                if ($mail) $tmail[] = $mail;
            }
            $gmail = implode(", ", array_unique($tmail));
            $this->SetValue("GRP_MAIL", $gmail);
        }
        
        if ($this->getRawValue("grp_hasmail") == "no") $this->clearValue("GRP_MAIL");
        
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
        
        $sqlfilters[] = sprintf("in_textlist(grp_idgroup,'%s')", $this->id);
        // $sqlfilters[]="fromid !=".getFamIdFromName($this->dbaccess,"IGROUP");
        $tgroup = internalGetDocCollection($this->dbaccess, 0, "0", "ALL", $sqlfilters, 1, "LIST", getFamIdFromName($this->dbaccess, "GROUP"));
        
        $tpgroup = array();
        $tidpgroup = array();
        /**
         * @var \Dcp\Family\Group $v
         */
        foreach ($tgroup as $k => $v) {
            $v->RefreshGroup();
            $tpgroup[] = $v->title;
            $tidpgroup[] = $v->id;
        }
        
        $this->SetValue("GRP_IDPGROUP", implode("\n", $tidpgroup));
        return $tgroup;
    }
    /**
     * refresh members of the group from USER database
     */
    function refreshMembers()
    {
        include_once ("FDL/Lib.Dir.php");
        // 2)groups
        $tu = internalGetDocCollection($this->dbaccess, $this->initid, "0", "ALL", array() , 1, "TABLE", "GROUP");
        $tmemid = array();
        $tmem = array();
        if (count($tu) > 0) {
            foreach ($tu as $k => $v) {
                $tmemid[] = $v["id"];
                $tmem[] = $v["title"];
            }
            $this->SetValue("GRP_IDGROUP", $tmemid);
        } else {
            $this->clearValue("GRP_IDGROUP");
        }
        $err = $this->modify();
    }
    
    function refreshMailMembersOnChange()
    {
        // Recompute mail/members when the hasmail/hasmembers enum is changed
        if ($this->getOldRawValue('GRP_HASMAIL') !== false || $this->getOldRawValue('GRP_HASMEMBERS') !== false) {
            $err = $this->refreshGroup();
            if ($err != '') {
                return $err;
            }
        }
        return '';
    }
}
