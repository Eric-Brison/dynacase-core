<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Document permissions
 *
 * @author Anakeen
 * @version $Id: Class.DocPerm.php,v 1.15 2007/06/14 15:48:25 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 */

include_once ("Class.DbObj.php");
/**
 * Managing permissions of documents
 * @package FDL
 *
 */
class DocPerm extends DbObj
{
    var $fields = array(
        "docid",
        "userid",
        "upacl"
    );
    
    var $id_fields = array(
        "docid",
        "userid"
    );
    public $docid;
    public $userid;
    public $upacl;
    public $uperm;
    
    var $dbtable = "docperm";
    
    var $order_by = "docid";
    
    var $sqlcreate = "
create table docperm ( 
                     docid int check (docid > 0),
                     userid int check (userid > 1),
                     upacl int  not null
                   );
create unique index idx_perm on docperm(docid, userid);";
    
    function preSelect($tid)
    {
        if (count($tid) == 2) {
            $this->docid = $tid[0];
            $this->userid = $tid[1];
        }
    }
    
    function preInsert()
    {
        if ($this->userid == 1) return _("not perm for admin");
        return '';
    }
    
    function preUpdate()
    {
        return $this->preInsert();
    }
    /**
     * return account vector for current user
     * to be use in getaperm sql function
     * @static
     * @param int $uid user identifier
     * @param bool $strict set to true to not use substitute
     * @return string
     */
    public static function getMemberOfVector($uid = 0, $strict = false)
    {
        if ($uid == 0) {
            global $action;
            if ($strict) $mof = $action->user->getStrictMemberOf();
            else $mof = $action->user->getMemberOf();
            $mof[] = $action->user->id;
        } else {
            
            $mof = Account::getUserMemberOf($uid, $strict);
            $mof[] = $uid;
        }
        return '{' . implode(',', $mof) . '}';
    }
    /**
     * @static
     * @param int $profid profil identifier
     * @param int $userid user identifier
     * @param bool $strict set to true to not use substitute
     * @return int
     */
    public static function getUperm($profid, $userid, $strict = false)
    {
        if ($userid == 1) return -1;
        $userMember = DocPerm::getMemberOfVector($userid, $strict);
        $sql = sprintf("select getaperm('%s',%d) as uperm", $userMember, $profid);
        simpleQuery(getDbAccess() , $sql, $uperm, true, true);
        if ($uperm === false) return 0;
        
        return $uperm;
    }
    
    public static function getStrictUperm($profid, $userid)
    {
        if ($userid == 1) return -1;
        $userMember = DocPerm::getMemberOfVector($userid);
        $sql = sprintf("select getaperm('%s',%d) as uperm", $userMember, $profid);
        simpleQuery(getDbAccess() , $sql, $uperm, true, true);
        if ($uperm === false) return 0;
        
        return $uperm;
    }
    /**
     * control access at $pos position (direct or indirect) (green or grey)
     * @param $pos
     * @return bool
     */
    function ControlU($pos)
    {
        if ($this->uperm == 0) {
            $this->uperm = $this->getUperm($this->docid, $this->userid);
        }
        return ($this->ControlMask($this->uperm, $pos));
    }
    // --------------------------------------------------------------------
    
    /**
     * @param $pos
     * @deprecated
     * @return bool
     */
    function ControlG($pos)
    {
        return false;
    }
    /**
     * control access at $pos position direct inly (green)
     * @param $pos
     * @return bool
     */
    function ControlUp($pos)
    {
        // --------------------------------------------------------------------
        if ($this->isAffected()) {
            return ($this->ControlMask($this->upacl, $pos));
        }
        return false;
    }
    // --------------------------------------------------------------------
    function ControlMask($acl, $pos)
    {
        return (($acl & (1 << ($pos))) != 0);
    }
    /**
     * no control for anyone
     */
    function UnSetControl()
    {
        $this->upacl = 0;
    }
    /**
     * set positive ACL in specified position
     * @param int $pos column number (0 is the first right column)
     */
    function SetControlP($pos)
    {
        $this->upacl = $this->upacl | (1 << $pos);
    }
    /**
     * unset positive ACL in specified position
     * @param int $pos column number (0 is the first right column)
     */
    function UnSetControlP($pos)
    {
        $this->upacl = $this->upacl & (~(1 << $pos));
    }
}
