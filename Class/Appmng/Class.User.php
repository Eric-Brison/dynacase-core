<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Class.User.php,v 1.20 2004/02/03 09:13:57 caroline Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: Class.User.php,v 1.20 2004/02/03 09:13:57 caroline Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Class/Appmng/Class.User.php,v $
// ---------------------------------------------------------------
//  O   Anakeen - 2000
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

$CLASS_USER_PHP = '$Id: Class.User.php,v 1.20 2004/02/03 09:13:57 caroline Exp $';
include_once('Class.DbObj.php');
include_once('Class.QueryDb.php');
include_once('Class.Log.php');
include_once('Class.Application.php');
include_once('Class.Group.php');
include_once('FDL/Class.Doc.php');
include_once('FDL/Lib.Dir.php');

define("ANONYMOUS_ID", 3);

Class User extends DbObj
{
  var $fields = array ( "id","iddomain","lastname","firstname","login","password","isgroup","expires","passdelay","status");

  var $id_fields = array ("id");

  var $dbtable = "users";

  var $order_by="lastname, isgroup desc";

  var $fulltextfields = array ("login","lastname","firstname");

  var $sqlcreate = "
create table users ( id      int not null,
                     iddomain int not null,
                primary key (id),
                        lastname   text,
                        firstname  text,
                        login      text not null,
                        password   varchar(30) not null,
                        isgroup    char,
                        expires    int,
                        passdelay  int,
                        status     char);
create index users_idx1 on users(id);
create index users_idx2 on users(lastname);
create index users_idx3 on users(login);
create sequence seq_id_users start 10";



  function SetLoginName($loginDomain)
    {
      $query = new QueryDb($this->dbaccess,"User");
      if (ereg("(.*)@(.*)",$loginDomain, $reg)) {
    
	$queryd = new QueryDb($this->dbaccess,"Domain");
	$queryd->AddQuery("name='".$reg[2]."'");
	$list = $queryd->Query();

	if ($queryd->nb == 1) {
	  $domainId=$list[0]->iddomain;
	  $query->AddQuery("iddomain='$domainId'");
	  $query->AddQuery("login='".$reg[1]."'");
	} else {
	  return false;
	}
    
      } else {

	$query->AddQuery("login='$loginDomain'");
      }
      $list = $query->Query();

      if ($query->nb == 1) {
	$this=$list[0];
      } else {
	return FALSE;
      }

      return TRUE;
    }
  function SetLogin($login,$domain)
    {
      $query = new QueryDb($this->dbaccess,"User");

      $query->basic_elem->sup_where=array("login='$login'",
					  "iddomain=$domain");

      $list = $query->Query();

      if ($query->nb != 0) {
	$this=$list[0];
      } else {
	return FALSE;
      }

      return TRUE;
    }

function CheckLogin($login,$domain,$whatid)
    {
      $query = new QueryDb($this->dbaccess,"User");                                                                                    
                                                                                      
      $query->basic_elem->sup_where=array("login='$login'",
                                          "iddomain=$domain");                                                                                      
                                                                                      
      $list = $query->Query();
      if ($query->nb==0 or ($query->nb==1 and $list[0]->id==$whatid))
        {return true;}
      else {return false;}
                                                                                      
                                                                                      
    }


  function SetUsers($lname,$fname,$expires,$passdelay,$login,$status,$pwd1,$pwd2,$fid,$expiresd,$expirest,$daydelay,$iddomain,$domain)
  {
	$this->lastname=$lname;
	$this->firstname=$fname;	
	$this->status=$status;
	$this->login=$login;

//ne modifie pas le password en base même si contrainte forcée 
	if ($pwd1==$pwd2 and $pwd1<>"")
	{$this->password_new=$pwd2;   
        $this->password=$pwd1;
	}

	$this->old_iddomain=$this->iddomain;

        $this->iddomain=$iddomain;               
	$this->domain=$domain;
        if ($this->iddomain=="") {$this->iddomain=1;$this->domain="Pas de compte mail";}

        $this->daydelay=$daydelay;
        $this->expires=$expires;
        $this->expiresd=$expiresd;
        $this->expirest=$expirest; 
 
//convert date 
	$expdate=$this->expiresd." ".$this->expirest.":00";
	$exptime=0;
        if ($expdate != "") {
          if (ereg("([0-9][0-9])/([0-9][0-9])/(2[0-9][0-9][0-9]) ([0-2][0-9]):([0-5][0-9]):([0-5][0-9])", $expdate, $reg)) {                                             $exptime=mktime($reg[4],$reg[5],$reg[6],$reg[2],$reg[1],$reg[3]);                                                                                          } 
        }

       if (($exptime>0) && ($exptime != $this->expires))  $this->expires=$exptime;
       else  $this->expires=0;   	  

       $this->passdelay=($this->daydelay)*3600*24;
       $this->fid=$fid;

//Affect mailAccount
if ($this->iddomain<>1)
 {
   $mail=new MailAccount("",$this->id);
   $mail->login=$this->login;
                                                                                                                                                             
   if ($this->old_iddomain==1)
   {//create a new  mail account
   $mail->iddomain=$this->iddomain;
   $mail->iduser=$this->id;
   $mail->Add();
   }
   else
   {//update login if olddomain<>1
   $mail->Modify();
   }
 }                                                                                                                                                     
}


//Add and Update expires and passdelay for password
//Call in PreUpdate and PreInsert
function GetExpires()
{
if (intval($this->passdelay) == 0) {$this->expires="0"; $this->passdelay="0";$this->daydelay="0";}// nether expire
      else if (intval($this->expires)==0)
              {$this->expires=time()+$this->passdelay;
              }

$this->daydelay=$this->passdelay/(3600*24);

if (intval($this->expires)>0)
    {$this->expiresd=strftime("%d/%m/%Y",intval($this->expires));
     $this->expirest=strftime("%X",intval($this->expires));
    }


}


function PreInsert()
    {
//     if ($this->Setlogin($this->login,$this->iddomain)) return "this login exists";                                                                                      
        if ($this->id == "") {
        $res = pg_exec($this->dbid, "select nextval ('seq_id_users')");
        $arr = pg_fetch_array ($res, 0);
        $this->id = $arr[0];
      }
                                                                                      
                                                                                        
                                                                                     
      if (isset($this->isgroup) && ($this->isgroup == "Y")) {
        $this->password_new="no"; // no passwd for group
      } else {
        $this->isgroup = "N";
      }

//Add default group to user
	$group=new group($this->dbaccess);
	$group->iduser=$this->id;
	//2 = default group
	$group->idgroup=2;
	$group->Add();                                                                                      

/*
      $this->login = strtolower($this->login);
*/

      if (isset($this->password_new) && ($this->password_new!="")) {
        $this->computepass($this->password_new, $this->password);
      }


                                                                                      
//expires and passdelay
     $this->GetExpires(); 	    


}
   

function PostInsert()     
 {
      // create default ACL for each application
      // only for group
      //    if ($this->isgroup == "Y") {
      // 	$app = new Application();
      // 	$app-> UpdateUserAcl($this->id);
      //       }
   $this->FreedomWhatUser();  
 }

function PostUpdate()     
    {
      $this->FreedomWhatUser();  
    }


function PreUpdate()
  {
      if (isset($this->password_new) && ($this->password_new!="")) {
	$this->computepass($this->password_new, $this->password);
       }

//expires and passdelay
     $this->GetExpires();
                                                                                                                                                             

}


function PostDelete()
    {
      // delete reference in group table
      $group = new Group($this->dbaccess, $this->id);
      $group-> Delete();
      //remove MailAccount
      //$mailaccount=new MailAccount("",$this->id);
      //$mailaccount-> Remove();
    }


function FreedomWhatUser() {

   $dbaccess=GetParam("FREEDOM_DB");

   if ($this->fid<>"") { $iuser=new Doc($dbaccess,$this->fid); 

   $iuser->SetValue("US_WHATID",$this->id);
   $iuser->SetValue("US_LNAME",$this->lastname);
   $iuser->SetValue("US_FNAME",$this->firstname);
   $iuser->SetValue("US_PASSWD",$this->password);
   $iuser->SetValue("US_PASSWD1"," ");
   $iuser->SetValue("US_PASSWD2"," ");
   $iuser->SetValue("US_LOGIN",$this->login);
   $iuser->SetValue("US_STATUS",$this->status);
   $iuser->SetValue("US_PASSDELAY",$this->passdelay);
   $iuser->SetValue("US_EXPIRES",$this->expires);
   $iuser->SetValue("US_DAYDELAY",$this->daydelay);
   $iuser->SetValue("US_IDDOMAIN",$this->iddomain);
   $iuser->SetValue("US_DOMAIN",$this->domain);
   
   if ($this->passdelay<>0)
   { 
   $iuser->SetValue("US_EXPIRESD",$this->expiresd);
   $iuser->SetValue("US_EXPIREST",$this->expirest);
   }
   else 
   {
   $iuser->SetValue("US_EXPIRESD"," ");
   $iuser->SetValue("US_EXPIREST"," ");
   }

   $iuser->modify();


}

//Update from what
else {
$filter = array("us_whatid = ".$this->id);
$tdoc = getChildDoc($dbaccess, 0,0,"ALL", $filter,1,"LIST",getFamIdFromName($dbaccess,"IUSER"));
if (count ($tdoc)==0)
{                                                                                               
//Create a new doc IUSER                                                       
   $iuser = createDoc($dbaccess,getFamIdFromName($dbaccess,"IUSER"));
   $iuser->SetValue("US_WHATID",$this->id);
   $iuser->SetValue("US_LNAME",$this->lastname);
   $iuser->SetValue("US_FNAME",$this->firstname);
   $iuser->SetValue("US_PASSWD",$this->password);
   $iuser->SetValue("US_PASSWD1"," ");
   $iuser->SetValue("US_PASSWD2"," ");
   $iuser->SetValue("US_LOGIN",$this->login);
   $iuser->SetValue("US_STATUS",$this->status);
   $iuser->SetValue("US_PASSDELAY",$this->passdelay);
   $iuser->SetValue("US_DAYDELAY",$this->daydelay);
   $iuser->SetValue("US_EXPIRESD",$this->expiresd);
   $iuser->SetValue("US_EXPIREST",$this->expirest);
   $iuser->SetValue("US_EXPIRES",$this->expires);
   $iuser->SetValue("US_IDDOMAIN",$this->iddomain);
   $iuser->SetValue("US_DOMAIN",$this->domain);

   $iuser->Add();$iuser->modify();

}                                                                                                                                                   
 

else {
   $tdoc[0]->SetValue("US_WHATID",$this->id);
   $tdoc[0]->SetValue("US_LNAME",$this->lastname);
   $tdoc[0]->SetValue("US_FNAME",$this->firstname);
   $tdoc[0]->SetValue("US_PASSWD",$this->password);
   $tdoc[0]->SetValue("US_PASSWD1"," ");
   $tdoc[0]->SetValue("US_PASSWD2"," ");
   $tdoc[0]->SetValue("US_LOGIN",$this->login);
   $tdoc[0]->SetValue("US_STATUS",$this->status);
   $tdoc[0]->SetValue("US_PASSDELAY",$this->passdelay);
   $tdoc[0]->SetValue("US_DAYDELAY",$this->daydelay);
   $tdoc[0]->SetValue("US_EXPIRES",$this->expires);
   $tdoc[0]->SetValue("US_IDDOMAIN",$this->iddomain);
   $tdoc[0]->SetValue("US_DOMAIN",$this->domain);

   if ($this->passdelay<>0)
   {$tdoc[0]->SetValue("US_EXPIRESD",$this->expiresd);
   $tdoc[0]->SetValue("US_EXPIREST",$this->expirest);                                                                                                           }                                                                                                                                                            
  else
  {$tdoc[0]->SetValue("US_EXPIRESD"," ");
   $tdoc[0]->SetValue("US_EXPIREST"," ");
  }

   $tdoc[0]->modify();

}	

}                                                                                                                                                           
/*
    $wsh = GetParam("CORE_PUBDIR")."/wsh.php";
    $cmd = $wsh . " --api=usercard_iuser --whatid={$this->id}";
    exec($cmd);
*/

  }


  // --------------------------------------------------------------------
  function computepass($pass, &$passk)
    {
      srand((double)microtime()*1000000);
      $salt = chr(rand(59,122)).chr(rand(59,122));
      $passk = crypt($pass, $salt);
    }

  function checkpassword($pass)
    {
      if ($this->isgroup == 'Y') return false; // don't log in group 
      return($this->checkpass($pass,$this->password));
    }    

  // --------------------------------------------------------------------
  function checkpass($pass, $passk)
    {
      $salt = substr($passk, 0, 2);
      $passres = crypt($pass, $salt);
      return ($passres == $passk);
    } 

  function PostInit() {


    $group = new group($this->dbaccess);

    // Create admin user
    $this->iddomain=1;
    $this->id=1;
    $this->lastname="Master";
    $this->firstname="What";
    $this->password_new="anakeen";
    $this->login="admin";
    $this->Add();
    $group->iduser=$this->id;

    // Create default group
    $this->iddomain=1;
    $this->id=2;
    $this->lastname="Default";
    $this->firstname="What Group";
    $this->login="all";
    $this->isgroup="Y";
    $this->Add();
    $group->idgroup=$this->id;
    $group->Add();
  
  
    // Create anonymous user
    $this->iddomain=1;
    $this->id=ANONYMOUS_ID;
    $this->lastname="anonymous";
    $this->firstname="guest";
    $this->login="anonymous";
    $this->isgroup="N";
    $this->Add();


    // Store error messages
     
  }

  // get All Users (not group)
  function GetUserList($qtype="LIST") {
    $query = new QueryDb($this->dbaccess,"User");
    $query->order_by="lastname";
    $query-> AddQuery("(isgroup != 'Y') OR (isgroup isnull)");
    return($query->Query(0,0,$qtype));
  }

  // get All groups
  function GetGroupList($qtype="LIST") {
    $query = new QueryDb($this->dbaccess,"User");
    $query->order_by="lastname";
    $query-> AddQuery("isgroup = 'Y'");
    return($query->Query(0,0,$qtype));
  }

  // get All users & groups
  function GetUserAndGroupList() {
    $query = new QueryDb($this->dbaccess,"User");
    $query->order_by="isgroup desc, lastname";
    return($query->Query());
  }


  // get All ascendant group ids of the object user
  function GetGroupsId() {
    $query = new QueryDb($this->dbaccess, "Group");

    $query-> AddQuery("iduser='{$this->id}'");

    $list = $query->Query(0,0,"TABLE");
    $groupsid=array();

    if ($query->nb >0) {
      while (list($k,$v) = each($list)) {
	$groupsid[] = $v["idgroup"];
      }
    
    } 

    return $groupsid;

  }

  
  // for group :: get All user & groups ids in all descendant(recursive);
  function GetRUsersList($id) {
    $query = new QueryDb($this->dbaccess, "User");
    $list = $query->Query(0,0,"TABLE",
			  "select users.* from users, groups where ".
			  "groups.iduser=users.id and ".
			  "idgroup=$id ;");


    $uid=array();

    if ($query->nb >0) {
      while (list($k,$v) = each($list)) {
	$uid[$v["id"]] = $v;
	if ($v["isgroup"]=="Y") {
	  $uid += $this->GetRUsersList($v["id"]);
	}
      }
    
    } 

    return $uid;

  }

  
  function GetUsersGroupList($gid) {
    $query = new QueryDb($this->dbaccess, "User");
    $list = $query->Query(0,0,"TABLE",
			  "select users.* from users, groups where ".
			  "groups.iduser=users.id and ".
			  "idgroup=$gid ;");


    $uid=array();

    if ($query->nb >0) {
      while (list($k,$v) = each($list)) {
	$uid[$v["id"]] = $v;	
      }
    
    } 

    return $uid;

  }

  // only use for group
  // get user member of group
  function getGroupUserList($qtype="LIST", $withgroup=false) {
    $query = new QueryDb($this->dbaccess,"User");
    $query->order_by="isgroup desc, lastname";
    $selgroup = "and (isgroup != 'Y' or isgroup is null)";
    if ($withgroup) $selgroup = "";
    return ($query->Query(0,0,$qtype,
			  "select users.* from users, groups where ".
			  "groups.iduser=users.id and ".
			  "idgroup={$this->id} {$selgroup};"));
  }
}
?>
