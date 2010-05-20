<?php
/**
 * Set WHAT user & mail parameters
 *
 * @author Anakeen 2003
 * @version $Id: Method.DocIGroup.php,v 1.40 2008/09/04 09:48:26 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage USERCARD
 */
 /**
 */


var $cviews=array("FUSERS:FUSERS_IGROUP");
var $eviews=array("USERCARD:CHOOSEGROUP");
var $defaultview="FUSERS:FUSERS_IGROUP:V";
var $defaultedit="FUSERS:FUSERS_EIGROUP:T";
var $exportLdap = array(
		      // posixGroup
			"gidNumber" => "GRP_GIDNUMBER",                        
			//			"mail" => "GRP_MAIL", // not in schema but used in mailing client application
			"description" => "GRP_DESC" );
var $ldapobjectclass="posixGroup";
function specRefresh() {
  //  $err=$this->ComputeGroup();
  $err="";
  $this->AddParamRefresh("US_WHATID","GRP_MAIL,US_LOGIN");
  if ($this->getValue("US_IDDOMAIN",1) > 1) $this->AddParamRefresh("US_WHATID","US_DOMAIN");
  $this->AddParamRefresh("US_IDDOMAIN","US_DOMAIN");

  // refresh MEID itself
  $this->SetValue("US_MEID",$this->id);
  $iduser = $this->getValue("US_WHATID");
  if ($iduser > 0) {
    $user = $this->getWUser();
    if (! $user) return sprintf(_("group #%d does not exist"), $iduser);
  } else {
    return _("group has not identificator");
  }

  if ($this->getValue("grp_isrefreshed")=="0") $err.= _("this groups must be refreshed");
  return $err;
}

/**
 * test if the document can be set in LDAP
 */
function canUpdateLdapCard() {
  return  true;

}

/**
 * get LDAP title for group
 */
function getLDAPTitle() {
  return sprintf(_("%s group"),$this->title);
}
/**
 * get LDAP array of members
 * @return array
 */
function getLDAPMember() {
  $t=$this->getTValue("GRP_IDUSER");
  $tdn=array();
  foreach ($t as $k=>$v) {
    $du=getTDoc($this->dbaccess,$v);
    $tdnu=explode("\n",$du["ldapdn"]);
    if (count($tdnu)>0) {
      $dnu=$tdnu[0];
      if ($dnu) $tdn[]=$dnu;
    }
  }
  if (count($tdn)==0) $tdn="cn=nobody,dc=users,".$this->racine;
  return $tdn;
}
/**
 * recompute only parent group 
 * call {@see ComputeGroup()}
 * 
 * @return string error message, if no error empty string
 */
function RefreshGroup() {
  if ($this->norefreshggroup) return;
  //  $err=_GROUP::RefreshGroup(); 
  $err.=$this->RefreshDocUser();
  $err.=$this->refreshMembers();
  $err.=$this->insertGroups();
  $err.=$this->SetGroupMail(($this->GetValue("US_IDDOMAIN")>1));
  $err.=$this->Modify();
  //  AddWarningMsg(sprintf("RefreshGroup %d %s",$this->id, $this->title));
  if ($err == "") {
     $this->setValue("grp_isrefreshed","1");
     $this->modify(true,array("grp_isrefreshed"),true);
   }
  return $err;
}


/**
 * Refresh folder parent containt
 */
function refreshParentGroup() {
  $tgid=$this->getTValue("GRP_IDPGROUP");
  foreach ($tgid as $gid) {
    $gdoc=new_Doc($this->dbaccess,$gid);
    if ($gdoc->isAlive()) {
      $gdoc->insertGroups();
    }
  }
}
function PostModify() {
  $uid=$this->GetValue("US_WHATID");
  $gname=$this->GetValue("GRP_NAME");
  $login=$this->GetValue("US_LOGIN");
  $iddomain=$this->GetValue("US_IDDOMAIN");

  $fid=$this->id;        
  $user=$this->getWUser();
  if (!$user) {
    $user=new User(""); // create new user
    $this->wuser=&$user;
  }
  $err.=$user->SetGroups($fid,$gname,
			 $login,
			 $iddomain);   
  if ($err=="") {
    $this->setValue("US_WHATID",$user->id);
    $this->modify(false,array("us_whatid"));
    if ($user)  $err=$this->setGroups();
    
    // get members 
    //$this->RefreshGroup(); // in postinsert
    //    $this->refreshParentGroup();
    $wrg=$this->RefreshLdapCard();  
    if ($wrg) AddWarningMsg($wrg);

    // add in default folder root groups : usefull for import
    $tgid=$this->getTValue("GRP_IDPGROUP");
    $fdoc=$this->getFamdoc();
    $dfldid=$fdoc->dfldid;
    if ($dfldid != "") {
      $dfld=new_doc($this->dbaccess,$dfldid);
      if ($dfld->isAlive()) {
	if (count($tgid)==0)  $dfld->AddFile($this->initid);
	else  $dfld->delFile($this->initid);
      }      
    }    
  } 


  if ($err=="") $err="-"; // don't do modify after because it is must be set by USER::setGroups
  return $err;
} 


