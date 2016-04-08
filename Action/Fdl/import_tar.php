<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Import Set of documents and files with directories
 *
 * @author Anakeen
 * @version $Id: import_tar.php,v 1.8 2007/08/02 15:34:12 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/import_file.php");
include_once ("WHAT/Lib.Prefix.php");

define("TARUPLOAD", DEFAULT_PUBDIR . "/var/upload/");
define("TAREXTRACT", "/extract/");
define("TARTARS", "/tars/");

function getTarUploadDir(Action & $action)
{
    global $pubdir;
    $dtar = $action->getParam("FREEDOM_UPLOADDIR");
    if (substr($dtar, 0, 1) != '/') {
        $dtar = $pubdir . '/' . $dtar;
    }
    if ($dtar == "") $dtar = TARUPLOAD;
    return $dtar . "/" . $action->user->login . TARTARS;
}
function getTarExtractDir(Action & $action, $tar)
{
    global $pubdir;
    $dtar = $action->getParam("FREEDOM_UPLOADDIR");
    if (substr($dtar, 0, 1) != '/') {
        $dtar = $pubdir . '/' . $dtar;
    }
    if ($dtar == "") $dtar = TARUPLOAD;
    return $dtar . "/" . $action->user->login . TAREXTRACT . $tar . "_D";
}
/**
 * import a directory files
 * @param action $action current action
 * @param string $ftar tar file
 */
function import_tar(&$action, $ftar, $dirid = 0, $famid = 7)
{
}
/**
 * import a directory files
 * @param action $action current action
 * @param string $ldir local directory path
 * @param int $dirid folder id to add new documents
 * @param int $famid default family for raw files
 * @param int $dfldid
 * @param bool $onlycsv if true only fdl.csv file is imported
 * @param bool $analyze dry-mode it true
 * @param string $csvLinebreak default line break sequence
 * @return array
 */
