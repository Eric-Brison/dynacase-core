<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: Class.SessionVar.php,v 1.3 2003/08/18 15:46:42 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage CORE
 */
/**
 */

include_once ('Class.DbObj.php');

class SessionVar extends DbObj
{
    
    var $fields = array(
        "session",
        "key",
        "val"
    );
    
    var $id_fields = array(
        "session",
        "key"
    );
    
    var $dbtable = "session_vars";
    
    var $sqlcreate = "create table session_vars ( session varchar(100), 
			    key	    varchar(50),
			    val	    varchar(200));
create index session_vars_idx on session_vars(session,key);";
}
?>
