<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * @var Action $action
 */

$usage = new ApiUsage();
$usage->setDefinitionText("Set Vault Big Keys");
$force = $usage->addHiddenParameter("force", "No Verify");
$dryRun = $usage->addEmptyParameter("dry-run", "Only verify");
$vaultid = $usage->addOptionalParameter("vaultid", "restrict to single Vault id", "", 0);
$stop = $usage->addEmptyParameter("stop-on-error", "Stop on first error");
$verbose = $usage->addEmptyParameter("verbose", "Verbose vault analysis");

$usage->verify();

ini_set("memory_limit", -1);

$r = new DcpApiVaultKeys();
$r->setVerbose($verbose);
$r->setStop($stop);
if (!$force) {
    $r->verifyIndexes();
}
$r->run($dryRun, $vaultid);

class DcpApiVaultKeys
{
    protected $attrIds = array();
    protected $vaultFile = null;
    protected $logFile = null;
    protected $dryRun = false;
    protected $verbose = false;
    protected $stop = false;
    private $randKey = 0;
    private $minIndex = 0;
    private $maxIndex = 0;
    const timeWindow = 10;
    const timeWindowFactor = 100;
    /**
     * @param boolean $verbose
     */
    public function setVerbose($verbose)
    {
        $this->verbose = $verbose;
    }
    /**
     * @param boolean $stop
     */
    public function setStop($stop)
    {
        $this->stop = $stop;
    }
    public function __construct()
    {
        $this->vaultFile = new VaultDiskStorage();
        $this->logFile = sprintf("%s/.renameVaultKeys.log", DEFAULT_PUBDIR);
        if (PHP_INT_SIZE >= 8) {
            $this->minIndex = 1 << 31;
        } else {
            $this->minIndex = $this->getSequenceNextVal('seq_id_vaultdiskstorage');
        }
        $this->maxIndex = PHP_INT_MAX;
    }
    public function getSequenceNextVal($seqName)
    {
        simpleQuery('', sprintf("SELECT nextval(%s)", pg_escape_literal($seqName)) , $res, true, true, true);
        return $res;
    }
    public function run($dryRun, $vaultid)
    {
        $this->dryRun = $dryRun;
        $this->recover();
        
        $sql = <<<'SQL'
PREPARE update_docvaultindex_vaultid (bigint, bigint) AS UPDATE docvaultindex SET vaultid = $1 WHERE vaultid = $2;
SQL;
        simpleQuery('', $sql, $res, false, false, true);
        
        $sql = <<<'SQL'
PREPARE update_vaultdiskstorage_id_file (bigint, bigint) AS UPDATE vaultdiskstorage SET id_file = $1 where id_file = $2;
SQL;
        simpleQuery('', $sql, $res, false, false, true);
        
        $sql = <<<'SQL'
PREPARE update_vaultdiskstorage_teng_id_file (bigint, bigint) AS UPDATE vaultdiskstorage SET teng_id_file = $1 where teng_id_file = $2;
SQL;
        simpleQuery('', $sql, $res, false, false, true);
        
        $sql = <<<'SQL'
SELECT
  docvaultindex.vaultid,
  count(docvaultindex.vaultid) AS n,
  array_agg(docfrom.id) AS docids,
  array_agg(docfrom.fromid) AS fromids
FROM
  docvaultindex,
  docfrom
WHERE
  docfrom.id = docvaultindex.docid AND
  docvaultindex.vaultid < %s %s
GROUP BY docvaultindex.vaultid
ORDER BY docvaultindex.vaultid
SQL;
        
        $sql = sprintf($sql, pg_escape_literal($this->minIndex) , empty($vaultid) ? "" : sprintf("and docvaultindex.vaultid = %s", pg_escape_literal($vaultid)));
        
        $this->verbose("Retrieve vault file to reindex...");
        simpleQuery("", $sql, $vaultIndexes);
        $count = count($vaultIndexes);
        $this->verbose(sprintf("%d to update.\n", $count));
        
        if ($this->dryRun) {
            $this->vaultFile->savePoint("DRY");
        }
        $kt = 0;
        $ticTime = [];
        foreach ($vaultIndexes as $ki => $vaultIndex) {
            $docids = explode(",", substr($vaultIndex["docids"], 1, -1));
            $fromids = array_unique(explode(",", substr($vaultIndex["fromids"], 1, -1)));
            $vaultId = $vaultIndex["vaultid"];
            
            $fileInfo = \Dcp\VaultManager::getFileInfo($vaultId);
            if (!$fileInfo) {
                //throw new \Dcp\Exception(sprintf("Unknow file ref \"%s\" in vault database", $vaultId));
                $this->verbose(sprintf("Unknow file ref \"%s\" in vault database", $vaultId));
                continue;
            }
            if ($fileInfo->public_access === "t") {
                continue;
            }
            
            try {
                
                $this->vaultFile->savePoint("VAULTKEYS");
                
                $newId = $this->getNewVaultId();
                $this->verbose("%d/%d) Updating vault key %s -> %s\n", $ki + 1, $count, $vaultId, $newId);
                foreach ($fromids as $fromid) {
                    if ($fromid > 0) {
                        $this->verbose("\tUpdating documents #%s\n", implode(",", $docids));
                        $this->updateDocData($vaultId, $newId, $fromid, $docids);
                    } else {
                        $this->updateFamilyData($vaultId, $newId, $docids);
                    }
                }
                $this->updateVaultData($newId, $fileInfo);
                
                $this->vaultFile->commitPoint("VAULTKEYS");
                $this->logVault(" ", " ");
                if ($ki % self::timeWindow === 0) {
                    $now = microtime(true);
                    $ticTime[($kt++) % self::timeWindowFactor] = $now;
                    $tenTime = $now - min($ticTime);
                    $remaid = ($count - $ki) / self::timeWindow * $tenTime / count($ticTime);
                    
                    $this->verboseColor("Time Left: %-20s (%d/%d)", $this->humanDelay($remaid) , $ki + 1, $count);
                }
            }
            catch(Exception $e) {
                $this->vaultFile->rollbackPoint("VAULTKEYS");
                if ($this->stop) {
                    throw $e;
                } else {
                    $color = "\033" . '[01;31m';
                    $nocolor = "\033" . '[0m';
                    fprintf(STDERR, $e->getMessage() . "\n");
                    print ($color . "Error:" . $e->getMessage() . $nocolor . "\n");
                }
            }
        }
        if ($this->dryRun) {
            $this->vaultFile->rollbackPoint("DRY");
        }
        print "\n";
    }
    
