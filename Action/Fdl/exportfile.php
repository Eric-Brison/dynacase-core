<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Export Vault Files
 *
 * @author Anakeen
 * @version $Id: exportfile.php,v 1.21 2008/05/20 15:26:48 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Doc.php");
include_once ("FDL/Class.DocAttr.php");
include_once ("FDL/Lib.Vault.php");
include_once ("VAULT/Class.VaultFile.php");

define("RESIZEDIR", DEFAULT_PUBDIR . "/var/cache/file/");
// --------------------------------------------------------------------
function exportfile(Action & $action)
{
    $dbaccess = $action->dbaccess;
    $usage = new ActionUsage($action);
    $usage->setDefinitionText("Download document attached file");
    $docid = $usage->addOptionalParameter("docid", "document identifier", null, 0);
    if (!$docid) {
        $docid = $usage->addHiddenParameter("id", "document identifier");
    }
    
    $vid = $usage->addHiddenParameter("vid", "vault file identifier - used for tmp file"); // only for construct url
    $usage->addHiddenParameter("filename", "vault file name - not used"); // only for construct url
    $attrid = $usage->addOptionalParameter("attrid", "attribute identifier");
    $vaultid = $usage->addOptionalParameter("vaultid", "public file identifier");
    
    if (!$docid && $attrid === "-" && $vid > 0) {
        $vaultid = $vid;
    }
    $index = $usage->addOptionalParameter("index", "attribute index identifier");
    $imgwidth = $usage->addOptionalParameter("width", "image width use only when file is image");
    $inline = ($usage->addOptionalParameter("inline", "inline download", array(
        "yes",
        "no"
    )) == "yes");
    $cache = ($usage->addOptionalParameter("cache", "use http cache", array(
        "yes",
        "no"
    ) , "yes") == "yes");
    $latest = $usage->addOptionalParameter("latest", "use latest revision", array(
        "Y",
        "N"
    ));
    $state = $usage->addOptionalParameter("state", "search doc in this state");
    $type = $usage->addOptionalParameter("type", "transformation type", array(
        "png",
        "pdf"
    ));
    $pngpage = $usage->addOptionalParameter("page", "page number if type=pdf");
    
    $cvViewId = $usage->addOptionalParameter("cvViewid", "view control id");
    
    $usage->setStrictMode(false);
    $usage->verify();
    $isControled = false;
    $othername = '';
    if ($vaultid == 0) {
        
        $doc = new_Doc($dbaccess, $docid);
        if ($state != "") {
            $docid = $doc->getRevisionState($state, true);
            if ($docid == 0) {
                header('HTTP/1.0 404 Document Not Found');
                $action->exitError(sprintf(_("Document %s in %s state not found") , $doc->title, _($state)));
            }
            $doc = new_Doc($dbaccess, $docid);
        } else {
            if (($latest == "Y") && ($doc->locked == - 1)) {
                // get latest revision
                $docid = $doc->getLatestId();
                $doc = new_Doc($dbaccess, $docid);
            }
        }
        if (!$doc->isAffected()) {
            header('HTTP/1.0 404 Document Not Found');
            $action->exitError(sprintf(_("Document %s not found") , $docid));
        }
        // ADD CONTROL ACCESS HERE
        $err = $doc->control("view");
        if ($err != "") {
            header('HTTP/1.0 403 Forbidden');
            $action->exiterror($err);
        }
        $isControled = true;
        $oa = null;
        
        if ($attrid !== "icon") {
            $oa = $doc->getAttribute($attrid);
            if (!$oa) {
                header('HTTP/1.0 404 Attribute Not Found');
                $action->exitError(sprintf(_("attribute %s not found") , $attrid));
            } else {
                if ($oa->type !== "file" && $oa->type !== "image") {
                    header('HTTP/1.0 403 Incorrect Attribute');
                    $action->exitError(sprintf("Not file attribute : %s ", $attrid));
                }
                
                if ($cvViewId != "" && $doc->cvid > 0) {
                    /**
                     * @var \Dcp\Family\Cvdoc $cvdoc
                     */
                    $cvdoc = new_Doc($dbaccess, $doc->cvid);
                    $cvdoc = clone $cvdoc;
                    $cvdoc->Set($doc);
                    /*
                     * Apply mask from requested view
                    */
                    $err = $cvdoc->control($cvViewId); // control special view
                    if ($err != "") {
                        header('HTTP/1.0 403 Forbidden');
                        $action->exitError($err);
                    }
                    $tview = $cvdoc->getView($cvViewId);
                    if ($tview) {
                        $mask = $tview["CV_MSKID"];
                        if ($mask) {
                            $doc->applyMask($mask);
                        }
                    }
                } else {
                    $doc->applyMask(Doc::USEMASKCVVIEW);
                }
                if ($oa->mvisibility == "I") {
                    header('HTTP/1.0 403 Forbidden');
                    $action->exitError(sprintf(_("Cannot see attribute %s") , $attrid));
                }
            }
        }
        
        $ovalue = getExportFileDocumentValue($doc, $attrid, $index);
        
        if ($ovalue == "") {
            header('HTTP/1.0 404 File Not Found');
            print (sprintf(_("no file referenced for %s document") , $doc->title));
            exit;
        }
        
        if ($oa && $oa->getOption("preventfilechange") == "yes") {
            if (preg_match(PREGEXPFILE, $ovalue, $reg)) {
                $vaultid = $reg[2];
                $othername = vault_uniqname($vaultid);
            }
        }
        
        preg_match(PREGEXPFILE, $ovalue, $reg);
        $vaultid = $reg["vid"];
        $mimetype = $reg["mime"];
        $fileName = $reg["name"];
        
        $fileInfo = \Dcp\VaultManager::getFileInfo($vaultid);
        
        if ($fileName !== $fileInfo->name) {
            //  header('HTTP/1.0 403 Forbidden');
            //  $action->exitError(sprintf("Incorrect file identifier"));
            
        }
    } else {
        $mimetype = "";
    }
    
    DownloadVault($action, $vaultid, $isControled, $mimetype, $imgwidth, $inline, $cache, $type, $pngpage, $othername);
    exit;
}

