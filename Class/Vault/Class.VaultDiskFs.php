<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * @author Anakeen
 * @package FDL
 */
// ---------------------------------------------------------------
include_once ("Class.QueryDb.php");
include_once ("Class.DbObj.php");
include_once ("VAULT/Class.VaultDiskDir.php");

class VaultDiskFs extends DbObj
{
    var $fields = array(
        "id_fs",
        "fsname",
        "max_size",
        "r_path"
    );
    var $id_fields = array(
        "id_fs"
    );
    var $dbtable_tmpl = "vaultdiskfs%s";
    var $order_by = "";
    var $seq_tmpl = "seq_id_vaultdiskfs%s";
    var $sqlcreate_tmpl = <<<EOF
           create table vaultdiskfs%s  (
                                id_fs int not null,
                                fsname text,
                                primary key (id_fs),
                                max_size int8,
                                r_path text
                                );
           create sequence seq_id_vaultdiskfs%s start 10;

EOF;
    
    
    /**
     * @var int file system id (10 is the first)
     */
    public $id_fs;
    public $fsname;
    public $max_size;
    /**
     * @var string path to vault root
     */
    public $r_path;
    public $specific;
    protected $seq;
    /**
     * @var VaultDiskDirStorage
     */
    protected $sd;
    private $htaccess = <<<EOF
Order Allow,Deny
Deny from all

EOF;
    // --------------------------------------------------------------------
    function __construct($dbaccess, $id_fs = '')
    {
        // --------------------------------------------------------------------
        $this->dbtable = sprintf($this->dbtable_tmpl, $this->specific);
        $this->sqlcreate = sprintf($this->sqlcreate_tmpl, $this->specific, $this->specific);
        $this->seq = sprintf($this->seq_tmpl, $this->specific);
        parent::__construct($dbaccess, $id_fs);
    }
    
    function createArch($maxsize, $path, $fsname = "-")
    {
        if (!is_dir($path)) {
            return sprintf(_("%s directory not found") , $path);
        }
        if (!is_writable($path)) {
            return sprintf(_("%s directory not writable") , $path);
        }
        if (($err = $this->setHtaccess($path)) != "") {
            return $err;
        }
        $this->fsname = $fsname;
        $this->max_size = $maxsize;
        $this->r_path = $path;
        return $this->Add();
    }
    /**
     * verify if fs is availlable (file system is mounted)
     * @return bool
     */
    function isAvailable()
    {
        if ($this->isAffected()) {
            if ($this->r_path) {
                if (is_dir($this->r_path)) return true;
            }
        }
        return false;
    }
    // --------------------------------------------------------------------
    function PreInsert()
    {
        // --------------------------------------------------------------------
        if ($this->Exists($this->r_path)) return (_("File System already exists"));
        $this->exec_query(sprintf("select nextval ('%s')", pg_escape_string($this->seq)));
        $arr = $this->fetch_array(0);
        $this->id_fs = $arr["nextval"];
        return '';
    }
    // --------------------------------------------------------------------
    function Exists($path)
    {
        // --------------------------------------------------------------------
        $query = new QueryDb($this->dbaccess, $this->dbtable);
        $query->basic_elem->sup_where = array(
            "r_path=E'" . pg_escape_string($path) . "'"
        );
        $query->Query(0, 0, "TABLE");
        return ($query->nb > 0);
    }
    // --------------------------------------------------------------------
    function SetFreeFs($f_size, &$id_fs, &$id_dir, &$f_path, $fsname)
    {
        // --------------------------------------------------------------------
        $id_fs = $id_dir = - 1;
        $f_path = "";
        
        $freeFs = $this->findFreeFS($f_size, $fsname);
        if ($freeFs) {
            $ifs = $this->getValues();
            
            $this->sd = new VaultDiskDir($this->dbaccess, '', $this->specific);
            $err = $this->sd->SetFreeDir($ifs);
            
            if (!$err) {
                $id_fs = $this->id_fs;
                $id_dir = $this->sd->id_dir;
                $f_path = $this->r_path . "/" . $this->sd->l_path;
                if (!is_dir($f_path)) {
                    if (!mkdir($f_path, VaultFile::VAULT_DMODE, true)) {
                        return (sprintf(_("Failed to create directory \"%s\" in vault") , $f_path));
                    }
                }
            } else {
                return ($err);
            }
            unset($t);
        } else {
            return (_("no empty vault file system found"));
        }
        return "";
    }
    
    public function closeCurrentDir()
    {
        return $this->sd->closeDir();
    }
    