function import_directory(&$action, $ldir, $dirid = 0, $famid = 7, $dfldid = 2, $onlycsv = false, $analyze = false, $csvLinebreak = '\n')
{
    // first see if fdl.csv file
    global $importedFiles;
    
    $dbaccess = $action->dbaccess;
    $tr = array();
    if (is_dir($ldir)) {
        if ($handle = opendir($ldir)) {
            $lfamid = 0;
            $lfldid = 0;
            while (false !== ($file = readdir($handle))) {
                $absfile = str_replace("//", "/", "$ldir/$file");
                if (is_file($absfile) && ($file == "fdl.csv")) {
                    $tr = analyze_csv($absfile, $dbaccess, $dirid, $lfamid, $lfldid, $analyze);
                }
            }
            if ($lfamid > 0) $famid = $lfamid; // set local default family identifier
            if ($lfldid > 0) $dfldid = $lfldid; // set local default family folder identifier
            rewinddir($handle);
            /* This is the correct way to loop over the directory. */
            $defaultdoc = createDoc($dbaccess, $famid);
            if (!$defaultdoc) $action->AddWarningMsg(sprintf(_("you cannot create this kind [%s] of document") , $famid));
            $fimgattr = null;
            if (($lfamid == 0) && ($famid == 7)) {
                $defaultimg = createDoc($dbaccess, "IMAGE");
                $fimgattr = $defaultimg->GetFirstFileAttributes();
            }
            $newdir = createDoc($dbaccess, $dfldid);
            if (!$newdir) $action->AddWarningMsg(sprintf(_("you cannot create this kind [%s] of folder") , $dfldid));
            $ffileattr = $defaultdoc->GetFirstFileAttributes();
            
            $dir = null;
            if ($dirid > 0) {
                /**
                 * @var Dir $dir
                 */
                $dir = new_Doc($dbaccess, $dirid);
            }
            
            $nfile = 0;
            while (false !== ($file = readdir($handle))) {
                $nfile++;
                $absfile = str_replace("//", "/", "$ldir/$file");
                $level = substr_count($absfile, "/");
                $index = "f$level/$nfile";
                if (is_file($absfile)) {
                    if (!$onlycsv) { // add also unmarked files
                        if (!isset($importedFiles[$absfile])) {
                            if (!isUTF8($file)) $file = utf8_encode($file);
                            if (!isUTF8($ldir)) $ldir = utf8_encode($ldir);
                            $tr[$index] = array(
                                "err" => ($defaultdoc) ? "" : sprintf(_("you cannot create this kind [%s] of document") , $famid) ,
                                "folderid" => 0,
                                "foldername" => $ldir,
                                "filename" => $file,
                                "title" => "$file",
                                "specmsg" => "",
                                "id" => "",
                                "anaclass" => "fileclass",
                                "familyid" => 0,
                                "familyname" => "",
                                "action" => ""
                            );
                            $err = AddVaultFile($dbaccess, $absfile, $analyze, $vfid);
                            
                            if ($err != "") {
                                $tr[$index]["err"] = $err;
                            } else {
                                if (($lfamid == 0) && ($famid == 7) && (substr($vfid, 0, 5) == "image")) {
                                    $ddoc = & $defaultimg;
                                    $fattr = $fimgattr->id;
                                } else {
                                    $ddoc = & $defaultdoc;
                                    if ($ffileattr) {
                                        $fattr = $ffileattr->id;
                                    } else {
                                        $tr[$index]["err"] = "no file attribute";
                                    }
                                }
                                
                                $tr[$index]["familyname"] = $ddoc->fromname;
                                $tr[$index]["familyid"] = $ddoc->fromid;
                                $tr[$index]["action"] = N_("to be add");
                                if (!$analyze) {
                                    $ddoc->Init();
                                    $ddoc->setValue($fattr, $vfid);
                                    $err = $ddoc->Add();
                                    if ($err != "") {
                                        $tr[$index]["action"] = N_("not added");
                                        $tr[$index]["err"] = $err;
                                    } else {
                                        $ddoc->addHistoryEntry(sprintf("create by import from archive %s", substr(basename($ldir) , 0, -2)));
                                        $tr[$index]["action"] = N_("added");
                                        $tr[$index]["id"] = $ddoc->id;
                                        $ddoc->postStore();
                                        $ddoc->Modify();
                                        if ($dirid > 0) {
                                            $dir->insertDocument($ddoc->id);
                                        }
                                        $tr[$index]["title"] = $ddoc->getTitle();
                                        $tr[$index]["id"] = $ddoc->id;
                                        $tr[$index]["familyid"] = $ddoc->fromid;
                                        $tr[$index]["familyname"] = $ddoc->fromname;
                                    }
                                }
                            }
                        }
                    }
                } else if (is_dir($absfile) && ($file[0] != '.')) {
                    
                    if (!isUTF8($file)) $file = utf8_encode($file);
                    if (!isUTF8($ldir)) $ldir = utf8_encode($ldir);
                    
                    if ((!$onlycsv) || (!preg_match("/^[0-9]+-.*_D$/i", $file))) {
                        $tr[$index] = array(
                            "err" => ($newdir) ? "" : sprintf(_("you cannot create this kind [%s] of folder") , $dfldid) ,
                            "folderid" => 0,
                            "foldername" => $ldir,
                            "filename" => $file,
                            "title" => "$file",
                            "specmsg" => "",
                            "id" => "",
                            "anaclass" => "fldclass",
                            "familyid" => $newdir->fromid,
                            "familyname" => $newdir->fromname,
                            "action" => N_("to be add")
                        );
                        if (!$analyze) {
                            $newdir->Init();
                            $newdir->setTitle($file);
                            $err = $newdir->Add();
                            if ($err != "") {
                                $tr[$index]["action"] = N_("not added");
                            } else {
                                $tr[$index]["action"] = N_("added");
                                $tr[$index]["id"] = $newdir->id;
                                if ($dirid > 0) {
                                    $dir->insertDocument($newdir->id);
                                }
                            }
                        }
                    }
                    $itr = import_directory($action, $absfile, $newdir->id, $famid, $dfldid, $onlycsv, $analyze);
                    $tr = array_merge($tr, $itr);
                }
            }
            closedir($handle);
            return $tr;
        }
    } else {
        $err = sprintf("cannot open local directory %s", $ldir);
        return array(
            "err" => $err
        );
    }
    return array();
}

