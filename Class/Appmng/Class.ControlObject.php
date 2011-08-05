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
 * @version $Id: Class.ControlObject.php,v 1.7 2003/08/18 15:46:41 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage CORE
 */
/**
 */
// $Id: Class.ControlObject.php,v 1.7 2003/08/18 15:46:41 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Class/Appmng/Class.ControlObject.php,v $
// ---------------------------------------------------------------
//
$CLASS_CONTROLOBJECT_PHP = '$Id: Class.ControlObject.php,v 1.7 2003/08/18 15:46:41 eric Exp $';
include_once ('Class.DbObjCtrl.php');
include_once ('Class.QueryDb.php');
include_once ('Class.Application.php');
include_once ('Class.Action.php');
include_once ('Class.Acl.php');
include_once ('Class.User.php');
include_once ('Class.Group.php');

class ControlObject extends DbObj
{
    var $fields = array(
        "id_obj",
        "id_class",
        "description"
    );
    
    var $id_fields = array(
        "id_obj",
        "id_class"
    );
    
    var $dbtable = "octrl";
    
    var $sqlcreate = '
create table octrl (id_obj int not null,
                    id_class  int not null,
                    description varchar(256));
create unique index i_octrl on octrl (id_obj, id_class);';
    // --------------------------------------------------------------------
    //---------------------- OBJECT CONTROL PERMISSION --------------------
    
    // --------------------------------------------------------------------
    function ControlObject($dbaccess = '', $id = '', $res = '', $dbid = 0)
    // --------------------------------------------------------------------
    
    {
        // change DB for permission : see 'dboperm' session var
        global $action;
        $dbaccess = $action->Read("dboperm", $dbaccess);
        
        DbObj::DbObj($dbaccess, $id, $res, $dbid);
    }
    // --------------------------------------------------------------------
    function PostDelete()
    // --------------------------------------------------------------------
    
    {
        // ------------------------------
        // delete object permision  object
        $dq = new QueryDb($this->dbaccess, "ObjectPermission");
        $dq->Query(0, 0, "TABLE", "delete from operm where id_obj not in (select id_obj from octrl)");
    }
    // --------------------------------------------------------------------
    
    // get controlled object for a specific class
    function GetOids($idclass)
    {
        
        $oids = array();
        $query = new QueryDb($this->dbaccess, "ControlObject");
        $query->AddQuery("id_class=$idclass");
        $table1 = $query->Query();
        if ($query->nb > 0) {
            while (list($k, $v) = each($table1)) {
                $oids[] = $v;
            }
        }
        return $oids;
    }
}
?>
