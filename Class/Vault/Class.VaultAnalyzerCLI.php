<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Vault;

require_once 'WHAT/Lib.Common.php';

class VaultAnalyzerCLIException extends \Dcp\Exception
{
}

class VaultAnalyzerCLI
{
    
    public static function main(\Action & $action)
    {
        $opts = array();
        $usage = new \ApiUsage($action);
        $usage->setDefinitionText('Analyze or clean orphan files');
        $usage->setStrictMode(true);
        $opts['analyze'] = ($usage->addEmptyParameter('analyze', 'Analyze orphan files (non-destructive)') !== false);
        $opts['clean'] = ($usage->addEmptyParameter('clean', 'Clean orphan files') !== false);
        $opts['missing-files'] = ($usage->addEmptyParameter('missing-files', "Use in conjunction with '--analyze' to analyze missing physical files instead of orphan files") !== false);
        $opts['skip-trash'] = ($usage->addEmptyParameter('skip-trash', "Really delete file and do not move them under '<vault_root>/.trash/'  sub-directory") !== false);
        $usage->verify();
        if (!$opts['analyze'] && !$opts['clean']) {
            $usage->exitError("Use '--analyze' or '--clean'.");
            return;
        }
        if ($opts['analyze'] xor !$opts['clean']) {
            $usage->exitError("Use either '--analyze' or '--clean', but not both.");
            return;
        }
        
        try {
            if ($opts['analyze']) {
                if ($opts['missing-files']) {
                    self::main_analyze_missing_files();
                } else {
                    self::main_analyze_orphans();
                }
            } elseif ($opts['clean']) {
                self::main_clean_orphans($opts['skip-trash']);
            }
        }
        catch(VaultAnalyzerCLIException $e) {
            printf("\nError: %s\n\n", $e->getMessage());
            exit(1);
        }
        return;
    }
    
    public static function main_analyze_orphans()
    {
        $vaultAnalyzer = new VaultAnalyzer();
        self::checkDocVaultIndex($vaultAnalyzer);
        
        printf("* Analyzing... ");
        $report = $vaultAnalyzer->summary();
        
        printf("Done.\n");
        printf("\n");
        printf("Analyze\n");
        printf("-------\n");
        printf("\n");
        printf("All:\n");
        printf("\tcount = %d\n", $report['all']['count']);
        printf("\tsize  = %d%s\n", $report['all']['size'], (empty($report['all']['size_pretty']) ? '' : ' (' . $report['all']['size_pretty'] . ')'));
        printf("\n");
        printf("Used:\n");
        printf("\tcount = %d\n", $report['used']['count']);
        printf("\tsize  = %d%s (%3.02f%%)\n", $report['used']['size'], ((empty($report['used']['size_pretty'])) ? '' : ' (' . $report['used']['size_pretty'] . ')') , (($report['all']['size'] != 0) ? ((100 * $report['used']['size']) / $report['all']['size']) : '0'));
        printf("\n");
        printf("Orphan:\n");
        printf("\tcount = %d\n", $report['orphan']['count']);
        printf("\tsize  = %d%s (%3.02f%%)\n", $report['orphan']['size'], ((empty($report['orphan']['size_pretty'])) ? '' : ' (' . $report['orphan']['size_pretty'] . ')') , (($report['all']['size'] != 0) ? ((100 * $report['orphan']['size']) / $report['all']['size']) : '0'));
        printf("\n");
        
        return;
    }
    
