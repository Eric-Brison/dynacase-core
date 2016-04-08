<?php
/*
 * @author Anakeen
 * @package FDL
*/

include_once ("FDL/Class.Doc.php");
include_once ("FDL/editutil.php");
function modonedefaultvalue(Action $action)
{
    $usage = new ActionUsage($action);
    
    $famid = $usage->addRequiredParameter("famid", "Family identifier", function ($value)
    {
        $family = new_doc("", $value);
        if ($family->doctype !== "C") {
            return "Must be a family identifier";
        }
        return '';
    });
    /**
     * @var DocFam $family
     */
    $family = new_doc("", $famid);
    $attrid = $usage->addRequiredParameter("attrid", "Attribute identifier", function ($value) use ($family)
    {
        $oa = $family->getAttribute($value);
        if (!$oa) {
            return sprintf("Attribute \"%s\" not found in family \"%s\"", $value, $family->name);
        }
        return '';
    });
    $value = $usage->addOptionalParameter("value", "New default value");
    $err = '';
    $oa = null;
    try {
        $usage->verify(true);
    }
    catch(Dcp\ApiUsage\Exception $e) {
        $err = $e->getDcpMessage();
    }
    
    if (!$err) {
        $err = $family->control("edit");
        if ($err) {
            $action->exitError($err);
        }
        
        $oa = $family->getAttribute($attrid);
        
        if ($oa->type === "file" || $oa->type === "image") {
            if (isset($_FILES["defaultFile"])) {
                $file = $_FILES["defaultFile"];
                if ($file["error"] === 0) {
                    $vid = \Dcp\VaultManager::storeFile($file["tmp_name"], $file["name"]);
                    $value = sprintf("%s|%s|%s", $file["type"], $vid, $file["name"]);
                } else {
                    $err = sprintf("Error file transfer : [code %s]", $file["error"]);
                }
            }
        }
        
        if (!$err) {
            $err = $family->setDefValue($oa->id, $value);
            
            if (!$err) {
                $err = $family->modify();
            }
        }
    }
    if ($err) {
        $out = array(
            "success" => false,
            "error" => $err
        );
        header('HTTP/1.0 400 Error');
    } else {
        $defval = $family->getDefValue($oa->id);
        $out = array(
            "success" => true,
            "message" => ($defval === "") ? sprintf(_("Default value erased")) : sprintf(_("Default value recorded")) ,
            "value" => $family->getDefValue($oa->id)
        );
    }
    header('Content-Type: application/json');
    $action->lay->template = json_encode($out);
    $action->lay->noparse = true;
}
