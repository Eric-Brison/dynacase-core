<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Access Control for application
 *
 * @author Anakeen
 * @version $Id: Class.Acl.php,v 1.8 2005/10/27 14:26:05 eric Exp $
 * @package FDL
 * @subpackage CORE
 */
/**
 */

include_once ('Class.DbObj.php');
include_once ('Class.QueryDb.php');
include_once ('Class.Application.php');
include_once ('Class.User.php');

class Acl extends DbObj
{
    var $fields = array(
        "id",
        "id_application",
        "name",
        "grant_level",
        "description",
        "group_default"
    );
    
    var $id_fields = array(
        "id"
    );
    public $id;
    public $id_application;
    public $name;
    public $grant_level;
    public $description;
    public $group_default;
    var $dbtable = "acl";
    
    var $sqlcreate = '
create table acl (id int not null,
                  id_application int not null,
                  name text not null,
                  grant_level int not null,
                  description text,
                  group_default char);
create index acl_idx1 on acl(id);
create index acl_idx2 on acl(id_application);
create index acl_idx3 on acl(name);
create sequence SEQ_ID_ACL;
                 ';
    
    function Set($name, $id_app)
    {
        $query = new QueryDb($this->dbaccess, "Acl");
        $query->basic_elem->sup_where = array(
            "name='$name'",
            "id_application=$id_app"
        );
        $query->Query(0, 0, "TABLE");
        
        if ($query->nb > 0) {
            $this->Affect($query->list[0]);
        } else {
            return false;
        }
        return true;
    }
    
    function Complete()
    {
    }
    
    function PreInsert()
    {
        if ($this->Exists($this->name, $this->id_application)) return "Acl {$this->name} already exists...";
        $msg_res = $this->exec_query("select nextval ('seq_id_acl')");
        $arr = $this->fetch_array(0);
        $this->id = $arr["nextval"];
        return '';
    }
    function PreUpdate()
    {
        if ($this->dbid == - 1) return FALSE;
        return '';
    }
    
    function Exists($name, $id_app)
    {
        $query = new QueryDb($this->dbaccess, "Acl");
        $query->basic_elem->sup_where = array(
            "name='$name'",
            "id_application=$id_app"
        );
        $query->Query(0, 0, "TABLE");
        return ($query->nb > 0);
    }
    
    function DelAppAcl($id)
    {
        $query = new QueryDb($this->dbaccess, "Acl");
        $query->basic_elem->sup_where = array(
            "id_application=$id"
        );
        $list = $query->Query();
        if ($query->nb > 0) {
            /**
             * @var Acl $v
             */
            foreach ($list as $v) {
                $v->Delete();
            }
        }
        // Remove Permission
        $permission = new Permission($this->dbaccess);
        $permission->DelAppPerm($id);
    }
    
