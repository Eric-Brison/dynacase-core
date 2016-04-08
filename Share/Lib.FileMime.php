<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Assosiate icon with mime type
 *
 * @author Anakeen
 * @version $Id: Lib.FileMime.php,v 1.9 2008/05/06 08:20:43 marc Exp $
 * @package FDL
 * @subpackage CORE
 */
/**
 */

function getIconMimeArray()
{
    return array(
        "application/msword" => "mime-wordprocessing",
        "application/vnd.openxmlformats-officedocument.wordprocessingml.document" => "mime-wordprocessing",
        "application/ogg" => "audio-x-generic",
        "application/pdf" => "mime-pdf",
        "application/postscript" => "mime-wordprocessing",
        "application/rtf" => "mime-wordprocessing",
        "application/vnd.lotus-1-2-3" => "mime-spreadsheet",
        "application/vnd.ms-excel" => "mime-spreadsheet",
        "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" => "mime-spreadsheet",
        "application/vnd.ms-powerpoint" => "mime-presentation",
        "application/vnd.oasis.opendocument.presentation" => "mime-presentation",
        "application/vnd.openxmlformats-officedocument.presentationml.presentation" => "mime-presentation",
        "application/vnd.oasis.opendocument.spreadsheet" => "mime-spreadsheet",
        "application/vnd.oasis.opendocument.text" => "mime-wordprocessing",
        "application/vnd.oasis.opendocument.graphics" => "mime-vectorgfx",
        "application/vnd.stardivision.calc" => "mime-spreadsheet",
        "application/vnd.stardivision.impress" => "mime-presentation",
        "application/vnd.stardivision.writer" => "mime-wordprocessing",
        "application/vnd.sun.xml.calc" => "mime-spreadsheet",
        "application/vnd.sun.xml.impress" => "mime-presentation",
        "application/vnd.sun.xml.writer" => "mime-wordprocessing",
        "application/vnd.sun.xml.writer.global" => "mime-wordprocessing",
        "application/vnd.sun.xml.draw" => "mime-vectorgfx",
        "application/wordperfect" => "mime-wordprocessing",
        "application/x-7z-compressed" => "mime-tgz",
        "application/x-abiword" => "mime-wordprocessing",
        "application/x-applix-spreadsheet" => "mime-spreadsheet",
        "application/x-applix-word" => "mime-wordprocessing",
        "application/x-archive" => "mime-tar",
        "application/x-arj" => "mime-tgz",
        "application/x-bzip-compressed-tar" => "mime-tgz",
        "application/x-bzip" => "mime-tgz",
        "application/x-compressed-tar" => "mime-tgz",
        "application/x-compress" => "mime-tgz",
        "application/x-cpio-compressed" => "mime-tgz",
        "application/x-cpio" => "mime-tgz",
        "application/x-deb" => "mime-tgz",
        "application/x-dvi" => "mime-wordprocessing",
        "application/x-executable" => "application/x-executable",
        "application/x-font-afm" => "font-x-generic",
        "application/x-font-bdf" => "font-x-generic",
        "application/x-font-linux-psf" => "font-x-generic",
        "application/x-font-pcf" => "font-x-generic",
        "application/x-font-sunos-news" => "font-x-generic",
        "application/x-font-ttf" => "font-x-generic",
        "application/x-gnumeric" => "mime-spreadsheet",
        "application/x-gzip" => "mime-tgz",
        "application/xhtml+xml" => "text-html",
        "application/x-killustrator" => "mime-vectorgfx",
        "application/x-kpresenter" => "mime-presentation",
        "application/x-kspread" => "mime-spreadsheet",
        "application/x-kword" => "mime-wordprocessing",
        "application/x-ms-dos-executable" => "application/x-executable",
        "application/x-perl" => "mime-shellscript",
        "application/x-php" => "text-html",
        "application/x-python-bytecode" => "mime-shellscript",
        "application/x-rar" => "mime-tgz",
        "application/x-rpm" => "mime-rpm",
        "application/x-shellscript" => "mime-shellscript",
        "application/x-shockwave-flash" => "video-x-generic",
        "application/x-tar" => "mime-tar",
        "application/x-tarz" => "mime-tgz",
        "application/x-tex" => "mime-tex",
        "application/x-zip" => "mime-zip",
        "application/zip" => "mime-zip",
        "application" => "mime-zip",
        "audio" => "mime-sound",
        "image" => "mime-image2",
        "video" => "mime-video",
        "image/jpeg" => "mime-image",
        "text/html" => "mime-html",
        "text/xml" => "mime-xml",
        "text/x-c++" => "mime-source_cpp",
        "text/x-c" => "mime-source_c",
        "text/x-c-header" => "mime-source_h",
        "text/x-php" => "mime-source_php",
        "text" => "mime-txt",
        "text/vnd.wap.wml" => "mime-html",
        "text/x-csh" => "mime-shellscript",
        "text/x-python" => "mime-shellscript",
        "text/x-sh" => "mime-shellscript",
        "text/x-vcalendar" => "mime-vcalendar",
        "text/x-vcard" => "mime-address-book",
        "text/x-zsh" => "mime-shellscript",
        "text/x-makefile" => "mime-make",
        "x-font-afm" => "font-x-generic"
    );
}