function getExportFileDocumentValue(\Doc $doc, $attrid, $index = - 1)
{
    
    if ($doc->control("view")) {
        return "";
    }
    
    if ($doc->doctype == "C") {
        /**
         * @var DocFam $doc
         */
        $ovalue = $doc->getParameterRawValue($attrid);
        if (!$ovalue) $ovalue = $doc->getDefValue($attrid);
    } else $ovalue = $doc->getRawValue($attrid);
    
    if (ctype_digit((string)$index) && ((int)$index >= 0)) {
        $index = (int)$index;
        $tvalue = Doc::rawValueToArray($ovalue);
        if (!isset($tvalue[$index])) {
            header('HTTP/1.0 404 File Not Found');
            throw new Exception(sprintf(_("File not found at index '%s'") , $index));
        }
        $ovalue = $tvalue[$index];
    }
    
    if ($attrid !== "icon") {
        $oa = $doc->getAttribute($attrid);
        if (!$oa) {
            header('HTTP/1.0 404 Attribute Not Found');
            throw new Exception(sprintf(_("attribute %s not found") , $attrid));
        } else {
            if ($oa->type !== "file" && $oa->type !== "image") {
                header('HTTP/1.0 403 Incorrect Attribute');
                throw new Exception(sprintf("Not file attribute : %s ", $attrid));
            }
            $doc->applyMask(Doc::USEMASKCVVIEW);
            if ($oa->mvisibility == "I") {
                header('HTTP/1.0 403 Forbidden');
                throw new Exception(sprintf(_("Cannot see attribute %s") , $attrid));
            }
        }
    } else {
        $ovalue = $doc->icon;
    }
    
    return $ovalue;
}

function getExportFileDocumentPath(\Doc $doc, $attrid, $index = - 1)
{
    $filePath = "";
    
    $fileValue = getExportFileDocumentValue($doc, $attrid, $index);
    
    if ($fileValue && preg_match(PREGEXPFILE, $fileValue, $reg)) {
        $vaultid = $reg["vid"];
        $fileInfo = \Dcp\VaultManager::getFileInfo($vaultid);
        $filePath = $fileInfo->path;
    }
    return $filePath;
}
/**
 * Idem like exportfile instead that download first file attribute found
 * @param Action $action
 * @throws \Dcp\Core\Exception
 */
