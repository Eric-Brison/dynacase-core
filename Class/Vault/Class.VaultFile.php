<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Retrieve and store file in Vault
 *
 * @author Anakeen
 * @version $Id: Class.VaultFile.php,v 1.23 2008/05/27 12:46:06 marc Exp $
 * @package FDL
 */
/**
 */

include_once ("VAULT/Class.VaultDiskStorage.php");
include_once ("Class.Log.php");
define("VAULT_FMODE", 0600);
define("VAULT_DMODE", 0700);

class VaultFile
{
    /**
     * @var int file ideentificator
     */
    public $idf;
    public $f_mode = 0600;
    public $d_mode = 0700;
    public $type = "fs";
    
    const VAULT_DMODE = VAULT_DMODE;
    /**
     * @var VaultDiskStorage
     */
    public $storage;
    function __construct($access, $vaultname = "Sample", $idf = - 1)
    {
        
        if (!isset($chrono)) $this->chrono = FALSE;
        else $this->chrono = $chrono;
        $this->idf = $idf;
        $this->name = $vaultname;
        $this->logger = new Log("", "vault", $this->name);
        if ($this->chrono) $this->logger->warning("Running with chrono !!!!");
        $this->dbaccess = $access;
        
        $this->f_mode = 0600;
        $this->d_mode = 0700;
        $this->type = "fs";
        switch ($this->type) {
            case "fs":
                $this->logger->debug("Set Storage Type to FS");
                $this->storage = new VaultDiskStorage($access);
                break;

            default:
                // Not implemented yet
                
        }
}
// ---------------------------------------------------------

/**
 * @param int $id_file vault file identifier
 * @param vaultFileInfo $infos file properties
 * @param string $teng_lname engine name
 * @return string error message
 */
function show($id_file, &$infos, $teng_lname = "")
{
    // ---------------------------------------------------------
    if ($this->chrono) $this->logger->start("Show");
    $msg = $this->storage->Show($id_file, $infos, $teng_lname);
    if ($msg != '') {
        $this->logger->error(sprintf("File #%s : %s", $id_file, $msg));
    }
    if ($this->chrono) $this->logger->end("Show");
    return ($msg);
}
/**
 * Set access date to now
 * @param int $id_file vault file identifier
 * @return void
 */
function updateAccessDate($id_file)
{
    $this->storage->updateAccessDate($id_file);
}
/**
 * retrieve information from vault id
 * @param int $id_file
 * @param VaultFileInfo $infos
 * @return string error message
 */
function retrieve($id_file, &$infos)
{
    // ---------------------------------------------------------
    if ($this->chrono) $this->logger->start("Retrieve");
    if (isset($info)) unset($infos);
    
    $msg = $this->storage->Show($id_file, $infos);
    
    if ($msg != '') $this->logger->error($msg);
    if ($this->chrono) $this->logger->end("Retrieve");
    return ($msg);
}
// ---------------------------------------------------------
function store($infile, $public_access, &$id, $fsname = "", $te_name = "", $te_id_file = 0, $tmp = null)
{
    // ---------------------------------------------------------
    if ($this->chrono) $this->logger->start("Store");
    $id = - 1;
    if (!file_exists($infile) || !is_readable($infile) || !is_file($infile)) {
        $this->logger->error("Can't access file [" . $infile . "].");
        $msg = _("can't access file");
    } else {
        if (!is_bool($public_access)) {
            $public_access = FALSE;
            $this->logger->warning("Access mode forced to RESTRICTED for " . $infile . "].");
        }
        $this->storage->id_tmp = $tmp;
        $msg = $this->storage->Store($infile, $public_access, $id, $fsname, $te_name, $te_id_file);
        if ($msg) $this->logger->error($msg);
    }
    if ($this->chrono) $this->logger->end("Store");
    return ($msg);
}
// ---------------------------------------------------------
function save($infile, $public_access, $id)
{
    // ---------------------------------------------------------
    if ($this->chrono) $this->logger->start("Save");
    
    if (!is_bool($public_access)) {
        $public_access = FALSE;
        $this->logger->warning("Access mode forced to RESTRICTED for " . $infile . "].");
    }
    
    $msg = $this->storage->Save($infile, $public_access, $id);
    if ($msg) $this->logger->error($msg);
    
    $this->storage->mime_t = getTextMimeFile($infile);
    $this->storage->mime_s = getSysMimeFile($infile, $this->storage->name);
    $msg = $this->storage->Modify();
    if ($this->chrono) $this->logger->end("Save");
    return ($msg);
}
/**
 * Modification of properties if file
 * @param int $id_file vault id
 * @param string $newname new file name
 * @return string error message (empty if no error)
 */
function rename($id_file, $newname)
{
    // ---------------------------------------------------------
    if ($this->chrono) $this->logger->start("Rename");
    $msg = '';
    if ($newname != "") {
        $nn = str_replace(array(
            '/',
            '\\',
            '?',
            '*',
            ':'
        ) , '-', $newname);
        if ($nn != $newname) {
            addWarningMsg(sprintf(_("Some characters are not authorized for file name %s. They are replaced by -") , $nn));
            $newname = $nn;
        }
        
        include_once ("WHAT/Lib.FileMime.php");
        $infile = $this->storage->getPath();
        $oldname = $this->storage->name;
        $this->storage->Show($id_file, $infos);
        $this->storage->name = $newname;
        $this->storage->mime_t = getTextMimeFile($infile, $this->storage->name);
        $this->storage->mime_s = getSysMimeFile($infile, $this->storage->name);
        $msg = $this->storage->Modify();
        if ($msg == "") {
            $pio = pathinfo($oldname);
            $pin = pathinfo($newname);
            $epio = isset($pio['extension']) ? $pio['extension'] : "";
            if ($epio == "") $epio = "nop";
            $epin = isset($pin['extension']) ? $pin['extension'] : "";
            if ($epin == "") $epin = "nop";
            if ($epio != $epin) {
                // need rename physically file
                if (preg_match("|(.*)/([0-9]+)\\.[^\\.]*|", $infos->path, $reg)) {
                    $newpath = $reg[1] . "/" . $reg[2] . "." . $epin;
                    rename($infos->path, $newpath);
                }
            }
        }
        if ($msg) $this->logger->error($msg);
    }
    
    if ($this->chrono) $this->logger->end("Rename");
    return ($msg);
}
// ---------------------------------------------------------
function listFiles(&$s)
{
    // ---------------------------------------------------------
    if ($this->chrono) $this->logger->start("ListFiles");
    $this->storage->ListFiles($s);
    if ($this->chrono) $this->logger->end("ListFiles");
    return '';
}
// ---------------------------------------------------------
function destroy($id)
{
    // ---------------------------------------------------------
    if ($this->chrono) $this->logger->start("Destroy");
    
    $msg = $this->storage->Destroy($id);
    if ($msg != '') $this->logger->error($msg);
    if ($this->chrono) $this->logger->end("Destroy");
    return $msg;
}
}
