<?php
/*
 * Reinit vault files
 *
 * @author Anakeen
 * @package FDL
*/

global $action;

include_once ('FDL/Class.Doc.php');
include_once ('FDL/Class.DocFam.php');
include_once ('FDL/Class.DocVaultIndex.php');
include_once ('VAULT/Class.VaultFile.php');
/**
 * Parse arguments
 */
$usage = new ApiUsage();
$usage->setDefinitionText("Re-initialize docvaultindex table");
/* --dryrun=no|yes (default 'no') */
$check = ($usage->addEmptyParameter("dryrun", "Check consistency only (non-destructive mode)") !== false);
$csv = $usage->addOptionalParameter("csv", "Output details to CSV file (with comma delimiter [,], double-quote enclosure [\"], and backslash escape char [\\])", null, false);
$usage->verify();

$vaultAnalyzer = new \Dcp\Vault\VaultAnalyzer();
$report = array();
$consistent = false;
if ($check) {
    $consistent = $vaultAnalyzer->checkDocVaultIndex($report);
} else {
    $consistent = $vaultAnalyzer->regenerateDocVaultIndex($report);
}
if ($csv !== false) {
    report2csv($report, $csv);
} else {
    report2cli($report);
}
if ($consistent) {
    exit(0);
} else {
    exit(1);
}

function report2cli($report)
{
    printf("\n");
    printf("[+] Summary:\n");
    printf("New entries: %d\n", $report['new']['count']);
    printf("Missing entries: %d\n", $report['missing']['count']);
}

function report2csv($report, $outfile)
{
    report2cli($report);
    if (($fh = fopen($outfile, 'w')) === false) {
        throw new \Dcp\Exception(sprintf("Error opening CSV output file '%s' for writing!", $outfile));
    }
    try {
        xfputcsv($fh, array(
            'new/missing',
            'docid',
            'vaultid'
        ));
        foreach ($report['new']['iterator'] as $row) {
            xfputcsv($fh, array(
                'new',
                $row['docid'],
                $row['vaultid']
            ));
        }
        foreach ($report['missing']['iterator'] as $row) {
            xfputcsv($fh, array(
                'missing',
                $row['docid'],
                $row['vaultid']
            ));
        }
    }
    catch(\Dcp\Exception $e) {
        fclose($fh);
        throw $e;
    }
}

function xfputcsv($fh, $fields)
{
    if (($ret = fputcsv($fh, $fields)) === false) {
        throw new \Dcp\Exception(sprintf(""));
    }
    return $ret;
}
