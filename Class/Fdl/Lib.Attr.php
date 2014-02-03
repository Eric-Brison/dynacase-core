<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Generation of PHP Document classes
 *
 * @author Anakeen
 * @version $Id: Lib.Attr.php,v 1.85 2009/01/14 09:18:05 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ('FDL/Class.Doc.php');
include_once ('FDL/Class.DocFam.php');
/**
 * Generate Class.Docxxx.php files
 *
 * @param string $dbaccess database specification
 * @param array $tdoc array of family definition
 */
function AttrToPhp($dbaccess, $tdoc)
{
    global $action;
    
    $GEN = getGen($dbaccess);
    $phpAdoc = new Layout("FDL/Layout/Class.Doc.xml", $action);
    $phpMethods = new Layout("FDL/Layout/Class.Method.xml");
    
    if ($tdoc["classname"] == "") { // default classname
        if ($tdoc["fromid"] == 0) $tdoc["classname"] = '\Dcp\Family\Document';
        else $tdoc["classname"] = "Doc" . $tdoc["fromid"];
    } else {
        $tdoc["classname"] = '\\' . $tdoc["classname"];
    }
    if ($tdoc["fromid"] > 0) {
        
        $tdoc["fromname"] = getNameFromId($dbaccess, $tdoc["fromid"]);
        $phpAdoc->Set("DBfromname",strtolower($tdoc["fromname"]));
    } else {
        $tdoc["fromname"] = "Document";
        $phpAdoc->Set("DBfromname","documents");
    }
        $phpAdoc->Set("DBdocname",strtolower($tdoc["name"]));
    $phpAdoc->Set("docid", $tdoc["id"]);
    $phpAdoc->Set("include", "");
    $phpAdoc->Set("GEN", "");
    if ($tdoc["fromid"] == 0) {
        $phpAdoc->Set("DocParent", $tdoc["classname"]);
        $phpAdoc->Set("AParent", "ADoc");
        $phpAdoc->Set("fromid", "");
        $phpAdoc->Set("pinit", '\DocCtrl');
    } else {
        $parentFile = sprintf("%s/FDLGEN/Class.Doc%d.php", DEFAULT_PUBDIR, $tdoc["fromid"]);
        if ((!file_exists($parentFile)) || filesize($parentFile) == 0) {
            throw new \Dcp\Exception("FAM0600", $parentFile, $tdoc["name"]);
        }
        $phpAdoc->Set("fromid", $tdoc["fromid"]);
        if ($tdoc["classname"] != "Doc" . $tdoc["fromid"]) {
            $phpAdoc->Set("DocParent", $tdoc["classname"]);
            $phpAdoc->Set("pinit", $tdoc["classname"]);
            $phpAdoc->Set("include", "include_once(\"FDL$GEN/Class.Doc" . $tdoc["fromid"] . ".php\");");
        } else {
            $phpAdoc->Set("GEN", $GEN);
            if ($tdoc["name"]) {
                $phpAdoc->Set("DocParent", '\\Dcp\\Family\\' . ucwords(strtolower($tdoc["fromname"])));
            } else {
                $phpAdoc->Set("DocParent", '\\Doc' . $tdoc["fromid"]);
            }
            $phpAdoc->Set("FileClassParent", 'Doc' . $tdoc["fromid"]);
            if (strstr($tdoc["usefor"], 'W')) $phpAdoc->Set("pinit", '\WDoc'); // special init for workflow
            else $phpAdoc->Set("pinit", '\DocCtrl');
        }
        $phpAdoc->Set("AParent", "ADoc" . $tdoc["fromid"]);
    }
    $phpAdoc->Set("title", $tdoc["title"]);
    $query = new QueryDb($dbaccess, "DocAttr");
    $query->AddQuery("docid=" . $tdoc["id"]);
    $query->order_by = "ordered";
    
    $table1 = $query->Query();
    
    $phpAdoc->Set("sattr", "");
    
    $phpAdoc->set("hasattr", false);
    $pa = getParentAttributes($dbaccess, $tdoc["fromid"]);
    
    if ($query->nb > 0) {
        
        $tmenu = array();
        $tfield = array();
        $tnormal = array();
        $tattr = array();
        $attrids = array();
        $tcattr = array();
        $taction = array();
        
        foreach ($table1 as $k => $v) {
            $type = trim(strtok($v->type, "("));
            if ($type == "docid") {
                $parentDoctitle = "";
                if (isset($pa[substr($v->id, 1)]) && preg_match("/doctitle=([A-Za-z0-9_-]+)/", $pa[substr($v->id, 1)]["options"], $reg)) {
                    $parentDoctitle = $reg[1];
                }
                // add title auto
                if (preg_match("/doctitle=([A-Za-z0-9_-]+)/", $v->options, $reg)) {
                    $doctitle = $reg[1];
                    if ($doctitle === $parentDoctitle) continue;
                    if ($doctitle == "auto") $doctitle = $v->id . "_title";
                    $doctitle = strtolower($doctitle);
                    $table1[$doctitle] = clone ($v);
                    $table1[$doctitle]->id = $doctitle;
                    $table1[$doctitle]->type = "text";
                    $table1[$doctitle]->visibility = "H";
                    $table1[$doctitle]->phpfile = "";
                    if (!preg_match("/docrev=(fixed|state)/", $v->options)) {
                        $table1[$doctitle]->phpfunc = "::getLastTitle(" . $v->id . ",' )";
                    } else {
                        $table1[$doctitle]->phpfunc = "::getTitle(" . $v->id . ",' )";
                    }
                    $table1[$doctitle]->options = "autotitle=yes";
                    $table1[$doctitle]->title = "N";
                    $table1[$doctitle]->abstract = "N";
                    $table1[$doctitle]->needed = "N";
                    $table1[$doctitle]->usefor = "A";
                    $table1[$doctitle]->link = "";
                    $table1[$doctitle]->phpconstraint = "";
                    $table1[$doctitle]->labeltext = $v->labeltext . ' ' . _("(title)");
                    $table1[$doctitle]->ordered = $v->ordered + 1;
                }
            }
        }
        $pM = new parseFamilyMethod();
        foreach ($table1 as $k => $v) {
            if ($v->id[0] == ':') {
                $v = completeAttribute($dbaccess, $v);
            }
            
            if ($v->visibility == "F") {
                $v->type = "frame";
            } // old notation compliant
            elseif ($v->visibility == "M") {
                $v->type = "menu";
            } // old notation compliant
            if ($v->type == "integer") {
                $v->type = "int";
            } // old notation compliant
            //$v->phpfunc = str_replace("\"", "\\\"", $v->phpfunc);
            switch (strtolower($v->type)) {
                case "menu": // menu
                    if (substr($v->link, 0, 2) == "::") {
                        if (preg_match('/::([^\(]+)\(([^\)]*)\)/', $v->link, $reg)) {
                            
                            $method = $reg[1];
                            $v->link = "%S%app=FDL&action=FDL_METHOD&id=%I%&method=" . urlencode($v->link);
                        }
                    }
                    $tmenu[strtolower($v->id) ] = array(
                        "attrid" => strtolower($v->id) ,
                        "label" => str_replace("\"", "\\\"", $v->labeltext) ,
                        "order" => intval($v->ordered) ,
                        "link" => str_replace("\"", "\\\"", $v->link) ,
                        "visibility" => $v->visibility,
                        "options" => str_replace("\"", "\\\"", $v->options) ,
                        "precond" => doubleslash($v->phpfunc)
                    );
                    break;

                case "tab":
                case "frame": // frame
                    $tfield[strtolower($v->id) ] = array(
                        "attrid" => strtolower($v->id) ,
                        "visibility" => $v->visibility,
                        "label" => str_replace("\"", "\\\"", $v->labeltext) ,
                        "usefor" => $v->usefor,
                        "type" => $v->type,
                        "options" => str_replace("\"", "\\\"", $v->options) ,
                        "frame" => ($v->frameid == "") ? "FIELD_HIDDENS" : strtolower($v->frameid)
                    );
                    break;

                case "action": // action
                    $taction[strtolower($v->id) ] = array(
                        "attrid" => strtolower($v->id) ,
                        "visibility" => $v->visibility,
                        "label" => str_replace("\"", "\\\"", $v->labeltext) ,
                        "order" => intval($v->ordered) ,
                        "options" => str_replace("\"", "\\\"", $v->options) ,
                        "wapplication" => $v->phpfile,
                        "waction" => doubleslash($v->phpfunc) ,
                        "precond" => str_replace("\"", "\\\"", $v->phpconstraint)
                    );
                    break;

                default: // normal
                    if (preg_match('/\[([a-z=0-9]+)\](.*)/', $v->phpfunc, $reg)) {
                        $v->phpfunc = $reg[2];
                        $funcformat = $reg[1];
                    } else {
                        $funcformat = "";
                    }
                    
                    if (preg_match("/([a-z]+)\([\"'](.*)[\"']\)/i", $v->type, $reg)) {
                        $atype = $reg[1];
                        $aformat = $reg[2];
                        if ($atype == "idoc") {
                            if (!is_numeric($aformat)) {
                                $aformat = getFamIdFromName($dbaccess, $aformat);
                            }
                        }
                    } else {
                        $atype = $v->type;
                        $aformat = "";
                    }
                    $repeat = "false";
                    if (preg_match("/([a-z]+)list/i", $atype, $reg)) {
                        $atype = $reg[1];
                        $repeat = "true";
                    } else {
                        if (strpos($v->options, "multiple=yes") !== false) {
                            $repeat = "true";
                        } else {
                            if (isset($tnormal[strtolower($v->frameid) ])) {
                                if (getTypeMain($tnormal[strtolower($v->frameid) ]["type"]) == "array") {
                                    $repeat = "true";
                                }
                            }
                            
                            if (($repeat == "false") && isset($pa[strtolower($v->frameid) ])) {
                                if (getTypeMain($pa[strtolower($v->frameid) ]["type"]) == "array") {
                                    $repeat = "true";
                                }
                            }
                        }
                    }
                    
                    $atype = strtolower(trim($atype));
                    // create code for calculated attributes
                    if ((!$v->phpfile) && preg_match('/^[a-z]*::[a-z0-9_ ]+\(/i', $v->phpfunc, $reg) && ($v->usefor != 'Q')) {
                        
                        $pM->parse($v->phpfunc);
                        $error = $pM->getError();
                        if ($error) {
                            throw new Dcp\Exception($error);
                        }
                        if (!$pM->outputString) {
                            $oAid = $v->id;
                        } else {
                            $oAid = $pM->outputs[0];
                        }
                        $tcattr[] = array(
                            "callmethod" => doubleslash($v->phpfunc) ,
                            "callattr" => $oAid
                        );
                    }
                    // complete attributes characteristics
                    $v->id = chop(strtolower($v->id));
                    
                    if (!$v->phpconstraint) {
                        if (($atype == "integer") || ($atype == "int")) {
                            $v->phpconstraint = sprintf("::isInteger(%s)", $v->id);
                        } elseif (($atype == "money") || ($atype == "double")) {
                            $v->phpconstraint = sprintf("::isFloat(%s)", $v->id);
                        }
                    }
                    if ($atype == "account") {
                        if (!$v->phpfile && !$v->phpfunc) {
                            $v->phpfile = 'fdl.php';
                            $options = $v->options;
                            if ($aformat) {
                                if ($options) {
                                    $options.= '|';
                                }
                                $options.= sprintf("family=%s", $aformat);
                            }
                            $v->phpfunc = sprintf('fdlGetAccounts(CT,15,"%s"):%s,CT', str_replace('"', '\\"', $options) , $v->id);
                        }
                    }
                    $tnormal[($v->id) ] = array(
                        "attrid" => ($v->id) ,
                        "label" => str_replace("\"", "\\\"", $v->labeltext) ,
                        "type" => $atype,
                        "format" => str_replace("\"", "\\\"", $aformat) ,
                        "eformat" => str_replace("\"", "\\\"", $funcformat) ,
                        "options" => doubleslash($v->options) , //(str_replace("\"", "\\\"", $v->options) ,
                        "order" => intval($v->ordered) ,
                        "link" => str_replace("\"", "\\\"", $v->link) ,
                        "visibility" => $v->visibility,
                        "needed" => ($v->needed == "Y") ? "true" : "false",
                        "title" => ($v->title == "Y") ? "true" : "false",
                        "repeat" => $repeat,
                        "abstract" => ($v->abstract == "Y") ? "true" : "false",
                        "frame" => ($v->frameid == "") ? "FIELD_HIDDENS" : strtolower($v->frameid) ,
                        "elink" => $v->elink,
                        "phpfile" => $v->phpfile,
                        "phpfunc" => doubleslash(str_replace(", |", ",  |", $v->phpfunc)) ,
                        "phpconstraint" => str_replace("\"", "\\\"", $v->phpconstraint) ,
                        "usefor" => $v->usefor
                    );
                    
                    if (($atype != "array") && ($v->usefor != "Q")) {
                        if ($atype != "array") {
                            $tattr[$v->id] = array(
                                "attrid" => ($v->id)
                            );
                        }
                        if ($repeat == "true") {
                            $attrids[$v->id] = ($v->id) . " text"; // for the moment all repeat are text
                            
                        } else {
                            switch ($atype) {
                                case 'double':
                                case 'float':
                                case 'money':
                                    $attrids[$v->id] = ($v->id) . " float8";
                                    break;

                                case 'int':
                                case 'integer':
                                    $attrids[$v->id] = ($v->id) . " int4";
                                    break;

                                case 'date':
                                    $attrids[$v->id] = ($v->id) . " date";
                                    break;

                                case 'timestamp':
                                    $attrids[$v->id] = ($v->id) . " timestamp without time zone";
                                    break;

                                case 'time':
                                    $attrids[$v->id] = ($v->id) . " time";
                                    break;

                                default:
                                    $attrids[$v->id] = ($v->id) . " text";
                            }
                        }
                    }
            }
        }
        
        $phpAdoc->Set("sattr", implode(",", $attrids));
        $phpAdoc->SetBlockData("MATTR", $tmenu);
        $phpAdoc->SetBlockData("FATTR", $tfield);
        $phpAdoc->SetBlockData("AATTR", $taction);
        $phpAdoc->SetBlockData("NATTR", $tnormal);
        $phpAdoc->SetBlockData("ATTRFIELD", $tattr);
        
        $phpAdoc->set("hasattr", (count($tattr) > 0));
        $phpAdoc->SetBlockData("ACALC", $tcattr);
    }
    
    $phpAdoc->Set("STARMETHOD", false);
    if ($tdoc["name"] == '') {
        $tdoc["name"] = 'F__' . $tdoc["id"];
    }
    if ($tdoc["name"] != "") { // create name alias classes
        $phpAdoc->SetBlockData("CLASSALIAS", array(
            array(
                "zou"
            )
        ));
        $phpAdoc->Set("docName", $tdoc["name"]);
        $phpAdoc->Set("PHPclassName", sprintf('%s', str_replace(array(
            ":",
            "-"
        ) , "_", ucwords(strtolower($tdoc["name"])))));
    }
    $phpAdoc->Set("docTitle", str_replace('"', '\\"', $tdoc["title"]));
    $phpAdoc->set("HOOKALIAS", "");
    //----------------------------------
    // Add specials methods
    $cmethod = ""; // method file which is use as inherit virtual class
    $contents2 = '';
    $hasMethod = false;
    if (isset($tdoc["methods"]) && ($tdoc["methods"] != "")) {
        $tfmethods = explode("\n", $tdoc["methods"]);
        $contents = "";
        foreach ($tfmethods as $fmethods) {
            if ($fmethods[0] == "*") {
                $cmethod = substr($fmethods, 1);
                $filename = GetParam("CORE_PUBDIR") . "/FDL/" . $cmethod;
                $fd = fopen($filename, "rb");
                $contents2 = fread($fd, filesize($filename)); // only one
                $contents2 = preg_replace('%(?:  //[^\n]*\@begin-method-ignore|  /\*+[^/]*?\@begin-method-ignore)(.*?)(?:  //[^\n]*\@end-method-ignore[^\n]*|  /\*+[^/]*?\@end-method-ignore[^/]*?\*/)%xms', '', $contents2);
                fclose($fd);
            } else {
                $filename = GetParam("CORE_PUBDIR") . "/FDL/" . $fmethods;
                $fd = fopen($filename, "rb");
                $contents.= fread($fd, filesize($filename));
                fclose($fd);
                $hasMethod = true;
            }
        }
        $contents = preg_replace('%(?:  //[^\n]*\@begin-method-ignore|  /\*+[^/]*?\@begin-method-ignore)(.*?)(?:  //[^\n]*\@end-method-ignore[^\n]*|  /\*+[^/]*?\@end-method-ignore[^/]*?\*/)%xms', '', $contents);
        $dm = new deprecatedHookManager();
        $dm->inspectContent($contents);
        $phpAdoc->set("HOOKALIAS", $dm->generateCompatibleMethods());
        $phpAdoc->Set("METHODS", str_replace(array(
            "<?php\n",
            "<?php\r\n",
            "\n?>"
        ) , "", $contents));
        $phpMethodName = sprintf("_Method_%s", $tdoc["name"]);
        $phpAdoc->set("PHPmethodName", $phpMethodName);
        $phpAdoc->set("ClassDocParent", $phpAdoc->Get("DocParent"));
        $phpAdoc->set("DocParent", '\\' . $phpMethodName);
    } else {
        $phpAdoc->Set("METHODS", "");
    }
    
    if ($cmethod != "") {
        $phpAdoc->Set("METHODS2", str_replace(array(
            "<?php\n",
            "<?php\r\n",
            "\n?>"
        ) , "", $contents2));
        $phpAdoc->Set("STARMETHOD", true);
        $phpAdoc->Set("docNameIndirect", '_SMethod_Doc' . $tdoc["id"] . "__");
        if ($hasMethod) {
            
            $phpAdoc->Set("RedirectDocParent", $phpAdoc->Get("ClassDocParent"));
            $phpAdoc->Set("ClassDocParent", '\\' . $phpAdoc->Get("docNameIndirect"));
        } else {
            $phpAdoc->Set("RedirectDocParent", $phpAdoc->Get("DocParent"));
            $phpAdoc->Set("DocParent", '\\' . $phpAdoc->Get("docNameIndirect"));
        }
    }
    return $phpAdoc->gen();
}

