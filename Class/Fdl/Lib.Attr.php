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
        $phpAdoc->Set("DBfromname", strtolower($tdoc["fromname"]));
    } else {
        $tdoc["fromname"] = "Document";
        $phpAdoc->Set("DBfromname", "documents");
    }
    $phpAdoc->Set("DBdocname", strtolower($tdoc["name"]));
    $phpAdoc->Set("docid", $tdoc["id"]);
    $phpAdoc->Set("include", "");
    $phpAdoc->Set("GEN", false);
    if ($tdoc["fromid"] == 0) {
        $phpAdoc->Set("DocParent", $tdoc["classname"]);
        $phpAdoc->Set("AParent", "ADoc");
        $phpAdoc->Set("fromid", "");
        $phpAdoc->Set("pinit", '\DocCtrl');
    } else {
        $parentFile = sprintf("%s", DEFAULT_PUBDIR, getFamilyFileName($tdoc["fromname"]));
        if ((!file_exists($parentFile)) || filesize($parentFile) == 0) {
            throw new \Dcp\Exception("FAM0600", $parentFile, $tdoc["name"]);
        }
        $phpAdoc->Set("fromid", $tdoc["fromid"]);
        if ($tdoc["classname"] != "Doc" . $tdoc["fromid"]) {
            $phpAdoc->Set("DocParent", $tdoc["classname"]);
            $phpAdoc->Set("pinit", $tdoc["classname"]);
            $phpAdoc->Set("include", sprintf('require_once("%s");', getFamilyFileName($tdoc["fromname"])));
        } else {
            $phpAdoc->Set("GEN", true);
            if ($tdoc["name"]) {
                $phpAdoc->Set("DocParent", '\\Dcp\\Family\\' . ucwords(strtolower($tdoc["fromname"])));
            } else {
                $phpAdoc->Set("DocParent", '\\Doc' . $tdoc["fromid"]);
            }
            $phpAdoc->Set("FileClassParent", getFamilyFileName($tdoc["fromname"]));
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
                if (isset($pa[substr($v->id, 1) ]) && preg_match("/doctitle=([A-Za-z0-9_-]+)/", $pa[substr($v->id, 1) ]["options"], $reg)) {
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
                    
                    if (preg_match("/([a-z]+)\\([\"'](.*)[\"']\\)/i", $v->type, $reg)) {
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
                        if (preg_match('/\bmultiple=yes\b/', $v->options) > 0) {
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
                        
                        $pgRepeat = ($repeat == "true") ? '[]' : '';
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
                        $attrids[$v->id].= $pgRepeat;
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

function createSearchTable($familyName)
{
    $familyName = strtolower($familyName);
    $sql = sprintf("select true from information_schema.tables where table_name='%s' and table_schema='search'", pg_escape_string($familyName));
    simpleQuery('', $sql, $tableExists, true, true);
    if (!$tableExists) {
        if ($familyName === "documents") {
            $sql = "create table search.documents (
                   id int not null,primary key (id),
                   svalues text,
                   fulltext tsvector )";
            simpleQuery('', $sql);
        } else {
            
            $sql = sprintf("select fromname from family.families where lower(name)='%s'", pg_escape_string($familyName));
            simpleQuery('', $sql, $familyParent, true, true);
            if ($familyParent === false) {
                throw new \Dcp\Exception("FAM0602", $familyName);
            }
            if (!$familyParent) $familyParent = 'documents';
            else $familyParent = strtolower($familyParent);
            $sql = sprintf("select true from information_schema.tables where table_name='%s' and table_schema='search'", $familyParent);
            simpleQuery('', $sql, $tableParentExists, true, true);
            
            if (!$tableParentExists) {
                createSearchTable($familyParent);
            }
            // try again
            $sql = sprintf("select true from information_schema.tables where table_name='%s' and table_schema='search'", $familyParent);
            simpleQuery('', $sql, $tableParentExists, true, true);
            if (!$tableParentExists) {
                throw new \Dcp\Exception("FAM0601", $familyName, $familyParent);
            }
            $sql = sprintf('create table search."%s" () inherits (search.%s);', $familyName, $familyParent);
            $sql.= sprintf('ALTER TABLE search."%s" ADD CONSTRAINT %s_pkey PRIMARY KEY(id);', $familyName, $familyName);
            $sql.= sprintf('create index %s_full on search."%s" using gin(fulltext);', $familyName, $familyName);
            simpleQuery('', $sql);
        }
    }
    $sql = sprintf("select title from family.families where lower(name)='%s'", pg_escape_string($familyName));
    simpleQuery('', $sql, $famTitle, true, true);
    $sql = sprintf("comment on table search.\"%s\" is 'for (\"%s\")';", $familyName, pg_escape_string($famTitle));
    simpleQuery('', $sql);
}
function createFileContentTable($familyName)
{
    $familyName = strtolower($familyName);
    $sql = sprintf("select true from information_schema.tables where table_name='%s' and table_schema='filecontent'", pg_escape_string($familyName));
    simpleQuery('', $sql, $tableExists, true, true);
    if (!$tableExists) {
        if ($familyName === "documents") {
            $sql = "create table filecontent.documents (
                   id int not null,primary key (id))";
            simpleQuery('', $sql);
        } else {
            
            $sql = sprintf("select fromname from family.families where lower(name)='%s'", pg_escape_string($familyName));
            simpleQuery('', $sql, $familyParent, true, true);
            if ($familyParent === false) {
                throw new \Dcp\Exception("FAM0602", $familyName);
            }
            if (!$familyParent) $familyParent = 'documents';
            else $familyParent = strtolower($familyParent);
            $sql = sprintf("select true from information_schema.tables where table_name='%s' and table_schema='filecontent'", $familyParent);
            simpleQuery('', $sql, $tableParentExists, true, true);
            
            if (!$tableParentExists) {
                createFileContentTable($familyParent);
            }
            // try again
            $sql = sprintf("select true from information_schema.tables where table_name='%s' and table_schema='filecontent'", $familyParent);
            simpleQuery('', $sql, $tableParentExists, true, true);
            if (!$tableParentExists) {
                throw new \Dcp\Exception("FAM0601", $familyName, $familyParent);
            }
            $sql = sprintf('create table filecontent."%s" () inherits (filecontent.%s);', pg_escape_string($familyName) , $familyParent);
            $sql.= sprintf('ALTER TABLE filecontent."%s" ADD CONSTRAINT %s_pkey PRIMARY KEY(id);', pg_escape_string($familyName) , pg_escape_string($familyName));
            simpleQuery('', $sql);
        }
    }
    $sql = sprintf("select title from family.families where lower(name)='%s'", pg_escape_string($familyName));
    simpleQuery('', $sql, $famTitle, true, true);
    $sql = sprintf("comment on table filecontent.\"%s\" is 'for (\"%s\")';", $familyName, pg_escape_string($famTitle));
    simpleQuery('', $sql);
    $sql = sprintf("select docattr.id, docattr.labeltext from docattr,family.families where substring(docattr.type for 4) = 'file' and docattr.docid=family.families.id and lower(family.families.name)='%s'", pg_escape_string($familyName));
    simpleQuery('', $sql, $attrIds, false, false);
    foreach ($attrIds as $attr) {
        $aid = $attr["id"];
        
        $sql = sprintf("select attributeIsMultiple('%s','%s') as multiple", pg_escape_string($familyName) , pg_escape_string($aid));
        simpleQuery('', $sql, $isMultiple, true, true);
        
        foreach (array(
            "txt",
            "vec"
        ) as $postfix) {
            $sql = sprintf("select true from information_schema.columns where table_schema ='filecontent' and table_name='%s' and column_name='%s_%s'", pg_escape_string($familyName) , pg_escape_string($aid) , $postfix);
            simpleQuery('', $sql, $columnExists, true, true);
            if (!$columnExists) {
                $sql = sprintf("alter table filecontent.\"%s\" add column \"%s_%s\" %s%s ;", pg_escape_string($familyName) , pg_escape_string($aid) , $postfix, ($postfix === 'txt') ? 'text' : 'tsvector', ($isMultiple ? '[]' : ''));
                simpleQuery('', $sql);
                if ($postfix == "vec" && !$isMultiple) {
                    $sql = sprintf("create index %s_full_%s on filecontent.\"%s\" using gin(%s_vec) ;", pg_escape_string($familyName) , pg_escape_string($aid) , pg_escape_string($familyName) , pg_escape_string($aid));
                    simpleQuery('', $sql);
                }
            }
            
            $sql = sprintf("comment on column filecontent.\"%s\".%s_%s is '%s %s';", $familyName, pg_escape_string($aid) , $postfix, ($postfix === 'txt') ? 'source text for' : "vector text for", pg_escape_string($attr["labeltext"]));
            simpleQuery('', $sql);
            $sql = sprintf("select droptrigger('filecontent', '%s')", $familyName);
            simpleQuery('', $sql);
            $sql = sprintf("create trigger full_%s BEFORE INSERT OR UPDATE ON filecontent.%s FOR EACH ROW EXECUTE PROCEDURE %s_fullvectorize();", $familyName, $familyName, $familyName);
            $sql.= sprintf("create trigger searchupt_%s AFTER INSERT OR UPDATE ON filecontent.%s FOR EACH ROW EXECUTE PROCEDURE updatesearchfilecontent();", $familyName, $familyName);
            simpleQuery('', $sql);
        }
    }
}
function PgUpdateFamilly($dbaccess, $docid, $oriDocname)
{
    $msg = '';
    $docname = strtolower($oriDocname);
    $doc = new_Doc($dbaccess);
    $sqlTestFamily = sprintf("select table_schema, table_name from information_schema.tables where table_schema='family' and table_name='%s'", pg_escape_string($docname));
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
            $msg.= "Cannot create Table family." . $docname . "\n";
        }
    }
    
    $sql = sprintf("select title from family.families where id=%d", $docid);
    simpleQuery($dbaccess, $sql, $famTitle, true, true);
    $ttmp["name"] = $oriDocname;
    $ttmp["title"] = $famTitle;
    $famTitle = getFamTitle($ttmp);
    
    if ($famTitle) {
        $sql = sprintf("comment on table %s is '%s (\"%s\")';", familyTableName($docid) , $docname, pg_escape_string($famTitle));
        simpleQuery($dbaccess, $sql);
    }
    // create view
    $needCompatibleView = (getParam("CORE_DBDOCVIEWCOMPAT") == "yes");
    if ($docname != "") {
        $docname = strtolower($docname);
        $viewName = sprintf("public.doc%s", $docid);
        $updateview = false;
        
        $sql = sprintf("select * from information_schema.views where table_schema = 'public' and table_name='doc%d'", $docid);
        simpleQuery($dbaccess, $sql, $result);
        if (count($result) > 0) {
            $sql = sprintf("drop view %s", $viewName);
            simpleQuery($dbaccess, $sql);
            $updateview = true;
        }
        if ($needCompatibleView) {
            if (!$updateview) $msg.= sprintf("Create view \"%s\"\n", $viewName);
            // create postgres table if new familly
            $sql = sprintf("create view public.\"doc%d\" as select * from family.\"%s\";", $docid, $docname);
            
            if ($famTitle) {
                $sql.= sprintf("comment on view public.\"doc%d\" is '%s (\"%s\") family';", $docid, $docname, pg_escape_string($famTitle));
            }
            simpleQuery($dbaccess, $sql);
            
            $sql = sprintf("select * from information_schema.views where table_schema = 'public' and table_name='doc%d'", $docid);
            simpleQuery($dbaccess, $sql, $result);
            if (count($result) == 0) {
                $msg.= "Cannot create view : $viewName\n";
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
            if (substr($attr->type, 0, 5) == "docid") {
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
                    $repeat = (preg_match('/\bmultiple=yes\b/', $attr->options) > 0);
                    
                    if (!$repeat) $repeat = (isset($tattr[$attr->frameid]) && $tattr[$attr->frameid]->type == "array");
                    
                    $pgRepeat = ($repeat) ? '[]' : '';
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
                    $sqltype.= $pgRepeat;
                    
                    $sqlquery = sprintf("ALTER TABLE family.%s add column %s %s;", pg_escape_string($docname) , pg_escape_string($ka) , pg_escape_string($sqltype));
                    
                    $sqlquery.= sprintf("comment on column family.%s.%s is '(%s) %s'", pg_escape_string($docname) , pg_escape_string($ka) , pg_escape_string($rtype) , pg_escape_string($attr->labeltext));
                    simpleQuery($dbaccess, $sqlquery);
                } else {
                    $sqlquery = sprintf("comment on column family.%s.%s is '(%s) %s'", pg_escape_string($docname) , pg_escape_string($ka) , pg_escape_string(strtok($attr->type, "(")) , pg_escape_string($attr->labeltext));
                    simpleQuery($dbaccess, $sqlquery);
                }
            }
        }
    }
    createSearchTable($docname);
    createFileContentTable($docname);
    
    return $msg;
}

function createDocFile($dbaccess, $tdoc)
{
    $pubdir = DEFAULT_PUBDIR;
    $dfile = "$pubdir/" . getFamilyFileName($tdoc["name"]);
    
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
    
    $attrfile = "$pubdir/FDLGEN/Class.Attrid" . ucfirst(strtolower($tdoc["name"])) . ".php";
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