function analyze_csv($fdlcsv, $dbaccess, $dirid, &$famid, &$dfldid, $analyze, $csvLinebreak = '\n')
{
    $tr = array();
    $fcsv = fopen($fdlcsv, "r");
    if ($fcsv) {
        $ldir = dirname($fdlcsv);
        $nline = 0;
        $nbdoc = 0;
        $tcolorder = array();
        $separator = $enclosure = "auto";
        importDocumentDescription::detectAutoCsvOptions($fdlcsv, $separator, $enclosure);
        if ($separator == '') {
            $separator = ';';
        }
        if ($enclosure == '') {
            $enclosure = '"';
        }
        while ($data = fgetcsv($fcsv, 0, $separator, $enclosure)) {
            $nline++;
            $level = substr_count($ldir, "/");
            $index = "c$level/$nline";
            $tr[$index] = array(
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
                "action" => "-"
            );
            if ($csvLinebreak) {
                $data = array_map(function ($v) use ($csvLinebreak)
                {
                    return str_replace($csvLinebreak, "\n", $v);
                }
                , $data);
            }
            switch ($data[0]) {
                    // -----------------------------------
                    
                case "DFAMID":
                    $famid = $data[1];
                    //print "\n\n change famid to $famid\n";
                    break;
                    // -----------------------------------
                    
                case "DFLDID":
                    $dfldid = $data[1];
                    //print "\n\n change dfldid to $dfldid\n";
                    break;

                case "ORDER":
                    if (is_numeric($data[1])) $orfromid = $data[1];
                    else $orfromid = getFamIdFromName($dbaccess, $data[1]);
                    
                    $tcolorder[$orfromid] = getOrder($data);
                    $tr[$index]["action"] = sprintf(_("new column order %s") , implode(" - ", $tcolorder[$orfromid]));
                    break;

                case "KEYS":
                    if (is_numeric($data[1])) $orfromid = $data[1];
                    else $orfromid = getFamIdFromName($dbaccess, $data[1]);
                    
                    $tkeys[$orfromid] = getOrder($data);
                    if (($tkeys[$orfromid][0] == "") || (count($tkeys[$orfromid]) == 0)) {
                        $tr[$index]["err"] = sprintf(_("error in import keys : %s") , implode(" - ", $tkeys[$orfromid]));
                        unset($tkeys[$orfromid]);
                        $tr[$index]["action"] = "ignored";
                    } else {
                        $tr[$index]["action"] = sprintf(_("new import keys : %s") , implode(" - ", $tkeys[$orfromid]));
                    }
                    break;

                case "DOC":
                    if (is_numeric($data[1])) $fromid = $data[1];
                    else $fromid = getFamIdFromName($dbaccess, $data[1]);
                    if (isset($tkeys[$fromid])) $tk = $tkeys[$fromid];
                    else $tk = array(
                        "title"
                    );
                    $tr[$index] = csvAddDoc($dbaccess, $data, $dirid, $analyze, $ldir, "update", $tk, array() , $tcolorder[$fromid]);
                    if ($tr[$index]["err"] == "") $nbdoc++;
                    
                    break;
                }
            }
            fclose($fcsv);
        }
        return $tr;
    }
    /**
     * decode characters wihich comes from windows zip
     * @param $s string to decode
     * @return string decoded string
     */
    function WNGBdecode($s)
    {
        $td = array(
            144 => "É",
            130 => "é",
            133 => "à",
            135 => "ç",
            138 => "è",
            151 => "ù",
            212 => "È",
            210 => "Ê",
            128 => "Ç",
            183 => "ê",
            136 => "û",
            183 => "À",
            136 => "ê",
            150 => "û",
            147 => "ô",
            137 => "ë",
            139 => "ï"
        );
        
        $s2 = $s;
        for ($i = 0; $i < strlen($s); $i++) {
            if (isset($td[ord($s[$i]) ])) $s2[$i] = $td[ord($s[$i]) ];
        }
        return $s2;
    }
    /**
     * rename file name which comes from windows zip
     * @param string $ldir directory to decode
     * @return string empty string on success, non-empty string with error message on failure
     */
    function WNGBDirRename($ldir)
    {
        $handle = opendir($ldir);
        if ($handle === false) {
            return sprintf(_("Error opening directory '%s'.") , $ldir);
        }
        while (false !== ($file = readdir($handle))) {
            if ($file[0] != ".") {
                $afile = "$ldir/$file";
                
                if (is_file($afile)) {
                    if (rename($afile, "$ldir/" . WNGBdecode($file)) === false) {
                        return sprintf(_("Error renaming '%s' to '%s'.") , $afile, WNGBdecode($file));
                    };
                } else if (is_dir($afile)) {
                    if (($err = WNGBDirRename($afile)) != '') {
                        return $err;
                    }
                }
            }
        }
        
        closedir($handle);
        if (rename($ldir, WNGBdecode($ldir)) === false) {
            return sprintf(_("Error renaming '%s' to '%s'.") , $ldir, WNGBdecode($ldir));
        }
        return '';
    }
    