    public static function main_clean_orphans($skipTrash = false)
    {
        $vaultAnalyzer = new VaultAnalyzer();
        self::checkDocVaultIndex($vaultAnalyzer);
        
        printf("* Cleanup docvaultindex: ");
        $report = $vaultAnalyzer->cleanDocVaultIndex();
        printf("removed %d entries.\n", $report['count']);
        
        $report = $vaultAnalyzer->analyzeOrphans();
        printf("* Deleting %d orphan files...\n", $report['count']);
        $pom = new \Dcp\ConsoleProgressOMeter();
        $pom->setMax($report['count'])->setInterval(1000)->start();
        $p = 0;
        foreach ($report['iterator'] as $t) {
            $p++;
            $vault_root = $t['vault_root'];
            $trash_root = $vault_root . '/.trash';
            $filename = $t['filename'];
            $vault_filename = $vault_root . '/' . $filename;
            $trash_filename = $trash_root . '/' . $filename;
            /*
             * Delete file
            */
            if (file_exists($vault_filename)) {
                if ($skipTrash === true) {
                    if (unlink($vault_filename) === false) {
                        printf("Error: could not delete '%s'.\n", $vault_filename);
                        continue;
                    }
                } else {
                    if (!is_dir($trash_root)) {
                        if (mkdir($trash_root) === false) {
                            throw new VaultAnalyzerCLIException("Could not create trash dir '%s'.", $trash_root);
                        }
                    }
                    $dir = dirname($trash_filename);
                    if (!is_dir($dir)) {
                        if (mkdir($dir, 0777, true) === false) {
                            printf("Error: could not create trash subdir '%s'.\n", $dir);
                            continue;
                        }
                    }
                    if (rename($vault_filename, $trash_filename) === false) {
                        printf("Error: could not move '%s' to '%s'.\n", $vault_filename, $trash_filename);
                        continue;
                    }
                }
            }
            /*
             * Delete vaultdiskstorage entry
            */
            $vaultAnalyzer->deleteIdFile($t['id_file']);
            $pom->progress($p);
        }
        $pom->finish();

        printf("* Reset sizes...\n");
        $fs=new \VaultDiskFsStorage(getDbAccess());
        $fs->recomputeDirectorySize();

        printf("\nDone.\n");
        printf("\n");
        
        return;
    }
    
    public static function main_analyze_missing_files()
    {
        $vaultAnalyzer = new vaultAnalyzer();
        self::checkDocVaultIndex($vaultAnalyzer);
        
        printf("* Counting entries: ");
        $report = $vaultAnalyzer->analyzePhysicalFiles();
        printf("found %d entries.\n", $report['count']);
        printf("\n");
        printf("* Checking missing physical files for %d entries...\n", $report['count']);
        printf("\n");
        $missing = array();
        $missing_nodoc = array();
        $missing_doc = array();
        foreach ($report['iterator'] as $t) {
            $vid = $t['id_file'];
            $docid = $t['docid'];
            $vault_root = $t['vault_root'];
            $filename = $t['filename'];
            $vault_filename = $vault_root . '/' . $filename;
            if (file_exists($vault_filename)) {
                continue;
            }
            if (!isset($missing[$vault_filename])) {
                $missing[$vault_filename] = 0;
            } else {
                $missing[$vault_filename]++;
            }
            if ($docid === null) {
                printf("Missing physical file '%s' (vid = %d) without docvaultindex entries\n", $vault_filename, $vid);
                if (!isset($missing_nodoc[$vault_filename])) {
                    $missing_nodoc[$vault_filename] = 0;
                } else {
                    $missing_nodoc[$filename]++;
                }
            } else {
                printf("Missing physical file '%s' (vid = %d) with valid docvaultindex entries (docid = %d)\n", $vault_filename, $vid, $docid);
                if (!isset($missing_doc[$vault_filename])) {
                    $missing_doc[$vault_filename] = 0;
                } else {
                    $missing_doc[$vault_filename]++;
                }
            }
        }
        printf("\n");
        printf("Analyze\n");
        printf("-------\n");
        printf("\n");
        printf("Found %d missing physical file:\n", count($missing));
        printf("\twith valid docvaultindex entries = %d\n", count($missing_doc));
        printf("\twithout docvaultindex entries    = %d\n", count($missing_nodoc));
        printf("\n");
        
        return;
    }
    
    protected static function checkDocVaultIndex(VaultAnalyzer & $vaultAnalyzer)
    {
        $report = array();
        if ($vaultAnalyzer->checkDocVaultIndex($report) === false) {
            throw new VaultAnalyzerCLIException(sprintf("Found inconsistencies in 'docvaultindex': you might need to regenerate docvaultindex with \"./wsh.php --api=refreshVaultIndex\""));
        }
    }
}
