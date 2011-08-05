<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * Mail Domain
 *
 * @author Anakeen 2000
 * @version $Id: Class.Domain.php,v 1.6 2005/10/05 16:28:42 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package WHAT
 * @subpackage CORE
 */
/**
 */

include_once ('Class.DbObj.php');
include_once ('Class.QueryDb.php');
include_once ('Class.Pop.php');

Class Domain extends DbObj
{
    
    var $Class = '$Id: Class.Domain.php,v 1.6 2005/10/05 16:28:42 eric Exp $';
    
    var $fields = array(
        "iddomain",
        "name",
        "status",
        "root",
        "gentime",
        "quotast",
        "quota",
        "quotaalert",
        "quotatext",
        "nobodymsg",
        "nobodyalert",
        "autoreplaymsg"
    );
    
    var $id_fields = array(
        "iddomain"
    );
    
    var $dbtable = "domain";
    
    var $sqlcreate = "
create table domain(
     iddomain   int not null,
     primary key (iddomain),
     name	varchar(100),
     status     int,
     root	varchar(255),
     gentime	int,
     quotast   int,
     quota      int,
     quotaalert varchar(255),
     quotatext  varchar(255),
     nobodymsg  varchar(255),
     nobodyalert varchar(255),
     autoreplaymsg varchar(255)
     );
create index domain_idx on domain(iddomain);
create sequence seq_iddomain start 10; 
";
    
    function PreInsert()
    {
        if ($this->exists($this->name)) return "Domain already exists";
        if ($this->iddomain == "") {
            $res = $this->exec_query("select nextval ('seq_iddomain')");
            $arr = $this->fetch_array(0);
            $this->iddomain = $arr["nextval"];
        }
        $this->name = strtolower($this->name);
        $this->status = '1';
        $this->log->info("Adding domain {$this->name} / {$this->iddomain}");
    }
    
    function PreUpdate()
    {
        if ($this->status != 2) $this->log->info("Modifying domain {$this->name} / {$this->iddomain}");
        $this->name = strtolower($this->name);
    }
    
    function ListAll($local = 1)
    {
        $this->qcount = 0;
        $this->qlist = NULL;
        $query = new QueryDb($this->dbaccess, "Domain");
        $query->basic_elem->sup_where = array(
            "iddomain>{$local}",
            "status != 2"
        );
        $this->qlist = $query->Query();
        $this->qcount = $query->nb;
        return;
    }
    
    function MasterPopId()
    {
        $p = new Pop($this->dbaccess);
        $pm = $p->GetMaster($this->iddomain);
        return $pm->idpop;
    }
    
    function Remove()
    {
        $this->status = '2';
        $this->log->info("Deleting domain {$this->name} / {$this->iddomain}");
        $this->Modify();
    }
    
    function Set($name = NULL)
    {
        if ($name == NULL) return FALSE;
        $query = new QueryDb($this->dbaccess, "Domain");
        $query->basic_elem->sup_where[] = "name = '$name'";
        $list = $query->Query(0, 0, "TABLE");
        if ($query->nb > 0) {
            $this->Affect($list[0]);
            return TRUE;
        } else {
            $this->log->warning("No such domain {$query->string}");
            return FALSE;
        }
    }
    
    function PostInit()
    {
        $this->iddomain = "0";
        $this->name = "externe";
        $this->Add();
        
        $this->iddomain = 1;
        $this->name = "local";
        $this->Add();
    }
    
    function Created()
    {
        $this->status = '0';
        $this->Modify();
    }
    
    function SetGenTime($time)
    {
        $this->gentime = $time;
        $this->update();
    }
    
    function Exists($name)
    {
        $query = new QueryDb($this->dbaccess, "Domain");
        $query->basic_elem->sup_where = array(
            "name='$name'"
        );
        $list = $query->Query();
        return ($query->nb > 0);
    }
}
?>