function exportfirstfile(Action & $action)
{
    
    $dbaccess = $action->dbaccess;
    $docid = $action->getArgument("docid", $action->getArgument("id", 0));
    
    $doc = new_Doc($dbaccess, $docid);
    if (!$doc->isAffected()) {
        $action->exitError(sprintf(_("unknow document reference '%s'") , $docid));
    }
    $attr = $doc->GetFirstFileAttributes();
    if (!$attr) $action->exiterror(_("no attribute file found"));
    
    setHttpVar("attrid", $attr->id);
    
    exportfile($action);
}
// --------------------------------------------------------------------
function DownloadVault(Action & $action, $vaultid, $isControled, $mimetype = "", $width = "", $inline = false, $cache = true, $type = "", $pngpage = 0, $othername = '')
{
    $dbaccess = $action->dbaccess;
    $vf = newFreeVaultFile($dbaccess);
    /**
     * @var vaultFileInfo $info
     */
    $info = null;
    if ($type == "pdf") {
        $teng_name = 'pdf';
        $err = $vf->Show($vaultid, $info, $teng_name);
        if ($err != "") $err = sprintf(_("PDF conversion not found")) . "\n$err";
    } else {
        $err = $vf->Show($vaultid, $info);
        if (substr($info->mime_s, 0, 5) == "image") {
            $type = "original";
        }
        
        if ($type == "png") {
            $teng_name = 'pdf';
            $err = $vf->Show($vaultid, $info, $teng_name);
            if ($err == "") {
                $filecache = sprintf("%s/var/cache/file/vid-%s-%d.png", DEFAULT_PUBDIR, $info->id_file, $pngpage);
                if (file_exists($filecache)) {
                    //  print_r2($filecache);
                    $resample = true;
                    if ($resample) {
                        $filename = $filecache;
                        $imageSize = getimagesize($filename);
                        if ($imageSize === false) {
                            $err = ErrorCode::getError('FILE0001', $filename);
                            error_log($err);
                            sendimgerror($err);
                        }
                        $owidth = $imageSize[0];
                        $oheight = $imageSize[1];
                        if ($owidth == 0 || $oheight == 0) {
                            $err = ErrorCode::getError('FILE0002', $owidth, $oheight, $filename);
                            error_log($err);
                            sendimgerror($err);
                        }
                        $newwidth = $width;
                        $newheight = $oheight * ($width / $owidth);
                        // chargement
                        $thumb = imagecreatetruecolor($newwidth, $newheight);
                        $source = imagecreatefrompng($filename);
                        // Redimensionnement
                        imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $owidth, $oheight);
                        // Affichage
                        header('Content-type: image/png');
                        imagepng($thumb);
                        exit;
                    } else {
                        Http_DownloadFile($filecache, $info->name . ".png", "image/png", $inline, $cache);
                        exit;
                    }
                }
                
                $cible = uniqid(getTmpDir() . "/thumb") . ".png";
                if (!$width) $width = 150;
                $quality = 200;
                $resample = false;
                // option 1
                //$cmd=sprintf("gs -q -dSAFER -dBATCH -dNOPAUSE -sDEVICE=png16m -r%d -sOutputFile=- -dFirstPage=%d -dLastPage=%d %s | convert -  -thumbnail %s %s",   min(intval($width/8.06),$quality),$pngpage+1,$pngpage+1,$info->path,$width,$cible);
                // option 2
                $cmd = sprintf("convert -strip -thumbnail %s -auto-orient -density %d %s[%d] %s 2>&1", escapeshellarg($width) , $quality, escapeshellarg($info->path) , $pngpage, escapeshellarg($cible));
                // option 3
                //$cmd=sprintf("gs -dSAFER -dBATCH -dNOPAUSE -sDEVICE=png16m -r%d -sOutputFile=%s -dFirstPage=%d -dLastPage=%d %s",		   min(intval($width/8.06),$quality),$cible,$pngpage+1,$pngpage+1,$info->path);
                // option 4
                //$cmd=sprintf("gs -dSAFER -dBATCH -dNOPAUSE -sDEVICE=png16m -r%d -sOutputFile=%s -dFirstPage=%d -dLastPage=%d %s",		   min(intval($width/8.06),$quality),$cible,$pngpage+1,$pngpage+1,$info->path); $resample=true;
                // option 5
                //$cmd=sprintf("gs -dSAFER -dBATCH -dNOPAUSE -sDEVICE=png16m -r%d -sOutputFile=%s -dFirstPage=%d -dLastPage=%d %s", intval($width/8.06),$cible,$pngpage+1,$pngpage+1,$info->path);
                exec($cmd, $out, $ret);
                
                if ($ret == 1) $err = implode("\n", $out);
                if (file_exists($cible)) {
                    // update cache
                    if ($pngpage == 0) {
                        createPdf2Png($info->path, $info->id_file);
                    }
                    if ($resample) {
                        $filename = $cible;
                        $imageSize = getimagesize($filename);
                        if ($imageSize === false) {
                            $err = ErrorCode::getError('FILE0003', $filename);
                            error_log($err);
                            sendimgerror($err);
                        }
                        $owidth = $imageSize[0];
                        $oheight = $imageSize[1];
                        if ($owidth == 0 || $oheight == 0) {
                            $err = ErrorCode::getError('FILE0004', $owidth, $oheight, $filename);
                            error_log($err);
                            sendimgerror($err);
                        }
                        list($owidth, $oheight) = getimagesize($filename);
                        $newwidth = $width;
                        $newheight = $oheight * ($width / $owidth);
                        // chargement
                        $thumb = imagecreatetruecolor($newwidth, $newheight);
                        $source = imagecreatefrompng($filename);
                        // Redimensionnement
                        imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $owidth, $oheight);
                        // Affichage
                        header('Content-type: image/png');
                        imagepng($thumb);
                        exit;
                    } else {
                        Http_DownloadFile($cible, $info->name . ".png", "image/png", $inline, $cache);
                    }
                    unlink($cible);
                    exit;
                } else $err = sprintf(_("cannot get image transformation for %s") , $info->name) . "\n$err";
            } else {
                $vf = newFreeVaultFile($dbaccess);
                $vf->Show($vaultid, $info);
                if ($info) $err = sprintf(_("conversion png not found for %s") , $info->name) . "\n$err";
            }
        } else {
            
            if ((substr($info->mime_s, 0, 5) == "image") && ($width > 0)) {
                $dest = rezizelocalimage($info->path, $width, $width . "-" . $info->id_file . ".png");
                if ($dest) Http_DownloadFile($dest, $info->name . ".png", "image/png", $inline);
            }
        }
    }
    
    if ($err != "") {
        sendimgerror($err);
        //  Http_DownloadFile("FREEDOM/Images/doc.gif", "unknow", "image/gif");
        
    } else {
        if ($info->mime_s) $mimetype = $info->mime_s;
        //Header("Location: $url");
        if ($isControled || ($info->public_access) || ($info->id_tmp)) {
            $vf->storage->updateAccessDate($info->id_file);
            
            if (($mimetype != "image/jpeg") || ($width == 0)) {
                if ($othername) $info->name = $othername;
                Http_DownloadFile($info->path, $info->name, $mimetype, $inline, $cache);
            } else {
                $filename = $info->path;
                
                $name = str_replace('"', '-', $info->name);
                $uName = iconv("UTF-8", "ASCII//TRANSLIT", $name);
                $name = rawurlencode($name);
                if ($inline) {
                    global $_SERVER;
                    $nav = $_SERVER['HTTP_USER_AGENT'];
                    $pos = strpos($nav, "MSIE");
                    if ($pos) {
                        // add special header for extension
                        header("Content-Disposition: inline;filename=\"$uName\";filename*=UTF-8''$name;");
                    }
                } else {
                    header("Content-Disposition: attachment;filename=\"$uName\";filename*=UTF-8''$name;");
                }
                //	  header("Cache-Control: private, max-age=3600"); // use cache client (one hour) for speed optimsation
                // header("Expires: ".gmdate ("D, d M Y H:i:s T\n",time()+3600));  // for mozilla
                // header("Pragma: "); // HTTP 1.0
                header('Content-type: image/jpeg');
                // Calcul des nouvelles dimensions
                list($owidth, $oheight) = getimagesize($filename);
                $newwidth = $width;
                $newheight = $oheight * ($width / $owidth);
                // chargement
                $thumb = imagecreatetruecolor($newwidth, $newheight);
                $source = imagecreatefromjpeg($filename);
                // Redimensionnement
                imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $owidth, $oheight);
                // Affichage
                imagejpeg($thumb);
                exit;
            }
            if (!$info->public_access) AddlogMsg(sprintf(_("%s has be sended") , $info->name));
        } else {
            $action->exiterror(_("file must be controlled : read permission needed"));
        }
    }
    
    exit;
}
/**
 * send a text in a image file
 * @param string $text text to display in the image
 */
