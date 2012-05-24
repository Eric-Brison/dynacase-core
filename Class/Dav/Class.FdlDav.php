<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * FREEDOM File system
 *
 * @author Anakeen 2006
 * @version $Id: Class.FdlDav.php,v 1.18 2008/12/01 16:29:18 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 */

require_once "DAV/Class.ServerDav.php";
require_once "System.php";
/**
 * Filesystem access using WebDAV
 *
 * @access public
 */
class HTTP_WebDAV_Server_Freedom extends HTTP_WebDAV_Server
{
    /**
     * Root directory for WebDAV access
     *
     * Defaults to webserver document root (set by ServeRequest)
     *
     * @access private
     * @var    string
     */
    var $base = "";
    
    public $db_freedom = "user=anakeen dbname=freedom";
    /**
     * Type : 'webdav' or 'freedav'
     *
     * Default type is 'webdav'
     */
    public $type = 'webdav';
    /**
     * Root directory id for WebDAV access
     *
     * Defaut root is 9 (freedom root folder)
     *
     * @var    int
     */
    public $racine = 9;
    /**
     * Maximum number of documents listed in a folder
     *
     * Default is 'ALL' (no limit)
     *
     * @var int
     */
    private $folder_max_item = 'ALL';
    /**
     * PgSQL database for property/locking information storage
     *
     * @access private
     * @var    string
     */
    public $db_webdav = "dbname=webdav user=anakeen";
    
    private $db_res; // db ressource
    function __construct($dbaccess = "")
    {
        // establish connection to property/locking db
        if ($dbaccess != "") $this->db_webdav = $dbaccess;
        
        $this->db_res = pg_connect($this->db_webdav) or die("connection error");
        
        parent::__construct();
    }
    /**
     * Serve a webdav request
     *
     * @access public
     * @param  string
     */
    function ServeRequest()
    {
        // special treatment for litmus compliance test
        // reply on its identifier header
        // not needed for the test itself but eases debugging
        foreach (apache_request_headers() as $key => $value) {
            if (stristr($key, "litmus")) {
                error_log("Litmus test $value");
                header("X-Litmus-reply: " . $value);
            }
        }
        $this->base = "";
        // TODO throw on connection problems
        // let the base class do all the work
        parent::ServeRequest();
    }
    /**
     * No authentication is needed here
     *
     * @access private
     * @param  string  HTTP Authentication type (Basic, Digest, ...)
     * @param  string  Username
     * @param  string  Password
     * @return bool    true on successful authentication
     */
    function check_auth($type, $user, $pass)
    {
        return true;
    }
    /**
     * PROPFIND method handler
     *
     * @param  array $options general parameter passing array
     * @param  array $files return array for file properties
     * @return bool   true on success
     */
    function PROPFIND(&$options, &$files)
    {
        // get absolute fs path to requested resource
        $fspath = $options["path"];
        
        error_log("  >PROPFIND depth:" . $options["depth"]);
        //$this->logArray($options);
        // prepare property array
        $files["files"] = array();
        // store information for the requested path itself
        // information for contained resources requested?
        if (!empty($options["depth"])) { // TODO check for is_dir() first?
            // make sure path ends with '/'
            $options["path"] = $this->_slashify($options["path"]);
            // try to open directory
            $freefiles = $this->readfolder($fspath);
            $files["files"] = $freefiles;
        } else {
            
            $freefiles = $this->readfolder($fspath, true);
            $files["files"] = $freefiles;
        }
        
        if (count($files["files"]) == 0) return false;
        // ok, all done
        error_log("PROPFIND OK");
        return true;
    }
    
    function readfolder($fspath, $onlyfld = false)
    {
        include_once ('FDL/Class.SearchDoc.php');
        
        $files = array();
        $fldid = $this->path2id($fspath, $vid);
        
        if (strtolower($fspath) == "/freedav") {
            $info = array();
            $info["props"] = array();
            $info["props"][] = $this->mkprop("resourcetype", "collection");
            $info["props"][] = $this->mkprop("getcontenttype", "httpd/unix-directory");
            $info["props"][] = $this->mkprop("displayname", $fspath);
            $info["path"] = $fspath;
            $files[] = $info;
        } else {
            if ($vid) {
                $files = $this->vidpropinfo($fspath, $fldid, (!$onlyfld));
            } else {
                $fld = new_doc($this->db_freedom, $fldid, true);
                if ($fld->isAlive()) {
                    //	  error_log("READFOLDER FIRST:".dirname($fspath)."/".$fld->title."ONLY:".intval($onlyfld));
                    //$files=$this->docpropinfo($fld,$this->_slashify(dirname($fspath)),true);
                    if (($fld->doctype == 'D') || ($fld->doctype == 'S')) $dpath = $this->_slashify($fspath);
                    else $dpath = $fspath;
                    $files = $this->docpropinfo($fld, $dpath, true);
                    if (!$onlyfld) {
                        $s = new SearchDoc($this->db_freedom);
                        $s->dirid = $fld->initid;
                        $s->slice = $this->getFolderMaxItem();
                        $s->setObjectReturn();
                        $s->search();
                        if ($s->count() > 0) {
                            while ($doc = $s->nextDoc()) {
                                $files = array_merge($files, $this->docpropinfo($doc, $fspath, false));
                            }
                        }
                    }
                }
            }
        }
        return $files;
    }
    
