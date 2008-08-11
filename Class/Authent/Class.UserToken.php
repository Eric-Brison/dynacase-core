<?php

include_once('Class.DbObj.php');

Class UserToken extends DbObj
{
  var $Class = '$Id: Class.UserToken.php,v 1.1 2008/08/11 15:40:01 jerome Exp $';

  var $fields = array(
		     'userid',
		     'token',
		     'expire'
		     );

  var $id_fields = array(
			 'userid'
			 );

  var $dbtable = 'usertoken';

  var $sqlcreate = "
    CREATE TABLE usertoken (
      userid INT NOT NULL PRIMARY KEY,
      token VARCHAR(256),
      expire TIMESTAMP
    );
    CREATE INDEX usertoken_idx ON usertoken(token);
  ";

  var $hAlg = 'sha1';
  var $rndSize = 4;
  var $expiration = 60*60*24;

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

    $today = new DateTime();
    $expireDate = clone $today;
    $expireDate->modify("+".$expiration." seconds");

    $this->expire = $expireDate->format(DATE_RFC3339);
    return $this->expire;
  }

  function genToken() {
    $rnd = gmp_strval(gmp_random($this->rndSize));
    switch( strtolower($hAlg) )
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

  function modify() {
    if( $this->token == "" ) {
      return "Error: token not set";
    }
    if( $this->userid == "" ) {
      return "Error: userid not set";
    }
    if( $this->expire == "" ) {
      return "Error: expire not set";
    }

    parent::modify();
  }

}

?>