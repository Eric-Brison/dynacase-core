<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: Class.MailAccount.php,v 1.6 2006/01/27 07:49:38 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage CORE
 */
/**
 */

include_once ('Class.DbObj.php');
include_once ('Class.Domain.php');
include_once ('Class.MailAlias.php');

class MailAccount extends DbObj
{
    var $Class = '$Id: Class.MailAccount.php,v 1.6 2006/01/27 07:49:38 eric Exp $';
    
    var $fields = array(
        "iddomain",
        "iduser",
        "login",
        "pop",
        "vacst",
        "vacmsg",
        "fwdst",
        "fwdadd",
        "type",
        "keepfwd",
        "uptime",
        "remove",
        "quota",
        "filterspam"
    );
    
    var $id_fields = array(
        "iduser"
    );
    
    var $dbtable = "mailaccount";
    
    var $sqlcreate = "
create table mailaccount(
     iddomain   int not null,
     iduser	int not null, 
     primary key (iduser),
     login	varchar(100),
     pop	varchar(100),
     vacst	int,
     vacmsg	text,
     fwdst	int,
     fwdadd	varchar(300),
     type	int,
     keepfwd    int,
     uptime     int,
     remove     int,
     quota      int,
     filterspam int );
create index mailaccount_idx on mailaccount(iduser);
";
    
    function setdef(&$v, $d)
    {
        if (!isset($v) || $v == "") $v = $d;
    }
    
    function PreInsert()
    {
        if (!isset($this->pop) || $this->pop == "" || $this->pop == 0) {
            $this->log->Debug("PreInsert for dom={$this->iddomain} iduser={$this->iduser}");
            $dom = new Domain($this->dbaccess, $this->iddomain);
            $this->pop = $dom->MasterPopId($this->dbaccess);
        }
        $this->setdef($this->quota, '0');
        $this->setdef($this->vacst, '0');
        $this->setdef($this->vacmsg, "");
        $this->setdef($this->fwdst, '0');
        $this->setdef($this->fwdadd, "");
        $this->setdef($this->keepfwd, '1');
        $this->setdef($this->filterspam, '0');
        $this->uptime = time();
        $this->type = '0';
        $this->remove = '0';
    }
    
    function PreUpdate()
    {
        $this->uptime = time();
    }
    
    function Remove()
    {
        $q = new QueryDb($this->dbaccess, "MailAlias");
        $q->basic_elem->sup_where = array(
            "iddomain={$this->iddomain}",
            "iduser={$this->iduser}"
        );
        $l = $q->Query();
        if ($q->nb > 0) {
            while (list($k, $v) = each($l)) {
                $a = new MailAlias();
                $a->Set($v->iddomain, $v->iduser);
                $a->Delete();
            }
        }
        $this->uptime = time();
        $this->remove = '1';
        $this->Modify();
    }
    
    function PostInsert()
    {
        //$adm = new MailAdmin($this->db, $this->iddomain);
        //$adm->Gen();
        
    }
    
    function PostUpdate()
    {
        $this->PostInsert();
    }
    
    function ListAccount($dom = 0, $admacc = 'N')
    {
        $this->qcount = 0;
        $this->qlist = 0;
        if ($dom != 0) {
            $q = new QueryDb($this->dbaccess, "MailAccount");
            if ($admacc != 'Y') $q->basic_elem->sup_where = array(
                "iddomain={$dom}",
                "type='{$admacc}'",
                "remove=0"
            );
            else $q->basic_elem->sup_where = array(
                "iddomain={$dom}",
                "remove=0"
            );
            $this->qlist = $q->Query();
            $this->qcount = $q->nb;
        }
        return;
    }
    
    function ListAlias()
    {
        $q = new QueryDb($this->dbaccess, "MailAlias");
        $q->basic_elem->sup_where = array(
            "iddomain={$this->iddomain}",
            "iduser={$this->iduser}",
            "type=0",
            "remove=0"
        );
        $a = $q->Query();
        return $a;
    }
    
    function CheckUptime($domain, $time)
    {
        $q = new QueryDb($this->dbaccess, "MailAccount");
        $q->basic_elem->sup_where = array(
            "iddomain={$domain->iddomain}",
            "uptime>$time"
        );
        $l = $q->Query();
        return ($q->nb > 0);
    }
} // End Class

?>
