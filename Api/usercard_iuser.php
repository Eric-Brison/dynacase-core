<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: usercard_iuser.php,v 1.18 2007/03/21 15:32:57 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



// refreah for a classname
// use this only if you have changed title attributes

include_once("FDL/Class.Doc.php");
include_once("FDL/Lib.Dir.php");




$appl = new Application();
$appl->Set("FDL",	   $core);


$dbaccess=$appl->GetParam("FREEDOM_DB");
if ($dbaccess == "") {
  print "Database not found : param FREEDOM_DB";
  return;
}


$whatid = GetHttpVars("whatid",""); // document
$fbar = GetHttpVars("bar"); // for progress bar
$onlygroup = (GetHttpVars("onlygroup")!=""); // for progress bar

  
$query = new QueryDb("","User");

if ($whatid>0) {
  $query->AddQuery("id=$whatid");
} else {
  $query->order_by="isgroup,id";
}

if ($onlygroup) $query->AddQuery("isgroup='Y'");
      
    
$table1 = $query->Query(0,0,"TABLE");

if ($query->nb > 0)	{

  printf("\n%d user to update\n", count($table1));
  $card=count($table1);
  $doc = createDoc($dbaccess,$famId,false);
  $reste=$card;
  foreach($table1 as $k=>$v) 	    {
    $fid=0;
	
    $reste--;
    // search already created card
    $title = strtolower($v["lastname"]. " ". $v["firstname"]);
    $mail = getMailAddr($v["id"]);
    // first in IUSER
    unset($tdoc);
    $foundoc=false;
    $fid=$v["fid"];
    if ($fid > 0){ 
      $udoc=new_doc($dbaccess,$fid);
      $foundoc=$udoc->isAlive();
    } 
    
    
    if (!$foundoc) {
      // search same doc with us_what id
      if ($v["isgroup"] == "Y") {
	$filter = array("us_whatid = '".$v["id"]."'");
	$tdoc = getChildDoc($dbaccess, 0,0,"ALL", $filter,1,"TABLE","IGROUP");
      } else {
	$filter = array("us_whatid = '".$v["id"]."'");
	$tdoc = getChildDoc($dbaccess, 0,0,"ALL", $filter,1,"TABLE","IUSER");
      }
    
      if (count($tdoc) > 0) {
	$fid=$tdoc["id"];
	$udoc=new_doc($dbaccess,$fid);
	$foundoc=$udoc->isAlive();
      }
    }
    if ($foundoc) {
      
      if (method_exists($udoc,"RefreshGroup")) $udoc->RefreshGroup();
      else if (method_exists($udoc,"RefreshDocUser")) $udoc->RefreshDocUser();
      //if (method_exists($tdoc[0],"SetGroupMail")) $tdoc[0]->SetGroupMail();
      //$tdoc[0]->refresh();
      //$tdoc[0]->postModify();
      $err=$udoc->modify();
      if ($err != "") print "$err\n";
      else {
	print "$reste)";printf( _("%s updated\n"),$udoc->title);
	$fid=$udoc->id;
      }

      
    } else {
      // search in all usercard same title
      if ($mail != "") $filter = array("us_mail = '$mail'");
      else $filter = array("lower(title) = '".pg_escape_string($title)."'");
      $tdoc = getChildDoc($dbaccess, 0,0,"ALL", $filter,1,"LIST",
			  getFamIdFromName($dbaccess,"USER"));
      if (count($tdoc) > 0) {
	if (count($tdoc) > 1) {
	  printf( _("find %s more than one, created aborded\n"),$title);
	} else {
	  if ($tdoc[0]->fromid == getFamIdFromName($dbaccess,"USER")) {
	    $tdoc[0]->convert(getFamIdFromName($dbaccess,"IUSER"),
			      array("US_WHATID"=>$v["id"]));
	    print "$reste)";printf( _("%s migrated\n"),$title);
	    $fid=$tdoc[0]->id;
	  }	else {
	    $udoc= new_Doc($dbaccess,$tdoc[0]->id);
	    $udoc->setValue("US_WHATID",$v["id"]);
	    $udoc->refresh();
	    $udoc->RefreshDocUser();
	    $udoc->modify();
	    $fid=$udoc->id;
	    print "$reste)";printf( _("%s updated\n"),$title);
	    unset($udoc);
	  }
	}
	
      } else {
	// create new card
	if ($v["isgroup"]=="Y") {
	  $iuser = createDoc($dbaccess,getFamIdFromName($dbaccess,"IGROUP"));
	  $iuser->setValue("US_WHATID",$v["id"]);
	  $iuser->Add();
	  $iuser->refresh();
	  $iuser->postmodify();
	  $iuser->modify();
	  print "$reste)";printf( _("%s igroup created\n"),$title);
	} else {
	  $iuser = createDoc($dbaccess,getFamIdFromName($dbaccess,"IUSER"));
	  $iuser->setValue("US_WHATID",$v["id"]);
	  $err=$iuser->Add();
	  if ($err == "") {
	    //$iuser->refresh();
	    //$iuser->RefreshDocUser();
	    //$iuser->modify();
	    print "$reste)";printf( _("%s iuser created\n"),$title);
	  } else {
	    print "$reste)$err";printf( _("%s iuser aborded\n"),$title);
	  }
	}
	$fid=$iuser->id;
	unset($iuser);
      }

      
    }
   
    if (($v["fid"] == 0) && ($fid > 0)) {
      $u= new User("",$v["id"]);
      $u->fid=$fid;
      $u->modify();
	unset($u);
    }

    wbar($reste,$card,$title);    
  }	  

  $doc->exec_query("update doc127 set name='GADMIN' where us_whatid=4");
  $doc->exec_query("update doc127 set name='GDEFAULT' where us_whatid=2");
}      
    

?>