    private static function logArray($a, $level = 0)
    {
        if (is_array($a)) {
            foreach ($a as $k => $v) {
                error_log(str_pad('.', $level * 3) . "$k=>" . self::logArray($v, $level + 1));
            }
        } else {
            error_log(str_pad('.', $level * 3) . $a);
        }
    }
    function path2id($fspath, &$vid = null)
    {
        //error_log("FSPATH :".$fspath);
        if ($fspath == '/') return $this->racine;
        
        $fspath = $this->_unslashify($fspath);
        if (preg_match('/\/vid-([0-9]+)-([0-9]+)-[a-f0-9]+$/', $fspath, $reg)) {
            // main directory
            $fid = $reg[1];
            $vid = $reg[2];
            //error_log("FSPATH4 :.$fspath vid:[$vid]");
            //	    $dvi=new DocVaultIndex($this->db_freedom);
            //$fid=$dvi->getDocId($vid);
            //error_log("FSPATH3 :.$fspath vid:[$vid]");
            
        } else if (preg_match('/\/vid-([0-9]+)-([0-9]+)/', $fspath, $reg)) {
            include_once('FDL/Lib.Vault.php');
            $fid = $reg[1];
            $tmpvid = $reg[2];
            $info=vault_properties($tmpvid);

            $fsbase = basename($fspath);
            if ($info->name == $fsbase) {
                $vid = $tmpvid;
            } else {
                $fid=0;
            }
            // error_log("FSPATH3 :.$fspath vid:[$vid]");
            
        } else {
            if (!seems_utf8($fspath)) $fspath = utf8_encode($fspath);
            $query = sprintf("SELECT  value FROM dav.properties WHERE name='fid' and path = '%s'", pg_escape_string($fspath));
            
            $res = pg_query($this->db_res, $query);
            while ($row = pg_fetch_assoc($res)) {
                $fid = $row["value"];
            }
            //      error_log("PATH2ID:".$query."[$fid]");
            pg_free_result($res);
        }
        //    error_log("FSPATH :".$fspath. "=>".$fid);
        return $fid;
    }
    
    function getFilesProperties(&$doc)
    {
        $fileAttributes = $doc->GetFileAttributes();
        $tinfo = array();
        foreach ($fileAttributes as $fileAttribute) {
            if ($fileAttribute->getOption('hideindav') == 'yes') {
                continue;
            }
            $result = $doc->vault_properties($fileAttribute);
            if (count($result) > 0 && is_array($result[0])) {
                foreach ($result as $tmp) {
                    $state = intval($tmp["teng_state"]);
                    if ($state == 0 || $state == 1) { // only valid files
                        $tinfo[] = $tmp;
                    }
                }
            }
        }
        return $tinfo;
    }
    
