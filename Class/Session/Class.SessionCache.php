<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Cache session date of validated
 *
 * @author Anakeen
 * @version $Id: Class.SessionCache.php,v 1.4 2005/06/28 13:53:24 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage CORE
 */
/**
 */

include_once ('Class.DbObj.php');

class SessionCache extends DbObj
{
    
    var $fields = array(
        "index",
        "lasttime"
    );
    
    var $id_fields = array(
        "index"
    );
    
    var $dbtable = "session_cache";
    
    var $sqlcreate = "create table session_cache ( index text, 
			    lasttime	    int);";
    
    function __construct($dbaccess = '', $id = '', $res = '', $dbid = 0)
    {
        parent::__construct($dbaccess, $id, $res, $dbid);
        if ((!$this->isAffected()) && ($id != '')) {
            $this->index = $id;
            
            $date = gettimeofday();
            $this->lasttime = $date['sec'];
            $this->Add();
        }
    }
    // modify with current date
    function setTime()
    {
        $date = gettimeofday();
        $this->lasttime = $date['sec'];
        $this->Modify();
    }
}
?>