    protected function humanDelay($delay)
    {
        if ($delay > 3600) {
            $sDelay = sprintf("%dh %02dmin", $delay / 3600, ($delay % 3600) / 60);
        } elseif ($delay > 60) {
            $sDelay = sprintf("%02dmin %02ds", $delay / 60, $delay % 60);
        } else {
            $sDelay = sprintf("%02ds", $delay);
        }
        return $sDelay;
    }
    /**
     * Check Vault integrity indexes
     * @throws \Dcp\Exception
     */
    public function verifyIndexes()
    {
        $vaultAnalyzer = new \Dcp\Vault\VaultAnalyzer();
        $report = array();
        $vaultAnalyzer->setVerbose($this->verbose);
        $consistent = $vaultAnalyzer->checkDocVaultIndex($report);
        if (!$consistent) {
            throw new \Dcp\Exception("Vault index is corrupted. Use \"refreshVaultIndex\" wsh script to verify/repair index");
        }
    }
    /**
     * Complete rename vault identifier : rename real file name
     * @param int $newId new vault identifier
     * @param VaultFileInfo $fileInfo
     * @throws \Dcp\Db\Exception
     * @throws \Dcp\Exception
     */
    protected function updateVaultData($newId, VaultFileInfo $fileInfo)
    {
        $vaultId = $fileInfo->id_file;
        $currentFileName = $fileInfo->path;
        $currentDirName = dirname($currentFileName);
        $currentBaseName = basename($currentFileName);
        if (!preg_match('/^(?P<vid>\d+)(?P<extension>\..*)$/', $currentBaseName, $m)) {
            throw new \Dcp\Exception(sprintf("Malformed filename '%s'.", $currentFileName));
        }
        if ($vaultId !== $m['vid']) {
            throw new \Dcp\Exception(sprintf("VID mismatch between filename '%s' and vid '%s'.", $currentFileName, $m['vid']));
        }
        $newBaseName = sprintf("%s%s", $newId, $m['extension']);
        $newFileName = $currentDirName . DIRECTORY_SEPARATOR . $newBaseName;
        $this->verbose("\tmv %s %s \n", $currentFileName, $newFileName);
        
        if (!$this->dryRun) {
            $sql = sprintf("EXECUTE update_docvaultindex_vaultid(%s, %s)", pg_escape_literal($newId) , pg_escape_literal($vaultId));
            simpleQuery("", $sql, $res, false, false, true);
            $sql = sprintf("EXECUTE update_vaultdiskstorage_id_file(%s, %s)", pg_escape_literal($newId) , pg_escape_literal($vaultId));
            simpleQuery("", $sql, $res, false, false, true);
            $sql = sprintf("EXECUTE update_vaultdiskstorage_teng_id_file(%s, %s)", pg_escape_literal($newId) , pg_escape_literal($vaultId));
            simpleQuery("", $sql, $res, false, false, true);
            $this->logVault($currentFileName, $newFileName);
            if (!rename($currentFileName, $newFileName)) {
                throw new \Dcp\Exception("Cannot rename $currentFileName to $newFileName");
            }
        }
    }
    /**
     * Log current file rename
     * @param string $currentName
     * @param string $newName
     * @throws \Dcp\Exception
     */
    protected function logVault($currentName, $newName)
    {
        if (!$this->dryRun) {
            if (file_put_contents($this->logFile, sprintf("%s\n%s", $currentName, $newName)) === false) {
                throw new \Dcp\Exception(sprintf("Error writing content to transaction's log file '%s'.", $this->logFile));
            }
        }
    }
    protected function recover()
    {
        if ($this->dryRun) {
            return;
        }
        if (is_file($this->logFile)) {
            if (($data = file($this->logFile)) === false) {
                throw new \Dcp\Exception(sprintf("Error reading content from transaction's log file '%s'.", $this->logFile));
            }
            if ($data) {
                $newFileName = trim($data[1]);
                $currentFileName = trim($data[0]);
                
                if ($newFileName && $currentFileName && !rename($newFileName, $currentFileName)) {
                    throw new \Dcp\Exception("Cannot rename $newFileName to $currentFileName");
                }
            }
        }
    }
    