function AttrIdToPhp($dbaccess, $tdoc)
{
    $phpAdoc = new Layout("FDL/Layout/Class.Attrid.xml");
    
    if ($tdoc["fromid"] == 0) {
        $phpAdoc->Set("extend", '');
    } else {
        $fromName = getNameFromId($dbaccess, $tdoc["fromid"]);
        $phpAdoc->Set("extend", ucwords(strtolower(str_replace(array(
            ":",
            "-"
        ) , "_", $fromName))));
    }
    
    $phpAdoc->Set("fromid", $tdoc["fromid"]);
    $phpAdoc->Set("title", $tdoc["title"]);
    $phpAdoc->Set("className", ucfirst(strtolower(str_replace(array(
        ":",
        "-"
    ) , "_", $tdoc["name"]))));
    
    $query = new QueryDb($dbaccess, "DocAttr");
    $query->AddQuery(sprintf("docid=%d", $tdoc["id"]));
    $query->AddQuery(sprintf("id !~ ':'"));
    $query->order_by = "ordered";
    $attrs = $query->Query(0, 0, "TABLE");
    
    if ($query->nb > 0) {
        $const = array();
        foreach ($attrs as $attr) {
            $const[$attr["id"]] = array(
                "attrid" => $attr["id"],
                "type" => $attr["type"],
                "label" => $attr["labeltext"],
                "famName" => $tdoc["name"]
            );
        }
        
        $phpAdoc->SetBlockData("CONST", $const);
    }
    
    return $phpAdoc->gen();
}

