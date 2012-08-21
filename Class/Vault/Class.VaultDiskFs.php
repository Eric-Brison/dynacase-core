<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
// ---------------------------------------------------------------
// $Id: Class.VaultDiskFs.php,v 1.17 2008/11/21 09:57:23 jerome Exp $
// $Source: /home/cvsroot/anakeen/freedom/vault/Class/Class.VaultDiskFs.php,v $
// ---------------------------------------------------------------
//
//
//
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
        "subdir_cnt_bydir",
        "subdir_deep",
        "max_entries_by_dir",
        "r_path"
    );
    var $id_fields = array(
        "id_fs"
    );
    var $dbtable_tmpl = "vaultdiskfs%s";
    var $order_by = "";
    var $seq_tmpl = "seq_id_vaultdiskfs%s";
    var $sqlcreate_tmpl = "
           create table vaultdiskfs%s  ( id_fs     int not null,
                                 fsname text,
                                 primary key (id_fs),
                                 max_size   int8,
                                 free_size   int8,
                                 subdir_cnt_bydir   int,
                                 subdir_deep   int,
                                 max_entries_by_dir   int,
                                 r_path varchar(2048)
                               );
           create sequence seq_id_vaultdiskfs%s start 10;";
    /**
     * @var int file system id (10 is the first)
     */
    public $id_fs;
    public $fsname;
    public $max_size;
    public $free_size;
    public $subdir_cnt_bydir;
    public $subdir_deep;
    public $max_entries_by_dir;
    /**
     * @var string path to vault root
     */
    public $r_path;
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
        $err = '';
        if (!is_dir($path)) $err = sprintf(_("%s directory not found") , $path);
        elseif (!is_writable($path)) $err = sprintf(_("%s directory not writable") , $path);
        if ($err == "") {
            $this->fsname = $fsname;
            $this->max_size = $maxsize;
            $this->free_size = $maxsize;
            $this->subdir_cnt_bydir = VAULT_MAXDIRBYDIR;
            $this->subdir_deep = 1;
            $this->max_entries_by_dir = VAULT_MAXENTRIESBYDIR;
            $this->r_path = $path;
            $err = $this->Add();
        }
        return $err;
    }
    /**
     * verify if fs is availlable (file system is mounted)
     * @return book
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
        $res = $this->exec_query("select nextval ('" . $this->seq . "')");
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
            "r_path='" . $path . "'"
        );
        $t = $query->Query(0, 0, "TABLE");
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
        $err = $this->Modify();
    }
    // --------------------------------------------------------------------
    function DelEntry($id_fs, $id_dir, $fs)
    {
        // --------------------------------------------------------------------
        DbObj::Select($id_fs);
        if ($this->IsAffected()) {
            $this->free_size = $this->free_size + $fs;
            $this->Modify();
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
            $sd = new VaultDiskDir($this->dbacces, '', $this->specific);
            $s["fs$k"]["free_entries"] = $sd->FreeEntries($v["id_fs"]);
            unset($sd);
        }
        return '';
    }
}
?>
