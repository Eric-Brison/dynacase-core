<?php
/**
 * User Document Object Definition
 *
 * @author Anakeen 2002
 * @version $Id:  $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package API
 */
/**
 */
include_once("DATA/Class.Document.php");

/**
 * Document Class
 *
 */
Class Fdl_User {
  private $_user=null;
  
  function __construct(&$user) {
    if ($user) {
      $this->_user=$user;
    }
  }
  /**
   * return document list
   * @return array Document
   */
  function getUser() {
    
    if (! $this->_user) {
      $this->error=sprintf(_("user not initialized"));
      return null;
    } else {
      $ti=array("id","mail","fid","firstname","lastname","login");
      foreach ($ti as $i)  $info->$i=$this->_user->$i;
      $info->locale=getParam("CORE_LANG");
      $out->info=$info;
    }
    $out->error=$this->error;
    return $out;
  } 

 
}

?>