    protected function verbose($format)
    {
        if ($this->verbose) {
            call_user_func_array("printf", func_get_args());
        }
    }
    protected function verboseColor($format)
    {
        $color = "\033" . '[1;33;40m';
        $nocolor = "\033" . '[0m';
        
        $args = func_get_args();
        if ($this->verbose) {
            
            $args[0] = "\t" . $color . $args[0] . $nocolor . "\n";
        } else {
            $color = "\033" . '[1;33m';
            $args[0] = "\r" . $color . $args[0] . $nocolor;
        }
        call_user_func_array("printf", $args);
    }
    /**
     * Update file family parameters
     * @param int $vaultId current vault identifier
     * @param int $newId new vault identifier
     * @param int[] $docids family identifiers
     * @throws \Dcp\Exception
     */
    protected function updateFamilyData($vaultId, $newId, $docids)
    {
        
        $s = new SearchDoc("", -1);
        $s->addFilter("id in (%s)", implode(",", $docids));
        $s->setObjectReturn();
        $dl = $s->getDocumentList();
        /**
         * @var DocFam $family
         */
        foreach ($dl as $family) {
            $attributes = $family->getAttributes();
            $parameters = $family->getOwnParams();
            $defVal = $family->getOwnDefValues();
            $needModify = false;
            foreach ($attributes as $oattr) {
                if (($oattr->type === "file" || $oattr->type === "image")) {
                    if ($oattr->usefor === "Q") {
                        if (!empty($parameters[$oattr->id])) {
                            $newValue = str_replace('|' . $vaultId . '|', '|' . $newId . '|', $parameters[$oattr->id]);
                            if ($newValue !== $parameters[$oattr->id]) {
                                $err = $family->setParam($oattr->id, $newValue);
                                if ($err) {
                                    throw new \Dcp\Exception(sprintf("Cannot update family (%s) parameter (%s) : %s ", $family->name, $oattr->id, $err));
                                }
                                $this->verbose("Update family \"%s\" parameter \"%s\"", $family->name, $oattr->id);
                                $needModify = true;
                            }
                        }
                    }
                    
                    if (!empty($defVal[$oattr->id])) {
                        $newValue = str_replace('|' . $vaultId . '|', '|' . $newId . '|', $defVal[$oattr->id]);
                        if ($newValue !== $defVal[$oattr->id]) {
                            $err = $family->setDefValue($oattr->id, $newValue);
                            if ($err) {
                                throw new \Dcp\Exception(sprintf("Cannot update family (%s) default value (%s) : %s ", $family->name, $oattr->id, $err));
                            }
                            $this->verbose("Update family \"%s\" default value \"%s\"", $family->name, $oattr->id);
                            $needModify = true;
                        }
                    }
                }
            }
            
            if ($needModify) {
                $err = $family->modify(false);
                if ($err) {
                    throw new \Dcp\Exception(sprintf("Cannot update family (%s) : %s ", $family->name, $err));
                }
            }
        }
    }
    /**
     * Set new index for file document attributes
     * @param int $vaultId current vault identifier
     * @param int $newId new vault identifier
     * @param int $fromid document's family identifier to search
     * @param int[] $docids document identifiers to update
     * @throws \Dcp\Exception
     */
    protected function updateDocData($vaultId, $newId, $fromid, $docids)
    {
        $attrs = $this->getFamilyFileAttrIds($fromid);
        
        if ($attrs) {
            $sql = sprintf("update doc%d ", $fromid);
            $first = true;
            foreach ($attrs as $attr) {
                if ($first) {
                    $sql.= " set ";
                    $first = false;
                } else {
                    $sql.= ", ";
                }
                $sql.= sprintf(" %s=regexp_replace(%s, E'\\\\|%s($|\\n|[\\\\|.*])',E'|%s\\\\1','g')", pg_escape_string($attr) , pg_escape_string($attr) , $vaultId, $newId);
            }
            
            $sql.= sprintf(" where id in (%s)", implode(",", $docids));
            
            if (!$this->dryRun) {
                simpleQuery("", $sql);
                
                $sql = sprintf("EXECUTE update_docvaultindex_vaultid(%s, %s)", pg_escape_literal($newId) , pg_escape_literal($vaultId));
                simpleQuery("", $sql);
            }
        }
    }
    /**
     * Get file attributes for given family
     * @param int $famId family identifier
     * @return mixed
     */
    protected function getFamilyFileAttrIds($famId)
    {
        if (!isset($this->attrIds[$famId])) {
            $d = new DocFam("", $famId);
            $this->attrIds[$famId] = [];
            $attributes = $d->getFileAttributes();
            foreach ($attributes as $oa) {
                $this->attrIds[$famId][] = $oa->id;
            }
        }
        return $this->attrIds[$famId];
    }
    protected function getNewVaultId()
    {
        if ($this->randKey === 0) {
            $this->randKey = 1;
            mt_srand($this->vaultFile->getNewVaultId());
        }
        
        $nogood = true;
        $newId = 0;
        while ($nogood) {
            $newId = mt_rand($this->minIndex, $this->maxIndex);
            simpleQuery("", sprintf("select true from vaultdiskstorage where id_file = %s", $newId) , $nogood, true, true);
        }
        
        return $newId;
    }
}
