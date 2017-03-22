<?php
/*
 * @author Anakeen
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
 * --app=FDL : <application name>
 * --action=REPORT_EXPORT_CSV : <action name>
 * --id=<the id of the report>
 * Options:
 * --refresh=<would you refresh doc before build report> [TRUE|FALSE], default is 'FALSE'
 * --kind=<the kind of report> [simple|pivot], default is 'simple'
 * --pivot=<the pivot attr>, default is 'id'
 * --delimiter=<the CSV delimiter>, default is ';'
 * --enclosure=<the CSV enclosure>, default is '"'
 * --encoding=<the CSV encoding>, default is 'ISO-8859-15//TRANSLIT'
 * --decimalSeparator=<the decimalSeparator>, default is '.'
 * --dateFormat=<the dateFormat> [US|FR|ISO], default is 'US
 * @endverbatim
 * @param Action $action
 * @throws \Dcp\Core\Exception
 */
function report_export_csv(Action & $action)
{
    $dbaccess = $action->dbaccess;
    setMaxExecutionTimeTo(1000);
    $argumentsCSV = array();
    $defaultArgument = json_decode(getParam("REPORT_DEFAULT_CSV", "[]") , true);
    $action->parent->addJsRef("lib/jquery/jquery.js");
    
    $usage = new ActionUsage($action);
    $defaultDocArg = array();
    
    $id = $usage->addRequiredParameter("id", "the id of the report");
    $currentDoc = null;
    if ($id != "") {
        $currentDoc = new_Doc($dbaccess, $id);
        $currentUserTag = $currentDoc->getUTag("document_export_csv");
        if ($currentUserTag) $defaultDocArg = json_decode($currentUserTag->comment, true);
    }
    
    $csvTmpFile = $usage->addHiddenParameter("csvDownloadFile", "tmp file to download");
    $expVarName = $usage->addHiddenParameter("exportId", "expert ident");
    $statusOnly = $usage->addHiddenParameter("statusOnly", "get status only");
    
    $refresh = ($usage->addOptionalParameter("refresh", "would you refresh doc before build report", array(
        "TRUE",
        "FALSE"
    ) , "FALSE") == "TRUE");
    
    $default = isset($defaultDocArg["kind"]) ? $defaultDocArg["kind"] : 'simple';
    $kind = $usage->addOptionalParameter("kind", "the kind of report", array(
        "simple",
        "pivot"
    ) , $default);
    $default = isset($defaultDocArg["pivot"]) ? $defaultDocArg["pivot"] : 'id';
    $pivot = $usage->addOptionalParameter("pivot", "the pivot attr", array() , $default);
    
    $default = isset($defaultDocArg["stripHtmlTag"]) ? $defaultDocArg["stripHtmlTag"] : false;
    $applyHtmlStrip = $usage->addOptionalParameter("stripHtmlTag", "strip html tags", array() , $default);
    $applyHtmlStrip = ($applyHtmlStrip != "1");
    
    $default = isset($defaultArgument["delimiter"]) ? $defaultArgument["delimiter"] : ';';
    $argumentsCSV["delimiter"] = $usage->addOptionalParameter("delimiter", "the CSV delimiter", array() , $default);
    $default = isset($defaultArgument["enclosure"]) ? $defaultArgument["enclosure"] : '"';
    $argumentsCSV["enclosure"] = $usage->addOptionalParameter("enclosure", "the CSV enclosure", array() , $default);
    $default = isset($defaultArgument["encoding"]) ? $defaultArgument["encoding"] : 'ISO-8859-15//TRANSLIT';
    $argumentsCSV["encoding"] = $usage->addOptionalParameter("encoding", "the CSV encoding", array() , $default);
    $default = isset($defaultArgument["decimalSeparator"]) ? $defaultArgument["decimalSeparator"] : '.';
    $argumentsCSV["decimalSeparator"] = $usage->addOptionalParameter("decimalSeparator", "the decimalSeparator", array() , $default);
    $default = isset($defaultArgument["dateFormat"]) ? $defaultArgument["dateFormat"] : 'US';
    
    $argumentsCSV["dateFormat"] = $usage->addOptionalParameter("dateFormat", "the dateFormat", array(
        'US',
        'FR',
        'ISO'
    ) , $default);
    
    $default = isset($defaultArgument["numericRender"]) ? $defaultArgument["numericRender"] : 'raw';
    $argumentsCSV["numericRender"] = $usage->addOptionalParameter("numericRender", "the number render", array(
        'raw',
        'format'
    ) , $default);
    
    $displayForm = $usage->addHiddenParameter("displayForm", "");
    $updateDefault = $usage->addHiddenParameter("updateDefault", "");
    
    $usage->setStrictMode(false);
    $usage->verify();
    $usageArguments = array(
        "sole",
        "app",
        "action",
        "id",
        "csvDownloadFile",
        "exportId",
        "statusOnly",
        "refresh",
        "kind",
        "pivot",
        "stripHtmlTag",
        "delimiter",
        "enclosure",
        "encoding",
        "decimalSeparator",
        "dateFormat",
        "displayForm",
        "updateDefault"
    );
    $addedArguments = array();
    
    foreach ($_GET as $key => $value) {
        if (!in_array($key, $usageArguments)) {
            $addedArguments[] = array(
                "argumentName" => $key,
                "argumentValue" => $value
            );
        }
    }
    
    $action->lay->eSetBlockData("addedArguments", $addedArguments);
    
    if ($csvTmpFile) {
        
        $fileName = sprintf("%s_%s.%s", $currentDoc->getTitle() , date("Y_m_d-H_m_s") , "csv");
        Http_DownloadFile($csvTmpFile, $fileName, "text/csv", false, false, true);
    }
    
    if ($statusOnly && $expVarName) {
        header('Content-Type: application/json');
        $action->lay->noparse = true;
        $action->lay->template = json_encode($action->read($expVarName));
        return;
    }
    if ($updateDefault) {
        $action->setParamU("REPORT_DEFAULT_CSV", json_encode($argumentsCSV));
        $err = $currentDoc->addUTag($action->user->id, "document_export_csv", json_encode(array(
            "kind" => $kind,
            "pivot" => $pivot,
            "stripHtmlTag" => $applyHtmlStrip
        )));
        if ($err) {
            error_log(__LINE__ . var_export($err, true));
        }
    }
    
    if ($displayForm) {
        $action->lay->set("id", $currentDoc->id);
        $expVarName = uniqid("EXPCSV");
        $action->lay->eset("exportId", $expVarName);
        $action->Register($expVarName, array(
            "status" => "init"
        ));
        $attr = getDocAttr($dbaccess, $currentDoc->getRawValue("se_famid", 1));
        $attributeLay = array();
        $attributeLay[] = array(
            "selected" => "",
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
                "libelle" => strip_tags($currentAttr[0]) ,
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
        
        $stripHtml = array(
            array(
                "key" => "0",
                "selected" => $isSelected("0", $applyHtmlStrip) ,
                "label" => _("Strip Html tags")
            ) ,
            array(
                "key" => "1",
                "selected" => $isSelected("1", $applyHtmlStrip) ,
                "label" => _("No strip Html tags")
            )
        );
        $action->lay->setBlockData("stripHtml", $stripHtml);
        
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
        
        $numericRender = array(
            array(
                "key" => "raw",
                "selected" => $isSelected("raw", $argumentsCSV["numericRender"]) ,
                "label" => _("EXPORT_CSV raw numbers")
            ) ,
            array(
                "key" => "format",
                "selected" => $isSelected("format", $argumentsCSV["numericRender"]) ,
                "label" => _("EXPORT_CSV formatted numbers ")
            )
        );
        $action->lay->setBlockData("numericRender", $numericRender);
        
        $action->lay->eset("delimiter", $argumentsCSV["delimiter"]);
        $action->lay->eset("enclosure", $argumentsCSV["enclosure"]);
        $action->lay->eset("decimalSeparator", $argumentsCSV["decimalSeparator"]);
    } else {
        
        $action->parent->setVolatileParam("exportSession", $expVarName);
        $reportFamId = getIdFromName($dbaccess, "REPORT");
        /**
         * @var \Dcp\Family\Report $currentDoc
         */
        
        $familyIdArray = $currentDoc->getFromDoc();
        if (in_array($reportFamId, $familyIdArray)) {
            switch ($kind) {
                case "pivot":
                    $csvStruct = $currentDoc->generateCSVReportStruct(true, $pivot, $argumentsCSV["decimalSeparator"], $argumentsCSV["dateFormat"], $refresh, $applyHtmlStrip, $argumentsCSV["numericRender"]);
                    break;

                default:
                    $csvStruct = $currentDoc->generateCSVReportStruct(false, "", $argumentsCSV["decimalSeparator"], $argumentsCSV["dateFormat"], $refresh, $applyHtmlStrip, $argumentsCSV["numericRender"]);
            }
            $csvFile = tempnam(getTmpDir() , "csv");
            if ($csvFile === false) {
                $err = sprintf(_("Error creating temporary file in '%s'.") , getTmpDir());
                $action->exitError($err);
            }
            $fp = fopen($csvFile, 'w');
            
            foreach ($csvStruct as $currentLine) {
                $encoding = $argumentsCSV["encoding"];
                if ($encoding != "UTF-8") {
                    $currentLine = convertLine($currentLine, $encoding);
                }
                fputcsv($fp, $currentLine, $argumentsCSV["delimiter"], $argumentsCSV["enclosure"]);
            }
            
            fclose($fp);
            
            if (empty($_SERVER['HTTP_HOST'])) {
                $content = file_get_contents($csvFile);
                $action->lay->noparse = true;
                $action->lay->template = $content;
                unlink($csvFile);
            } else {
                
                $fileName = sprintf("%s_%s.%s", $currentDoc->getTitle() , date("Y_m_d-H_m_s") , "csv");
                
                $action->Register($expVarName, array(
                    "status" => _("Export Done") ,
                    "end" => true
                ));
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
