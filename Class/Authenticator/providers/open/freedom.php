<?php

/**
 * basicFreedomProvider class
 *
 * This class provides methods for HTTP Basic authentication against
 * the Freedom "core" database
 *
 * @author Anakeen 2009
 * @version $Id: freedom.php,v 1.4 2009/01/16 13:33:01 jerome Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage
 */
 /**
 */

Class openFreedomProvider {
  private $parms = array();
  private $privatelogin=false;
  public function __construct($parms) {
    $this->parms = $parms;
    
  }
  
  public function checkAuthorization($opt) {
    include_once('WHAT/Lib.Http.php');
    $privatekey=getHttpVars("privateid");
    if (! $privatekey) return false;
    
    return $this->getLoginFromPrivateKey($privatekey);
  }


  public function getLoginFromPrivateKey($privatekey) {
    include_once('WHAT/Class.UserToken.php');
    include_once('WHAT/Class.QueryDb.php');
    include_once('WHAT/Class.User.php');
    $q=new QueryDb("","UserToken");
    $q->addQuery("token='".pg_escape_string($privatekey)."'");
    $tu=$q->Query(0,1,"TABLE");
    if ($q->nb > 0) {
      $uid=$tu[0]["userid"];
      $u=new User("",$uid);
      $this->privatelogin=$u->login;
    }
    
    return  $this->privatelogin;
  }
  public function getAuthUser() {    
    return $this->privatelogin;
  }
}

?>
