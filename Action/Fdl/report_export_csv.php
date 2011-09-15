<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

require_once 'FDL/Class.Doc.php';
require_once 'FDL/freedom_util.php';
require_once 'EXTERNALS/fdl.php';
/**
 * Export a report in CSV
 *
 * @note 
 * @verbatim
 Usage :
 	--app=FDL : <application name>
 	--action=REPORT_EXPORT_CSV : <action name>
 	--id=<the id of the report>
    Options:
 	--refresh=<would you refresh doc before build report> [TRUE|FALSE], default is 'FALSE'
 	--kind=<the kind of report> [simple|pivot], default is 'simple'
 	--pivot=<the pivot attr>, default is 'id'
 	--delimiter=<the CSV delimiter>, default is ';'
 	--enclosure=<the CSV enclosure>, default is '"'
 	--encoding=<the CSV encoding>, default is 'ISO-8859-15//TRANSLIT'
 	--decimalSeparator=<the decimalSeparator>, default is '.'
 	--dateFormat=<the dateFormat> [US|FR|ISO], default is 'US
@endverbatim
*/
function report_export_csv(Action & $action)
{
    $dbaccess = getParam('FREEDOM_DB');
    
    $argumentsCSV = array();
    $defaultArgument = json_decode(getParam("REPORT_DEFAULT_CSV", "[]") , true);
    
    $usage = new ActionUsage($action);
    $defaultDocArg = array();
    
    $id = $usage->addNeeded("id", "the id of the report");
    
    if ($id != "") {
        $currentDoc = new_Doc($dbaccess, $id);
        $currentUserTag = $currentDoc->getUTag("document_export_csv");
        $defaultDocArg = json_decode($currentUserTag->comment, true);
    }
    
    $refresh = $usage->addOption("refresh", "would you refresh doc before build report", array(
        "TRUE",
        "FALSE"
    ) , "FALSE");
    
    $default = isset($defaultDocArg["kind"]) ? $defaultDocArg["kind"] : 'simple';
    $kind = $usage->addOption("kind", "the kind of report", array(
        "simple",
        "pivot"
    ) , $default);
    $default = isset($defaultDocArg["pivot"]) ? $defaultDocArg["pivot"] : 'id';
    $pivot = $usage->addOption("pivot", "the pivot attr", array() , $default);
    
    $default = isset($defaultArgument["delimiter"]) ? $defaultArgument["delimiter"] : ';';
    $argumentsCSV["delimiter"] = $usage->addOption("delimiter", "the CSV delimiter", array() , $default);
    $default = isset($defaultArgument["enclosure"]) ? $defaultArgument["enclosure"] : '"';
    $argumentsCSV["enclosure"] = $usage->addOption("enclosure", "the CSV enclosure", array() , $default);
    $default = isset($defaultArgument["encoding"]) ? $defaultArgument["encoding"] : 'ISO-8859-15//TRANSLIT';
    $argumentsCSV["encoding"] = $usage->addOption("encoding", "the CSV encoding", array() , $default);
    $default = isset($defaultArgument["decimalSeparator"]) ? $defaultArgument["decimalSeparator"] : '.';
    $argumentsCSV["decimalSeparator"] = $usage->addOption("decimalSeparator", "the decimalSeparator", array() , $default);
    $default = isset($defaultArgument["dateFormat"]) ? $defaultArgument["dateFormat"] : 'US';
    $argumentsCSV["dateFormat"] = $usage->addOption("dateFormat", "the dateFormat", array(
        'US',
        'FR',
        'ISO'
    ) , $default);
    
    $displayForm = $usage->addHidden("displayForm", "");
    $updateDefault = $usage->addHidden("updateDefault", "");
    
    $usage->verify();
    
    if ($updateDefault) {
        $action->setParamU("REPORT_DEFAULT_CSV", json_encode($argumentsCSV));
        $err = $currentDoc->addUTag($action->user->id, "document_export_csv", json_encode(array(
            "kind" => $kind,
            "pivot" => $pivot
        )));
        error_log(__LINE__ . var_export($err, true));
    }
    
    if ($displayForm) {
        $action->lay->set("id", $id);
        $attr = getDocAttr($dbaccess, $currentDoc->getValue("se_famid", 1));
        $attributeLay = array();
        $attributeLay[] = array(
            "key" => "id",
            "libelle" => _("EXPORT_CSV : identifiant unique")
        );
        
        $isSelected = function ($currentValue, $selectedValue)
        {
            return $currentValue == $selectedValue ? "selected='selected'" : "";
        };
        
        foreach ($attr as $currentAttr) {
            $attributeLay[] = array(
                "key" => $currentAttr[1],
                "libelle" => $currentAttr[0],
                "selected" => $isSelected($currentAttr[1], $pivot) ,
            );
        }
        
        $action->lay->setBlockData("pivotAttribute", $attributeLay);
        
        $kinds = array(
            array(
                "key" => "simple",
                "selected" => $isSelected("simple", $kind) ,
                "label" => _("EXPORT_CSV Simple")
            ) ,
            array(
                "key" => "pivot",
                "selected" => $isSelected("pivot", $kind) ,
                "label" => _("EXPORT_CSV pivot")
            )
        );
        $action->lay->setBlockData("kinds", $kinds);
        
        $encodings = array(
            array(
                "key" => "UTF-8",
                "selected" => $isSelected("UTF-8", $argumentsCSV["encoding"]) ,
                "label" => _("EXPORT_CSV utf8")
            ) ,
            array(
                "key" => "ISO-8859-15//TRANSLIT",
                "selected" => $isSelected("ISO-8859-15//TRANSLIT", $argumentsCSV["encoding"]) ,
                "label" => _("EXPORT_CSV ISO-8859-15 (european)")
            )
        );
        $action->lay->setBlockData("encodings", $encodings);
        
        $dateFormats = array(
            array(
                "key" => "US",
                "selected" => $isSelected("US", $argumentsCSV["dateFormat"]) ,
                "label" => _("EXPORT_CSV Date format US")
            ) ,
            array(
                "key" => "FR",
                "selected" => $isSelected("FR", $argumentsCSV["dateFormat"]) ,
                "label" => _("EXPORT_CSV Date format FR")
            ) ,
            array(
                "key" => "ISO",
                "selected" => $isSelected("ISO", $argumentsCSV["dateFormat"]) ,
                "label" => _("EXPORT_CSV Date format ISO")
            )
        );
        $action->lay->setBlockData("dateFormats", $dateFormats);
        
        $action->lay->set("delimiter", $argumentsCSV["delimiter"]);
        $action->lay->set("enclosure", $argumentsCSV["enclosure"]);
        $action->lay->set("decimalSeparator", $argumentsCSV["decimalSeparator"]);
    } else {
        
        $reportFamId = getIdFromName($dbaccess, "REPORT");
        $familyIdArray = $currentDoc->getFromDoc();
        
        if (in_array($reportFamId, $familyIdArray)) {
            switch ($kind) {
                case "pivot":
                    $csvStruct = $currentDoc->generateCSVReportStruct(true, $pivot, $argumentsCSV["decimalSeparator"], $argumentsCSV["dateFormat"], $refresh);
                    break;

                default:
                    $csvStruct = $currentDoc->generateCSVReportStruct(false, "", $argumentsCSV["decimalSeparator"], $argumentsCSV["dateFormat"], $refresh);
            }
            
            $csvFile = tempnam($action->GetParam("CORE_TMPDIR", "/tmp") , "csv$id") . ".csv";
            $fp = fopen($csvFile, 'w');
            
            foreach ($csvStruct as $currentLine) {
                if ($encoding != "utf8") {
                    $currentLine = convertLine($currentLine, $argumentsCSV["encoding"]);
                }
                fputcsv($fp, $currentLine, $argumentsCSV["delimiter"], $argumentsCSV["enclosure"]);
            }
            
            fclose($fp);
            
            if (!$_SERVER['HTTP_HOST']) {
                $handle = fopen($csvFile, 'r');
                $content = fread($handle, filesize($csvFile));
                fclose($handle);
                $action->lay->noParse = true;
                $action->lay->template = $content;
            } else {
                $fileName = sprintf("%s_%s.%s", $currentDoc->getTitle() , date("Y_m_d-H_m_s") , "csv");
                Http_DownloadFile($csvFile, $fileName, "text/csv", false, false, true);
            }
        } else {
            $action->exitError('The document is not a report');
        }
    }
}

function convertLine($currentLine, $encoding)
{
    $returnArray = array();
    foreach ($currentLine as $currentValue) {
        $returnArray[] = iconv("UTF-8", $encoding, $currentValue);
    }
    return $returnArray;
}