function doubleslash($s)
{
    $s = str_replace('\\', '\\\\', $s);
    $s = str_replace('"', '\\"', $s);
    return $s;
}

function PgUpdateFamilly($dbaccess, $docid, $docname)
{
    $msg = '';
    $docname = strtolower($docname);
    $doc = new_Doc($dbaccess);
    $sqlTestFamily=sprintf("select table_schema, table_name from information_schema.tables where table_schema='family' and table_name='%s'" ,
            pg_escape_string($docname));
    simpleQuery($dbaccess, $sqlTestFamily, $result);
    if (count($result) == 0) {
        $msg.= "Create table family." . $docname . "\n";
        // create postgres table if new familly
        $cdoc = createTmpDoc($dbaccess, $docid, false);
        $triggers = $cdoc->sqltrigger(false, true);
        $cdoc->exec_query($triggers, 1);
        // step by step
        $cdoc->Create();
        setSqlIndex($dbaccess, $docid);
        
        simpleQuery($dbaccess, $sqlTestFamily, $result);
            if (count($result) == 0) {
            $msg.= "Cannot create Table family." . $docname."\n";
        }
    }

    // create view
    // @TODO 4456 CREATE VIEW
    if (false && $docname != "") {
        $docname = strtolower($docname);
        $err = $doc->exec_query(sprintf("SELECT oid from pg_class where relname='%s' and relnamespace=(select oid from pg_namespace where nspname='family');", $docname));
        $updateview = false;
        if ($doc->numrows() == 1) {
            // update view
            $sql = sprintf("drop view family.\"%s\"", $docname);
            $doc->exec_query($sql, 1);
            $updateview = true;
        }
        $err = $doc->exec_query(sprintf("SELECT oid from pg_class where relname='%s' and relnamespace=(select oid from pg_namespace where nspname='family');", $docname));
        if ($doc->numrows() == 0) {
            if (!$updateview) $msg.= "Create view family." . $docname . "\n";
            // create postgres table if new familly
            $sql = sprintf("create view family.\"%s\" as select * from doc%d", ($docname) , $docid);
            $doc->exec_query($sql, 1);
            
            $err = $doc->exec_query(sprintf("SELECT oid from pg_class where relname='%s' and relnamespace=(select oid from pg_namespace where nspname='family');", $docname));
            if ($doc->numrows() == 0) {
                $msg.= "Cannot create view : $err\n";
            }
        }
    }
    
    $sqlquery = sprintf("select column_name FROM information_schema.columns where table_schema='family' and table_name='%s';", $docname);
    simpleQuery($dbaccess, $sqlquery, $pgatt, true);

    // -----------------------------
    // add column attribute
    $qattr = new QueryDb($dbaccess, "DocAttr");
    $qattr->AddQuery("docid=" . $docid);
    $qattr->AddQuery("type != 'menu'");
    $qattr->AddQuery("type != 'frame'");
    $qattr->AddQuery("type != 'tab'");
    $qattr->AddQuery("type != 'action'");
    $qattr->AddQuery("id !~ '^:'");
    //  $qattr->AddQuery("type !~ '^array'"); // must be visible to know for child attributes
    $qattr->AddQuery("visibility != 'M'");
    $qattr->AddQuery("visibility != 'F'");
    $qattr->AddQuery("usefor != 'Q' or usefor is null");
    
    $oattr = $qattr->Query();
    /**
     * @var DocAttr[] $tattr
     */
    $tattr = array();
    if ($qattr->nb > 0) {
        /**
         * @var DocAttr $attr
         */
        foreach ($oattr as $ka => $attr) {
            $tattr[strtolower($attr->id) ] = $attr;
            if ($attr->type == 'file') {
                $tattr[strtolower($attr->id) . '_txt'] = $attr;
                $tattr[strtolower($attr->id) . '_vec'] = clone ($attr);
                $tattr[strtolower($attr->id) . '_vec']->type = 'tsvector';
            } else if (substr($attr->type, 0, 5) == "docid") {
                if (preg_match("/doctitle=([A-Za-z0-9_-]+)/", $attr->options, $reg)) {
                    $doctitle = $reg[1];
                    if ($doctitle == "auto") $doctitle = $attr->id . "_title";
                    $doctitle = strtolower($doctitle);
                    $tattr[$doctitle] = $attr;
                    $tattr[$doctitle]->id = $doctitle;
                    $tattr[$doctitle]->type = "text";
                }
            }
        }
        
        foreach ($tattr as $ka => $attr) {
            $attr->id = chop($attr->id);
            if (substr($attr->type, 0, 5) == "array") continue; // skip array but must be in table to search element in arrays
            if ($attr->docid == $docid) { // modify my field not inherited fields
                if (!in_array($ka, $pgatt)) {
                    $msg.= "add field $ka in table doc" . $docid . "\n";
                    $repeat = (strpos($attr->options, "multiple=yes") !== false);
                    if (!$repeat) $repeat = (isset($tattr[$attr->frameid]) && $tattr[$attr->frameid]->type == "array");
                    if (($repeat && ($attr->type != 'tsvector'))) {
                        
                        $sqltype = " text"; // for the moment all repeat are text
                        
                    } else {
                        $rtype = strtok($attr->type, "(");
                        switch ($rtype) {
                            case 'double':
                            case 'float':
                            case 'money':
                                $sqltype = " float8";
                                break;

                            case 'int':
                            case 'integer':
                                $sqltype = " int4";
                                break;

                            case 'date':
                                $sqltype = " date";
                                break;

                            case 'timestamp':
                                $sqltype = " timestamp without time zone";
                                break;

                            case 'time':
                                $sqltype = " time";
                                break;

                            case 'tsvector':
                                $sqltype = " tsvector";
                                break;

                            default:
                                $sqltype = " text";
                        }
                    }
                    $sqlquery = sprintf("ALTER TABLE family.%s add column %s %s",  pg_escape_string($docname) ,pg_escape_string ($ka), pg_escape_string($sqltype));

                    simpleQuery($dbaccess,$sqlquery);
                    
                }
            }
        }
    }
    
    return $msg;
}

