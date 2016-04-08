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
        "free_size",
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
                                free_size int8,
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
    public $free_size;
    /**
     * @var string path to vault root
     */
    public $r_path;
    public $specific;
    protected $seq;
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
        $this->free_size = $maxsize;
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
        $query = new QueryDb($this->dbaccess, $this->dbtable);
        $qs = array();
        $qs[0] = "free_size>" . $f_size;
        if ($fsname != "") {
            $qs[1] = "fsname='" . pg_escape_string($fsname) . "'";
        }
        $query->basic_elem->sup_where = $qs;
        $t = $query->Query(0, 1, "TABLE");
        if ($query->nb > 0) {
            $ifs = 0;
            $msg = '';
            $sd = null;
            $dirfound = FALSE;
            while (!$dirfound && ($ifs < $query->nb)) {
                $sd = new VaultDiskDir($this->dbaccess, '', $this->specific);
                $msg = $sd->SetFreeDir($t[$ifs]);
                if ($msg == '') $dirfound = TRUE;
                else $ifs++;
            }
            if ($dirfound) {
                $this->Select($t[0]["id_fs"]);
                $id_fs = $this->id_fs;
                $id_dir = $sd->id_dir;
                $f_path = $this->r_path . "/" . $sd->l_path;
                if (!is_dir($f_path)) {
                    if (!mkdir($f_path, VAULT_DMODE, true)) {
                        return (sprintf(_("Failed to create directory \"%s\" in vault") , $f_path));
                    }
                }
            } else {
                return ($msg);
            }
            unset($t);
        } else {
            return (_("no empty vault file system found"));
        }
        return "";
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
    function AddEntry($fs)
    {
        // --------------------------------------------------------------------
        $this->free_size = $this->free_size - $fs;
        $this->modify();
    }
    // --------------------------------------------------------------------
    function DelEntry($id_fs, $id_dir, $fs)
    {
        // --------------------------------------------------------------------
        DbObj::Select($id_fs);
        if ($this->IsAffected()) {
            $this->free_size = $this->free_size + $fs;
            $this->modify();
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
    function Stats(&$s)
    {
        // --------------------------------------------------------------------
        $query = new QueryDb($this->dbaccess, $this->dbtable);
        $t = $query->Query(0, 0, "TABLE");
        while ($query->nb > 0 && (list($k, $v) = each($t))) {
            $s["fs$k"]["root_dir"] = $v["r_path"];
            $s["fs$k"]["allowed_size"] = $v["max_size"];
            $s["fs$k"]["free_size"] = $v["free_size"];
            $sd = new VaultDiskDir($this->dbaccess, '', $this->specific);
            $s["fs$k"]["free_entries"] = $sd->FreeEntries($v["id_fs"]);
            unset($sd);
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