/**
 * update LDAP menbers after imodification of containt
 */
function specPostInsert() {
    return $this->RefreshLdapCard();  
}
/**
 * update groups table in USER database
 * @return string error message 
 */
function postInsertDoc($docid,$multiple) {
  $err="";
  if ($multiple == false) {
    $gid = $this->getValue("US_WHATID");
    if ($gid > 0) {
      $du = new_Doc($this->dbaccess,$docid);
      $uid = $du->getValue("us_whatid");
      if ($uid > 0) {
	$g = new Group("",$uid);
	$g->iduser=$uid;
	$g->idgroup=$gid;
	$err=$g->Add();
	if ($err=="OK") $err="";
	if ($err=="") {
	  $du->RefreshDocUser(); // to refresh group of user attributes
	  $this->RefreshGroup();
	}	
      }      
    }    
  }
  return $err;
}
/**
 * update groups table in USER database
 * @return string error message 
 */
function postMInsertDoc($tdocid) {

  $err="";

  $gid = $this->getValue("US_WHATID");
  if ($gid > 0) {

    $g = new Group("",$uid);
    foreach ($tdocid as $k=>$docid) {
      $du = new_Doc($this->dbaccess,$docid);
      $uid = $du->getValue("us_whatid");
      if ($uid > 0) {
	$g->iduser=$uid;
	$g->idgroup=$gid;
	$err=$g->Add();
	if ($err=="") $du->RefreshDocUser();
      }	      
    }     

    $this->RefreshGroup();
  }
  return $err;
}
/**
 * update groups table in USER database before suppress
 * @return string error message 
 */
function postUnlinkDoc($docid) {

  $err="";
  $gid = $this->getValue("US_WHATID");
  if ($gid > 0) {
      $du = new_Doc($this->dbaccess,$docid);
      $uid = $du->getValue("us_whatid");
      if ($uid > 0) {
	$g = new Group("",$gid);
	$g->iduser=$gid;
	$err=$g->SuppressUser($uid);
	if ($err=="") {
	  $du->RefreshDocUser();
	  $this->RefreshGroup();
	}	
      }      
  }    
  return $err;
}
function PostDelete() {

  $user=$this->getWUser();
  if ($user) $user->Delete();
                                                                                     
}          
/**
 * (re)insert members of the group in folder from USER databasee
 * 
 * @return string error message, if no error empty string
 */
function insertGroups() { 
  $user=$this->getWUser();
  $err="";
    
  // get members 
  $tu  = $user->GetUsersGroupList($user->id);
    
  if (is_array($tu)) {
    $this->Clear();
    $tfid=array();
    foreach($tu as $k=>$v) {
      //	if ($v["fid"]>0)  $err.=$this->AddFile($v["fid"]);
      if ($v["fid"]>0) $tfid[]=$v["fid"];	
    }
    $err=$this->QuickInsertMSDocId($tfid);// without postInsert
    
    $this->specPostInsert();

  } 
  return $err;
}
/**
 * insert members in a group in folder
 * it does not modify anakeen database (use only when anakeen database if updated)
 * must be use after a group add in anakeen database (use only for optimization in ::setGroups
 * 
 * @param int $docid user doc parameter
 * @return string error message, if no error empty string
 */
function insertMember($docid) { 
  $err = $this->AddFile($docid,"latest",true); // without postInsert
  $this->setValue("grp_isrefreshed","0");
  $this->modify(true,array("grp_isrefreshed"),true);

  return $err;
}
/**
 * suppress members of the group in folder
 * it does not modify anakeen database (use only when anakeen database if updated)
 * must be use after a group add in anakeen database (use only for optimization in ::setGroups
 * 
 * @param int $docid user doc parameter
 * @return string error message, if no error empty string
 */
function deleteMember($docid) { 
  $err = $this->DelFile($docid,true); // without postInsert
  $this->setValue("grp_isrefreshed","0");
  $this->modify(true,array("grp_isrefreshed"),true);

  return $err;
}

 /**
 * recompute intranet values from USER database
 */
function RefreshDocUser() {  
  $err="";
  $wid=$this->getValue("us_whatid");
  if ($wid > 0) { 
    $wuser=$this->getWUser(true);
    if ($wuser->isAffected()) {
      $this->SetValue("US_WHATID",$wuser->id);
      $this->SetValue("GRP_NAME",$wuser->lastname);
      //   $this->SetValue("US_FNAME",$wuser->firstname);
      $this->SetValue("US_LOGIN",$wuser->login);
      $this->SetValue("US_IDDOMAIN",$wuser->iddomain);
      include_once("Class.Domain.php");
      $dom = new Domain("",$wuser->iddomain);
      $this->SetValue("US_DOMAIN",$dom->name);
           if ($wuser->iddomain>1) $this->SetValue("GRP_MAIL",getMailAddr($wid) );
   
    
      $this->SetValue("US_MEID",$this->id);

      // search group of the group
      $g = new Group("",$wid);

      if (count($g->groups) > 0) {
	foreach ($g->groups as $gid) {
	  $gt=new User("",$gid);
	  $tgid[$gid]=$gt->fid;
	  $tglogin[$gid]=$this->getTitle($gt->fid);
	}
	$this->SetValue("GRP_PGROUP", $tglogin);
	$this->SetValue("GRP_IDPGROUP", $tgid);
      } else {
	$this->SetValue("GRP_PGROUP"," ");
	$this->SetValue("GRP_IDPGROUP"," ");
      }
      $err=$this->modify();
  
    } else     {
      $err= sprintf(_("group %d does not exist"),$wid);
    }
  }
  return $err;
}

