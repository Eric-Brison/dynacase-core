<?
// $Id: Class.ControlObject.php,v 1.4 2002/03/08 14:37:36 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Class/Appmng/Class.ControlObject.php,v $
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

//
$CLASS_CONTROLOBJECT_PHP = '$Id: Class.ControlObject.php,v 1.4 2002/03/08 14:37:36 eric Exp $';
include_once('Class.DbObjCtrl.php');
include_once('Class.QueryDb.php');
include_once('Class.Application.php');
include_once('Class.Action.php');
include_once('Class.Acl.php');
include_once('Class.User.php');
include_once('Class.Group.php');

Class ControlObject extends DbObj
{
  var $fields = array ( "id_obj","id_class", "description");

  var $id_fields = array ( "id_obj" ,"id_class");

  var $dbtable = "octrl";
  

  var $sqlcreate = '
create table octrl (id_obj int not null,
                    id_class  int not null,
                    description varchar(256));
create unique index i_octrl on octrl (id_obj, id_class);';


  // --------------------------------------------------------------------
  //---------------------- OBJECT CONTROL PERMISSION --------------------

 

  // --------------------------------------------------------------------
  function ControlObject($dbaccess='', $id='',$res='',$dbid=0)
  // --------------------------------------------------------------------
    {
      

      // change DB for permission : see 'dboperm' session var
      global $action;
      $dbaccess= $action->Read("dboperm");


      DbObj::DbObj($dbaccess, $id,$res,$dbid);
      
      

    }

  // --------------------------------------------------------------------
  


  // get controlled object for a specific class
  function GetOids ($idclass) {
    
    $oids = array();
    $query = new QueryDb($this->dbaccess,"ControlObject");
    $query-> AddQuery("id_class=$idclass");
    $table1 = $query->Query();
    if ($query->nb > 0)
      {
	while(list($k,$v) = each($table1)) 
	    {
	      $oids[]= $v;
	      
	    }
	
      }
    return $oids;
  }

}


?>
