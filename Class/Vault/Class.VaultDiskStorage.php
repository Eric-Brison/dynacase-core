<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Retrieve and store file in Vault for unix fs
 *
 * @author Anakeen
 * @version $Id: Class.VaultDiskStorage.php,v 1.8 2007/11/14 09:53:37 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 */

include_once ("VAULT/Class.VaultDiskFsStorage.php");
include_once ("VAULT/Class.VaultDiskFsCache.php");
include_once ("VAULT/Class.VaultDiskDirStorage.php");
include_once ("VAULT/Lib.VaultCommon.php");

class VaultDiskStorage extends DbObj
{
    var $fields = array(
        "id_file",
        "id_fs",
        "id_dir",
        "public_access",
        "size",
        "name",
        
        "mime_t", // file mime type text
        "mime_s", // file mime type system
        "cdate", // creation date
        "mdate", // modification date
        "adate", // access date
        "teng_state", // Transformation Engine state
        "teng_lname", // Transformation Engine logical name (VIEW, THUMBNAIL, ....)
        "teng_id_file", // Transformation Engine source file id
        "teng_comment", // Comment for transformation
        
    );
    var $id_fields = array(
        "id_file"
    );
    var $dbtable = "vaultdiskstorage";
    var $seq = "seq_id_vaultdiskstorage";
    var $sqlcreate = "create table vaultdiskstorage  ( 
                                     id_file       int not null, primary key (id_file),
                                     id_fs         int,
                                     id_dir        int,
                                     public_access bool,
                                     size int,
                                     name text,

                                     mime_t           text DEFAULT '',
                                     mime_s           text DEFAULT '',

                                     cdate            timestamp DEFAULT null,
                                     mdate            timestamp DEFAULT null,
                                     adate            timestamp DEFAULT null,
 
