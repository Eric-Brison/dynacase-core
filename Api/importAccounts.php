<?php
/*
 * Account export
 * @author Anakeen
 * @package FDL
*/

$usage = new ApiUsage();
$usage->setDefinitionText("Import accounts definition");
$filename = $usage->addRequiredParameter("file", "the input XML file", function ($values, $argName, ApiUsage $apiusage)
{
    if ($values === ApiUsage::GET_USAGE) {
        return "";
    }
    if (is_file($values) && !is_readable($values)) {
        $apiusage->exitError(sprintf("Error: file output \"%s\" not readable.", $values));
    }
    return '';
});
$outfile = $usage->addOptionalParameter("report-file", "the output report file", function ($values, $argName, ApiUsage $apiusage)
{
    if ($values === ApiUsage::GET_USAGE) {
        return "";
    }
    if ($values !== "-" && is_file($values) && !is_writable($values)) {
        $apiusage->exitError(sprintf("Error: file output \"%s\" not writable.", $values));
    }
    return '';
});
$dry = $usage->addEmptyParameter("dry-run", "Analyse file only - no import is proceed");

$usage->verify();

$import = new \Dcp\Core\ImportAccounts();
$import->setFile($filename);
$import->setAnalyzeOnly($dry);
$import->import();

$ext = substr($outfile, strrpos($outfile, '.') + 1);
if ($outfile) {
    $report = $import->getReport();
    switch ($ext) {
        case "json":
            if ($outfile === "-.json") {
                $outfile = "php://stdout";
            }
            file_put_contents($outfile, json_encode($report, JSON_PRETTY_PRINT));
            break;

        case "csv":
            printCsv($report, $outfile);
            break;

        default:
            printText($report, $outfile);
            break;
    }
}

function printCsv($report, $outfile)
{
    if ($outfile === "-.csv") {
        $outfile = "php://stdout";
    }
    $csvFile = fopen($outfile, "w+");
    fputcsv($csvFile, array(
        "login",
        "action",
        "error",
        "message",
        "node"
    ));
    foreach ($report as $row) {
        fputcsv($csvFile, array(
            $row["login"],
            $row["action"],
            $row["error"],
            $row["message"],
            $row["node"]
        ));
    }
    fclose($csvFile);
}

function printText($report, $outfile)
{
    $out = '';
    $format = "| %20s | %20s | %30s | %20s |\n";
    $out = sprintf($format, "login", "action", "error", "message");
    $spaces = sprintf("%80s", " ");
    $out.= "---------------------------------------------------------------------------------------------------\n";
    foreach ($report as $row) {
        if ($row["error"]) {
            $out.= chr(0x1b) . "[1;31m";
        }
        
        $out.= sprintf($format, $row["login"], $row["action"], $row["error"], str_replace("\n", ", ", $row["message"]));
        if ($row["error"]) {
            $out.= chr(0x1b) . "[0;39m";
        }
    }
    if ($outfile) {
        if ($outfile === "-") {
            $outfile = "php://stdout";
        }
    }
    file_put_contents($outfile, $out);
}
