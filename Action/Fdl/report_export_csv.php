<?php

require_once 'FDL/Class.Doc.php';
require_once 'FDL/freedom_util.php';
require_once 'EXTERNALS/fdl.php';

function usage()
{
    $usageString = "Usage: ./wsh.php";
    $usageString .= " --api=report_export_csv";
    $usageString .= " --id=report id\n";
    $usageString .= "\t[--pivot=pivot]\n";
    return $usageString;
}

function report_export_csv(&$action)
{
    $dbaccess = getParam('FREEDOM_DB');

    $id = $action->getArgument('id', false);
    $kind = $action->getArgument('kind', "simple");
    $pivot = $action->getArgument('pivot', false);
    $refresh = $action->getArgument('refresh', false);
    $delimiter = $action->getArgument('delimiter', ",");
    $enclosure = $action->getArgument('enclosure', '"');
    $encoding = $action->getArgument('encoding', 'utf8');
    $decimalSeparator = $action->getArgument('decimalSeparator', ".");
    $displayForm = $action->getArgument('displayForm', false);

    if (!$id) {
        $action->exitError(usage() . 'no report id given');
    }

    $currentDoc = new_Doc($dbaccess, $id);

    if ($displayForm) {
        $action->lay->set("id", $id);
        $attr = getDocAttr($dbaccess, $currentDoc->getValue("se_famid", 1));
        $attributeLay = array();
        $attributeLay[]= array("key" => "id", "libelle" => _("EXPORT_CSV : identifiant unique"));
        foreach ($attr as $currentAttr) {
            $attributeLay[]= array("key" => $currentAttr[1], "libelle" => $currentAttr[0]);
        }
        $action->lay->setBlockData("pivotAttribute", $attributeLay);
    }else {

        $reportFamId = getIdFromName($dbaccess, "REPORT");
        $familyIdArray = $currentDoc->getFromDoc();

        if (in_array($reportFamId, $familyIdArray)) {

            switch ($kind) {
            case "pivot":
                $csvStruct = $currentDoc->generateCSVReportStruct($decimalSeparator, $refresh, true, $pivot);
                break;
            default:
                $csvStruct = $currentDoc->generateCSVReportStruct($decimalSeparator, $refresh, false);
            }

            $csvFile = tempnam($action->GetParam("CORE_TMPDIR", "/tmp"), "csv$id") . ".csv";
            $fp = fopen($csvFile, 'w');

            foreach ( $csvStruct as $currentLine ) {
                if ($encoding != "utf8") {
                    $currentLine = convertLine($currentLine, $encoding);
                }
                fputcsv($fp, $currentLine, $delimiter, $enclosure);
            }

            fclose($fp);

            if (!$_SERVER['HTTP_HOST']) {
                print $csvFile . "\n";
            }else {
                $fileName = sprintf("%s_%s.%s", $currentDoc->getTitle(), date("Y_m_d-H_m_s"), "csv");
                 Http_DownloadFile($csvFile, $fileName , "text/csv", false, false, true);
            }

            exit(0);

        } else {
            $action->exitError('The document is not a report');
        }
    }

}

function convertLine($currentLine, $encoding)
{
    $returnArray = array();
    foreach ( $currentLine as $currentValue ) {
        $returnArray[] = iconv("utf8", $encoding, $currentValue);
    }
    return $returnArray;
}