                                     teng_state       int DEFAULT 0,
                                     teng_lname       text DEFAULT '',
                                     teng_id_file        int DEFAULT -1,
                                     teng_comment        text DEFAULT ''

                               );
           create sequence seq_id_vaultdiskstorage start 10;
           CREATE INDEX vault_teng on vaultdiskstorage (teng_state);";
    public $id_file;
    public $id_fs;
    public $id_dir;
    public $name;
    public $size;
    public $public_access;
    public $mime_t;
    public $mime_s;
    public $cdate;
    public $mdate;
    public $adate;
    public $teng_state;
    public $teng_lname;
    public $teng_id_file;
    public $teng_comment;
    var $storage = 1;
    /**
     * @var VaultDiskFsStorage
     */
    public $fs;
    // --------------------------------------------------------------------
    function __construct($dbaccess = '', $id = '', $res = '', $dbid = 0)
    {
        DbObj::__construct($dbaccess, $id, $res, $dbid);
        $this->logger = new Log("", "vault", $this->name);
        $this->fs = new VaultDiskFsStorage($this->dbaccess);
    }
    /**
     * set fs object
     */
    function Complete()
    {
        if ($this->storage == 1) {
            if (!$this->fs) {
                $this->fs = new VaultDiskFsStorage($this->dbaccess);
            }
            $this->fs->Select($this->id_fs);
        } else {
            // not implemented
            $this->fs = new VaultDiskFsCache($this->dbaccess, $this->id_fs);
        }
    }
    // --------------------------------------------------------------------
    function PreInsert()
    {
        // --------------------------------------------------------------------
        $res = $this->exec_query("select nextval ('" . $this->seq . "')");
        $arr = $this->fetch_array(0);
        $this->id_file = $arr["nextval"];
        return '';
    }
    // --------------------------------------------------------------------
    function fStat(&$fc, &$fv)
    {
        // --------------------------------------------------------------------
        $query = new QueryDb($this->dbaccess, $this->dbtable);
        $t = $query->Query(0, 0, "TABLE");
        $fc = $query->nb;
        while ($fc > 0 && (list($k, $v) = each($t))) $fv+= $v["size"];
        unset($t);
        return '';
    }
    // --------------------------------------------------------------------
    function ListFiles(&$list)
    {
        // --------------------------------------------------------------------
        $query = new QueryDb($this->dbaccess, $this->dbtable);
        $list = $query->Query(0, 0, "TABLE");
        $fc = $query->nb;
        return $fc;
    }
    // --------------------------------------------------------------------
    function Stats(&$s)
    {
        // --------------------------------------------------------------------
        $this->fs->Stats($s);
        $this->fStat($file_count, $vol);
        $s["general"]["file_count"] = $file_count;
        $s["general"]["file_size"] = $vol;
        return '';
    }
    
    function seems_utf8($Str)
    {
        return preg_match('!!u', $Str);
    }
    /**
     * Add new file in VAULT
     * @param string $infile complete server path of file to store
     * @param bool $public_access set true if can be access without any permission
     * @param int &$id new file identifier
     * @param string $fsname name of the VAULT to store (can be empty=>store in one of available VAULT)
     * @return string error message (empty if OK)
     */
    function Store($infile, $public_access, &$idf, $fsname = "", $te_lname = "", $te_id_file = 0)
    {
        // --------------------------------------------------------------------
        include_once ("WHAT/Lib.FileMime.php");
        
        if (!is_file($infile)) {
            return ErrorCode::getError('FILE0007', $infile);
        }
        if (!is_readable($infile)) {
            return ErrorCode::getError('FILE0008', $infile);
        }
        $this->size = filesize($infile);
        $msg = $this->fs->SetFreeFs($this->size, $id_fs, $id_dir, $f_path, $fsname);
        if ($msg != '') {
            $this->logger->error("Can't find free entry in vault. [reason $msg]");
            return ($msg);
        }
        $this->id_fs = $id_fs;
        $this->id_dir = $id_dir;
        $this->public_access = $public_access;
        $this->name = my_basename($infile);
        if (!$this->seems_utf8($this->name)) $this->name = utf8_encode($this->name);
        
        $this->mime_t = getTextMimeFile($infile);
        $this->mime_s = getSysMimeFile($infile, $this->name);
        $this->cdate = $this->mdate = $this->adate = date("c", time());
        
        $this->teng_state = '';
        $this->teng_lname = $te_lname;
        $this->teng_id_file = $te_id_file;
        
        $msg = $this->Add();
        if ($msg != '') return ($msg);
        
        $idf = $this->id_file;
        
        $f = vaultfilename($f_path, $infile, $this->id_file);
        if (!@copy($infile, $f)) {
            // Free entry
            $this->logger->error(sprintf(_("Failed to copy %s to %s") , $infile, $f));
            return (sprintf(_("Failed to copy %s to vault") , $infile));
        }
        
        $this->fs->AddEntry($this->size);
        $this->logger->debug("File $infile stored in $f");
        return "";
    }
    /**
     * Get the VaultDiskStorage transforming object corresponding to the current object
     * @param  VaultDiskStorage &$ngf returned object
     * @return string error message (empty if OK)
     */
    function GetEngineObject($te_name, &$ngf)
    {
        
        if (!$this->isAffected()) return _("vault file is not initialized");
        $err = '';
        $q = new QueryDb($this->dbaccess, "VaultDiskStorage");
        $q->AddQuery("teng_id_file=" . $this->id_file);
        $q->AddQuery("teng_lname='" . pg_escape_string($te_name) . "'");
        $tn = $q->Query();
        if ($q->nb == 0) {
            $ngf = new VaultDiskStorage($this->dbaccess);
            $ngf->teng_id_file = $this->id_file;
            $ngf->teng_lname = $te_name;
            $size = 1;
            $ngf->fs->SetFreeFs($size, $id_fs, $id_dir, $f_path, $fsname = '');
            $ngf->cdate = $ngf->mdate = $ngf->adate = date("c", time());
            $ngf->id_fs = $id_fs;
            $ngf->id_dir = $id_dir;
            $ngf->size = 0;
            $err = $ngf->Add();
            if ($err) return $err;
        } else {
            $ngf = $tn[0];
        }
        return $err;
    }
    /**
     * @param int $id_file vault file identifier
     * @param VaultFileInfo $f_infos
     * @param string $teng_lname engine name
     * @return string
     */
    function show($id_file, &$f_infos, $teng_lname = "")
    {
        // --------------------------------------------------------------------
        $this->id_file = - 1;
        if ($teng_lname != "") {
            $query = new QueryDb($this->dbaccess, $this->dbtable);
            $query->AddQuery("teng_id_file=" . $id_file);
            $query->AddQuery("teng_lname='" . pg_escape_string($teng_lname) . "'");
            
            $t = $query->Query(0, 0, "TABLE");
            
            if ($query->nb > 0) {
                $msg = DbObj::Select($t[0]["id_file"]);
            }
        }
        
        if (($this->id_file == - 1) && ($teng_lname == "")) {
            $msg = DbObj::Select($id_file);
        }
        
        if ($this->id_file != - 1) {
            $this->fs->Show($this->id_fs, $this->id_dir, $f_path);
            $f_infos = new VaultFileInfo();
            $f_infos->id_file = $this->id_file;
            $f_infos->name = $this->name;
            $f_infos->size = $this->size;
            $f_infos->public_access = $this->public_access;
            $f_infos->mime_t = $this->mime_t;
            $f_infos->mime_s = $this->mime_s;
            $f_infos->cdate = $this->cdate;
            $f_infos->mdate = $this->mdate;
            $f_infos->adate = $this->adate;
            $f_infos->teng_state = $this->teng_state;
            $f_infos->teng_lname = $this->teng_lname;
            $f_infos->teng_vid = $this->teng_id_file;
            $f_infos->teng_comment = $this->teng_comment;
            $f_infos->path = vaultfilename($f_path, $this->name, $this->id_file);
            
            $this->adate = date("c", time());
            $this->modify(true, array(
                "adate"
            ) , true);
            
            return '';
        } else {
            return (_("file does not exist in vault"));
        }
    }
    /**
     * return the complete path in file system
     * @return string the path
     */
    function getPath()
    {
        $this->fs->Show($this->id_fs, $this->id_dir, $f_path);
        return vaultfilename($f_path, $this->name, $this->id_file);
    }
    // --------------------------------------------------------------------
    function Destroy($id)
    {
        // --------------------------------------------------------------------
        $msg = $this->Show($id, $inf);
        if ($msg == '') {
            @unlink($inf->path);
            $msg = $this->fs->DelEntry($this->id_fs, $this->id_dir, $inf->size);
            $this->Delete();
        }
        
        return $msg;
    }
    // --------------------------------------------------------------------
    function Save($infile, $public_access, $idf)
    {
        $err = '';
        $vf = new VaultFile($this->dbaccess);
        if ($vf->Show($idf, $info) == "") {
            $path = str_replace("//", "/", $info->path);
            
            $size = $this->size;
            $this->size = filesize($infile);
            $newsize = $this->size - $size;
            // Verifier s'il y a assez de places ???
            $this->public_access = $public_access;
            //$this->name = my_basename($infile); // not rename
            $fd = fopen($path, "w+");
            //    if (!unlink($path))
            //	return("NOT UNLINK $path\n");
            $this->mdate = date("c", time());
            
            $msg = $this->modify();
            if ($msg != '') return ($msg);
            
            if (!copy($infile, $path)) {
                $err = sprintf(_("Cannot copy file %s to %s") , $infile, $path);
            } else {
                $this->fs->select($this->id_fs);
                $this->fs->AddEntry($newsize - $size);
                $this->logger->debug("File $infile saved in $path");
                
                $this->resetTEFiles();
            }
        } else {
            $err = sprintf("cannot save file : invalid vault descriptor %s", $idf);
        }
        return $err;
    }
    /**
     * reset all files product by transform engine
     */
    function resetTEFiles()
    {
        $up = "update " . $this->dbtable . " set teng_state=2 where teng_id_file=" . $this->id_file . ";";
        $this->exec_query($up);
    }
} // End Class.VaultFileDisk.php
class VaultFileInfo
{
    public $id_file;
    public $name;
    public $size;
    public $public_access;
    public $mime_t;
    public $mime_s;
    public $cdate;
    public $mdate;
    public $adate;
    public $teng_state;
    public $teng_lname;
    public $teng_vid;
    public $teng_comment;
    public $path;
}
?>
