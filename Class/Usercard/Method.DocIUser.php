<?php
/**
 * User manipulation
 *
 * @author Anakeen 2004
 * @version $Id: Method.DocIUser.php,v 1.49 2008/08/13 14:07:54 jerome Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage USERCARD
 */
 /**
 */

/**
 * @begin-method-ignore
 * this part will be deleted when construct document class until end-method-ignore
 */
class _IUSER extends _USER {
        /*
         * @end-method-ignore
         */
var $cviews=array("FUSERS:FUSERS_IUSER");
var $eviews=array("USERCARD:CHOOSEGROUP");
var $defaultview="FDL:VIEWBODYCARD";
var $defaultedit="FDL:EDITBODYCARD";
 

function SpecRefresh() {
  $err=_USER::SpecRefresh();

    $this->AddParamRefresh("US_WHATID","US_LOGIN,US_GROUP");
    $this->AddParamRefresh("US_AUTOMAIL","US_EXTMAIL");
    if ($this->getValue("US_IDDOMAIN",1) > 1) $this->AddParamRefresh("US_WHATID","US_DOMAIN");
    $this->AddParamRefresh("US_IDDOMAIN","US_DOMAIN");
    
    if ($this->getValue("US_STATUS")=='D') $err .= ($err==""?"":"\n")._("user is desactivated");
    // refresh MEID itself
    $this->SetValue("US_MEID",$this->id);
    $iduser = $this->getValue("US_WHATID");
    if ($iduser > 0) {
      $user = $this->getWUser();
      if (! $user->isAffected()) return sprintf(_("user #%d does not exist"), $iduser);
    } else {
      if ($this->getValue("us_login")!='-') $err= _("user has not identificator");
      $oa=$this->getAttribute("us_passwd1");
      if ($oa) $oa->needed=true;
      $oa=$this->getAttribute("us_passwd2");
      if ($oa) $oa->needed=true;
      $oa=$this->getAttribute("us_tab_system");
      $oa->setOption("firstopen", "yes");
    }
    return $err;
}

/**
 * test if the document can be set in LDAP
 */
function canUpdateLdapCard() {
  return  ($this->getValue("US_STATUS")!='D');

}

/**
 * @deprecated
 */
function getOtherGroups() {
  if ($this->id == 0) return array();
  
  include_once("FDL/freedom_util.php");  
  include_once("FDL/Lib.Dir.php");  

  $sqlfilters[]="in_textlist(grp_idruser,{$this->id})";
  // $sqlfilters[]="fromid !=".getFamIdFromName($this->dbaccess,"IGROUP");
  $tgroup=getChildDoc($this->dbaccess, 
		      0, 
		      "0", "ALL", $sqlfilters, 
		      1, 
		      "TABLE", getFamIdFromName($this->dbaccess,"GROUP"));
  
  return $tgroup;
}

/**
 * get all direct group document identificators of the isuser
 * @return @array of group document id, the index of array is the system identificator
 */
public function getUserGroups() {
	$err=simpleQuery($this->dbaccess, 
	       sprintf("SELECT id, fid from users, groups where groups.iduser=%d and users.id = groups.idgroup;",$this->getValue("us_whatid")),
	       $groupIds,false,false);
	if (! $err) {
		$gids=array();
		foreach($groupIds as $gid) {
			$gids[$gid["id"]]=$gid["fid"];
		}
		return $gids;
	}
	return null;
}

/**
 * return all direct group and parent group document identificators of $gid
 * @param string $gid systeme identificator group or users
 */
protected function getAscendantGroup($gid) {
	$groupIds=array();
	if ($gid > 0) {
		$err=simpleQuery($this->dbaccess,
		       sprintf("SELECT id, fid from users, groups where groups.iduser=%d and users.id = groups.idgroup;",$gid),
		       $groupIds,false,false);
		$gids=array(); // current level
        $pgids=array(); // fathers
		foreach ($groupIds as $gid) {
			$gids[$gid["id"]]=$gid["fid"];
		}
		
		foreach ($gids as $systemGid=>$docGid) {
			$pgids+= $this->getAscendantGroup($systemGid);
		}
		$groupIds=$gids + $pgids;
	}
	return $groupIds;
}
/**
 * get all direct group and parent group document identificators of the isuser
 * @return @array of group document id the index of array is the system identificator
 */
public function getAllUserGroups() {
	return $this->getAscendantGroup($this->getValue("us_whatid"));
}


/**
 * Refresh folder parent containt
 */
function refreshParentGroup() {
  $tgid=$this->getTValue("US_IDGROUP");
  foreach ($tgid as $gid) {
    $gdoc=new_Doc($this->dbaccess,$gid);
    if ($gdoc->isAlive()) {
      $gdoc->insertGroups();
    }
  }
}
/**
 * recompute intranet values from USER database
 */
function RefreshDocUser() {

  $err="";
  $wid=$this->getValue("us_whatid");
  if ($wid > 0) { 
    $wuser=$this->getWuser(true);

    if ($wuser->isAffected()) {
      $this->SetValue("US_WHATID",$wuser->id);
      $this->SetValue("US_LNAME",$wuser->lastname);
      $this->SetValue("US_FNAME",$wuser->firstname);
      $this->SetValue("US_PASSWD",$wuser->password);
      $this->SetValue("US_PASSWD1"," ");
      $this->SetValue("US_PASSWD2"," ");
      $this->SetValue("US_LOGIN",$wuser->login);
      $this->SetValue("US_STATUS",$wuser->status);
      $this->SetValue("US_PASSDELAY",$wuser->passdelay);
      $this->SetValue("US_EXPIRES",$wuser->expires);
      $this->SetValue("US_DAYDELAY",$wuser->passdelay/3600/24);
      $this->SetValue("US_IDDOMAIN",$wuser->iddomain);
      include_once("Class.Domain.php");
      $dom = new Domain("",$wuser->iddomain);
      $this->SetValue("US_DOMAIN",$dom->name);
      $mail=$wuser->getMail();
      if (! $mail) $this->DeleteValue("US_MAIL");
      else $this->SetValue("US_MAIL", $mail);
      if ($wuser->passdelay<>0) { 
	$this->SetValue("US_EXPIRESD",strftime("%d/%m/%Y",$wuser->expires));
	$this->SetValue("US_EXPIREST",strftime("%H:%M",$wuser->expires));
      } else  {
	$this->SetValue("US_EXPIRESD"," ");
	$this->SetValue("US_EXPIREST"," ");
      }


      $this->SetValue("US_MEID",$this->id);

      // search group of the user
      $g = new Group("",$wid);

      if (count($g->groups) > 0) {
	foreach ($g->groups as $gid) {
	  $gt=new User("",$gid);
	  $tgid[$gid]=$gt->fid;
	  $tglogin[$gid]=$this->getTitle($gt->fid);
	}
	$this->SetValue("US_GROUP", $tglogin);
	$this->SetValue("US_IDGROUP", $tgid);
      } else {
	$this->SetValue("US_GROUP"," ");
	$this->SetValue("US_IDGROUP"," ");
      }
      $err=$this->modify();


    } else     {
      $err= sprintf(_("user %d does not exist"),$wid);
    }
  } 
  
  
  return $err;
}

/**
 * affect to default group
 */
function setToDefaultGroup() {
  $grpid=$this->getParamValue("us_defaultgroup");
  if ($grpid) {
    $grp=new_doc($this->dbaccess,$grpid);
    if ($grp->isAlive()) {
      $err=$grp->addFile($this->initid);
    }
  }
  return $err;
}

function postCreated() {
  $err = "";
  global $action;
  $ed = $action->getParam("AUTHENT_ACCOUNTEXPIREDELAY");
  if ( $ed>0 ) {
    $expdate = time() + ($ed*24*3600); 
    $err = $this->SetValue("us_accexpiredate",strftime("%d/%m/%Y 00:00:00",$expdate));
    if ($err=='') $err = $this->modify(true, array("us_accexpiredate"), true);
  }
  return $err;
}

/**
 * Modify IUSER via Freedom    
 */
function PostModify() {                                                                                    
  $uid=$this->GetValue("US_WHATID");
  $lname=$this->GetValue("US_LNAME");
  $fname=$this->GetValue("US_FNAME");
  $pwd1=$this->GetValue("US_PASSWD1");
  $pwd2=$this->GetValue("US_PASSWD2");
  $pwd=$this->GetValue("US_PASSWD");
  $expires=$this->GetValue("US_EXPIRES");
  $daydelay=$this->GetValue("US_DAYDELAY");
  if ($daydelay==-1) $passdelay=$daydelay;
  else $passdelay=intval($daydelay)*3600*24;
  $status=$this->GetValue("US_STATUS");
  $login=$this->GetValue("US_LOGIN");
  $extmail=$this->GetValue("US_EXTMAIL",$this->getValue("us_homemail"," "));

  if ($login != "-") {
    // compute expire for epoch
  
    $expiresd=$this->GetValue("US_EXPIRESD");
    $expirest=$this->GetValue("US_EXPIREST","00:00");
    //convert date 
    $expdate=$expiresd." ".$expirest.":00";
    $expires=0;
    if ($expdate != "") {
      if (preg_match("|([0-9][0-9])/([0-9][0-9])/(2[0-9][0-9][0-9]) ([0-2][0-9]):([0-5][0-9]):([0-5][0-9])|", 
	       $expdate, $reg)) {   
	$expires=mktime($reg[4],$reg[5],$reg[6],$reg[2],$reg[1],$reg[3]);
      } else  if (preg_match("|(2[0-9][0-9][0-9])-([0-9][0-9])-([0-9][0-9]) ([0-2][0-9]):([0-5][0-9]):([0-5][0-9])|", 
	       $expdate, $reg)) {   
	$expires=mktime($reg[4],$reg[5],$reg[6],$reg[2],$reg[3],$reg[1]);
      }
      
    }


    $iddomain=$this->GetValue("US_IDDOMAIN");
    $domain=$this->GetValue("US_DOMAIN");

    $fid=$this->id;        
    $newuser=false;
    $user=$this->getWUser();
    if (!$user) {
      $user=new User(""); // create new user
      $this->wuser=&$user;
      $newuser=true;
    }
    $err.=$user->SetUsers($fid,$lname,$fname,$expires,$passdelay,
			  $login,$status,$pwd1,$pwd2,
			  $iddomain,$extmail);  
    if ($err=="") { 
      if ($user)  {
	$this->setValue("US_WHATID",$user->id);
	$this->modify(false,array("us_whatid"));
	$err=$this->setGroups(); // set groups (add and suppress) may be long
	if ($newuser) $err.=$this->setToDefaultGroup();
      }
      if (($pwd1 == "") && ($pwd1==$pwd2) && ($pwd!="")) {
	if (($pwd != $user->password) && (strlen($pwd)>12)) {
	  $user->password=$pwd;
	  $err=$user->modify();
	}
      }
    }
 
    if ($err=="") {
      $err=$this->RefreshDocUser();// refresh from core database 
      
      //      $this->refreshParentGroup();
      $errldap=$this->RefreshLdapCard();
      if ($errldap!="") AddWarningMsg($errldap);
    } 

  } else { 
    // tranfert extern mail if no login specified yet
    if ($this->getValue("us_login")=="-") {
      $this->setValue("US_IDDOMAIN","0");
      $email=$this->getValue("us_extmail",$this->getValue("us_homemail"));
      if (($email != "")&&($email[0]!="<")) $this->setValue("us_mail",$email);
      else $this->deleteValue("us_mail");
    }
  }

  $this->setValue("US_LDAPDN",$this->getLDAPValue("dn",1));
  return $err;

}



function PostDelete() {
  _USER::PostDelete();

  $user=$this->getWUser();
  if ($user) $user->Delete();
                                                                                     
}                                                                                    
                                                                                    
                                                                                      

/**
 * Do not call ::setGroup if its import 
 * called only in initialisation
 */
function preImport() {
  if ($this->id > 0) {
    global $_POST;
    $_POST["gidnew"]="N";
  }
}
                                                                                      
function ConstraintPassword($pwd1,$pwd2,$login) {
  $sug=array();
  $err="";

  if ($pwd1<>$pwd2) {
    $err= _("the 2 passwords are not the same");
  }  else if (($pwd1 == "")&&($this->getValue("us_whatid") == "")) {
    if ($login != "-") $err= _("passwords must not be empty");
  }    
  
                                                                                      
  return array("err"=>$err,
	       "sug"=>$sug);                                                                              
                                                                                  
}

function ConstraintExpires($expiresd,$expirest,$daydelay) {
  $sug=array();
  if (($expiresd<>"") && ($daydelay==0)) {
    $err= _("Expiration delay must not be 0 to keep expiration date");
  }
                                       
  return array("err"=>$err,
	       "sug"=>$sug);
}

function editlikeperson($target="finfo",$ulink=true,$abstract="Y") {
  global $action;
  
  $this->lay = new Layout(getLayoutFile("FDL","editbodycard.xml"), $action);
  
  $this->attributes->attr['us_tab_system']->visibility='R';
  $this->attributes->attr['us_fr_userchange']->visibility='R';
  $this->ApplyMask();
  if ($this->getValue("us_iddomain") == 0) {
    $this->attributes->attr['us_extmail']->mvisibility='W';
    $this->attributes->attr['us_extmail']->fieldSet= $this->attributes->attr['us_fr_coord'];
    $this->attributes->attr['us_extmail']->ordered=$this->attributes->attr['us_pphone']->ordered - 1;
    uasort($this->attributes->attr,"tordered"); 
  }
  
  $this->editbodycard($target,$ulink,$abstract);
  
}

function fusers_iuser($target="finfo",$ulink=true,$abstract="Y") {
  global $action;
  //setHttpVar("specialmenu","menuab");
  $this->viewdefaultcard($target,$ulink,$abstract);
  $action->parent->AddCssRef("USERCARD:faddbook.css",true);
  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/USERCARD/Layout/faddbook.js");

  
  // list of attributes displayed directly in layout
  $ta=array("us_workweb","us_photo","us_lname","us_fname","us_society","us_civility","us_mail","us_phone","us_mobile","us_fax","us_intphone","us_workaddr","us_workcedex","us_country","us_workpostalcode","us_worktown","us_groups","us_whatid","us_state","us_login","us_status","us_domain","us_expiresd","us_expirest","us_daydelay","us_idsociety");
  //$ta["ident"]=array("us_lo

  $la=$this->getAttributes();
  $to=array();
  $tabs=array();
  foreach ($la as $k=>$v) {
    $va=$this->getValue($v->id);
    if (($va || ($v->type=="array")) && (! in_array($v->id,$ta)) &&(!$v->inArray()) ) {
	  
     if ((($v->mvisibility == "R") || ($v->mvisibility == "W"))) {
	if ($v->type=="array") {
	  $hv=$this->getHtmlValue($v,$va,$target,$ulink);
	  if ($hv) {
	    $to[]=array("lothers"=>$v->labelText,
		      "aid"=>$v->id,
		      "vothers"=>$hv,
		      "isarray"=>true);	
	    $tabs[$v->fieldSet->labelText][]=$v->id;
	  }
	} else {
	  $to[]=array("lothers"=>$v->labelText,
		      "aid"=>$v->id,
		      "vothers"=>$this->getHtmlValue($v,$va,$target,$ulink),
		      "isarray"=>false);
	$tabs[$v->fieldSet->labelText][]=$v->id;
	}
      }
    }
  }
  $this->lay->setBlockData("OTHERS",$to);
  $this->lay->set("HasOTHERS",(count($to)>0));
  $this->lay->set("HasDOMAIN",($this->getValue("US_IDDOMAIN")>9));
  $this->lay->set("HasDPassword",(intval($this->getValue("US_DAYDELAY"))!=0));
  $ltabs=array();
  foreach ($tabs as $k=>$v) {
    $ltabs[$k]=array("tabtitle"=>$k,
		     "aids"=>"['".implode("','",$v)."']");
  }
  $this->lay->setBlockData("TABS",$ltabs);
  $this->lay->set("CanEdit",($this->control("edit")==""));
}


/**
 * interface to only modify name and password
 */
function editchangepassword() {
  $this->viewprop();
  $this->editattr(false);
}
function fusers_eiuser() {
  global $action;
  $this->editattr();
  $action->parent->AddCssRef("USERCARD:faddbook.css",true);
  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/USERCARD/Layout/faddbook.js");
  $firsttab=getHttpVars("tab"); // first tab displayed
  
  // list of attributes displayed directly in layout
  $ta=array("us_workweb","us_photo","us_lname","us_fname","us_society","us_idsociety","us_civility","us_mail","us_phone","us_mobile","us_fax","us_intphone","us_workaddr","us_workcedex","us_country","us_workpostalcode","us_worktown","us_groups","us_whatid","us_state","us_login","us_status","us_domain","us_iddomain","us_expiresd","us_expirest","us_daydelay","us_passwd1","us_passwd2","us_extmail","us_role","us_scatg","us_pfax","us_pphone","us_job","us_type","us_initials","us_service","us_idservice","us_socaddr");
  //$ta["ident"]=array("us_lo

  $la=$this->getNormalAttributes();

  $this->lay->set("editgroup",($la["us_group"]->mvisibility=="W"));
  $this->lay->set("firsttab",$firsttab);
  $to=array();
  $th=array();
  $tabs=array();
  foreach ($la as $k=>$v) {
    $va=$this->getValue($v->id);
    if (!$v->inArray() && (! in_array($v->id,$ta)))  {	      
      if ($v->mvisibility != "I") {
	if ($v->type=="array") {
	  $hv=getHtmlInput($this,$v,$va);
	  if ($hv) {
	    if ($v->mvisibility != "H") {
	      $to[]=array("lothers"=>$v->labelText,
			  "aid"=>$v->id,
			  "vothers"=>$hv,
			  "isarray"=>true);		    
	      $tabs[$v->fieldSet->labelText][]=$v->id;
	    } else {
	      $th[]=array("aid"=>$v->id,
			  "vothers"=>getHtmlInput($this,$v,$va));
	    }
	  }
	} else {
	  if ($v->mvisibility != "H") {
	    $to[]=array("lothers"=>$v->labelText,
			"aid"=>$v->id,
			"vothers"=>getHtmlInput($this,$v,$va),
			"isarray"=>false);
	    $tabs[$v->fieldSet->labelText][]=$v->id;
	  } else {
	    $th[]=array("aid"=>$v->id,
			"vothers"=>getHtmlInput($this,$v,$va));
	    
	  }
	
	}
      }
    }
  }
  $this->lay->setBlockData("OTHERS",$to);
  $this->lay->setBlockData("IHIDDENS",$th);
  $this->lay->set("HasOTHERS",(count($to)>0));
  $ltabs=array();
  foreach ($tabs as $k=>$v) {
    $ltabs[$k]=array("tabtitle"=>$k,
		     "aids"=>"['".implode("','",$v)."']");
  }
  $this->lay->setBlockData("TABS",$ltabs);
  $this->viewprop();
}

/**
 * Set/change user password
 */
function setPassword($password) {
  $idwuser = $this->getValue("US_WHATID");

  $wuser = $this->getWUser();
  if( !$wuser->isAffected() ) {
    return sprintf(_("user #%d does not exist"), $idwuser);
  }

  // Change what user password
  $wuser->password_new = $password;
  $err = $wuser->modify();
  if( $err != "" ) {
    return $err;
  }

  // Change IUSER password
  $err = $this->SetValue("US_PASSWD",$password);
  if( $err != "" ) {
    return $err;
  }
  $err = $this->modify();
  if( $err != "" ) {
    return $err;
  }

  return "";
}
/**
 * Increase login failure count
 */
function increaseLoginFailure() {
  if (!$this->canExecute(FUSERS,FUSERS_IUSER)) return "";
  if ($this->getValue("us_whatid")==1) return ""; // it makes non sense for admin
  $this->disableEditControl();
  $lf = $this->getValue("us_loginfailure",0) + 1;
  $err = $this->SetValue("us_loginfailure",$lf);
  if( $err=="") {
    $err = $this->modify(true, array("us_loginfailure"), true);
  }
  $this->enableEditControl();
  return "";
}

/**
 * Reset login failure count
 */
function resetLoginFailure() {
  if ($this->getValue("us_whatid")==1) return ""; // it makes non sense for admin
  $this->disableEditControl();
  $err = $this->SetValue("us_loginfailure",0);
  if( $err=="") {
    $err = $this->modify(true, array("us_loginfailure"), true);
  }
  $this->enableEditControl();
  return "";
}

function canResetLoginFailure() {
  if ($this->getValue("us_whatid")==1) return false; // it makes non sense for admin
  return ($this->getValue("us_loginfailure")>0?true:false);
}

/**
 * Manage account security
 */
function isAccountActive() {
  if ($this->getValue("us_whatid")==1) return false; // it makes non sense for admin
  return ($this->getValue("us_status",'A')=='A');
}
function activateAccount() {
  if (!$this->canExecute(FUSERS,FUSERS_IUSER)) return "";
  if ($this->getValue("us_whatid")==1) return "";
  $this->disableEditControl();
  $err = $this->SetValue("us_status",'A');
  if( $err=="") {
    $err = $this->modify(true, array("us_status"), true);
  }
  $this->enableEditControl();
  return "";
}
function isAccountInactive() {
  if ($this->getValue("us_whatid")==1) return false; // it makes non sense for admin
  return ($this->getValue("us_status",'A')!='A');
}
function desactivateAccount() {
  if (!$this->canExecute(FUSERS,FUSERS_IUSER)) return "";
  if ($this->getValue("us_whatid")==1) return "";
  $this->disableEditControl();
  $err = $this->SetValue("us_status",'D');
  if( $err=="") {
    $err = $this->modify(true, array("us_status"), true);
  }
  $this->enableEditControl();
  return "";
}
function accountHasExpired() {
  if ($this->getValue("us_whatid")==1) return false;
    $expd=$this->GetValue("us_accexpiredate");
    //convert date 
    $expires=0;
    if ($expd != "") {
      if (preg_match("|([0-9][0-9])/([0-9][0-9])/(2[0-9][0-9][0-9])|", 
	       $expd, $reg)) {   
	$expires=mktime(0,0,0,$reg[2],$reg[1],$reg[3]);
      } else  if (preg_match("|(2[0-9][0-9][0-9])-([0-9][0-9])-([0-9][0-9]|", 
	       $expd, $reg)) {   
	$expires=mktime(0,0,0,$reg[2],$reg[3],$reg[1]);
      }
      return ($expires<=time());
    }
    return false;
}
     /**
        * @begin-method-ignore
        * this part will be deleted when construct document class until end-method-ignore
        */
}

/*
 * @end-method-ignore
 */
?>
