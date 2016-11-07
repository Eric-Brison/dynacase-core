<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * UserToken class
 *
 * This class provides methods to store and manage authentication
 * tokens with expiration time
 *
 * @author Anakeen
 * @version $Id: Class.UserToken.php,v 1.6 2009/01/16 13:33:00 jerome Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ('Class.DbObj.php');

class UserToken extends DbObj
{
    var $Class = '$Id: Class.UserToken.php,v 1.6 2009/01/16 13:33:00 jerome Exp $';
    
    var $fields = array(
        'token',
        'type',
        'cdate',
        'authorid',
        'userid',
        'expire',
        'expendable',
        'description',
        'context'
    );
    
    public $token;
    public $userid;
    public $authorid;
    public $expire;
    public $expendable;
    public $context;
    public $cdate;
    public $description;
    public $type = "CORE";
    
    var $id_fields = array(
        'token'
    );
    
    var $dbtable = 'usertoken';
    
    var $sqlcreate = "
    CREATE TABLE usertoken (
      token text NOT NULL PRIMARY KEY,
      type text,
      cdate timestamp without time zone,
      authorid int,
      userid INT NOT NULL,
      expire TIMESTAMP NOT NULL,
      expendable BOOLEAN DEFAULT FALSE,
      description text,
      context text
    );
    CREATE INDEX usertoken_idx ON usertoken(token);
  ";
    
    var $hAlg = 'sha1';
    var $rndSize = 4;
    var $expiration = 86400; // 24 hours
    const INFINITY = "infinity";
    
    public function preInsert()
    {
        if (is_array($this->context)) {
            $this->context = serialize($this->context);
        }
        $this->cdate = date("Y-m-d H:i:s");
        $this->authorid = getCurrentUser()->id;
    }
    
    public function setHAlg($hAlg)
    {
        $this->hAlg = $hAlg;
        return $this->hAlg;
    }
    
    public function setRndSize($rndSize)
    {
        $this->rndSize = $rndSize;
        return $this->rndSize;
    }
    
    public function setExpiration($expiration = "")
    {
        if ($expiration == "") {
            $expiration = $this->expiration;
        }
        $this->expire = self::getExpirationDate($expiration);
        
        return $this->expire;
    }
    public static function getExpirationDate($delayInSeconds)
    {
        if (preg_match('/^-?infinity$/', $delayInSeconds)) {
            $expireDate = $delayInSeconds;
        } else {
            if (!is_numeric($delayInSeconds)) {
                return false;
            }
            $expireDate = strftime("%Y-%m-%d %H:%M:%S", time() + $delayInSeconds);
        }
        
        return $expireDate;
    }
    public function genToken()
    {
        $rnd = rand();
        for ($i = 0; $i < $this->rndSize; $i++) {
            $rnd.= mt_rand();
        }
        
        switch (strtolower($this->hAlg)) {
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
        
        error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "Unknown hAlg " . $this->hAlg . ". Will return raw random value.");
        return $rnd;
    }
    
    public function getToken()
    {
        if ($this->token == "") {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "token is not defined.");
        }
        return $this->token;
    }
    
    public static function deleteExpired()
    {
        $sql = sprintf("DELETE FROM usertoken WHERE expire < now()");
        simpleQuery('', $sql);
    }
    
    public function preUpdate()
    {
        if ($this->token == "") {
            return "Error: token not set";
        }
        if ($this->userid == "") {
            return "Error: userid not set";
        }
        if ($this->expire == "") {
            return "Error: expire not set";
        }
        return '';
    }
}