    public function findFreeFS($size, $specificFs = "")
    {
        $sql = <<<SQL

select vaultdiskfsstorage.*, y.size 
from vaultdiskfsstorage, ( 
    select sum(c) as size, id_fs from (
                (select sum(vaultdiskstorage.size) as c, vaultdiskdirstorage.id_fs 
                from vaultdiskstorage, vaultdiskdirstorage 
                where vaultdiskdirstorage.id_dir = vaultdiskstorage.id_dir and not isfull 
                group by vaultdiskdirstorage.id_fs )
            union
                (select sum(size) as c, vaultdiskdirstorage.id_fs 
                from vaultdiskdirstorage 
                where isfull 
                group by vaultdiskdirstorage.id_fs)) as z group by id_fs) as y 
 where y.id_fs =vaultdiskfsstorage.id_fs :SQLFSNAME:
 and vaultdiskfsstorage.max_size > (y.size + %d)
 order by vaultdiskfsstorage.id_fs
 ;
SQL;
        
        if ($specificFs) {
            $sqlName = sprintf("and vaultdiskfsstorage.fsname='%s'", pg_escape_string($specificFs));
        } else {
            $sqlName = '';
        }
        
        $sql = sprintf(str_replace(":SQLFSNAME:", $sqlName, $sql) , $size);
        $this->exec_query($sql);
        if ($this->numrows() > 0) {
            $result = $this->fetch_array(0);
            if ($result) {
                $this->affect($result);
                
                return $result["id_fs"];
            }
        } else {
            // May be a new vault
            $sql = "select * from vaultdiskfsstorage where id_fs not in (select id_fs from vaultdiskdirstorage ) order by id_fs;";
            $this->exec_query($sql);
            if ($this->numrows() > 0) {
                $result = $this->fetch_array(0);
                if ($result) {
                    $this->affect($result);
                    
                    return $result["id_fs"];
                }
            }
        }
        return false;
    }
    
    public function getSize()
    {
        $sql = <<<SQL

select vaultdiskfsstorage.*, y.size 
from vaultdiskfsstorage, ( 
    select sum(c) as size, id_fs from (
                (select sum(vaultdiskstorage.size) as c, vaultdiskdirstorage.id_fs 
                from vaultdiskstorage, vaultdiskdirstorage 
                where vaultdiskdirstorage.id_dir = vaultdiskstorage.id_dir and not isfull 
                group by vaultdiskdirstorage.id_fs )
            union
                (select sum(size) as c, vaultdiskdirstorage.id_fs 
                from vaultdiskdirstorage 
                where isfull 
                group by vaultdiskdirstorage.id_fs)) as z group by id_fs) as y 
 where y.id_fs =vaultdiskfsstorage.id_fs 
 and vaultdiskfsstorage.id_fs = %d
 ;
SQL;
        
        $sql = sprintf($sql, $this->id_fs);
        $this->exec_query($sql);
        if ($this->numrows() > 0) {
            $result = $this->fetch_array(0);
            if ($result) {
                $this->affect($result);
                
                return intval($result["size"]);
            }
        }
        return -1;
    }
    
    public function recomputeDirectorySize()
    {
        $sql = "update vaultdiskdirstorage set size=(select sum(size) from vaultdiskstorage where id_dir=vaultdiskdirstorage.id_dir) where isfull;";
        $this->exec_query($sql);
        $sql = "update vaultdiskdirstorage set size=0 where isfull and size is null;";
        $this->exec_query($sql);
    }
    // --------------------------------------------------------------------
    function Show($id_fs, $id_dir, &$f_path)
    {
        // --------------------------------------------------------------------
        $query = new QueryDb($this->dbaccess, $this->dbtable);
        $query->basic_elem->sup_where = array(
            sprintf("id_fs=%d", $id_fs)
        );
        $t = $query->Query(0, 0, "TABLE");
        if ($query->nb > 0) {
            $sd = new VaultDiskDir($this->dbaccess, $id_dir, $this->specific);
            if ($sd->IsAffected()) {
                $f_path = $t[0]["r_path"] . "/" . $sd->l_path;
            } else {
                return (_("no vault directory found"));
            }
        } else {
            return (_("no vault file system found"));
        }
        return '';
    }
    // --------------------------------------------------------------------
    function DelEntry($id_fs, $id_dir, $fs)
    {
        // --------------------------------------------------------------------
        DbObj::Select($id_fs);
        if ($this->IsAffected()) {
            $sd = new VaultDiskDir($this->dbaccess, $id_dir, $this->specific);
            if ($sd->IsAffected()) {
                $sd->DelEntry();
            } else {
                return (_("no vault directory found"));
            }
        } else {
            return (_("no vault file system found"));
        }
        return '';
    }
    // --------------------------------------------------------------------
    private function setHtaccess($path)
    {
        $htaccess = sprintf("%s/.htaccess", $path);
        if (file_exists($htaccess)) {
            return "";
        }
        if (file_put_contents($htaccess, $this->htaccess) === false) {
            return sprintf(_("Error writing content to '%s'.") , $htaccess);
        }
        return "";
    }
}
