<?php
// ---------------------------------------------------------------
// $Id: Class.DbObjCtrl.php,v 1.8 2002/09/24 13:57:13 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Class/Appmng/Class.DbObjCtrl.php,v $
// ---------------------------------------------------------------
//  O   Anakeen - 2001
// O*O  Anakeen development team
//  O   dev@anakeen.com
// ---------------------------------------------------------------
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or (at
//  your option) any later version.
//
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
// or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
// for more details.
//
// You should have received a copy of the GNU General Public License along
// with this program; if not, write to the Free Software Foundation, Inc.,
// 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
// ---------------------------------------------------------------



$CLASS_DBOBJCTRL_PHP = '$Id: Class.DbObjCtrl.php,v 1.8 2002/09/24 13:57:13 eric Exp $';

include_once('Class.ObjectPermission.php');
include_once('Class.Application.php');

Class DbObjCtrl extends DbObj
{
  var $obj_acl = array (); //should be replaced by the Child Class 

  var $operm;
  var $action;
  // --------------------------------------------------------------------
  function DbObjCtrl ($dbaccess='', $id='',$res='',$dbid=0) {
    // --------------------------------------------------------------------

    global $action; // necessary to see information about user privilege

    if (isset($action)) {
      $this->classid = $this->getClassId();
      $cid = $this->classid;
      $this->userid=$action->parent->user->id;
    }
    DbObj::DbObj($dbaccess, $id,$res,$dbid);



      
  }

  function getClassId() {
    // must be set by child
    return 0;
  }
  function PostSelect()
    {

      if ($this->IsControlled()) {

    $this->operm= new ObjectPermission("", 
                                       array($this->userid,
				             $this->id,
					     $this->classid ));
      }



    }


  // --------------------------------------------------------------------
  function Control ($aclname) {
    // -------------------------------------------------------------------- 
    if ($this->IsAffected())
      if ($this->IsControlled()) 
	return $this->operm->Control($this, $aclname);
      else return "";

    return "object not initialized : $aclname";
  }
  // --------------------------------------------------------------------
  function PostUpdate()
    // --------------------------------------------------------------------    
    {
      // add controlled object
      	      
      if (!isset($this->id)) return "";

      $cobj = new ControlObject("",array($this->id, $this->classid));

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
      $cobj->id_obj= $this->id;
      $cobj->id_class = $this->classid;
      $cobj->description = $this->Description();
      $cobj->Add();

      $this->operm= new ObjectPermission("", 
                                         array($this->userid,
				               $this->id,
					       $this->classid ));

      $this->operm->Delete();
      $acl =new Acl();

      $acl-> Set("modifyacl", $this->classid);
      $this->operm->AddAcl($acl->id);

      $acl-> Set("viewacl", $this->classid);
      $this->operm->AddAcl($acl->id);


      // set all permissions  
      while(list($k,$v) = each($this->obj_acl)) {
	$acl-> Set($v["name"], $this->classid);
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
      $cobj = new ControlObject("", array($this->id, $this->classid));

      $cobj->Delete();
    }

  // --------------------------------------------------------------------
  function IsControlled() {
  // --------------------------------------------------------------------
    $cobj = new ControlObject("", array($this->id, $this->classid));
    return $cobj->IsAffected();

  }

  // --------------------------------------------------------------------
  function PostDelete()    
    // --------------------------------------------------------------------
    {
      // ------------------------------
      // delete control object
      
      $cobj = new ControlObject($this->dbaccess, array($this->id, $this->classid ));
      $cobj-> Delete();

    }

  // --------------------------------------------------------------------
  function Description() {
    // -------------------------------------------------------------------- 
    // This function should be replaced by the Child Class 
    return "None";
  }


  // --------------------------------------------------------------------
  function InitObjectAcl()
    // init ACL table with object acls
    // -------------------------------------------------------------------- 
    {

      $defacl =array(array(
			   "name"		=>"modifyacl",
			   "description"	=>N_("modify object acl")),
		     array(
			   "name"		=>"viewacl",
			   "description"	=>N_("view object acl"))
		     );

      if (! ((isset ($obj->obj_acl)) && (is_array($obj->obj_acl)))) {
	$this->log->warning("InitObjectAcl no Acl for object class ".get_class($this));
      }
      
      
      $this->obj_acl= array_merge($this->obj_acl, $defacl);
      
      $app = new Application();

      if (($id_app = $app->GetIdFromName(get_class($this)) ) == 0) {
	// create if not exist
	$app->name = get_class($this);
	$app->short_name = get_class($this)." Class";
	$app->description = get_class($this)." Class";
	$app->access_free = "N";
	$app->available = "N";
	$app->icon = "";
	$app->displayable = "N";
	$app->objectclass = "Y";
	$app -> Add();
	$id_app = $app->id;
      } else {
	$app->Select($id_app);
      }

      $acl = new Acl();
    
      $acl->Init($app, $this->obj_acl, true);

            
    }
}
?>