function getIconMimeFile($sysmime)
{
    $icon = "";
    if ($sysmime) {
        $tmime = explode(";", $sysmime);
        $mime = trim($tmime[0]);
        $mimeIcon = getIconMimeArray();
        if (isset($mimeIcon[$mime])) {
            $icon = $mimeIcon[$mime] . ".png";
        } else {
            $p = strpos($mime, '/');
            $mime = substr($mime, 0, $p);
            if (isset($mimeIcon[$mime])) {
                $icon = $mimeIcon[$mime] . ".png";
            }
        }
    }
    return $icon;
}
/**
 * return system file mime
 * @param string $f filename
 * @param string $fn basename of file (can be different of real path)
 * return string mime like text/html
 */
function getSysMimeFile($f, $fn = "")
{
    if (!file_exists($f)) return false;
    
    $ret = getMimeFile($fn, 'sys');
    if ($ret !== false) {
        return $ret;
    }
    
    $sys = trim(shell_exec(sprintf("file --mime -b %s", escapeshellarg($f))));
    $txt = getTextMimeFile($f);
    $sys = strtok($sys, " ;\n\t"); // only first part
    // correct errors of file function
    if (preg_match('/Makefile/', $fn)) return 'text/x-makefile';
    if (preg_match('/ASCII C\+\+/', $txt)) {
        if (preg_match('/\.h$/', $fn)) return 'text/x-c-header';
        return 'text/x-c++';
    }
    if (preg_match('/PHP script/', $txt)) return 'text/x-php';
    // open office archive
    if (preg_match('/zip$/', $sys) || (preg_match('/octet-stream$/', $sys))) {
        
        if (preg_match('/OpenOffice/', $txt)) {
            if (preg_match('/\.sxw$/', $fn)) return 'application/vnd.sun.xml.writer';
            if (preg_match('/\.sxc$/', $fn)) return 'application/vnd.sun.xml.calc';
            if (preg_match('/\.sxi$/', $fn)) return 'application/vnd.sun.xml.impress';
            if (preg_match('/\.sxd$/', $fn)) return 'application/vnd.sun.xml.draw';
            if (preg_match('/\.sxg$/', $fn)) return 'application/vnd.sun.xml.writer.global';
            return 'application/vnd.sun.xml.writer';
        }
        if (preg_match('/OpenDocument/', $txt)) {
            if (preg_match('/\.odp$/', $fn)) return 'application/vnd.oasis.opendocument.presentation';
            if (preg_match('/\.odt$/', $fn)) return 'application/vnd.oasis.opendocument.text';
            if (preg_match('/\.ods$/', $fn)) return 'application/vnd.oasis.opendocument.spreadsheet';
            if (preg_match('/\.odg$/', $fn)) return 'application/vnd.oasis.opendocument.graphics';
            return 'application/vnd.oasis.opendocument.text';
        }
        
        if (preg_match('/\.sxw$/', $fn)) return 'application/vnd.sun.xml.writer';
        if (preg_match('/\.sxc$/', $fn)) return 'application/vnd.sun.xml.calc';
        if (preg_match('/\.sxi$/', $fn)) return 'application/vnd.sun.xml.impress';
        if (preg_match('/\.sxd$/', $fn)) return 'application/vnd.sun.xml.draw';
        if (preg_match('/\.sxg$/', $fn)) return 'application/vnd.sun.xml.writer.global';
        if (preg_match('/\.odp$/', $fn)) return 'application/vnd.oasis.opendocument.presentation';
        if (preg_match('/\.odt$/', $fn)) return 'application/vnd.oasis.opendocument.text';
        if (preg_match('/\.ods$/', $fn)) return 'application/vnd.oasis.opendocument.spreadsheet';
        if (preg_match('/\.odg$/', $fn)) return 'application/vnd.oasis.opendocument.graphics';
    }
    if (!$sys) {
        // try with text only
        if ($txt) {
            if (preg_match('/^XML/', $txt)) return 'text/xml';
        }
        
        if (preg_match('/\.xls$/', $fn)) return 'application/vnd.ms-excel';
        if (preg_match('/\.doc$/', $fn)) return 'application/msword';
        if (preg_match('/\.ppt$/', $fn)) return 'application/vnd.ms-powerpoint';
    }
    if ($sys == 'application/msword') { // for old sys mime info
        if (preg_match('/\.xls$/', $fn)) return 'application/vnd.ms-excel';
        if (preg_match('/\.ppt$/', $fn)) return 'application/vnd.ms-powerpoint';
    }
    
    return strtok($sys, " ;\n\t");
}
function getTextMimeFile($f, $fn = '')
{
    $ret = getMimeFile($fn, 'text');
    if ($ret !== false) {
        return $ret;
    }
    
    $txt = trim(shell_exec(sprintf("file -b %s", escapeshellarg($f))));
    if ($txt == 'data') {
        if (preg_match('/\.ods$/', $f)) $txt = 'OpenDocument Spreadsheet';
        else if (preg_match('/\.odt$/', $f)) $txt = 'OpenDocument Text';
        else if (preg_match('/\.odp$/', $f)) $txt = 'OpenDocument Presentation';
        else if (preg_match('/\.odg$/', $f)) $txt = 'OpenDocument Drawing';
    }
    if (!$txt) return " ";
    return $txt;
}
/**
 * get current extension from system mime
 * @return string (empty string if no extension found)
 */
