<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * reset profiling use when restore context from archive
 * all document has the same profil
 *
 * @author Anakeen
 * @version $Id:  $
 *
 * @package FDL
 *
 *
 * @global login string Http var : login
 * @global password string Http var : password
 /**
 */

include_once ("FDL/Class.Doc.php");

$usage = new ApiUsage();

$usage->setDefinitionText("Reset profiling use when restore context from archive");
$user = $usage->addRequiredParameter("login", "login");
$password = $usage->addRequiredParameter("password", "password");

$usage->verify();
/**
 * @var Action $action
 */
global $action;
$dbaccess = $action->dbaccess;
$coreaccess = $action->dbaccess;

if ($dbaccess == "") {
    print "Database not found : action->dbaccess";
    exit;
}

$u = new Account($coreaccess);
$u->setLoginName($user);
$uid = $u->id;
$err = '';
if (!$uid) {
    $du = createDoc($dbaccess, "IUSER");
    if ($du) {
        $du->setValue("us_login", $user);
        $du->setValue("us_lname", $user);
        $du->setValue("us_fname", "");
        $du->setValue("us_passwd1", $password);
        $du->setValue("us_passwd2", $password);
        $err = $du->Add();
        if ($err == "") {
            $err = $du->postStore();
            if ($err == "") {
                $err = $du->modify();
                if ($err == "") {
                    printf(_("new user # %d") , $du->getRawValue("us_whatid")); // affichage de l'identifiant système
                    
                    /**
                     * @var Dir $g
                     */
                    $g = new_Doc($dbaccess, "GDEFAULT");
                    if ($g) {
                        $err = $g->insertDocument($du->initid);
                    }
                }
            }
        }
        if ($err) print "\nerreur:$err\n";
        $uid = $du->getRawValue("us_whatid");
    }
}
if ($uid > 0) {
    $pname = strtoupper($user . "PROFIL");
    $pdoc = new_doc($dbaccess, $pname);
    
    if (!$pdoc->isAffected()) {
        $pdoc = createDoc($dbaccess, "PDIR");
        if ($pdoc) {
            $pdoc->setValue("ba_title", "profil de " . $user);
            $pdoc->setValue("prf_desc", "profil de " . $user);
            $pdoc->name = strtoupper($pname); // on donne un nom logique pour le retrouver après
            $err = $pdoc->Add();
            if ($err == "") {
                // ajout d'ACLs
                $pdoc->setControl(false); // activ profil
                printf(_("new profil %d") , $pdoc->id);
                
                $perm = new DocPerm($dbaccess);
                $perm->docid = $pdoc->id;
                $perm->userid = $uid;
                $perm->upacl = - 2 & (~(1 << 2)); // all privileges except read => read only
                // add all privileges to  user
                $err = $perm->Add();
                if ($err == "") {
                    // reset all profil big security
                    $err = simpleQuery($dbaccess, sprintf("update doc set profid=%d,dprofid=0", $pdoc->id) , $res);
                    $err.= simpleQuery($dbaccess, sprintf("INSERT INTO permission (id_user, id_application, id_acl)  SELECT users.id as uid,  id_application as appid,acl.id as aclid  from acl, users where users.id=%d", $uid));
                }
            }
        }
        if ($err) print "\nerreur:$err\n";
    }
}