function sendimgerror($text)
{
    // Set font size
    $font_size = 4;
    
    if (seems_utf8($text)) $text = utf8_decode($text); // support only iso8859
    $ts = explode("\n", $text);
    $width = 0;
    foreach ($ts as $string) {
        $width = max($width, strlen($string));
    }
    // Create image width dependant on width of the string
    $width = imagefontwidth($font_size) * $width;
    // Set height to that of the font
    $height = imagefontheight($font_size) * count($ts);
    $el = imagefontheight($font_size);
    $em = imagefontwidth($font_size);
    // Create the image pallette
    $img = imagecreatetruecolor($width, $height);
    // Dark red background
    $bg = imagecolorallocate($img, 0xAA, 0x00, 0x00);
    // White font color
    imagefilledrectangle($img, 0, 0, $width, $height, $bg);
    $color = imagecolorallocate($img, 255, 255, 255);
    
    foreach ($ts as $k => $string) {
        // Length of the string
        $len = strlen($string);
        // Y-coordinate of character, X changes, Y is static
        // Loop through the string
        for ($i = 0; $i < $len; $i++) {
            // Position of the character horizontally
            $xpos = $i * $em;
            $ypos = $k * $el;
            // Draw character
            imagechar($img, $font_size, $xpos, $ypos, $string, $color);
            // Remove character from string
            $string = substr($string, 1);
        }
    }
    // Return the image
    header("Content-Type: image/png");
    imagepng($img);
    // Remove image
    imagedestroy($img);
    exit;
}

