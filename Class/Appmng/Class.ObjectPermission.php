<?
// $Id: Class.ObjectPermission.php,v 1.1 2002/01/08 12:41:34 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Class/Appmng/Class.ObjectPermission.php,v $
// ---------------------------------------------------------------
//  O   Anakeen - 2001
// O*O  Anakeen Development Team
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
// $Log: Class.ObjectPermission.php,v $
// Revision 1.1  2002/01/08 12:41:34  eric
// first
//
// Revision 1.3  2001/11/14 15:16:45  eric
// modif pour optimisation
//
// Revision 1.2  2001/09/10 16:58:06  eric
// objet accessibilité
//
// Revision 1.1  2001/09/07 16:48:59  eric
// gestion des droits sur les objets
//

//
// ---------------------------------------------------------------------------
//
$CLASS_PERMISSION_PHP = '$Id: Class.ObjectPermission.php,v 1.1 2002/01/08 12:41:34 eric Exp $';
include_once('Class.DbObj.php');
include_once('Class.QueryDb.php');
include_once('Class.Acl.php');
include_once('Class.ControlObject.php');
Class ObjectPermission extends DbObj
{
  var $fields = array ( "id_user","id_obj","id_acl");

  var $id_fields = array ( "id_user","id_obj");

  var $dbtable = "operm";

  var $sqlcreate = '
create table operm (id_user int ,
                    id_obj int not null,
                    id_acl int not null);
create index operm_idx1 on operm(id_user);
                 ';

  var $classid=0; // if 0 not a controlled object
  var $description="";
  var $coid=array();
  var $dprivileges = array(); // default privilege from permission table
  var $iscomplete = false; // indicate if all privileges are computed

  function ObjectPermission($dbaccess='', $id='',$res='',$dbid=0)
    {
      if (is_array($id)) {
	  $this->Affect(array("id_user" => $id[0],
			      "id_obj" => $id[1]));
      }

      if (! $this->DbObj ($dbaccess, $id,$res,$dbid)) {
      
	if (is_array($id)) {	
	  $this-> GetPrivileges();
	}
      }

    }

  function PreSelect($id)
    {
      if (is_array($id)) {
	  $this->Affect(array("id_user" => $id[0],
			      "id_obj" => $id[1]));
      }
      $octrl = new ControlObject("",$this->id_obj);

      if ( $octrl->IsAffected()) {
	$this->classid = $octrl->id_class;
	$this->description = $octrl->description;
      }
      

      $uperm = new Permission("", array($this->id_user, $this->classid));    
      $this->dprivileges = $uperm->privileges; 
      $this->privileges = $this->dprivileges;
    }

  function PostSelect($id)
    {      

      // init privileges
      $this-> GetPrivileges();
    }
  function PostDelete()
    {
      // update privileges
      $this-> GetPrivileges();
    }

  function PostUpdate()
    {
      // update privileges
      $this-> GetPrivileges();
    }

  function PreInsert( )
    {
      // no duplicate items
      if ($this->Exists($this->id_user,$this->id_obj,$this->id_acl)) return "ObjectPermission ({$this->id_user},{$this->id_obj},{$this->id_acl}) already exists...";  
  
      return "";
  
    }

  
  function Exists($userid,$oid,$aclid=0) {
    $query = new QueryDb($this->dbaccess,"ObjectPermission");
    $query->basic_elem->sup_where = array ("id_obj='{$oid}'",
					   "id_user='{$userid}'");
    if ($aclid != 0) {
      $naclid= - $aclid;
      $query->AddQuery("(id_acl={$aclid}) OR (id_acl= {$naclid}) ");
    }
    $list = $query->Query();

    return($query->nb>0);
  }



  // return ACL list for a user and a oid
  function GetPrivileges() {

    
    if ( $this->iscomplete) return ($this->privileges);

    $this->privileges= array(); 
    $this->upprivileges= array();// privileges array for a user (including group) in an application
    $this->unprivileges= array();// specifific NO privileges array for a user in an application

    $this->gprivileges= array();// privileges array for the group user

    if (true){
    // add groups privilege
    $ugroup = new Group($this->dbaccess,
			$this->id_user);
  
    
    while (list($k,$gid) = each($ugroup->groups)) {

      $gperm = new ObjectPermission($this->dbaccess, 
				    array($gid, 
					  $this->id_obj));
    
      // add group 

      while (list($k2,$gacl) = each($gperm->privileges)) {
	if (! in_array($gacl, $this->privileges)) {
	  $this->gprivileges[]= $gacl;
	  $this->privileges[]= $gacl;
	}    
      }
    }
    }

    $query = new QueryDb($this->dbaccess,"ObjectPermission");
    $query->basic_elem->sup_where = array ("id_obj='{$this->id_obj}'",
					   "id_user='{$this->id_user}'");
    $list = $query->Query();
    if ($query->nb > 0) {
      while (list($k,$v) = each($list)) {
	if ($v->id_acl > 0) {
	  // add privilege
	  $this->upprivileges[]= $v->id_acl;
	  if (! in_array($v->id_acl, $this->privileges)) {
	    $this->privileges[]= $v->id_acl;
	  } 
	}else { 
	  // suppress privilege
	  $this->unprivileges[]= -($v->id_acl);
	
	  $nk=array_search(-($v->id_acl), $this->privileges, false);
	  if (is_integer($nk)) {
	    unset($this->privileges[$nk]);
	  }
	}
      
      }
    }

    $this->AddDefaultPrivileges();
    $this->iscomplete= true; // to avoid another computing
    return($this->privileges);
  }

  // recompute privilege with default permission
 
  function AddDefaultPrivileges() {
    // Add uperm->privileges in $this
    $this->privileges = array_unique(array_merge($this->dprivileges, $this->privileges));
    
    // Remove this->unprivileges 
    $this->privileges = array_diff ($this->privileges, $this->unprivileges);
	  
	
    
  }

  function HasPrivilege($idacl)
    {
      return(($this->id_user == 1) || // admin user
	     (in_array($idacl, $this->privileges)));
    }


  function Control(&$object, $method)
    {
      // return "" if the current user can apply method on object
      // else return string error
      $this->GetPrivileges();
      $err = $this-> ControlOid(  $object->classid, $method);
      //print "Control : $this->id_user, $object->oid, $this->id_obj, $object->classid, $method : $err<BR>";
      //print "<BR>up<BR>";print_r($this->upprivileges);
      //print "<BR>un<BR>";print_r($this->unprivileges);
      //print "<BR>d<BR>";print_r($this->dprivileges);
      //print "<BR>g<BR>";print_r($this->gprivileges);
      //print "<BR>r<BR>";print_r($this->privileges);
      return ($err);
    }

  function ControlOid($idclassapp, $method ) 
    {

      if ($this->id_user == 1) return ""; // Admin can control everything

      // case already computed
      if (isset($this->coid[$method])) return $this->coid[$method];


      //    print "ControlOid : $this->oid, $this->id_user, $this->id_obj, $idclassapp, $method <BR>";
      

      //$this->AddDefaultPrivileges();
      // now determine all privileges for current user and for oid parameter
      if ($this->id_user == "") {	
	    return "Object Permission : current user not found";	  	
      }


      $acl=new Acl();
      if ( ! $acl->Set($method, $idclassapp)) {
	    $this->log->warning("Acl $method not available for App $idclassapp ");    
	    $err = "Acl $method not available for App $idclassapp ";
	    $this->coid[$method]=$err; // memo for optimization (no new computing)
	    return $err;
      }
     
	

      if (! $this->HasPrivilege($acl->id)) {
	$err = "Object Permission : permission $acl->description needed ($this->description - #".$this->id_obj.")";
	$this->coid[$method]=$err; // memo for optimization (no new computing)
	return $err;
	           	  		
	}

      $this->coid[$method]="";
      return ""; // OK
    }


}


?>
