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
 * @version $Id: Class.DbObjCtrl.php,v 1.9 2003/08/18 15:46:42 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage CORE
 */
/**
 */
// ---------------------------------------------------------------
// $Id: Class.DbObjCtrl.php,v 1.9 2003/08/18 15:46:42 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Class/Appmng/Class.DbObjCtrl.php,v $
// ---------------------------------------------------------------

$CLASS_DBOBJCTRL_PHP = '$Id: Class.DbObjCtrl.php,v 1.9 2003/08/18 15:46:42 eric Exp $';

include_once ('Class.ObjectPermission.php');
include_once ('Class.Application.php');

class DbObjCtrl extends DbObj
{
    var $obj_acl = array(); //should be replaced by the Child Class
    var $operm;
    var $action;
    // --------------------------------------------------------------------
    function DbObjCtrl($dbaccess = '', $id = '', $res = '', $dbid = 0)
    {
        // --------------------------------------------------------------------
        global $action; // necessary to see information about user privilege
        if (isset($action)) {
            $this->classid = $this->getClassId();
            $cid = $this->classid;
            $this->userid = $action->parent->user->id;
        }
        DbObj::DbObj($dbaccess, $id, $res, $dbid);
    }
    
    function getClassId()
    {
        // must be set by child
        return 0;
    }
    function PostSelect()
    {
        
        if ($this->IsControlled()) {
            
            $this->operm = new ObjectPermission("", array(
                $this->userid,
                $this->id,
                $this->classid
            ));
        }
    }
    // --------------------------------------------------------------------
    function Control($aclname)
    {
        // --------------------------------------------------------------------
        if ($this->IsAffected()) if ($this->IsControlled()) return $this->operm->Control($this, $aclname);
        else return "";
        
        return "object not initialized : $aclname";
    }
    // --------------------------------------------------------------------
    function PostUpdate()
    // --------------------------------------------------------------------
    
    {
        // add controlled object
        if (!isset($this->id)) return "";
        
        $cobj = new ControlObject("", array(
            $this->id,
            $this->classid
        ));
        
        $cobj->description = $this->Description();
        $cobj->Modify();
    }
    // --------------------------------------------------------------------
    function SetControl()
    // --------------------------------------------------------------------
    
    {
        // record object as controlled object
        //print "SetControl:$this->id, $this->classid";
        if (!isset($this->id)) return "";
        
        $cobj = new ControlObject();
        $cobj->id_obj = $this->id;
        $cobj->id_class = $this->classid;
        $cobj->description = $this->Description();
        $cobj->Add();
        
        $this->operm = new ObjectPermission("", array(
            $this->userid,
            $this->id,
            $this->classid
        ));
        
        $this->operm->Delete();
        $acl = new Acl();
        
        $acl->Set("modifyacl", $this->classid);
        $this->operm->AddAcl($acl->id);
        
        $acl->Set("viewacl", $this->classid);
        $this->operm->AddAcl($acl->id);
        // set all permissions
        while (list($k, $v) = each($this->obj_acl)) {
            $acl->Set($v["name"], $this->classid);
            $this->operm->AddAcl($acl->id);
        }
        
        $this->operm->Add();
    }
    // --------------------------------------------------------------------
    function UnsetControl()
    // --------------------------------------------------------------------
    
    {
        // delete object as controlled object
        if (!isset($this->id)) return "";
        
        $this->operm->delete();
        $cobj = new ControlObject("", array(
            $this->id,
            $this->classid
        ));
        
        $cobj->Delete();
    }
    // --------------------------------------------------------------------
    function IsControlled()
    {
        // --------------------------------------------------------------------
        $cobj = new ControlObject("", array(
            $this->id,
            $this->classid
        ));
        return $cobj->IsAffected();
    }
    // --------------------------------------------------------------------
    function PostDelete()
    // --------------------------------------------------------------------
    
    {
        // ------------------------------
        // delete control object
        $cobj = new ControlObject($this->dbaccess, array(
            $this->id,
            $this->classid
        ));
        $cobj->Delete();
    }
    // --------------------------------------------------------------------
    function Description()
    {
        // --------------------------------------------------------------------
        // This function should be replaced by the Child Class
        return "None";
    }
    // --------------------------------------------------------------------
    function InitObjectAcl()
    // init ACL table with object acls
    // --------------------------------------------------------------------
    
    {
        
        $defacl = array(
            array(
                "name" => "modifyacl",
                "description" => N_("modify object acl")
            ) ,
            array(
                "name" => "viewacl",
                "description" => N_("view object acl")
            )
        );
        
        if (!((isset($obj->obj_acl)) && (is_array($obj->obj_acl)))) {
            $this->log->warning("InitObjectAcl no Acl for object class " . get_class($this));
        }
        
        $this->obj_acl = array_merge($this->obj_acl, $defacl);
        
        $app = new Application();
        
        if (($id_app = $app->GetIdFromName(get_class($this))) == 0) {
            // create if not exist
            $app->name = get_class($this);
            $app->short_name = get_class($this) . " Class";
            $app->description = get_class($this) . " Class";
            $app->access_free = "N";
            $app->available = "N";
            $app->icon = "";
            $app->displayable = "N";
            $app->objectclass = "Y";
            $app->Add();
            $id_app = $app->id;
        } else {
            $app->Select($id_app);
        }
        
        $acl = new Acl();
        
        $acl->Init($app, $this->obj_acl, true);
    }
}
?>