    function docpropinfo(&$doc, $path, $firstlevel)
    {
        //    error_log("docpropinfo $doc->initid $doc->id $path #$firstlevel");
        // map URI path to filesystem path
        $fspath = $this->base . $path;
        // create result array
        $tinfo = array();
        $info = array();
        // TODO remove slash append code when base clase is able to do it itself
        //$info["path"]  = is_dir($fspath) ? $this->_slashify($path) : $path;
        if ($doc->id == $this->racine) $doc->title = '';
        // no special beautified displayname here ...
        // creation and modification time
        // type and size (caller already made sure that path exists)
        if (($doc->doctype == 'D') || ($doc->doctype == 'S')) {
            // directory (WebDAV collection)
            $info = array();
            $info["props"] = array();
            $info["props"][] = $this->mkprop("resourcetype", "collection");
            $info["props"][] = $this->mkprop("getcontenttype", "httpd/unix-directory");
            $info["props"][] = $this->mkprop("displayname", strtr($doc->title, "/", "-"));
            $path = $this->_slashify($path);
            if ($firstlevel) $info["path"] = $path;
            else $info["path"] = $path . strtr($doc->title, "/", "-");
            //$info["path"]  = $path;
            $info["props"][] = $this->mkprop("creationdate", $doc->revdate);
            $info["props"][] = $this->mkprop("getlastmodified", $doc->revdate);
            //error_log("FOLDER:".$path.":".$doc->title);
            // get additional properties from database
            $query = sprintf("SELECT ns, name, value FROM dav.properties WHERE path = '%s'", pg_escape_string($path));
            $res = pg_query($this->db_res, $query);
            while ($row = pg_fetch_assoc($res)) {
                $info["props"][] = $this->mkprop($row["ns"], $row["name"], $row["value"]);
            }
            pg_free_result($res);
            $tinfo[] = $info;
            if (($firstlevel) || ($doc->title != "")) {
                //$query = "REPLACE INTO dav.properties SET path = '".pg_escape_string($this->_unslashify($info["path"]))."', name = 'fid', ns= '$prop[ns]', value = '".$doc->initid."'";
                $query = sprintf("delete from dav.properties where path=  '%s' and name= 'fid'", pg_escape_string($this->_unslashify($info["path"])));
                
                pg_query($this->db_res, $query);
                $query = sprintf("INSERT INTO dav.properties (path, name, ns, value) values ('%s', 'fid',  '%s', '%s')", pg_escape_string($this->_unslashify($info["path"])) , pg_escape_string($prop['ns']) , pg_escape_string($doc->initid));
                
                pg_query($this->db_res, $query);
            }
        } else {
            // simple document : search attached files
            // $info["props"][] = $this->mkprop("getcontenttype", $this->_mimetype($fspath));
            $afiles = $this->GetFilesProperties($doc);
            //error_log("READFILES examine :".count($afiles).'-'.$doc->title.'-'.$doc->id);
            $bpath = $this->mybasename($path);
            $dpath = $this->_slashify(dirname($path));
            //error_log("FILEDEBUG:".$path."-".$bpath."- $path #$firstlevel");
            $path = $this->_slashify($path);
            foreach ($afiles as $afile) {
                $info = array();
                $info["props"][] = $this->mkprop("resourcetype", "");
                $aname = strtr($afile["name"], "/", "-");
                //error_log("FILE TEST [$aname] [$bpath] #$firstlevel");
                if ((!$firstlevel) || ($aname == $bpath)) {
                    if ($firstlevel) $info["path"] = $dpath . $aname;
                    else $info["path"] = $path . $aname;
                    $filename = $afile["path"];
                    
                    if (file_exists($filename)) {
                        $info["props"][] = $this->mkprop("displayname", $aname);
                        $info["props"][] = $this->mkprop("creationdate", filectime($filename));
                        $info["props"][] = $this->mkprop("getlastmodified", filemtime($filename));
                        $info["props"][] = $this->mkprop("getcontenttype", $this->_mimetype($filename));
                        $info["props"][] = $this->mkprop("getcontentlength", intval($afile["size"]));
                        // get additional properties from database
                        $query = sprintf("SELECT ns, name, value FROM dav.properties WHERE path = '%s'", pg_escape_string($this->_unslashify($info["path"])));
                        $res = pg_query($this->db_res, $query);
                        while ($row = pg_fetch_assoc($res)) {
                            $info["props"][] = $this->mkprop($row["ns"], $row["name"], $row["value"]);
                        }
                        pg_free_result($res);
                        //		error_log("PROP:".print_r($info,true));
                        // error_log("PROP:".$query);
                        $tinfo[] = $info;
                        //$query = "REPLACE INTO properties SET path = '".pg_escape_string($this->_unslashify($info["path"]))."', name = 'fid', ns= '$prop[ns]', value = '".$doc->id."'";
                        $query = sprintf("delete from dav.properties where path=  '%s' and name= 'fid'", pg_escape_string($this->_unslashify($info["path"])));
                        pg_query($this->db_res, $query);
                        $query = sprintf("INSERT INTO dav.properties (path, name, ns, value) values ('%s', 'fid', '%s', '%s')", pg_escape_string($this->_unslashify($info["path"])) , pg_escape_string($prop['ns']) , pg_escape_string($doc->initid));
                        pg_query($this->db_res, $query);
                        //  error_log($query);
                        //error_log("FILE:".$afile["name"]."-".$afile["size"]."-".$path);
                        
                    } else {
                        error_log("FILE ERROR:" . $doc->title . "-" . $doc->id . "-" . $filename);
                    }
                }
                //error_log("PROP:".$query);
                
            }
        }
        
        return $tinfo;
    }
    /**
     * virtual path
     */
    function vidpropinfo($path, $docid, $withfile = false)
    {
        // map URI path to filesystem path
        // create result array
        $tinfo = array();
        $info = array();
        // TODO remove slash append code when base clase is able to do it itself
        //$info["path"]  = is_dir($fspath) ? $this->_slashify($path) : $path;
        // no special beautified displayname here ...
        $onlyfile = false;
        if (preg_match("/\/vid-([^\/]*)\/(.*)/", $path, $reg)) {
            $onlyfile = $reg[2];
        }
        // creation and modification time
        // directory (WebDAV collection)
        if (!$onlyfile) {
            $info = array();
            $info["props"] = array();
            $info["props"][] = $this->mkprop("resourcetype", "collection");
            $info["props"][] = $this->mkprop("getcontenttype", "httpd/unix-directory");
            $info["props"][] = $this->mkprop("displayname", $path);
            //      $info["props"][] = $this->mkprop("urn:schemas-microsoft-com:", "Win32FileAttributes", "00000001");
            $path = $this->_slashify($path);
            if ($firstlevel) $info["path"] = $path;
            else $info["path"] = $path;
            //$info["path"]  = $path;
            $info["props"][] = $this->mkprop("creationdate", time());
            $info["props"][] = $this->mkprop("getlastmodified", time());
            //error_log("VIRTUAL FOLDER:".$path.":");
            
        }
        $tinfo[] = $info;
        if ($withfile || $onlyfile) {
            // simple document : search attached files
            $doc = new_doc($this->db_freedom, $docid, true);
            // $info["props"][] = $this->mkprop("getcontenttype", $this->_mimetype($fspath));
            $afiles = $this->GetFilesProperties($doc);
            //error_log("VIDPROP examine :".count($afiles).'-'.$doc->title.'-'.$doc->id);
            $bpath = $this->mybasename($path);
            $dpath = $this->_slashify(dirname($path));
            //error_log("FILEDEBUG:".$path."-".$bpath."-".$path);
            $path = $this->_slashify($path);
            foreach ($afiles as $afile) {
                $aname = $afile["name"];
                //error_log("SEARCH FILE:[$aname] [$onlyfile]");
                if ((!$onlyfile) || ($aname == $onlyfile)) {
                    $info = array();
                    //error_log("FOUND FILE:".$aname);
                    $info["props"][] = $this->mkprop("resourcetype", "");
                    
                    $info["props"][] = $this->mkprop("displayname", $aname);
                    if ($firstlevel) $info["path"] = $dpath . $aname;
                    else $info["path"] = $path . $aname;
                    $filename = $afile["path"];
                    $info["props"][] = $this->mkprop("creationdate", filectime($filename));
                    $info["props"][] = $this->mkprop("getlastmodified", filemtime($filename));
                    $info["props"][] = $this->mkprop("getcontenttype", $this->_mimetype($filename));
                    $info["props"][] = $this->mkprop("getcontentlength", intval($afile["size"]));
                    $err = $doc->canEdit();
                    if ($err != "") {
                        // add read only attributes for windows
                        $info["props"][] = $this->mkprop("urn:schemas-microsoft-com:", "Win32FileAttributes", "00000001");
                    }
                    $tinfo[] = $info;
                }
                //error_log("PROP:".$query);
                
            }
        }
        
        return $tinfo;
    }
    /**
     * detect if a given program is found in the search PATH
     *
     * helper function used by _mimetype() to detect if the
     * external 'file' utility is available
     *
     * @param  string  program name
     * @param  string  optional search path, defaults to $PATH
     * @return bool    true if executable program found in path
     */
    function _can_execute($name, $path = false)
    {
        // path defaults to PATH from environment if not set
        if ($path === false) {
            $path = getenv("PATH");
        }
        // check method depends on operating system
        if (!strncmp(PHP_OS, "WIN", 3)) {
            // on Windows an appropriate COM or EXE file needs to exist
            $exts = array(
                ".exe",
                ".com"
            );
            $check_fn = "file_exists";
        } else {
            // anywhere else we look for an executable file of that name
            $exts = array(
                ""
            );
            $check_fn = "is_executable";
        }
        // now check the directories in the path for the program
        foreach (explode(PATH_SEPARATOR, $path) as $dir) {
            // skip invalid path entries
            if (!file_exists($dir)) continue;
            if (!is_dir($dir)) continue;
            // and now look for the file
            foreach ($exts as $ext) {
                if ($check_fn("$dir/$name" . $ext)) return true;
            }
        }
        
        return false;
    }
    /**
     * try to detect the mime type of a file
     *
     * @param  string  file path
     * @return string  guessed mime type
     */
    function _mimetype($fspath)
    {
        return strtok(trim(shell_exec(sprintf("file -ib %s", escapeshellarg($fspath)))) , ';');
        if (@is_dir($fspath)) {
            // directories are easy
            return "httpd/unix-directory";
        } else if (function_exists("mime_content_type")) {
            // use mime magic extension if available
            $mime_type = mime_content_type($fspath);
        } else if ($this->_can_execute("file")) {
            // it looks like we have a 'file' command,
            // lets see it it does have mime support
            $fp = popen("file -i '$fspath' 2>/dev/null", "r");
            $reply = fgets($fp);
            pclose($fp);
            // popen will not return an error if the binary was not found
            // and find may not have mime support using "-i"
            // so we test the format of the returned string
            // the reply begins with the requested filename
            if (!strncmp($reply, "$fspath: ", strlen($fspath) + 2)) {
                $reply = substr($reply, strlen($fspath) + 2);
                // followed by the mime type (maybe including options)
                if (preg_match('/^[[:alnum:]_-]+/[[:alnum:]_-]+;?.*/', $reply, $matches)) {
                    $mime_type = $matches[0];
                }
            }
        }
        
        if (empty($mime_type)) {
            // Fallback solution: try to guess the type by the file extension
            // TODO: add more ...
            // TODO: it has been suggested to delegate mimetype detection
            //       to apache but this has at least three issues:
            //       - works only with apache
            //       - needs file to be within the document tree
            //       - requires apache mod_magic
            // TODO: can we use the registry for this on Windows?
            //       OTOH if the server is Windos the clients are likely to
            //       be Windows, too, and tend do ignore the Content-Type
            //       anyway (overriding it with information taken from
            //       the registry)
            // TODO: have a seperate PEAR class for mimetype detection?
            switch (strtolower(strrchr($this->mybasename($fspath) , "."))) {
                case ".html":
                    $mime_type = "text/html";
                    break;

                case ".gif":
                    $mime_type = "image/gif";
                    break;

                case ".jpg":
                    $mime_type = "image/jpeg";
                    break;

                default:
                    $mime_type = "application/octet-stream";
                    break;
            }
        }
        
        return $mime_type;
    }
    /**
     * GET method handler
     *
     * @param  array  parameter passing array
     * @return bool   true on success
     */
    function GET(&$options)
    {
        error_log("---------->GET :" . $options["path"]);
        include_once ("FDL/Class.Doc.php");
        // get absolute fs path to requested resource
        $fspath = $this->base . $options["path"];
        
        $fldid = $this->path2id($options["path"], $vid);
        $doc = new_doc($this->db_freedom, $fldid, true);
        if (!$doc->isAlive()) {
            $this->cleanDeleted($fldid);
            return false;
        }
        $afiles = $this->GetFilesProperties($doc);
        
        $bpath = $options["path"];
        if (!seems_utf8($bpath)) $bpath = utf8_encode($bpath);
        
        $bpath = $this->mybasename($bpath); // basename
        foreach ($afiles as $afile) {
            $path = $afile["name"];
            //error_log("GET SEARCH:".$bpath.'->'.$path);
            if (($vid == $afile["vid"]) || ($path == $bpath)) {
                error_log("GET FOUND:" . $path . '-' . $afile["path"]);
                $fspath = $afile["path"];
                break;
            }
        }
        // sanity check
        if (!file_exists($fspath)) return false;
        // is this a collection?
        if (is_dir($fspath)) {
            return $this->GetDir($fspath, $options);
        }
        // detect resource type
        $options['mimetype'] = $this->_mimetype($fspath);
        // detect modification time
        // see rfc2518, section 13.7
        // some clients seem to treat this as a reverse rule
        // requiering a Last-Modified header if the getlastmodified header was set
        $options['mtime'] = filemtime($fspath);
        // detect resource size
        $options['size'] = filesize($fspath);
        // no need to check result here, it is handled by the base class
        $options['stream'] = fopen($fspath, "r");
        
        header("Cache-control: no-cache");
        header("Pragma: no-cache"); // HTTP 1.0
        error_log("GET NO CACHE :" . $options["path"]);
        return true;
    }
    /**
     * GET method handler for directories
     *
     * This is a very simple mod_index lookalike.
     * See RFC 2518, Section 8.4 on GET/HEAD for collections
     *
     * @param  string  directory path
     * @return void    function has to handle HTTP response itself
     */
    function GetDir($fspath, &$options)
    {
        
        echo "<html><head><title>Index of " . htmlspecialchars($options['path']) . "</title></head>\n";
        
        echo "<h1>Index of " . htmlspecialchars($options['path']) . "</h1>\n";
        
        echo "<pre>WebDAV Server: HTML view is not implemented yet";
        
        echo "<hr>";
        
        echo "</pre>";
        
        echo "</html>\n";
        
        exit;
    }
    /**
     * PUT method handler
     *
     * @param  array  parameter passing array
     * @return bool   true on success
     */
    function PUT(&$options)
    {
        error_log("---------->PUT :" . $options["path"]);
        include_once ("FDL/Class.Doc.php");
        
        $bpath = $this->mybasename($options["path"]);
        if (!seems_utf8($bpath)) $bpath = utf8_encode($bpath);
        $fldid = $this->path2id($options["path"], $vid);
        if ($fldid) {
            $stat = "204 No Content";
            $options["new"] = false;
            $doc = new_doc($this->db_freedom, $fldid, true);
            $err = $doc->canEdit();
            if ($err == "") {
                if ($doc->doctype == 'C') {
                    /**
                     * @var DocFam $doc
                     */
                    $doc->saveVaultFile($vid, $options["stream"]);
                } else {
                    $afiles = $doc->GetFileAttributes();
                    //error_log("PUT SEARCH FILES:".count($afiles));
                    
                    /**
                     * @var NormalAttribute $afile
                     */
                    foreach ($afiles as $afile) {
                        if ($afile->getOption('hideindav') == 'yes') {
                            continue;
                        }
                        $fnames = array();
                        if ($afile->inArray()) {
                            $tval = $doc->getTValue($afile->id);
                            foreach ($tval as $k => $v) {
                                $fnames[$k] = $doc->vault_filename($afile->id, false, $k);
                            }
                        } else $fnames[-1] = $doc->vault_filename($afile->id);
                        foreach ($fnames as $k => $fname) {
                            //	  error_log("PUT SEARCH:.$bpath $fname");
                            if ($fname == $bpath) {
                                error_log("PUT FOUND:" . $path . '-' . $fname);
                                
                                $bpath = $bpath;
                                $doc->saveFile($afile->id, $options["stream"], $bpath, $k);
                                $err = $doc->postModify();
                                $err = $doc->Modify();
                                
                                break;
                            }
                        }
                    }
                }
            }
        } else {
            error_log("PUT " . $this->type);
            if ($this->type == 'freedav') {
                error_log(" CANCEL PUT :" . $options["path"]);
                return false; // no creation in freedav
                
            }
            $options["new"] = true;
            $stat = "201 Created";
            if ($options["new"]) {
                $dir = dirname($options["path"]);
                $fldid = $this->path2id($dir);
                $fld = new_doc($this->db_freedom, $fldid);
                $err = $fld->canModify();
                if ($err == "") {
                    //error_log("PUT NEW FILE IN:".$dir);
                    $ndoc = createDoc($this->db_freedom, "SIMPLEFILE");
                    if ((!$ndoc) || (!$ndoc->fromid)) $ndoc = createDoc($this->db_freedom, "FILE");
                    if ($ndoc && $ndoc->fromid) {
                        $fa = $ndoc->GetFirstFileAttributes();
                        $bpath = $bpath;
                        $ndoc->saveFile($fa->id, $options["stream"], $bpath);
                        //		$ndoc->setTitle($bpath);
                        $err = $ndoc->Add();
                        $err = $ndoc->postModify();
                        $err = $ndoc->Modify();
                        error_log("PUT NEW FILE:" . $fa->id . "-" . $ndoc->id);
                        if ($err == "") {
                            $err = $fld->addFile($ndoc->initid);
                            error_log("PUT ADD IN FOLDER:" . $err . $fld->id . "UID:" . ($fld->userid));
                            $this->readfolder($dir);
                        }
                    } else {
                        $err = "not allowed";
                    }
                }
            }
        }
        if (!$err) {
            error_log(" CREATE PUT OK :" . $options["path"]);
        } else {
            error_log(" CREATE PUT KO : $err:" . $options["path"]);
        }
        if ($err != "") $stat = false;
        
        return $stat;
    }
    /**
     * MKCOL method handler
     *
     * @param  array  general parameter passing array
     * @return bool   true on success
     */
    function MKCOL($options)
    {
        
        error_log("---------- >MKCOL :" . $options["path"]);
        $err='';
        include_once ("FDL/Class.Doc.php");
        
        if (!empty($_SERVER["CONTENT_LENGTH"])) { // no body parsing yet
            return "415 Unsupported media type";
        }
        $path = $this->_unslashify($options["path"]);
        $fldid = $this->path2id(dirname($options["path"]));
        if ($fldid) {
            $fld = new_doc($this->db_freedom, $fldid);
            $nfld = createDoc($this->db_freedom, "SIMPLEFOLDER");
            if ((!$nfld) || (!$nfld->fromid)) $nfld = createDoc($this->db_freedom, "DIR");
            if ((!$nfld) || (!$nfld->fromid)) return "403 Forbidden : $err";
            $nreptitle = $this->mybasename($path);
            $nfld->setTitle($nreptitle);
            $err = $nfld->Add();
            if ($err == "") {
                $err = $fld->AddFile($nfld->initid);
                error_log("NEW FLD:" . $nfld->initid);
                $this->docpropinfo($nfld, $path, true);
            }
        }
        /*
        if (!file_exists($parent)) {
        return "409 Conflict";
        }
        
        if (!is_dir($parent)) {
        $name = $this->mybasename($path);    return "403 Forbidden";
        }
        
        if ( file_exists($parent."/".$name) ) {
        return "405 Method not allowed";
        }
        */
        
        if ($err != "") {
            return "403 Forbidden : $err";
        }
        
        return ("201 Created");
    }
    /**
     * DELETE method handler
     *
     * @param  array  general parameter passing array
     * @return bool   true on success
     */
    function DELETE($options)
    {
        error_log("---------- >DELETE :" . $options["path"]);
        
        if ($this->type == 'freedav') {
            $err = sprintf("unsupported DELETE method with freedav access.");
            error_log(sprintf("---------- > %s", $err));
            return "403 Forbidden: $err";
        }
        
        include_once ("FDL/Class.Doc.php");
        $fldid = $this->path2id($options["path"]);
        $doc = new_doc($this->db_freedom, $fldid, true);
        
        if (!$doc->isAlive()) {
            return "404 Not found";
        }
        if ($doc->doctype == 'D') {
            // just rm the folder : is normally empty
            $err = $doc->delete();
            if ($err != "") {
                return "403 Forbidden:$err";
            }
            if ($err == "") {
                $query = sprintf("DELETE FROM dav.properties WHERE path LIKE '%s%%'", pg_escape_string($this->_slashify($options["path"])));
                pg_query($this->db_res, $query);
            }
        } else {
            if ($doc->isLocked()) {
                $err = $doc->unlock();
            }
            
            if ($err != "") {
                return "403 Forbidden:$err";
            }
            $err = $doc->delete();
            if ($err != "") {
                return "403 Forbidden:$err";
            }
            $query = sprintf("DELETE FROM dav.properties WHERE name='fid' and value='%s'", pg_escape_string($doc->initid));
            error_log($query);
            pg_query($this->db_res, $query);
        }
        
        return "204 No Content";
    }
    /**
     * MOVE method handler
     *
     * @param  array  general parameter passing array
     * @return bool   true on success
     */
    function MOVE($options)
    {
        error_log("---------- >MOVE :" . $options["path"] . "->" . $options["dest"]);
        // no copying to different WebDAV Servers yet
        if (isset($options["dest_url"])) {
            return "502 bad gateway";
        }
        
        include_once ("FDL/Class.Doc.php");
        $psource = $this->_unslashify($options["path"]);
        $pdirsource = $this->_unslashify(dirname($options["path"]));
        $bsource = $this->mybasename($psource);
        
        $srcid = $this->path2id($psource);
        $src = new_doc($this->db_freedom, $srcid);
        //error_log ("SRC : $psource ".$srcid );
        $err = $src->canEdit();
        if ($err == "") {
            
            $pdest = $this->_unslashify($options["dest"]);
            $bdest = $this->mybasename($pdest);
            $destid = $this->path2id($pdest);
            
            $pdirdest = $this->_unslashify(dirname($options["dest"]));
            $dirdestid = $this->path2id($pdirdest);
            $ppdest = new_doc($this->db_freedom, $dirdestid);
            
            if ($destid) {
                $dest = new_doc($this->db_freedom, $destid);
                if ($dest->doctype == 'D') {
                    //error_log ("MOVE TO FOLDER : $destid:".$dest->title);
                    return "502 bad gateway";
                } else {
                    
                    error_log("DELETE FILE : $destid:" . $dest->title);
                    // delete file
                    $err = $dest->delete();
                    if ($err == "") {
                        $query = sprintf("DELETE FROM dav.properties WHERE name='fid' and value='%s'", pg_escape_string($dest->initid));
                        error_log($query);
                        pg_query($this->db_res, $query);
                        // move
                        $err = $ppdest->addFile($srcid);
                        if ($err == "") {
                            // delete ref from source
                            $psrcid = $this->path2id($pdirsource);
                            $psrc = new_doc($this->db_freedom, $psrcid);
                            if ($psrc->isAlive()) {
                                $err = $psrc->delFile($srcid);
                                if ($err == "") {
                                    
                                    $src->addComment(sprintf(_("Move file from %s to %s") , ($psrc->title) , ($ppdest->title)));
                                    $query = "DELETE FROM dav.properties WHERE path = '$psource'";
                                }
                            }
                        }
                    }
                    
                    if ($bdest != $bsource) {
                        error_log(" RENAMETO2  : $bdest");
                        $src->setTitle($bdest);
                        $err = $src->modify();
                        $this->docpropinfo($src, $pdest, true);
                        if ($err == "") {
                            
                            $query = sprintf("DELETE FROM dav.properties WHERE path = '%s'", pg_escape_string($psource));
                            error_log($query);
                            pg_query($this->db_res, $query);
                        }
                        error_log(" RENAMETO  : $bdest : $err");
                    }
                }
            } else {
                if ($pdirsource != $pdirdest) {
                    // move
                    $err = $ppdest->addFile($srcid);
                    if ($err == "") {
                        $this->docpropinfo($src, $pdest, true);
                        // delete ref from source
                        $psrcid = $this->path2id($pdirsource);
                        $psrc = new_doc($this->db_freedom, $psrcid);
                        if ($psrc->isAlive()) {
                            $err = $psrc->delFile($srcid);
                            if ($err == "") {
                                $src->addComment(sprintf(_("Move file from %s to %s") , ($psrc->title) , ($ppdest->title)));
                                $query = sprintf("DELETE FROM dav.properties WHERE path = '%s'", pg_escape_string($psource));
                                pg_query($this->db_res, $query);
                            }
                        }
                    }
                    error_log("MOVE TO PARENT2 FOLDER : $dirdestid:" . $err);
                }
                if ($err == "") {
                    if ($bdest != $bsource) {
                        if ($src->doctype == 'D') {
                            $src->setTitle($bdest);
                        } else {
                            
                            $afiles = $this->GetFilesProperties($src);
                            foreach ($afiles as $afile) {
                                $path = $afile["name"];
                                error_log("RENAME SEARCH:" . $bsource . '->' . $path);
                                if ($path == $bsource) {
                                    error_log("RENAME FOUND:" . $path . '-' . $afile["path"]);
                                    $fspath = $afile["path"];
                                    
                                    $vf = newFreeVaultFile($this->db_freedom);
                                    $vf->Rename($afile["vid"], $bdest);
                                    $src->addComment(sprintf(_("Rename file as %s") , $bdest));
                                    $src->postModify();
                                    $err = $src->modify();
                                }
                            }
                        }
                        $err = $src->modify();
                        $this->docpropinfo($src, $pdest, true);
                        if ($err == "") {
                            
                            $query = sprintf("DELETE FROM dav.properties WHERE path = '%s'", pg_escape_string($psource));
                            error_log($query);
                            pg_query($this->db_res, $query);
                        }
                        error_log(" RENAMETO2  : $bdest : $err");
                    }
                }
            }
            if ($src->doctype == 'D') {
                $query = sprintf("UPDATE dav.properties SET path = REPLACE(path, '%s', '%s') WHERE path LIKE '%s%%'", pg_escape_string($psource) , pg_escape_string($pdest) , pg_escape_string($psource));
                pg_query($this->db_res, $query);
                error_log($query);
            }
            
            if ($err == "") return "201 Created";
        }
        error_log("DAV MOVE:$err");
        return "403 Forbidden";
    }
    /**
     * COPY method handler
     *
     * @param  array  general parameter passing array
     * @return bool   true on success
     */
    function COPY($options)
    {
        error_log("---------- >COPY :" . $options["path"] . "->" . $options["dest"]);
        // no copying to different WebDAV Servers yet
        if (isset($options["dest_url"])) {
            return "502 bad gateway";
        }
        
        include_once ("FDL/Class.Doc.php");
        $psource = $this->_unslashify($options["path"]);
        $pdirsource = $this->_unslashify(dirname($options["path"]));
        $bsource = $this->mybasename($psource);
        
        $srcid = $this->path2id($psource);
        $src = new_doc($this->db_freedom, $srcid);
        error_log("SRC : $psource " . $srcid);
        
        $pdest = $this->_unslashify($options["dest"]);
        $bdest = $this->mybasename($pdest);
        $destid = $this->path2id($pdest);
        
        $pdirdest = $this->_unslashify(dirname($options["dest"]));
        $dirdestid = $this->path2id($pdirdest);
        $ppdest = new_doc($this->db_freedom, $dirdestid);
        
        if ($destid) {
            $dest = new_doc($this->db_freedom, $destid);
            if ($dest->doctype == 'D') {
                error_log("COPY FILE TO REPLACE FOLDER NOT POSSIBLE NORMALLY: $destid:" . $dest->title);
                return "502 bad gateway";
            } else {
                error_log("DELETE FILE : $destid:" . $dest->title);
                // delete file
                $err = $dest->delete();
                
                if ($err == "") {
                    
                    $query = sprintf("DELETE FROM dav.properties WHERE name='fid' and value='%s'", pg_escape_string($dest->initid));
                    error_log($query);
                    pg_query($this->db_res, $query);
                }
            }
        }
        if ($err == "") {
            // copy
            if ($src->doctype == "D") {
                // copy of directory
                return "501 not implemented";
            } else {
                
                $copy = $src->copy();
                
                error_log("COPY :" . $copy->id);
                $afiles = $this->GetFilesProperties($copy);
                error_log("# FILE :" . count($afiles));
                $ff = $copy->GetFirstFileAttributes();
                
                $f = $copy->getValue($ff->id);
                error_log("RENAME SEARCH:" . $f);
                if (preg_match(PREGEXPFILE, $f, $reg)) {
                    $vf = newFreeVaultFile($this->db_freedom);
                    $vid = $reg[2];
                    
                    $vf->Rename($vid, $bdest);
                    $copy->addComment(sprintf(_("Rename file as %s") , $bdest));
                    $copy->postModify();
                    $err = $copy->modify();
                }
                
                $err = $ppdest->addFile($copy->id);
                if ($err == "") {
                    $this->docpropinfo($copy, $pdest, true);
                }
                
                error_log("MOVE TO PARENT FOLDER : $dirdestid:" . $err);
                if ($bdest != $bsource) {
                    $copy->setTitle($bdest);
                    $err = $copy->modify();
                    $this->docpropinfo($copy, $pdest, true);
                    
                    error_log(" RENAMETO  : $bdest : $err");
                }
            }
        }
        
        if ($err == "") return "201 Created";
        
        error_log("DAV MOVE:$err");
        return "403 Forbidden";
    }
    /**
     * PROPPATCH method handler
     *
     * @param  array  general parameter passing array
     * @return bool   true on success
     */
    function PROPPATCH(&$options)
    {
        global $prefs, $tab;
        error_log("---------- >PROPPATCH :" . $options["path"]);
        
        $msg = "";
        
        $path = $options["path"];
        
        $dir = dirname($path) . "/";
        $base = $this->mybasename($path);
        
        foreach ($options["props"] as $key => $prop) {
            if ($prop["ns"] == "DAV:") {
                $options["props"][$key]['status'] = "403 Forbidden";
            } else {
                if (isset($prop["val"])) {
                    //$query = "REPLACE INTO properties SET path = '$options[path]', name = '$prop[name]', ns= '$prop[ns]', value = '$prop[val]'";
                    $query = sprintf("delete from dav.properties where path='%s' and name= '%s' and ns='%s'", pg_escape_string($prop['path']) , pg_escape_string($prop['name']) , pg_escape_string($prop['ns']));
                    pg_query($this->db_res, $query);
                    $query = sprintf("INSERT INTO dav.properties (path, name, ns, value) values ('%s', '%s',  '%s', '%s')", pg_escape_string($prop['path']) , pg_escape_string($prop['name']) , pg_escape_string($prop['ns']) , pg_escape_string($prop['val']));
                    //	  pg_query($this->db_res,$query);
                    //	  $query = "REPLACE INTO properties SET path = '$options[path]', name = '$prop[name]', ns= '$prop[ns]', value = '$prop[val]'";
                    
                } else {
                    $query = sprintf("DELETE FROM dav.properties WHERE path = '%s' AND name = '%s' AND ns = '%s'", pg_escape_string($options['path']) , pg_escape_string($prop['name']) , pg_escape_string($prop['ns']));
                }
                pg_query($this->db_res, $query);
            }
        }
        
        return "";
    }
    /**
     * LOCK method handler
     *
     * @param  array  general parameter passing array
     * @return bool   true on success
     */
    function LOCK(&$options)
    {
        error_log("---------- >LOCK :" . $options["path"]);
        include_once ("FDL/Class.Doc.php");
        if (isset($options["update"])) { // Lock Update
            $query = sprintf("UPDATE dav.locks SET expires = %s where token='%s'", pg_escape_string((time() + 300)) , pg_escape_string($options["update"]));
            $res = pg_query($this->db_res, $query);
            
            if (pg_affected_rows($res)) {
                $options["timeout"] = 300; // 5min hardcoded
                error_log("LOCK Update succeed");
                return true;
            } else {
                error_log(print_r($options, true));
                error_log($query);
                error_log(pg_last_error($this->db_res));
                error_log("LOCK Update failed");
                return false;
            }
        }
        
        $fldid = $this->path2id($options["path"], $vid);
        $doc = new_doc($this->db_freedom, $fldid, true);
        if ($doc->isAffected()) {
            error_log("LOCK " . $doc->title . ":" . $options['locktoken']);
            
            $err = $doc->lock(true);
            if ($err == "") {
                $options["timeout"] = time() + 300; // 5min. hardcoded
                $query = sprintf("INSERT INTO dav.locks (token,path,owner,expires,exclusivelock) values ('%s', '%s', '%s', '%s', %s)", pg_escape_string($options['locktoken']) , pg_escape_string($options['path']) , pg_escape_string($options['owner']) , pg_escape_string($options['timeout']) , ($options['scope'] === "exclusive" ? "1" : "0"));
                
                $res = pg_query($this->db_res, $query);
                if (pg_affected_rows($res)) {
                    return "200 OK";
                }
            } else {
                error_log("Cannot lock " . $doc->title . ":$err");
            }
        } else {
            return true;
        }
        return "409 Conflict";
    }
    /**
     * UNLOCK method handler
     *
     * @param  array  general parameter passing array
     * @return bool   true on success
     */
    function UNLOCK(&$options)
    {
        
        error_log("---------- >UNLOCK :" . $options["path"]);
        include_once ("FDL/Class.Doc.php");
        $fldid = $this->path2id($options["path"], $vid);
        $doc = new_doc($this->db_freedom, $fldid, true);
        
        if ($doc->isAffected()) {
            $err = $doc->unlock(true);
            if ($err == "") {
                $query = sprintf("DELETE FROM dav.locks WHERE path = '%s' AND token = '%s'", pg_escape_string($options['path']) , pg_escape_string($options['token']));
                $res = pg_query($this->db_res, $query);
                if (pg_affected_rows($res)) {
                    error_log(" unlock success :" . $doc->title . ":" . $options['token']);
                    return "204 No Content";
                }
            }
        } else {
            return "204 No Content";
        }
        error_log("Cannot unlock " . $doc->title . ":[$err][" . $options['token']) . "]";
        return "409 Conflict";
    }
    /**
     * checkLock() helper
     *
     * @param  string resource path to check for locks
     * @return bool   true on success
     */
    function checkLock($path)
    {
        $result = false;
        if (!seems_utf8($path)) $path = utf8_encode($path);
        $query = sprintf("SELECT owner, token, expires, exclusivelock FROM dav.locks WHERE path = '%s'", pg_escape_string($path));
        
        $res = pg_query($this->db_res, $query);
        
        if ($res) {
            $row = pg_fetch_array($res);
            pg_free_result($res);
            
            if ($row) {
                $result = array(
                    "type" => "write",
                    "scope" => $row["exclusivelock"] ? "exclusive" : "shared",
                    "depth" => 0,
                    "owner" => $row['owner'],
                    "token" => $row['token'],
                    "expires" => $row['expires']
                );
            }
        }
        if (!$result) {
            
            include_once ("FDL/Class.Doc.php");
            $fldid = $this->path2id($options["path"], $vid);
            $doc = new_doc($this->db_freedom, $fldid);
            
            if ($doc->isAffected()) {
                if ($doc->isLocked(true)) {
                    $result = array(
                        "type" => "write",
                        "scope" => "exclusive",
                        "depth" => 0,
                        "owner" => $doc->locked,
                        "token" => 'opaquelocktoken:' . md5($doc->id) ,
                        "expires" => time() + 3600
                    );
                    error_log("LOCK " . $doc->title);
                }
            }
        }
        
        return $result;
    }
    /**
     * create database tables for property and lock storage
     *
     * @param  void
     * @return bool   true on success
     */
    function create_database()
    {
        // TODO
        return false;
    }
    /**
     * create database tables for property and lock storage
     *
     * @param  void
     * @return bool   true on success
     */
    function addsession($sessid, $vid, $docid, $owner, $expire = 0)
    {
        
        $query = "INSERT INTO dav.sessions (session,vid,fid,owner,expires) values (
                         '$sessid'   , $vid  , $docid , '$owner'   , '$expire')";
        $query = sprintf("INSERT INTO dav.sessions (session, vid, fid, owner, expires) values ('%s', %s, %s, '%s', %s)", pg_escape_string($sessid) , pg_escape_string($vid) , pg_escape_string($docid) , pg_escape_string($owner) , pg_escape_string($expire));
        
