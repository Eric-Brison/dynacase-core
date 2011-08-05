<?php

/**
 * UserToken class
 *
 * This class provides methods to store and manage authentication
 * tokens with expiration time
 *
 * @author Anakeen 2009
 * @version $Id: Class.UserToken.php,v 1.6 2009/01/16 13:33:00 jerome Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package WHAT
 * @subpackage
 */
 /**
 */

include_once('Class.DbObj.php');

Class UserToken extends DbObj
{
  var $Class = '$Id: Class.UserToken.php,v 1.6 2009/01/16 13:33:00 jerome Exp $';

  var $fields = array(
		     'token',
		     'userid',
		     'expire',
		     'expendable',
		     'context'
		     );

  var $id_fields = array(
			 'token'
			 );

  var $dbtable = 'usertoken';

  var $sqlcreate = "
    CREATE TABLE usertoken (
      token VARCHAR(256) NOT NULL PRIMARY KEY,
      userid INT NOT NULL,
      expire TIMESTAMP NOT NULL,
      expendable BOOLEAN DEFAULT FALSE,
      context text
    );
    CREATE INDEX usertoken_idx ON usertoken(token);
  ";

  var $hAlg = 'sha1';
  var $rndSize = 4;
  var $expiration = 86400; // 24 hours

  function setHAlg($hAlg) {
    $this->hAlg = $hAlg;
    return $this->hAlg;
  }

  function setRndSize($rndSize) {
    $this->rndSize = $rndSize;
    return $this->rndSize;
  }

  function setExpiration($expiration="") {
    if( $expiration == "" ) {
      $expiration = $this->expiration;
    }

    if( preg_match('/^-?infinity$/', $expiration) ) {
      $this->expire = $expiration;
    } else {
      if( ! is_numeric($expiration) ) {
	return false;
      }
     
      $this->expire=strftime("%Y-%m-%d %H:%M:%S",time()+$expiration);
    }

    return $this->expire;
  }

  function genToken() {
    $rnd = rand();
    for($i = 0; $i < $this->rndSize; $i++) {
      $rnd .= rand();
    }

    switch( strtolower($this->hAlg) )
      {
      case 'sha1':
	return sha1($rnd);
	break;
      case 'md5':
	return md5($rnd);
	break;
      case 'raw':
        return $rnd;
	break;
      }

    error_log(__CLASS__."::".__FUNCTION__." "."Unknown hAlg ".$this->hAlg.". Will return raw random value.");
    return $rnd;
  }

  function getToken() {
    if( $this->token == "" ) {
      error_log(__CLASS__."::".__FUNCTION__." "."token is not defined.");
    }
    return $this->token;
  }

  function deleteExpired() {
    $err = $this->exec_query("DELETE FROM ".pg_escape_string($this->dbtable). " WHERE expire < now()");
    return $err;
  }



  function preUpdate() {
    if( $this->token == "" ) {
      return "Error: token not set";
    }
    if( $this->userid == "" ) {
      return "Error: userid not set";
    }
    if( $this->expire == "" ) {
      return "Error: expire not set";
    }
  }

}

?>