    function Init($app, $app_acl, $update = FALSE)
    {
        if (sizeof($app_acl) == 0) {
            $this->log->debug("No acl available");
            return ("");
        }
        
        $default_grant_level_found = false; // indicate user default set explicitly
        if (isset($app_acl[0]["grant_level"])) $oldacl = true; // for old ACL description (for compatibility with old application)
        else $oldacl = false;
        // read init file
        $default_user_acl = array(); // default acl ids
        $default_acl = false; // to update default acl id
        $smalestgrant = null;
        foreach ($app_acl as $k => $tab) {
            $acl = new Acl($this->dbaccess);
            if ($acl->Exists($tab["name"], $app->id)) {
                $acl->Set($tab["name"], $app->id);
            }
            $acl->id_application = $app->id;
            $acl->name = $tab["name"];
            if (isset($tab["description"])) {
                $acl->description = $tab["description"];
            }
            if (isset($tab["grant_level"])) {
                $acl->grant_level = $tab["grant_level"];
            } else {
                $acl->grant_level = 1;
            }
            // initialise grant level default
            if ((isset($tab["group_default"])) && ($tab["group_default"] == "Y")) {
                if ($oldacl) {
                    $default_grant_level = $tab["grant_level"];
                    $default_grant_level_found = true;
                }
                $acl->group_default = "Y";
                $default_acl = true;
            } else {
                $acl->group_default = "N";
                
                if ($oldacl) {
                    if ((!$default_grant_level_found) && ((!isset($smalestgrant)) || ($tab["grant_level"] < $smalestgrant)) && (!((isset($tab["admin"]) && $tab["admin"])))) {
                        // default acl admin must be specified explicitly
                        $smalestgrant = $tab["grant_level"];
                    }
                }
            }
            
            if ($acl->Exists($acl->name, $acl->id_application)) {
                $this->log->info("Acl Modify : {$acl->name}, {$acl->description}");
                $acl->Modify();
            } else {
                $this->log->info("Acl Add : {$acl->name}, {$acl->description}");
                $acl->Add();
            }
            if (isset($tab["admin"]) && $tab["admin"]) {
                $permission = new Permission($this->dbaccess);
                $permission->id_user = 1;
                $permission->id_application = $app->id;
                $permission->id_acl = $acl->id;
                if ($permission->Exists($permission->id_user, $app->id, $permission->id_acl)) {
                    $this->log->info("Modify admin permission : {$acl->name}");
                    $permission->Modify();
                } else {
                    $this->log->info("Create admin permission : {$acl->name}");
                    $permission->Add();
                }
            }
            if ($default_acl) {
                $default_user_acl[] = $acl->id;
                $default_acl = false;
            }
        }
        // default privilige is the smallest if no definition (for old old application)
        if (count($default_user_acl) == 0) {
            if (isset($smalestgrant)) {
                $default_user_acl[] = $smalestgrant;
                $default_grant_level = $smalestgrant;
            }
        }
        
        if ($oldacl) {
            // ----------------------------------------------
            // for old acl form definition (with grant_level)
            // set default acl for grant level under the default
            if (isset($default_grant_level)) {
                $query = new QueryDb($this->dbaccess, "Acl");
                $query->AddQuery("id_application = " . $app->id);
                $query->AddQuery("grant_level < $default_grant_level");
                if ($qacl = $query->Query()) {
                    foreach ($qacl as $k2 => $acl) {
                        if (!in_array($acl->id, $default_user_acl)) {
                            $default_user_acl[] = $acl->id;
                        }
                    }
                }
            }
        }
        // create default permission
        reset($default_user_acl);
        foreach ($default_user_acl as $ka => $aclid) {
            // set the default user access
            $defaultacl = new Acl($this->dbaccess, $aclid);
            $defaultacl->group_default = "Y";
            $defaultacl->Modify();
            
            if (!$update) {
                // set default access to 'all' group only
                $permission = new Permission($this->dbaccess);
                $permission->id_user = 2;
                $permission->id_application = $app->id;
                $permission->id_acl = $aclid;
                if (!$permission->Exists($permission->id_user, $app->id, $permission->id_acl)) {
                    $permission->Add();
                }
            }
        }
        return '';
        // Remove unused Acl in case of update
        //   if ($update) {
        //     $query=new QueryDb($this->dbaccess,"Acl");
        //     $query->basic_elem->sup_where=array ("id_application = {$app->id}");
        //     $list=$query->Query();
        //     while (list($k,$v)=each($list)) {
        //       // Check if the ACL still exists
        //       $find=FALSE;
        //       reset($app_acl);
        //       while ( (list($k2,$v2) = each($app_acl)) && ($find==FALSE) ) {
        //         $find=( $v2["name"] == $v->name );
        //       }
        //       if (!$find) {
        //         // remove the ACL and all associated permissions
        //         $this->log->info("Removing the {$v->name} ACL");
        //         $query2 = new QueryDb($this->dbaccess,"Permission");
        //         $query2->basic_elem->sup_where=array("id_application= {$app->id}",
        //                                              "id_acl = {$v->id}");
        //         $list_perm = $query2->Query();
        //         if ($query2->nb>0) {
        //           while (list($k2,$p) = each ($list_perm)) {
        //             $p->Delete();
        //           }
        //         }
        //         $v->Delete();
        //       }
        //     }
        //   }
        
        
    }
    // get default ACL for an application
    function getDefaultAcls($idapp)
    {
        
        $aclids = array();
        $query = new QueryDb($this->dbaccess, "Acl");
        $query->AddQuery("id_application = $idapp");
        $query->AddQuery("group_default = 'Y'");
        if ($qacl = $query->Query()) {
            foreach ($qacl as $k2 => $acl) {
                $aclids[] = $acl->id;
            }
        }
        return $aclids;
    }
    
    function getAclApplication($idapp)
    {
        
        $query = new QueryDb($this->dbaccess, "Acl");
        $query->AddQuery("id_application = $idapp");
        if ($qacl = $query->Query()) return $qacl;
        return 0;
    }
}
?>
