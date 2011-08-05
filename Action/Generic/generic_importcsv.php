<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Import CSV
 *
 * @author Anakeen 2004
 * @version $Id: generic_importcsv.php,v 1.19 2008/09/01 06:21:09 marc Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Dir.php");
include_once ("FDL/import_file.php");
include_once ("FDL/modcard.php");
include_once ("GENERIC/generic_util.php");
/**
 * View a document
 * @param Action &$action current action
 * @global policy Http var : add|update|keep police case of similar document
 * @global category Http var :
 * @global analyze Http var : "Y" if just analyze
 * @global key1 Http var : primary key for double
 * @global key2 Http var : secondary key for double
 * @global classid Http var : document family to import
 * @global colorder Http var : array to describe CSV column attributes
 * @global file Http var : path to import file (only with wsh)
 * @global  Http var :
 */
function generic_importcsv(&$action)
{
    // -----------------------------------
    global $_FILES;
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/subwindow.js");
    // Get all the params
    $policy = GetHttpVars("policy", "update");
    $category = GetHttpVars("category");
    $analyze = (GetHttpVars("analyze", "N") == "Y"); // just analyze
    $key1 = GetHttpVars("key1", "title"); // primary key for double
    $key2 = GetHttpVars("key2", ""); // secondary key for double
    $classid = GetHttpVars("classid", getDefFam($action)); // document family to import
    $comma = GetHttpVars("comma", ";"); // Column separator -default is comma-
    $tcolorder[$classid] = GetHttpVars("colorder"); // column order
    $dbaccess = $action->GetParam("FREEDOM_DB");
    if (ini_get("max_execution_time") < 180) ini_set("max_execution_time", 180); // 3 minutes
    $ddoc = createDoc($dbaccess, $classid);
    setPostVars($ddoc); // memorize default import values
    
    if (isset($_FILES["vcardfile"])) {
        // importation
        $vcardfile = $_FILES["vcardfile"]["tmp_name"];
    } else {
        $vcardfile = GetHttpVars("file");
    }
    
    if (seemsODS($vcardfile)) {
        $cvsfile = ods2csv($vcardfile);
        $fdoc = fopen($cvsfile, "r");
    } else {
        $fdoc = fopen($vcardfile, "r");
    }
    
    if (!$fdoc) $action->exitError(_("no csv import file specified"));
    $dir = new_Doc($dbaccess, getDefFld($action));
    
    if ($analyze) $action->lay->set("importresult", _("import analysis result"));
    else $action->lay->set("importresult", _("import results"));
    
    $tvalue = array();
    
    $line = 0;
    while (!feof($fdoc)) {
        $buffer = rtrim(fgets($fdoc, 4096));
        $data = explode($comma, $buffer);
        $line++;
        $num = count($data);
        if ($num < 1) continue;
        if (is_numeric($data[1])) $fromid = $data[1];
        else $fromid = getFamIdFromName($dbaccess, $data[1]);
        switch ($data[0]) {
            case "DOC":
                if (isset($tkeys[$fromid])) $tk = $tkeys[$fromid];
                else $tk = array(
                    $key1,
                    $key2
                );
                
                $cr[$line] = csvAddDoc($dbaccess, $data, getDefFld($action) , $analyze, '', $policy, $tk, $ddoc->getValues() , $tcolorder[$fromid]);
                if ($cr[$line]["err"] != "") {
                } else {
                    
                    if ($cr[$line]["id"] > 0) {
                        // add in each selected folder
                        if (is_array($category)) {
                            
                            foreach ($category as $k => $v) {
                                
                                $catg = new_Doc($dbaccess, $v);
                                $err = $catg->AddFile($cr[$line]["id"]);
                                $cr[$line]["err"].= $err;
                                if ($err == "") $cr[$line]["msg"].= sprintf(_("Add it in %s folder") , $catg->title);
                            }
                        }
                    }
                }
                break;

            case "ORDER":
                $cr[$line] = array(
                    "err" => "",
                    "msg" => "",
                    "specmsg" => "",
                    "folderid" => 0,
                    "foldername" => "",
                    "filename" => "",
                    "title" => "",
                    "id" => "",
                    "values" => array() ,
                    "familyid" => 0,
                    "familyname" => "",
                    "action" => " "
                );
                $tcolorder[$fromid] = getOrder($data);
                $cr[$line]["msg"] = sprintf(_("new column order %s") , implode(" - ", $tcolorder[$fromid]));
                
                break;

            case "KEYS":
                $cr[$line] = array(
                    "err" => "",
                    "msg" => "",
                    "specmsg" => "",
                    "folderid" => 0,
                    "foldername" => "",
                    "filename" => "",
                    "title" => "",
                    "id" => "",
                    "values" => array() ,
                    "familyid" => 0,
                    "familyname" => "",
                    "action" => " "
                );
                $tkeys[$fromid] = getOrder($data);
                if (($tkeys[$fromid][0] == "") || (count($tkeys[$fromid]) == 0)) {
                    $cr[$line]["err"] = sprintf(_("error in import keys : %s") , implode(" - ", $tkeys[$fromid]));
                    unset($tkeys[$fromid]);
                    $cr[$line]["action"] = "ignored";
                } else {
                    $cr[$line]["msg"] = sprintf(_("new import keys : %s") , implode(" - ", $tkeys[$fromid]));
                }
                
                break;
            }
        }
        
        fclose($fdoc);
        foreach ($cr as $k => $v) {
            $cr[$k]["taction"] = _($v["action"]); // translate action
            $cr[$k]["order"] = $k; // translate action
            $cr[$k]["svalues"] = "";
            
            foreach ($v["values"] as $ka => $va) {
                $cr[$k]["svalues"].= "<LI>[$ka:$va]</LI>"; //
                
            }
        }
        $action->lay->SetBlockData("ADDEDDOC", $cr);
        $nbdoc = count(array_filter($cr, "isdoc2"));
        $action->lay->Set("nbdoc", "$nbdoc");
    }
    function isdoc2($var)
    {
        return (($var["action"] == "added") || ($var["action"] == "updated"));
    }
?>