/**
 * refresh members of the group from USER database
 */
function refreshMembers() { 

  $wid=$this->getValue("us_whatid");
  if ($wid > 0) { 
    $u = $this->getWUser(true);

    $tu=$u->GetUsersGroupList($wid);
    if (count($tu) > 0) {

      foreach ($tu as $uid=>$tvu) {
	if ($tvu["isgroup"]=="Y") {
	  $tgid[$uid]=$tvu["fid"];
	  //	  $tglogin[$uid]=$this->getTitle($tvu["fid"]);
	  $tglogin[$tvu["fid"]]=$tvu["lastname"];
	} else {
	  $tuid[$uid]=$tvu["fid"];
	  //	  $tulogin[$uid]=$this->getTitle($tvu["fid"]);
	  $tulogin[$tvu["fid"]]=trim($tvu["lastname"]." ".$tvu["firstname"]);
	}
      }
    }
    if (is_array($tulogin)) {
      uasort($tulogin, "strcasecmp");
      $this->SetValue("GRP_USER", $tulogin);
      $this->SetValue("GRP_IDUSER", array_keys($tulogin));
    } else {
      $this->DeleteValue("GRP_USER");
      $this->DeleteValue("GRP_IDUSER");
    }
    if (is_array($tglogin)) {
      uasort($tglogin, "strcasecmp");
      $this->SetValue("GRP_GROUP", $tglogin);
      $this->SetValue("GRP_IDGROUP",array_keys($tglogin) );
    } else {
      $this->DeleteValue("GRP_GROUP");
      $this->DeleteValue("GRP_IDGROUP");
    }
    $err=$this->modify();
  }
}


function fusers_igroup($target="finfo",$ulink=true,$abstract="Y") {
  global $action;

  $user=$this->getTvalue("grp_ruser");
  $toomany=(count($user) > 100) ;

  if ($toomany) {
  $oa=$this->getAttribute("grp_users");
  $oa->mvisibility='H';
  $oa=$this->getAttribute("grp_rusers");
  $oa->mvisibility='H';
  $oa=$this->getAttribute("grp_user");
  $oa->mvisibility='H';
  $oa=$this->getAttribute("grp_ruser");
  $oa->mvisibility='H';
  }
  //setHttpVar("specialmenu","menuab");

  $this->viewdefaultcard($target,$ulink,$abstract);
  $action->parent->AddCssRef("USERCARD:faddbook.css",true);
  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/USERCARD/Layout/faddbook.js");

  
  // list of attributes displayed directly in layout
  $ta=array("grp_groups","grp_rusers","grp_users","grp_parent","us_login","us_whatid","grp_mail","us_iddomain","us_domain","grp_name","grp_role","grp_type");
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
  $this->lay->set("Toomany",$toomany);
  $ltabs=array();
  foreach ($tabs as $k=>$v) {
    $ltabs[$k]=array("tabtitle"=>$k,
		     "aids"=>"['".implode("','",$v)."']");
  }
  $this->lay->setBlockData("TABS",$ltabs);
  $this->lay->set("ICON",$this->getIcon());
  $this->lay->set("nmembers",count($this->getTValue("GRP_IDUSER")));
  $this->lay->set("HasDOMAIN",($this->getValue("US_IDDOMAIN")>9));
  $this->lay->set("CanEdit",($this->control("edit")==""));
}
function fusers_eigroup() {
  global $action;
  $this->editattr();
  $action->parent->AddCssRef("USERCARD:faddbook.css",true);
  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/USERCARD/Layout/faddbook.js");
  $firsttab=getHttpVars("tab"); // first tab displayed

  
  // list of attributes displayed directly in layout
  $ta=array("us_login","us_whatid","grp_mail","us_iddomain","us_domain","grp_name","grp_role","grp_type","grp_hasmail");

  $q=new QueryDb("","Domain");
  $q->AddQuery("iddomain>9");
  $this->lay->set("hasdomain",($q->count()>0));

  $this->lay->set("firsttab",$firsttab);
  $la=$this->getNormalAttributes();
  $to=array();
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
  $ltabs=array();
  foreach ($tabs as $k=>$v) {
    $ltabs[$k]=array("tabtitle"=>$k,
		     "aids"=>"['".implode("','",$v)."']");
  }
  $this->lay->setBlockData("TABS",$ltabs);
  $this->viewprop();
  $this->lay->set("HasOTHERS",(count($to)>0));
  $this->lay->set("ICON",$this->getIcon());
}
?>
