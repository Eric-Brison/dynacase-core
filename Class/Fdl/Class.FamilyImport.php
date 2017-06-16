<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generation of PHP Document classes
 *
 * @author Anakeen
 * @package FDL
 * @subpackage
 */
/**
 */

namespace Dcp;

class FamilyImport
{
    /**
     * Write PHP content to destination file if PHP syntax is correct.
     *
     * - The content is first written to a temporary file next to the
     *   final destination file.
     * - The syntax of the temporary file is checked.
     * - If the syntax is correct, then the temporary file is "commited"
     *   to the destination file.
     *
     * @param string $fileName destination file
     * @param string $content  content to write
     *
     * @return string empty string on success or error message on failure
     */
    protected static function __phpLintWriteFile($fileName, $content)
    {
        $dir = dirname($fileName);
        $temp = tempnam($dir, basename($fileName) . '.tmp');
        if ($temp === false) {
            return sprintf(_("Error creating temporary file in '%s'.") , $dir);
        }
        if (file_put_contents($temp, $content) === false) {
            unlink($temp);
            return sprintf(_("Error writing content to file '%s'.") , $temp);
        }
        if (\CheckClass::phpLintFile($temp, $output) === false) {
            // Leave temp file for syntax error analysis
            return sprintf(_("Syntax error in file '%s': %s") , $temp, join("\n", $output));
        }
        if (rename($temp, $fileName) === false) {
            unlink($temp);
            return sprintf(_("Error renaming '%s' to '%s'.") , $temp, $fileName);
        }
        return '';
    }
    /**
     * Generate Class.Docxxx.php files
     *
     * @param string $dbaccess database specification
     * @param array  $tdoc     array of family definition
     *
     * @return string
     * @throws Db\Exception
     * @throws Exception
     */
    protected static function generateFamilyPhpClass($dbaccess, $tdoc)
    {
        global $action;
        
        $GEN = getGen($dbaccess);
        $phpAdoc = new \Layout("FDL/Layout/Class.Doc.xml", $action);
        
        if ($tdoc["classname"] == "") { // default classname
            if ($tdoc["fromid"] == 0) {
                $tdoc["classname"] = '\Dcp\Family\Document';
            } else {
                $tdoc["classname"] = "Doc" . $tdoc["fromid"];
            }
        } else {
            $tdoc["classname"] = '\\' . $tdoc["classname"];
        }
        if ($tdoc["fromid"] > 0) {
            $fromName = getNameFromId($dbaccess, $tdoc["fromid"]);
            if ($fromName == '') {
                throw new \Dcp\Exception("FAM0601", $tdoc["fromid"], $tdoc["name"]);
            }
            $tdoc["fromname"] = $fromName;
        } else {
            $tdoc["fromname"] = "Document";
        }
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
                if (strstr($tdoc["usefor"], 'W')) {
                    $phpAdoc->Set("pinit", '\WDoc');
                } // special init for workflow
                else {
                    $phpAdoc->Set("pinit", '\DocCtrl');
                }
            }
            $phpAdoc->Set("AParent", "ADoc" . $tdoc["fromid"]);
        }
        $phpAdoc->Set("title", $tdoc["title"]);
        $query = new \QueryDb($dbaccess, "DocAttr");
        $query->AddQuery("docid=" . $tdoc["id"]);
        $query->order_by = "ordered";
        
        $table1 = $query->Query();
        
        $phpAdoc->Set("sattr", "");
        
        $phpAdoc->set("hasattr", false);
        $pa = self::getParentAttributes($dbaccess, $tdoc["fromid"]);
        $allAttributes = [];
        if ($query->nb > 0) {
            
            $tmenu = array();
            $tfield = array();
            $tnormal = array();
            $tattr = array();
            $attrids = array();
            $tcattr = array();
            $taction = array();
            /**
             * @var $v \DocAttr
             */
            foreach ($table1 as $k => $v) {
                $type = trim(strtok($v->type, "("));
                if ($type === "docid" || $type == "account" || $type == "thesaurus") {
                    $parentDoctitle = "";
                    if (isset($pa[substr($v->id, 1) ]) && preg_match("/doctitle=([A-Za-z0-9_-]+)/", $pa[substr($v->id, 1) ]["options"], $reg)) {
                        $parentDoctitle = $reg[1];
                    }
                    // add title auto
                    if ($v->usefor !== 'Q' && preg_match("/doctitle=([A-Za-z0-9_-]+)/", $v->options, $reg)) {
                        $doctitle = $reg[1];
                        if ($doctitle === $parentDoctitle) {
                            continue;
                        }
                        if ($doctitle == "auto") {
                            $doctitle = $v->id . "_title";
                        }
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
                        $table1[$doctitle]->options = "autotitle=yes|relativeOrder=" . $v->id;
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
            $pM = new \parseFamilyMethod();
            foreach ($pa as $parentAttr) {
                $previousOrder = ""; //FamilyAbsoluteOrder::autoOrder;
                if (preg_match("/relativeOrder=([A-Za-z0-9_:-]+)/", $parentAttr["options"], $reg)) {
                    $previousOrder = strtolower($reg[1]);
                }
                if ($parentAttr["id"][0] !== ":") {
                    $allAttributes[$parentAttr["id"] . "/" . $parentAttr["docid"]] = ["id" => $parentAttr["id"], "parent" => $parentAttr["frameid"], "family" => $parentAttr["docid"], "prev" => $previousOrder, "numOrder" => intval($parentAttr["ordered"]) ];
                } else {
                    if (is_numeric($parentAttr["ordered"])) {
                        $pattern = sprintf("/%s\\/([0-9]+)/", substr($parentAttr["id"], 1));
                        
                        foreach ($allAttributes as $ka => $attrData) {
                            if (preg_match($pattern, $ka, $reg)) {
                                $allAttributes[$ka]["numOrder"] = $parentAttr["ordered"];
                            }
                        }
                    }
                }
            }
            
            foreach ($table1 as $k => $v) {
                if ($v->id[0] == ':') {
                    $v = self::completeAttribute($dbaccess, $v);
                    if (is_numeric($v->ordered)) {
                        $pattern = sprintf("/%s\\/([0-9]+)/", $v->id);
                        foreach ($allAttributes as $ka => $attrData) {
                            if (preg_match($pattern, $ka, $reg)) {
                                $allAttributes[$ka]["numOrder"] = $v->ordered;
                            }
                        }
                    }
                }
                
                $previous = ""; //FamilyAbsoluteOrder::autoOrder;
                if (preg_match("/relativeOrder=([A-Za-z0-9_:-]+)/", $v->options, $reg)) {
                    $previous = strtolower($reg[1]);
                }
                
                $allAttributes[$v->id . "/" . $v->docid] = ["id" => $v->id, "parent" => $v->frameid, "family" => $v->docid, "prev" => $previous, "numOrder" => intval($v->ordered) ];
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
                            "precond" => self::doubleslash($v->phpfunc)
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
                            "frame" => ($v->frameid == "") ? \Adoc::HIDDENFIELD : strtolower($v->frameid)
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
                            "waction" => self::doubleslash($v->phpfunc) ,
                            "precond" => str_replace("\"", "\\\"", $v->phpconstraint)
                        );
                        break;

                    default: // normal
                        if (preg_match('/^\[([a-z=0-9]+)\](.*)/', $v->phpfunc, $reg)) {
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
                            if (strpos($v->options, "multiple=yes") !== false) {
                                $repeat = "true";
                            } else {
                                if (isset($tnormal[strtolower($v->frameid) ])) {
                                    if (self::getTypeMain($tnormal[strtolower($v->frameid) ]["type"]) == "array") {
                                        $repeat = "true";
                                    }
                                }
                                
                                if (($repeat == "false") && isset($pa[strtolower($v->frameid) ])) {
                                    if (self::getTypeMain($pa[strtolower($v->frameid) ]["type"]) == "array") {
                                        $repeat = "true";
                                    }
                                }
                            }
                        }
                        
                        $atype = strtolower(trim($atype));
                        // create code for calculated attributes
                        if ((!$v->phpfile) && preg_match('/^(?:(?:[a-z_][a-z0-9_]*\\\\)*[a-z_][a-z0-9_]*)?::[a-z_][a-z0-9_]*\(/i', $v->phpfunc, $reg) && ($v->usefor != 'Q')) {
                            
                            $pM->parse($v->phpfunc);
                            $error = $pM->getError();
                            if ($error) {
                                throw new \Dcp\Exception($error);
                            }
                            if (!$pM->outputString) {
                                $oAid = $v->id;
                            } else {
                                $oAid = $pM->outputs[0];
                            }
                            $tcattr[] = array(
                                "callmethod" => self::doubleslash($v->phpfunc) ,
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
                            "options" => self::doubleslash($v->options) ,
                            //(str_replace("\"", "\\\"", $v->options) ,
                            "order" => intval($v->ordered) ,
                            "link" => str_replace("\"", "\\\"", $v->link) ,
                            "visibility" => $v->visibility,
                            "needed" => ($v->needed == "Y") ? "true" : "false",
                            "title" => ($v->title == "Y") ? "true" : "false",
                            "repeat" => $repeat,
                            "abstract" => ($v->abstract == "Y") ? "true" : "false",
                            "frame" => ($v->frameid == "") ? \Adoc::HIDDENFIELD : strtolower($v->frameid) ,
                            "elink" => $v->elink,
                            "phpfile" => $v->phpfile,
                            "phpfunc" => self::doubleslash(str_replace(", |", ",  |", $v->phpfunc)) ,
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
            FamilyAbsoluteOrder::completeForNumericOrder($allAttributes, $tdoc["id"]);
            $absoluteOrders = FamilyAbsoluteOrder::getAbsoluteOrders($allAttributes, $tdoc["id"]);
            $tAbsOrders = [];
            foreach ($absoluteOrders as $kOrder => $attrid) {
                $tAbsOrders[] = sprintf('"%s"=>%d', $attrid, ($kOrder + 1) * 10);
            }
            $phpAdoc->Set("sattr", implode(",", $attrids));
            $phpAdoc->Set("sAbsoluteOrders", implode(",", $tAbsOrders));
            $phpAdoc->SetBlockData("MATTR", $tmenu);
            $phpAdoc->SetBlockData("FATTR", $tfield);
            $phpAdoc->SetBlockData("AATTR", $taction);
            $phpAdoc->SetBlockData("NATTR", $tnormal);
            $phpAdoc->SetBlockData("ATTRFIELD", $tattr);
            
            $phpAdoc->set("hasattr", (count($tattr) > 0));
            $phpAdoc->SetBlockData("ACALC", $tcattr);
        } else {
            $phpAdoc->Set("sAbsoluteOrders", "");
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
        $contents = '';
        $contents2 = '';
        $hasMethod = false;
        if (isset($tdoc["methods"]) && ($tdoc["methods"] != "")) {
            $tfmethods = explode("\n", $tdoc["methods"]);
            foreach ($tfmethods as $fmethods) {
                if ($fmethods[0] == "*") {
                    $cmethod = substr($fmethods, 1);
                    $filename = DEFAULT_PUBDIR . "/FDL/" . $cmethod;
                    $contents2 = self::getMethodFileInnerContents($filename);
                    /* Skip empty method file */
                    if (strlen(trim($contents2)) <= 0) {
                        $cmethod = '';
                        $contents2 = '';
                    }
                } else {
                    $filename = DEFAULT_PUBDIR . "/FDL/" . $fmethods;
                    $innerContents = self::getMethodFileInnerContents($filename);
                    /* Concatenate non-empty method file */
                    if (strlen(trim($innerContents)) > 0) {
                        $contents.= $innerContents;
                        $hasMethod = true;
                    }
                }
            }
        }
        if ($hasMethod) {
            $dm = new \deprecatedHookManager();
            $dm->inspectContent("<?php\n" . $contents . "\n?>");
            $phpAdoc->set("HOOKALIAS", $dm->generateCompatibleMethods());
            $phpAdoc->Set("METHODS", $contents);
            $phpMethodName = sprintf("_Method_%s", $tdoc["name"]);
            $phpAdoc->set("PHPmethodName", $phpMethodName);
            $phpAdoc->set("ClassDocParent", $phpAdoc->Get("DocParent"));
            $phpAdoc->set("DocParent", '\\' . $phpMethodName);
        } else {
            $phpAdoc->Set("METHODS", "");
        }
        
        if ($cmethod != "") {
            $phpAdoc->Set("METHODS2", $contents2);
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
    
    protected static function AttrIdToPhp($dbaccess, $tdoc)
    {
        $phpAdoc = new \Layout("FDL/Layout/Class.Attrid.xml");
        
        if ($tdoc["fromid"] == 0) {
            $phpAdoc->Set("extend", '');
        } else {
            $fromName = getNameFromId($dbaccess, $tdoc["fromid"]);
            if ($fromName == '') {
                throw new \Dcp\Exception("FAM0602", $tdoc["fromid"], $tdoc["name"]);
            }
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
        
        $query = new \QueryDb($dbaccess, "DocAttr");
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
    
    protected static function doubleslash($s)
    {
        $s = str_replace('\\', '\\\\', $s);
        $s = str_replace('"', '\\"', $s);
        return $s;
    }
    
    protected static function pgUpdateFamily($dbaccess, $docid, $docname = "")
    {
        $docname = strtolower($docname);
        $msg = '';
        /* Create family's table if not exists */
        if (!self::tableExists($dbaccess, "public", "doc$docid")) {
            $msg.= sprintf("Create table 'doc%d'\n", $docid);
            self::createFamilyTable($dbaccess, $docid);
            
            if (self::tableExists($dbaccess, "public", "doc$docid")) {
                /* Re-create family's view */
                self::recreateFamilyView($dbaccess, $docname, $docid);
            } else {
                $msg.= sprintf("Could not create table 'doc%d'.\n", $docid);
            }
        }
        
        $pgatt = self::getTableColumns($dbaccess, "public", "doc$docid");
        // -----------------------------
        // add column attribute
        $qattr = new \QueryDb($dbaccess, "DocAttr");
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
         * @var \DocAttr[] $tattr
         */
        $tattr = array();
        if ($qattr->nb > 0) {
            /**
             * @var \DocAttr $attr
             */
            foreach ($oattr as $ka => $attr) {
                $tattr[strtolower($attr->id) ] = $attr;
                if ($attr->type == 'file') {
                    $tattr[strtolower($attr->id) . '_txt'] = $attr;
                    $tattr[strtolower($attr->id) . '_vec'] = clone ($attr);
                    $tattr[strtolower($attr->id) . '_vec']->type = 'tsvector';
                } else {
                    $type = trim(strtok($attr->type, "("));
                    if ($type === "docid" || $type === "account" || $type === "thesaurus") {
                        if ($attr->usefor !== "Q" && preg_match("/doctitle=([A-Za-z0-9_-]+)/", $attr->options, $reg)) {
                            $doctitle = $reg[1];
                            if ($doctitle == "auto") {
                                $doctitle = $attr->id . "_title";
                            }
                            $doctitle = strtolower($doctitle);
                            $tattr[$doctitle] = $attr;
                            $tattr[$doctitle]->id = $doctitle;
                            $tattr[$doctitle]->type = "text";
                        }
                    }
                }
            }
            
            $updateView = false;
            foreach ($tattr as $ka => $attr) {
                $attr->id = chop($attr->id);
                if (substr($attr->type, 0, 5) == "array") {
                    continue;
                } // skip array but must be in table to search element in arrays
                if ($attr->docid == $docid) { // modify my field not inherited fields
                    if (!in_array($ka, $pgatt)) {
                        $msg.= "add field $ka in table doc" . $docid . "\n";
                        $repeat = (strpos($attr->options, "multiple=yes") !== false);
                        if (!$repeat) {
                            $repeat = (isset($tattr[$attr->frameid]) && $tattr[$attr->frameid]->type == "array");
                        }
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
                        self::alterTableAddColumn($dbaccess, "public", "doc$docid", $ka, $sqltype);
                        $updateView = true;
                    }
                }
            }
            /* Update family's view if table structure has changed */
            if ($updateView) {
                self::recreateFamilyView($dbaccess, $docname, $docid);
            }
        }
        return $msg;
    }
    
    protected static function tableExists($dbaccess, $schemaName, $tableName)
    {
        simpleQuery($dbaccess, sprintf("SELECT 'true' FROM information_schema.tables WHERE table_schema = %s AND table_name = %s", pg_escape_literal($schemaName) , pg_escape_literal($tableName)) , $res, true, true, true);
        return ($res == 'true');
    }
    
    protected static function viewExists($dbaccess, $schemaName, $viewName)
    {
        simpleQuery($dbaccess, sprintf("SELECT 'true' FROM information_schema.views WHERE table_schema = %s AND table_name = %s", pg_escape_literal($schemaName) , pg_escape_literal($viewName)) , $res, true, true, true);
        return ($res == 'true');
    }
    
    protected static function createFamilyTable($dbaccess, $docid)
    {
        // create postgres table if new \familly
        $cdoc = createTmpDoc($dbaccess, $docid, false);
        $triggers = $cdoc->sqltrigger(false, true);
        $cdoc->exec_query($triggers, 1);
        // step by step
        $cdoc->Create();
        self::setSqlIndex($dbaccess, $docid);
    }
    
    protected static function recreateFamilyView($dbaccess, $docname, $docid)
    {
        simpleQuery($dbaccess, sprintf("CREATE OR REPLACE VIEW family.%s AS SELECT * FROM %s", pg_escape_identifier($docname) , pg_escape_identifier(sprintf("doc%s", $docid))) , $res, true, true, true);
    }
    
    protected static function getTableColumns($dbaccess, $schemaName, $tableName)
    {
        simpleQuery($dbaccess, sprintf("SELECT column_name FROM information_schema.columns WHERE table_schema = %s AND table_name = %s", pg_escape_literal($schemaName) , pg_escape_literal($tableName)) , $res, true, false, true);
        return $res;
    }
    
    protected static function alterTableAddColumn($dbaccess, $schemaName, $tableName, $columnName, $columnType)
    {
        simpleQuery($dbaccess, sprintf("ALTER TABLE %s.%s ADD COLUMN %s %s", pg_escape_identifier($schemaName) , pg_escape_identifier($tableName) , pg_escape_identifier($columnName) , $columnType) , $res, true, true, true);
    }
    
    public static function createDocFile($dbaccess, $tdoc)
    {
        $GEN = getGen($dbaccess);
        $pubdir = DEFAULT_PUBDIR;
        $dfile = "$pubdir/FDL$GEN/Class.Doc" . $tdoc["id"] . ".php";
        
        $err = self::__phpLintWriteFile($dfile, self::generateFamilyPhpClass($dbaccess, $tdoc));
        if ($err != '') {
            throw new \Dcp\Exception(sprintf("Error generating file '%s': %s", $dfile, $err));
        }
        
        $attrfile = "$pubdir/FDL$GEN/Class.Attrid" . $tdoc["id"] . ".php";
        
        $err = self::__phpLintWriteFile($attrfile, self::AttrIdtoPhp($dbaccess, $tdoc));
        if ($err != '') {
            throw new \Dcp\Exception(sprintf("Error generating file '%s': %s", $attrfile, $err));
        }
        
        return $dfile;
    }
    
    public static function activateTrigger($dbaccess, $docid)
    {
        $cdoc = createTmpDoc($dbaccess, $docid, false);
        $cdoc->exec_query($cdoc->sqltrigger(false, true) , 1);
        $sqlcmds = explode(";", $cdoc->SqlTrigger());
        //$cdoc = new_Doc($dbacceanss, $docid);
        //  print $cdoc->SqlTrigger();
        foreach ($sqlcmds as $k => $sqlquery) {
            if ($sqlquery != "") {
                $cdoc->exec_query($sqlquery, 1);
            }
        }
    }
    
    public static function setSqlIndex($dbaccess, $docid)
    {
        $cdoc = createTmpDoc($dbaccess, $docid, false);
        $indexes = $cdoc->GetSqlIndex();
        $msg = '';
        if ($indexes) {
            foreach ($indexes as $sqlIndex) {
                $msg.= $cdoc->exec_query($sqlIndex);
            }
        }
        return $msg;
    }
    /**
     * refresh PHP Class & Postgres Table Definition
     *
     * @param string $dbaccess
     * @param int    $docid
     *
     * @return string error message
     */
    public static function refreshPhpPgDoc($dbaccess, $docid)
    {
        $err = '';
        $query = new \QueryDb($dbaccess, "DocFam");
        $query->AddQuery("doctype='C'");
        $query->AddQuery("id=$docid");
        $table1 = $query->Query(0, 0, "TABLE");
        if ($query->nb > 0) {
            $v = $table1[0];
            $err = self::buildFamilyFilesAndTables($dbaccess, $v, false);
        }
        
        return $err;
    }
    
    public static function buildFamilyFilesAndTables($dbaccess, $familyData, $interactive = false)
    {
        $doc = new_Doc($dbaccess);
        $locked = false;
        $savepointed = false;
        try {
            if (($err = $doc->setMasterLock(true)) !== '') {
                throw new \Dcp\Core\Exception($err);
            }
            $locked = true;
            if (($err = $doc->savePoint(__METHOD__)) !== '') {
                throw new \Dcp\Core\Exception($err);
            }
            $savepointed = true;
            
            $phpfile = self::createDocFile($dbaccess, $familyData);
            if ($interactive) {
                print "$phpfile [" . $familyData["title"] . "(" . $familyData["name"] . ")]\n";
            }
            $msg = self::pgUpdateFamily($dbaccess, $familyData["id"], $familyData["name"]);
            if ($interactive) {
                print $msg;
            } else {
                AddLogMsg($msg);
            }
            self::activateTrigger($dbaccess, $familyData["id"]);
            self::resetSystemEnum($familyData["id"]);
            
            if (($err = $doc->commitPoint(__METHOD__)) !== '') {
                throw new \Dcp\Core\Exception($err);
            }
            $savepointed = false;
            if (($err = $doc->setMasterLock(false)) !== '') {
                throw new \Dcp\Core\Exception($err);
            }
        }
        catch(\Exception $e) {
            if ($savepointed) {
                $doc->rollbackPoint(__METHOD__);
            }
            if ($locked) {
                $doc->setMasterLock(false);
            }
            return $e->getMessage();
        }
        return '';
    }
    /**
     * reset and record system enum into docenum table
     *
     * @param int $famid
     */
    protected static function resetSystemEnum($famid)
    {
        $sql = sprintf("select * from docattr where docid=%d and type = 'enum' and (phpfile is null or phpfile='-') and options ~ 'system=yes'", $famid);
        simpleQuery('', $sql, $results);
        foreach ($results as $attr) {
            $attrid = $attr["id"];
            \importDocumentDescription::recordEnum($famid, $attrid, $attr["phpfunc"], true);
        }
    }
    /**
     * complete attribute properties from  parent attribute
     *
     * @param string $dbaccess
     * @param \DocAttr $ta
     *
     * @return mixed
     * @throws Db\Exception
     */
    protected static function completeAttribute($dbaccess, $ta)
    {
        $ta->id = substr($ta->id, 1);
        $fromid = getFamFromId($dbaccess, $ta->docid);
        $tfromid[] = $fromid;
        while ($fromid = getFamFromId($dbaccess, $fromid)) {
            $tfromid[] = $fromid;
        }
        $tfromid[] = $ta->docid; // itself
        $query = new \QueryDb($dbaccess, "DocAttr");
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
                    if ($v && (!$ta->$k)) {
                        $tw->$k = $v;
                    }
                    if ($ta->$k == "-") {
                        $tw->$k = "";
                    } // suppress value
                    
                }
            }
            
            return $tw;
        }
    }
    /**
     * get parent attributes
     *
     * @param string $dbaccess
     * @param string $fromid
     *
     * @return array
     * @throws Db\Exception
     */
    protected static function getParentAttributes($dbaccess, $fromid)
    {
        if ($fromid > 0) {
            $query = new \QueryDb($dbaccess, "DocAttr");
            $query->AddQuery(sprintf("docid=%d", $fromid));
            
            $pa = $query->Query(0, 0, "TABLE");
            if (!$pa) {
                $pa = [];
            }
            
            $nextfromid = getFamFromId($dbaccess, $fromid);
            if ($nextfromid > 0) {
                $pa = array_merge(self::getParentAttributes($dbaccess, $nextfromid) , $pa);
            }
            $paf = array();
            foreach ($pa as $v) {
                $paf[$v["id"]] = $v;
                if (preg_match("/\bdoctitle=auto\b/", $v["options"])) {
                    $vtitle = $v;
                    $vtitle["id"] = $v["id"] . "_title";
                    $vtitle["type"] = "text";
                    $vtitle["options"] = "";
                    $paf[$vtitle["id"]] = $vtitle;
                }
            }
            return $paf;
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
    public static function parseType($type)
    {
        if (preg_match('/^\s*(?P<type>[a-z]+)(?P<format>\(.+\))?\s*$/i', $type, $m)) {
            /* Remove leading and trailing parenthesis from format */
            if (empty($m['format'])) {
                $m['format'] = '';
            }
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
    
    protected static function getTypeMain($type)
    {
        $p = parseType($type);
        return $p['type'];
    }
    
    protected static function getTypeFormat($type)
    {
        $p = parseType($type);
        return $p['format'];
    }
    /**
     * Get the content of a METHOD file without the PHP opening/closing tags and
     * without the @begin-method-ignore/@end-method-ignore sections.
     *
     * @param $filename
     *
     * @return string
     */
    protected static function getMethodFileInnerContents($filename)
    {
        $contents = file_get_contents($filename);
        if ($contents === false) {
            return '';
        }
        $contents = preg_replace('%(?:  //[^\n]*\@begin-method-ignore|  /\*+[^/]*?\@begin-method-ignore)(.*?)(?:  //[^\n]*\@end-method-ignore[^\n]*|  /\*+[^/]*?\@end-method-ignore[^/]*?\*/)%xms', '', $contents);
        $contents = str_replace(array(
            "<?php\n",
            "<?php\r\n",
            "\n?>"
        ) , "", $contents);
        return (string)$contents;
    }
}
