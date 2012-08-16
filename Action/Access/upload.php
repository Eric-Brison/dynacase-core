<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: upload.php,v 1.10 2004/03/22 15:21:40 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage ACCESS
 */
/**
 */
// ---------------------------------------------------------------
// $Id: upload.php,v 1.10 2004/03/22 15:21:40 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Access/upload.php,v $
// ---------------------------------------------------------------
include_once ("Class.QueryDb.php");
include_once ("Class.Application.php");
include_once ("Class.User.php");
include_once ("Class.Acl.php");
include_once ("Class.Permission.php");
include_once ("Lib.Http.php");
// -----------------------------------
function upload(&$action)
{
    // -----------------------------------
    global $_FILES;
    $action->log->debug("UPLOAD");
    // select the first user if not set
    // What user are we working on ? ask session.
    $filename = ($_FILES["upfile"]["tmp_name"]);
    
    if (!file_exists($filename)) {
        $action->ExitError("File not found : $filename : " . $_FILES["upfile"]["name"]);
    }
    $content = file($filename);
    
    $tnewacl = array();
    while (list($k, $v) = each($content)) {
        switch (substr($v, 0, 1)) {
            case "U":
                changeuser($action, substr($v, 2));
                break;

            case "A":
                changeacl($action, substr($v, 2));
                break;
        }
    }
    
    redirect($action, "ACCESS", "USER_ACCESS");
}

function changeuser(&$action, $line, $verbose = false)
{
    
    $col = explode("|", $line);
    // eric.brison@local|hb7Qj/yFqxCGs|eric|brison|N|all@local;
    $uname = $col[0];
    
    $use = new Account($action->dbaccess);
    
    $use->SetLoginName($uname);
    $use->password = $col[1];
    $use->firstname = $col[2];
    $use->lastname = $col[3];
    $use->isgroup = $col[4];
    if ($use->IsAffected()) {
        $err = $use->Modify(true);
        if ($err != "") print $err;
        
        if ($verbose) printf(_("user %s %s has been modified\n") , $use->firstname, $use->lastname);
    } else {
        $use->login = $uname;
        $err = $use->Add(true);
        if ($err != "") print $err;
        if ($verbose) printf(_("user %s %s has been added\n") , $use->firstname, $use->lastname);
    }
    // add group
    $groups = explode(";", $col[5]);
    
    $group = new Group($action->dbaccess, $use->id);
    if ($group->isAffected()) $group->delete(true);
    
    while (list($kg, $gd) = each($groups)) {
        
        $gr = new Account($action->dbaccess);
        
        $gr->SetLoginName($gd);
        if ($gr->IsAffected()) {
            $group->iduser = $use->id;
            $group->idgroup = $gr->id;
            $group->add(true);
        }
    }
}
function changeacl(&$action, $line, $verbose = false)
{
    // INCIDENT|all@cir.fr|INCIDENT_READ;INCIDENT
    $col = explode("|", $line);
    if (!is_array($col)) return;
    if (count($col) != 3) return;
    if (substr($line, 0, 1) == "#") return; // comment line
    $app = new Application($action->dbaccess);
    $app->Set($col[0], $action->parent);
    $uname = $col[1];
    
    $use = new Account($action->dbaccess);
    
    $use->SetLoginname($uname);
    // update the permission in database
    // first remove then add
    $perm = new Permission($action->dbaccess, array(
        $use->id,
        $app->id
    ));
    if (!$perm->IsAffected()) {
        $perm->Affect(array(
            "id_user" => $use->id,
            "id_application" => $app->id
        ));
    }
    $taclname = explode(";", $col[2]);
    
    if (count($taclname) > 0) {
        $perm->Delete();
        $taclid = array();
        foreach ($taclname as $aclname) {
            $unp = false; // is negative privilege ?
            $aclname = chop($aclname);
            if (substr($aclname, 0, 1) == "-") {
                $aclname = substr($aclname, 1);
                $unp = true;
            };
            // search acl id
            $acl = new Acl($action->dbaccess);
            $acl->Set($aclname, $app->id);
            
            if ($acl->id == "") continue;
            
            if ($unp) {
                $taclid[] = - $acl->id;
            } else {
                $taclid[] = $acl->id;
            }
        }
        
        foreach ($taclid as $aclid) {
            $perm->id_acl = $aclid;
            
            if ($aclid != 0) {
                //print "ADD "."-".$perm->id_application."-". $perm->id_user."-". $perm->id_acl."<BR>";
                $err = $perm->Add();
                if ($err != "") print $err;
                if ($verbose) printf(_("add acl %s for %s %s\n") , $perm->id_acl, $use->firstname, $use->lastname);
            }
        }
    }
}
?>