function createDocFile($dbaccess, $tdoc)
{
    $GEN = getGen($dbaccess);
    $pubdir = GetParam("CORE_PUBDIR");
    $dfile = "$pubdir/FDL$GEN/Class.Doc" . $tdoc["id"] . ".php";
    
    $fphp = fopen($dfile, "w");
    if ($fphp) {
        $err = fwrite($fphp, AttrtoPhp($dbaccess, $tdoc));
        if ($err === false) {
            throw new \Dcp\Exception("cannot generate  $dfile");
        }
        fclose($fphp);
        @chmod($dfile, 0666); // write for nobody
        
    } else {
        throw new \Dcp\Exception("cannot generate  $dfile");
    }
    
    $attrfile = "$pubdir/FDL$GEN/Class.Attrid" . $tdoc["id"] . ".php";
    
    $fphp = fopen($attrfile, "w");
    if ($fphp) {
        $err = fwrite($fphp, AttrIdtoPhp($dbaccess, $tdoc));
        if ($err === false) {
            throw new \Dcp\Exception("cannot generate  $attrfile");
        }
        fclose($fphp);
        @chmod($attrfile, 0666); // write for nobody
        
    } else {
        throw new \Dcp\Exception("cannot access $attrfile");
    }
    
    return $dfile;
}

