<?php

/**
 * reset profiling use when restore context from archive
 * all document has the same profil
 *
 * @author Anakeen 2010
 * @version $Id:  $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * 
 *
 * @global login Http var : login
 * @global password Http var : password
 /**
 */


include_once("FDL/Class.Doc.php");

$usage="usage  --login=<user login> --password=<user password>";

$dbaccess=$action->GetParam("FREEDOM_DB");
$coreaccess=$action->GetParam("CORE_DB");

if ($dbaccess == "") {
  print "Freedom Database not found : param FREEDOM_DB";
  exit;
}

$user = $action->getArgument("login"); // special docid
$password = $action->getArgument("password"); // number of childs

if (empty($user))   $action->exitError("login needed :\n $usage");  
if (empty($password))   $action->exitError("password needed :\n $usage");   

$u=new User($coreaccess);
$u->setLogin($user,0);
$uid=$u->id;
if (! $uid) {
$du=createDoc($dbaccess,"IUSER");
if ($du) {
	$du->setValue("us_login", $user);
	$du->setValue("us_lname", $user);
	$du->setValue("us_fname","");
	$du->setValue("us_passwd1",$password);
	$du->setValue("us_passwd2",$password);
	$du->setValue("us_iddomain","0");
	$err=$du->Add();
	if ($err == "") {
		$err=$du->postModify();
		if ($err == "") {
			$err=$du->modify();
			if ($err == "") {
				printf(_("new user # %d"),$du->getValue("us_whatid")); // affichage de l'identifiant système

				$g=new_Doc($dbaccess,"GDEFAULT");
				if($g)  {
					$err=$g->addFile($du->initid);
					
				}
			}
		}
	}
	if ($err) print "\nerreur:$err\n";
	$uid=$du->getValue("us_whatid");
}
}
if ($uid > 0) {
    $pname=strtoupper($user."PROFIL");
    $pdoc=new_doc($dbaccess,$pname);
    
    if (! $pdoc->isAffected()) {
        $pdoc=createDoc($dbaccess,"PDIR");
        if ($pdoc) {
            $pdoc->setValue("ba_title","profil de ".$user);
            $pdoc->setValue("prf_desc","profil de ".$user );
            $pdoc->name=strtoupper($pname); // on donne un nom logique pour le retrouver après
            $err=$pdoc->Add();
            if ($err == "") {
                // ajout d'ACLs
                $pdoc->setControl(false); // activ profil
                printf(_("new profil %d"),$pdoc->id);
                
                $perm = new DocPerm($dbaccess);
                $perm->docid=$pdoc->id;
                $perm->userid=$uid;
                $perm->upacl= -2 & (~(1 << 2)); // all privileges except read => read only
                $perm->unacl=0;
                $perm->cacl=0;
                 
                // add all privileges to  user
                $err=$perm->Add();
                if ($err=="") {
                    // reset all profil big security
                    $err=simpleQuery($dbaccess,sprintf("update doc set profid=%d,dprofid=0",$pdoc->id),$res);
                    $err.=simpleQuery($dbaccess,sprintf("INSERT INTO permission (id_user, id_application, id_acl)  SELECT users.id as uid,  id_application as appid,acl.id as aclid  from acl, users where users.id=%d",$uid));
                }
            }
        }
        if ($err) print "\nerreur:$err\n";
    }
}


?>