        $res = pg_query($this->db_res, $query);
        
        $err = pg_last_error($this->db_res);
        if ($err != "") error_log("$err [$query]");
        
        if (pg_affected_rows($res)) {
            return true;
        }
        return false;
    }
    /**
     * get login from session
     *
     * @param  void
     * @return bool   true on success
     */
    function getLogin($docid, $vid, $sessid)
    {
        
        $query = "select owner from  dav.sessions where 
                         session   = '$sessid' and
                         vid = $vid and
                         fid = $docid";
        $query = sprintf("select owner from  dav.sessions where session   = '%s' and vid = %s and fid = %s", pg_escape_string($sessid) , pg_escape_string($vid) , pg_escape_string($docid));
        //error_log("getLogin $query");
        $res = pg_query($this->db_res, $query);
        $row = pg_fetch_assoc($res);
        $owner = $row["owner"];
        
        pg_free_result($res);
        
        return $owner;
        
        return false;
    }
    /**
     * get session from login
     *
     * @param  int $docid document identificator
     * @param  int $vid vault identificator
     * @param  string $owner user login
     * @return string
     */
    function getSession($docid, $vid, $owner)
    {
        
        $query = "select session from  dav.sessions where 
                         owner   = '$owner' and
                         vid = $vid and
                         fid = $docid";
        $query = sprintf("select session from  dav.sessions where owner   = '%s' and vid = %s and fid = %s", pg_escape_string($owner) , pg_escape_string($vid) , pg_escape_string($docid));
        //error_log("getSession $query");
        $res = pg_query($this->db_res, $query);
        $row = pg_fetch_assoc($res);
        $sid = $row["session"];
        
        pg_free_result($res);
        
        return $sid;
    }
    function mybasename($p)
    {
        //return basename($p);
        $r = strrpos($p, "/");
        return ($r !== false) ? substr($p, $r + 1) : $p;
    }
    
    function cleanDeleted($fid)
    {
        $fid = intval($fid);
        $query = sprintf("delete from dav.properties where value='%s' and name= 'fid'", pg_escape_string($fid));
        
        pg_query($this->db_res, $query);
    }
    /**
     * Set the maximum number of documents returned when listing
     * content of a folder
     *
     * @param int $limit limit value (value < 0 for no limit)
     *
     * @return int the current limit
     */
    function setFolderMaxItem($limit)
    {
        if (!is_numeric($limit) || $limit < 0) {
            $limit = 'ALL';
        }
        $this->folder_max_item = $limit;
        return $this->folder_max_item;
    }
    /**
     * Get the currently applied maximum number
     *
     * @return int the current limit
     */
    function getFolderMaxItem()
    {
        if (!is_numeric($this->folder_max_item) || $this->folder_max_item < 0) {
            return 'ALL';
        }
        return $this->folder_max_item;
    }
}
?>