function activateTrigger($dbaccess, $docid)
{
    $cdoc = createTmpDoc($dbaccess, $docid, false);
    $msg = $cdoc->exec_query($cdoc->sqltrigger(false, true) , 1);
    $sqlcmds = explode(";", $cdoc->SqlTrigger());
    //$cdoc = new_Doc($dbacceanss, $docid);
    //  print $cdoc->SqlTrigger();
    foreach ($sqlcmds as $k => $sqlquery) {
        if ($sqlquery != "") $msg = $cdoc->exec_query($sqlquery, 1);
    }
}
function setSqlIndex($dbaccess, $docid)
{
    $cdoc = createTmpDoc($dbaccess, $docid, false);
    $indexes = $cdoc->GetSqlIndex();
    $msg = '';
    if ($indexes) {
        foreach ($indexes as $sqlIndex) $msg.= $cdoc->exec_query($sqlIndex);
    }
    return $msg;
}
/**
 * refresh PHP Class & Postgres Table Definition
 * @param string $dbaccess
 * @param int $docid
 * @return string error message
 */
function refreshPhpPgDoc($dbaccess, $docid)
{
    $query = new QueryDb($dbaccess, "DocFam");
    $query->AddQuery("doctype='C'");
    $query->AddQuery("id=$docid");
    $table1 = $query->Query(0, 0, "TABLE");
    if ($query->nb > 0) {
        $v = $table1[0];
        $df = createDocFile($dbaccess, $v);
        
        $msg = PgUpdateFamilly($dbaccess, $v["id"], $v["name"]);
        //------------------------------
        // see if workflow
        AddLogMsg($msg);
        // -----------------------------
        // activate trigger by trigger
        activateTrigger($dbaccess, $docid);
        resetSystemEnum($docid);
    }
    
    return '';
}
/**
 * reset and record system enum into docenum table
 * @param int $famid
 */