function getExtension($smime)
{
    include_once ("MimeExtension.php");
    $exts = getExtensions();
    if (!empty($exts[$smime])) {
        return strtok($exts[$smime], " \n\t");
    }
    return "";
}
/**
 * get extension from file name
 */
function getFileExtension($filename)
{
    $ext = false;
    if ($filename && strrpos($filename, '.') > 0) $ext = substr($filename, strrpos($filename, '.') + 1);
    return $ext;
}
/**
 * get MIME type/text from mime.conf and mime-user.conf files
 */
function getMimeFile($filename, $type = 'sys')
{
    $conf_user = loadUserMimeConf();
    $conf_global = loadMimeConf();
    
    $conf = array_merge($conf_user, $conf_global);
    
    foreach ($conf as $rule) {
        $ext = $rule['ext'];
        if (preg_match("/\.\Q$ext\E$/i", $filename)) {
            return $rule[$type];
        }
    }
    
    return false;
}
/**
 * load mime-user.conf XML file into PHP array
 */
function loadUserMimeConf()
{
    include_once ('WHAT/Lib.Prefix.php');
    
    $rules = array();
    
    $conf_file = DEFAULT_PUBDIR . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "mime-user.conf";
    if (!file_exists($conf_file) || !is_readable($conf_file)) {
        return $rules;
    }
    
    $xml = simplexml_load_file($conf_file);
    if ($xml === false) {
        error_log(__FUNCTION__ . " " . sprintf("Could not load user MIME config '%s'.", $conf_file));
        return $rules;
    }
    
    foreach ($xml->mime as $mimeNode) {
        $rule = array();
        foreach (array(
            'ext',
            'sys',
            'text'
        ) as $attrName) {
            $rule[$attrName] = (string)$mimeNode[$attrName];
        }
        array_push($rules, $rule);
    }
    
    return $rules;
}
/**
 * load mime.conf XML file into PHP array
 */
function loadMimeConf()
{
    include_once ('WHAT/Lib.Prefix.php');
    
    $rules = array();
    
    $conf_file = DEFAULT_PUBDIR . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "mime.conf";
    $xml = simplexml_load_file($conf_file);
    if ($xml === false) {
        error_log(__FUNCTION__ . " " . sprintf("Could not load MIME config '%s'.", $conf_file));
        return $rules;
    }
    
    foreach ($xml->mime as $mimeNode) {
        $rule = array();
        foreach (array(
            'ext',
            'sys',
            'text'
        ) as $attrName) {
            $rule[$attrName] = (string)$mimeNode[$attrName];
        }
        array_push($rules, $rule);
    }
    
    return $rules;
}
/**
 * get number of pages from pdf file
 * @return int
 */
function getPdfNumberOfPages($file)
{
    $nbpages = 0;
    if (file_exists($file)) {
        $nbpages = intval(trim(shell_exec(sprintf('grep -c "/Type[[:space:]]*/Page\>" %s', escapeshellarg($file)))));
    }
    return $nbpages;
}
?>
