<?php

require_once 'FDL/Class.Doc.php';

function usage()
{
    $usageString = "Usage: ./wsh.php";
    $usageString .= " --api=report_export_csv";
    $usageString .= " --id=report id\n";
    $usageString .= "\t[--pivot=pivot]\n";
    return $usageString;
}


function report_export_csv(&$action) {
$dbaccess = getParam('FREEDOM_DB');

$id = $action->getArgument('id', false);
$pivot = $action->getArgument('pivot', false);
$refresh = $action->getArgument('refresh', false);
$decimalSeparator = $action->getArgument('decimalSeparator', ".");

if (!$id) {
    $action->exitError(usage().'no report id given');
}

$currentDoc = new_Doc($dbaccess, $id);

$reportFamId = getIdFromName($dbaccess, "REPORT");

$familyIdArray = $currentDoc->getFromDoc();

if (in_array($reportFamId, $familyIdArray)) {

        if ($pivot) {
            $csvStruct = $currentDoc->generateCSVReportStruct($decimalSeparator ,$refresh, true, $pivot);
        }else {
            $csvStruct = $currentDoc->generateCSVReportStruct($decimalSeparator ,$refresh, false);
        }

        $csvFile = tempnam($action->GetParam("CORE_TMPDIR", "/tmp"), "csv$id").".csv";

        $fp = fopen($csvFile, 'w');

        foreach ( $csvStruct as $currentLine ) {
            fputcsv($fp, $currentLine);
        }

        fclose($fp);

        print $csvFile."\n";

        exit(0);

}else {
    $action->exitError('The document is not a report');
}

}