function resetSystemEnum($famid)
{
    $sql = sprintf("select * from docattr where docid=%d and type = 'enum' and (phpfile is null or phpfile='-') and options ~ 'system=yes'", $famid);
    simpleQuery('', $sql, $results);
    foreach ($results as $attr) {
        $attrid = $attr["id"];
        importDocumentDescription::recordEnum($famid, $attrid, $attr["phpfunc"], true);
    }
}
/**
 * complete attribute properties from  parent attribute
 */
function completeAttribute($dbaccess, $ta)
{
    $ta->id = substr($ta->id, 1);
    $fromid = getFamFromId($dbaccess, $ta->docid);
    $tfromid[] = $fromid;
    while ($fromid = getFamFromId($dbaccess, $fromid)) {
        $tfromid[] = $fromid;
    }
    $tfromid[] = $ta->docid; // itself
    $query = new QueryDb($dbaccess, "DocAttr");
    $query->AddQuery(GetSqlCond($tfromid, 'docid'));
    $query->AddQuery("id='" . pg_escape_string($ta->id) . "'");
    $query->order_by = "docid";
    $tas = $query->Query(0, 0, "TABLE");
    
    if ($query->nb == 0) {
        error_log("MODATTR error for " . $ta->id);
        return $ta;
    } else {
        $tw = $ta;
        
        foreach ($tas as $ta1) {
            foreach ($ta1 as $k => $v) {
                if ($v && (!$ta->$k)) $tw->$k = $v;
                if ($ta->$k == "-") $tw->$k = ""; // suppress value
                
            }
        }
        
        return $tw;
    }
}
/**
 * get parent attributes
 */
