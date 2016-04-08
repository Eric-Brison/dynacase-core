<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: Class.SessionVar.php,v 1.3 2003/08/18 15:46:42 eric Exp $
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
