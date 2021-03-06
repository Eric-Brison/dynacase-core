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
 * @version $Id: object_access.php,v 1.4 2003/08/18 15:46:41 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage ACCESS
 */
/**
 */
// ---------------------------------------------------------------
// $Id: object_access.php,v 1.4 2003/08/18 15:46:41 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Access/object_access.php,v $
// ---------------------------------------------------------------
// $Log: object_access.php,v $
// Revision 1.4  2003/08/18 15:46:41  eric
// phpdoc
//
// Revision 1.3  2002/04/29 15:33:24  eric
// correction id pour cache multibase + CORE_FGIMG
//
// Revision 1.2  2002/03/08 14:37:36  eric
// mise en place des permissions objet multibase
//
// Revision 1.1  2002/01/08 12:41:33  eric
// first
//
// Revision 1.1  2001/09/07 16:52:01  eric
// gestion des droits sur les objets
//
// ---------------------------------------------------------------
include_once ("ACCESS/appl_access.php");
// -----------------------------------
function object_access(&$action)
{
    // -----------------------------------
    $coid = intval(GetHttpVars("oid", $action->Read("access_oid", 0)));
    $dboperm = GetHttpVars("dboperm", $action->Read("dboperm")); // object permission database
    $dbopname = GetHttpVars("dbopname"); // object permission database
    $appId = $action->Read("access_class_id");
    
    $action->lay->Set("appid", $appId);
    // reaffect operm session variable
    if (preg_match("/(.*)dbname=(.*)/", $dboperm, $reg)) {
        if ($dbopname != "") {
            
            $action->Register("dboperm", $reg[1] . "dbname=" . $dbopname);
        } else {
            $dbopname = $reg[2];
        }
    }
    
    $action->lay->SetBlockData("DBNAME", getDb($action->dbaccess, $dbopname));
    //-------------------
    // contruct object id list
    $octrl = new ControlObject();
    if (preg_match("/dbname=(.*)/", $octrl->dbaccess, $reg)) {
        $action->lay->Set("dboperm", $octrl->dbaccess);
        $action->lay->Set("dbopname", $reg[1]);
    }
    
    $toid = $octrl->GetOids($appId);
    
    if (count($toid) > 0) {
        if ($coid == 0) $coid = $toid[0]->id_obj;
        $oids = array();
        while (list($k, $v) = each($toid)) {
            
            if ($v->id_obj == $coid) $oids[$k]["selectedoid"] = "selected";
            else $oids[$k]["selectedoid"] = "";
            $oids[$k]["coid"] = $v->id_obj;
            $oids[$k]["descoid"] = $v->description;
        }
        
        $action->lay->SetBlockData("ZONOID", array(
            array(
                "zou"
            )
        ));
        $action->lay->SetBlockData("ZONSELOID", array(
            array(
                "zou"
            )
        ));
        $action->lay->SetBlockData("OID", $oids);
        appl_access($action, $coid);
    } else {
        
        $action->lay->SetBlockData("ZONOID", array(
            array(
                "zou"
            )
        ));
        appl_access($action, -1);
    }
    
    $action->lay->Set("soid", $coid);
    $action->Register("access_oid", $coid);
}
// get all database name
function getDb($dbaccess, $dbname)
{
    
    global $CORE_DBID;
    $dbid = $CORE_DBID["$dbaccess"];
    $result = pg_exec($dbid, "select datname from pg_database");
    
    $arr = array();
    if (pg_numrows($result) > 0) {
        $nrow = pg_numrows($result);
        for ($irow = 0; $irow < $nrow; $irow++) {
            $row = pg_fetch_array($result, $irow);
            if ($row[0] == $dbname) $arr[$irow]["selecteddb"] = "selected";
            else $arr[$irow]["selecteddb"] = "";
            $arr[$irow]["dbopname"] = $row[0];
        }
    }
    
    return $arr;
}
?>