function getParentAttributes($dbaccess, $fromid)
{
    if ($fromid > 0) {
        $query = new QueryDb($dbaccess, "DocAttr");
        $query->AddQuery("docid=" . $fromid);
        
        $pa = $query->Query(0, 0, "TABLE");
        if (is_array($pa)) {
            
            $nextfromid = getFamFromId($dbaccess, $fromid);
            if ($nextfromid > 0) $pa = array_merge($pa, getParentAttributes($dbaccess, $nextfromid));
            $paf = array();
            foreach ($pa as $v) $paf[$v["id"]] = $v;
            return $paf;
        }
    }
    return array();
}
/**
 * Extract the main type and the format from a type string
 *
 * @param string $type e.g. 'array("empty")'
 *
 * @return array() struct e.g. array('type' => 'array', 'format' => '"empty"')
 */
function parseType($type)
{
    if (preg_match('/^\s*(?P<type>[a-z]+)(?P<format>\(.+\))?\s*$/i', $type, $m)) {
        /* Remove leading and trailing parenthesis from format */
        if (empty($m['format'])) $m['format'] = '';
        $m['format'] = substr($m['format'], 1, -1);
        return array(
            'type' => $m['type'],
            'format' => $m['format']
        );
    }
    return array(
        'type' => $type,
        'format' => ''
    );
}

function getTypeMain($type)
{
    $p = parseType($type);
    return $p['type'];
}

function getTypeFormat($type)
{
    $p = parseType($type);
    return $p['format'];
}
?>