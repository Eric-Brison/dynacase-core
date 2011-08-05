<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Class.ObjectPermission.php,v 1.13 2003/08/18 15:46:42 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage CORE
 */
 /**
 */

// $Id: Class.ObjectPermission.php,v 1.13 2003/08/18 15:46:42 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Class/Appmng/Class.ObjectPermission.php,v $
// ---------------------------------------------------------------


$CLASS_OBJECTPERMISSION_PHP = '$Id: Class.ObjectPermission.php,v 1.13 2003/08/18 15:46:42 eric Exp $';
include_once('Class.DbObj.php');
include_once('Class.QueryDb.php');
include_once('Class.Acl.php');
include_once('Class.ControlObject.php');
class ObjectPermission extends DbObj
{
  var $fields = array ( "id_user","id_obj","id_class","ids_acl");

  var $id_fields = array ( "id_user","id_obj","id_class");

  var $dbtable = "operm";

  var $sqlcreate = '
create table operm (id_user int ,
                    id_obj int not null,
                    id_class int not null,
                    ids_acl int[] );
create unique index i_operm on operm (id_user, id_obj, id_class); ';

  var $classid=0; // if 0 not a controlled object
  var $description="";
  var $coid=array();
  var $privileges = array(); // default privilege from permission table

  var $iscomplete = false; // indicate if all privileges are computed

  function ObjectPermission($dbaccess='', $id='',$res='',$dbid=0)
    {
      if (is_array($id)) {
	  $this->Affect(array("id_user" => $id[0],
			      "id_obj" => $id[1],
			      "id_class" => $id[2]));
      }

      // change DB for permission : see 'dboperm' session var
      global $action;
      $dbaccess= $action->Read("dboperm",$dbaccess);


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
			      "id_obj" => $id[1],
			      "id_class" => $id[2]));
      }
    }
  function GetDescription()
    {
      $octrl = new ControlObject($this->dbaccess,array($this->id_obj,$this->id_class));

      if ( $octrl->IsAffected()) {
	$this->classid = $octrl->id_class;
	$this->description = $octrl->description;
      }
      return $this->description;

    }

//   function PostSelect($id)
//     {      

//       // init privileges
//       $this-> GetPrivileges();
//     }
//   function PostDelete()
//     {
//       // update privileges
//       $this-> GetPrivileges();
//     }

//   function PostUpdate()
//     {
//       // update privileges
//       $this-> GetPrivileges();
//     }




  // return ACL list for a user and a oid
//   function GetPrivilegesOld() {

    
//     if ( $this->iscomplete) return ($this->privileges);

//     $this->privileges= array(); 
//     $this->upprivileges= array();// privileges array for a user (not including group) in an application
//     $this->unprivileges= array();// specifific NO privileges array for a user in an application


//     if (true){
//     // add groups privilege
//     $ugroup = new Group($this->dbaccess,
// 			$this->id_user);
  
    
//     while (list($k,$gid) = each($ugroup->groups)) {

//       $gperm = new ObjectPermission($this->dbaccess, 
// 				    array($gid, 
// 					  $this->id_obj,
// 					  $this->id_class));
    
//       // add group 

//       while (list($k2,$gacl) = each($gperm->privileges)) {
// 	if (! in_array($gacl, $this->privileges)) {
// 	  $this->gprivileges[]= $gacl;
// 	  $this->privileges[]= $gacl;
// 	}    
//       }
//     }
//     }

    
//     if (is_array($this->ids_acl) ) {
//       while (list($k,$v) = each($this->ids_acl)) {
// 	if ($v->id_acl > 0) {
// 	  // add privilege
// 	  $this->upprivileges[]= $v->id_acl;
// 	  if (! in_array($v->id_acl, $this->privileges)) {
// 	    $this->privileges[]= $v->id_acl;
// 	  } 
// 	}else { 
// 	  // suppress privilege
// 	  $this->unprivileges[]= -($v->id_acl);
	
// 	  $nk=array_search(-($v->id_acl), $this->privileges, false);
// 	  if (is_integer($nk)) {
// 	    unset($this->privileges[$nk]);
// 	  }
// 	}
      
//       }
//     }

//     //    $this->AddDefaultPrivileges();
//     $this->iscomplete= true; // to avoid another computing
//     return($this->privileges);
//   }
  // return ACL list for a user and a oid


  function GetGroupPrivileges() {
    if (isset($this->gprivileges)) return ($this->gprivileges);

     $this->gprivileges= array();// group privilege
     $result = pg_exec($this->init_dbid(),
		       "select getprivilege({$this->id_user},{$this->id_obj},{$this->id_class},true)");
    if (pg_numrows ($result) > 0) {
      $arr = pg_fetch_array ($result, 0);
      $this->gprivileges = explode(",",substr($arr[0],1,-1));
    }
  }
  function GetPrivileges() {

    
    if ( $this->iscomplete) return ($this->privileges);

    $this->privileges= array(); 
    $this->upprivileges= array();// privileges array for a user (not including group) in an application
    $this->unprivileges= array();// specifific NO privileges array for a user in an application
    


      
      $acls = explode(",",substr($this->ids_acl,1,-1));

      while (list($k,$v) = each($acls)) {
	if ($v>0) $this->upprivileges[] = $v;
	else $this->unprivileges[] = -$v;
      }

     $result = pg_exec($this->init_dbid(),
		       "select getprivilege({$this->id_user},{$this->id_obj},{$this->id_class},false)");
    //  print "select getprivilege({$this->id_user},{$this->id_obj},{$this->id_class},false)";
    //print $result."<HR>";
    if (pg_numrows ($result) > 0) {
      $arr = pg_fetch_array ($result, 0);
      $this->privileges = array_unique(explode(",",substr($arr[0],1,-1)));
    }
    // print_r( $this->privileges);



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


  function AddAcl($idacl) {
    if ($this->ids_acl == "") $this->ids_acl = "{".$idacl."}";
    else   $this->ids_acl=str_replace("}",",$idacl}",$this->ids_acl) ;    
  }

  function HasPrivilege($idacl)
    {
      return(($this->id_user == 1) || // admin user
	     (in_array($idacl, $this->privileges)));
    }


  function PostDelete() {
    $this->ids_acl="";
  }
  function Control( $method)
    {
      // return "" if the current user can apply method on object
      // else return string error
      $this->GetPrivileges();
      $err = $this-> ControlOid( $this->id_class , $method);
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
	$err = sprintf(_("Object Permission : permission %s needed (%s) - # %d"),
		       _($acl->description),
		       $this->GetDescription(),
		       $this->id_obj);
	
	$this->coid[$method]=$err; // memo for optimization (no new computing)
	return $err;
	           	  		
	}

      $this->coid[$method]="";
      return ""; // OK
    }


}


?>
