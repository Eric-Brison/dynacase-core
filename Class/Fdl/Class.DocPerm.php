<?php
/**
 * Document permissions
 *
 * @author Anakeen 2000 
 * @version $Id: Class.DocPerm.php,v 1.15 2007/06/14 15:48:25 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 */
 /**
 */


include_once("Class.DbObj.php");

/**
 * Managing permissions of documents
 * @package FREEDOM
 *
 */
Class DocPerm extends DbObj
{
  var $fields = array ("docid",
		       "userid",
		       "upacl",
		       "unacl",
		       "cacl");

  var $sup_fields = array("getuperm(userid,docid) as uperm");
  var $id_fields = array ("docid","userid");

  var $dbtable = "docperm";

  var $order_by="docid";

  var $isCacheble= false;
  var $sqlcreate = "
create table docperm ( 
                     docid int check (docid > 0),
                     userid int check (userid > 1),
                     upacl int  not null,
                     unacl int  not null,
                     cacl int not null
                   );
create unique index idx_perm on docperm(docid, userid);
create trigger tinitacl AFTER INSERT OR UPDATE ON docperm FOR EACH ROW EXECUTE PROCEDURE initacl();";
  

  function preSelect($tid) {
    if (count($tid) == 2) {
      $this->docid=$tid[0];
      $this->userid=$tid[1];
    }
  }

  function preInsert() {
    if ($this->userid==1) return _("not perm for admin");   
    if (($this->upacl==0) && ($this->unacl==0)) return "";
    if ($this->unacl==="") $this->unacl="0";
    if ($this->cacl==="") $this->cacl="0";
  }

  function preUpdate() {
    return $this->preInsert();
  }

  /**
   * reinitialize computed acl
   * @param integer $docid docid acl to reset
   */
  function resetComputed($docid="") {
      $err="";
      if (! $docid) $docid=$this->docid;
      if ($docid > 0) {
          $err=$this->exec_query(sprintf("update docperm set cacl=0 where docid=%d and cacl != 0;",$docid));          
      }
      return $err;
  }
  function getUperm($docid, $userid) {
    $q = new QueryDb($this->dbaccess, "docperm");
    $t = $q->Query(0,1,"TABLE","select getuperm($userid,$docid) as uperm");
    
    return (($q->nb>0)?$t[0]["uperm"]:0);
  }
  function recomputeControl() {
    if ($this->docid > 0) 
      $this->exec_query("select getuperm(userid,docid) as uperm from docperm where docid=".$this->docid);
  }
  // --------------------------------------------------------------------
  function ControlU ($pos) {
    // --------------------------------------------------------------------     
        
    if ( $this->cacl == 0) {       
      $this->cacl = $this->getUperm($this->docid,$this->userid);
    }
    return ($this->ControlMask($this->cacl,$pos));
  }

  // --------------------------------------------------------------------
  function ControlG ($pos) {
    // --------------------------------------------------------------------     
        
    if ( ! isset($this->gacl)) {       
      $q = new QueryDb($this->dbaccess, "docperm");
      $t = $q -> Query(0,1,"TABLE","select computegperm({$this->userid},{$this->docid}) as uperm");

      $this->gacl=$t[0]["uperm"];
    }
    
    return ($this->ControlMask($this->gacl,$pos));
  }

  
  // --------------------------------------------------------------------
  function ControlUp ($pos) {
    // --------------------------------------------------------------------     
        
    if ($this->isAffected()) {            
      return ($this->ControlMask($this->upacl,$pos));
    } 
    return false;
  }
  
  // --------------------------------------------------------------------
  function ControlUn ($pos) {
    // --------------------------------------------------------------------     
        
    if ($this->isAffected()) {            
      return ($this->ControlMask($this->unacl,$pos));
    } 
    return false;
  }

  // --------------------------------------------------------------------
  function ControlMask ($acl, $pos) {
    // --------------------------------------------------------------------     
        
    return (($acl & (1 << ($pos ))) != 0);
  }


  /**
   * no control for anyone
   */
  function UnSetControl() {
    $this->upacl=0;
    $this->unacl=0;
    $this->cacl=1;
  }  

  /**
   * set positive ACL in specified position
   * @param int $pos column number (0 is the first right column)
   */
  function SetControlP($pos) {  
    $this->upacl = $this->upacl | (1 << $pos);
    $this->UnSetControlN($pos);
  }
  /**
   * unset positive ACL in specified position
   * @param int $pos column number (0 is the first right column)
   */
  function UnSetControlP($pos) {
    $this->upacl = $this->upacl & (~(1 << $pos));
  }

  /**
   * set negative ACL in specified position
   * @param int $pos column number (0 is the first right column)
   */
  function SetControlN($pos) {
    $this->unacl = $this->unacl | (1 << $pos);
    $this->UnSetControlP($pos);
  }

  /**
   * unset negative ACL in specified position
   * @param int $pos column number (0 is the first right column)
   */
  function UnSetControlN($pos) {
    $this->unacl = $this->unacl & (~(1 << $pos));
  }

}
?>