function rezizelocalimage($img, $size, $basedest)
{
    $source = $img;
    
    $dest = RESIZEDIR . $basedest;
    
    if (!is_dir(RESIZEDIR)) {
        mkdir(RESIZEDIR);
    }
    if (!file_exists($dest)) {
        $cmd = sprintf("convert -strip -auto-orient -thumbnail %d %s %s", $size, escapeshellarg($source) , escapeshellarg($dest));
        system($cmd);
        if (file_exists($dest)) return $dest;
    } else {
        return $dest;
    }
    return false;
}

function createPdf2Png($file, $vid)
{
    if (file_exists($file) && ($vid > 0)) {
        $density = 200;
        $width = 1200;
        $nbpages = trim(shell_exec(sprintf('grep -c "/Type[[:space:]]*/Page\>" %s', escapeshellarg($file))));
        /* Remove existing cached files for the given $vid */
        $dh = opendir(sprintf("%s/var/cache/file", DEFAULT_PUBDIR));
        if ($dh !== false) {
            while (($f = readdir($dh)) !== false) {
                if ($f == '.' || $f == '..') {
                    continue;
                }
                if (preg_match(sprintf('/^vid-%s-/', preg_quote($vid, '/')) , $f)) {
                    unlink(sprintf("%s/var/cache/file/%s", DEFAULT_PUBDIR, $f));
                }
            }
        }
        /* Generate pages in background */
        $cmd = array();
        for ($i = 0; $i < $nbpages; $i++) {
            $cible = DEFAULT_PUBDIR . "/var/cache/file/vid-${vid}-${i}.png";
            $cmd[] = sprintf("nice convert -strip -interlace plane -thumbnail %d  -density %d %s[%d] %s", $width, $density, escapeshellarg($file) , $i, escapeshellarg($cible));
        }
        bgexec($cmd, $result, $err);
    }
}
