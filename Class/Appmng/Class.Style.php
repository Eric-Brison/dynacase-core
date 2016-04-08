<?php
/*
 * @author Anakeen
 * @package FDL
*/
namespace {
    /**
     * Generated Header (not documented yet)
     *
     * @author Anakeen
     * @version $Id: Class.Style.php,v 1.5 2003/08/18 15:46:42 eric Exp $
     * @package FDL
     * @subpackage CORE
     */
    
    class Style extends DbObj
    {
        const RULE_FLAG_PARSE_ON_RUNTIME = 1;
        
        var $fields = array(
            "name",
            "description",
            "parsable",
            "rules"
        );
        
        var $id_fields = array(
            "name"
        );
        public $name;
        public $description;
        public $parsable;
        protected $rules;
        /**
         * @var Application
         */
        public $parent;
        
        var $sqlcreate = "
    create table style (
        name text not null,
        primary key (name),
        description text,
        parsable char default 'N',
        rules text default '{}'
    );
    create sequence SEQ_ID_STYLE start 10000;
";
        
        var $dbtable = "style";
        
        protected $_expanded_rules = array();
        
        public function __construct($dbaccess = '', $id = '', $res = '', $dbid = 0)
        {
            parent::__construct($dbaccess, $id, $res, $dbid);
            if (!empty($this->rules)) {
                $this->_expanded_rules = json_decode($this->rules, true);
            }
        }
        
        public function preupdate()
        {
            $this->encodeRules();
        }
        
        public function preInsert()
        {
            $this->encodeRules();
        }
        
        protected function encodeRules()
        {
            $this->rules = json_encode($this->_expanded_rules);
        }
        
        function set(&$parent)
        {
            $this->parent = & $parent;
        }
        
        function getImageUrl($img, $default)
        {
            $root = DEFAULT_PUBDIR;
            
            $socStyle = $this->parent->Getparam("CORE_SOCSTYLE");
            // first see if i have an society style
            if (($socStyle != "") && file_exists($root . "/STYLE/" . $socStyle . "/Images/" . $img)) {
                return ("STYLE/" . $socStyle . "/Images/" . $img);
            }
            
            if (file_exists($root . "/STYLE/" . $this->name . "/Images/" . $img)) {
                return ("STYLE/" . $this->name . "/Images/" . $img);
            } else {
                return ($default);
            }
        }
        
        function getLayoutFile($layname, $default = "")
        {
            $root = DEFAULT_PUBDIR;
            
            $socStyle = $this->parent->Getparam("CORE_SOCSTYLE");
            // first see if i have an society style
            if ($socStyle != "") {
                $file = $root . "/STYLE/" . $socStyle . "/Layout/" . $layname;
                if (file_exists($file)) return ($file);
            }
            
            $file = $root . "/STYLE/" . $this->name . "/Layout/" . $layname;
            if (file_exists($file)) return ($file);
            
            return ($default);
        }
        
        public function setRules(Array $filesDefinition)
        {
            $this->_expanded_rules = $filesDefinition;
        }
        
        public function setRule($fileType, $file, $definition)
        {
            $this->_expanded_rules[$fileType][$file] = $definition;
        }
        
        public function getRules()
        {
            return $this->_expanded_rules;
        }
        
        public function getRule($fileType, $file)
        {
            $file = substr($file, 4); // delete "css/"
            if (!isset($this->_expanded_rules[$fileType])) {
                return null;
            }
            
            if (!isset($this->_expanded_rules[$fileType][$file])) {
                return null;
            }
            return $this->_expanded_rules[$fileType][$file];
        }
    }
    
    class ErrorCodeSTY
    {
        /**
         * @errorCode sty file not reachable
         */
        const STY0001 = 'sty file not reachable: %s';
        /**
         * @errorCode malformed sty file
         */
        const STY0002 = 'malformed sty file: %s';
        /**
         * @errorCode style registration error
         */
        const STY0003 = 'style registration error: %s';
        /**
         * @errorCode source file not readable for sty target
         */
        const STY0004 = 'source file not readable for sty target: %s';
        /**
         * @errorCode file creation error
         */
        const STY0005 = 'file creation error: %s';
        /**
         * @errorCode parser does not implements required interfaces
         */
        const STY0006 = 'parser does not implements required interfaces: %s';
        /**
         * @errorCode unimplemented feature
         */
        const STY0007 = 'unimplemented feature: %s';
        /**
         * @errorCode when try create target directory
         * @see dcpCssCopyDirectory
         */
        const STY0008 = 'cannot create target directory: %s';
        /**
         * @errorCode when try create view target directory
         * @see dcpCssCopyDirectory
         */
        const STY0009 = 'origin "%s" is not a directory';
        /**
         * @errorCode copy error when copy directory for css
         * @see dcpCssCopyDirectory
         */
        const STY0010 = 'cannot copy from origin "%s" to "%s"';
    }
}

namespace Dcp\Style
{
    class Exception extends \Dcp\Exception
    {
        /**
         * for beautifier
         */
        private function _bo()
        {
            if (true) $a = 1;
        }
    }
    
    interface IParser
    {
        /**
         * @param string|string[] $srcFiles path or array of path of source file(s) relative to server root
         * @param array $options
         * @param array $styleConfig full style configuration
         */
        public function __construct($srcFiles, Array $options, Array $styleConfig);
        /**
         * @param string $destFile destination file path relative to server root (if null, parsed result is returned)
         * @throws Exception
         * @return mixed
         */
        public function gen($destFile = null);
    }
    
    interface ICssParser extends IParser
    {
    }
    
    class dcpCssConcatParser implements ICssParser
    {
        
        protected $_srcFiles = null;
        /**
         * @param string|string[] $srcFiles path or array of path of source file(s) relative to server root
         * @param array $options
         * @param array $styleConfig full style configuration
         */
        public function __construct($srcFiles, Array $options, Array $styleConfig)
        {
            if (is_array($srcFiles)) {
                $this->_srcFiles = $srcFiles;
            } else {
                $this->_srcFiles = array(
                    $srcFiles
                );
            }
        }
        /**
         * @param string $destFile destination file path relative to server root (if null, parsed result is returned)
         * @throws Exception
         * @return mixed
         */
        public function gen($destFile = null)
        {
            $pubDir = DEFAULT_PUBDIR;
            // prepare target dir
            $fullTargetPath = $pubDir . DIRECTORY_SEPARATOR . $destFile;
            $fullTargetDirname = dirname($fullTargetPath);
            if (!is_dir($fullTargetDirname) && (false === mkdir($fullTargetDirname, 0777, true))) {
                throw new Exception("STY0005", "$fullTargetDirname dir could not be created for file $destFile");
            }
            
            $targetHandler = fopen($fullTargetPath, 'w');
            if (false === $targetHandler) {
                throw new Exception("STY0005", "$destFile dir could not be created");
            }
            
            foreach ($this->_srcFiles as $srcPath) {
                $srcFullPath = $pubDir . DIRECTORY_SEPARATOR . $srcPath;
                if (!is_readable($srcFullPath)) {
                    if (!file_exists($srcFullPath)) {
                        $msg = "source file $srcFullPath does not exists for file $destFile";
                    } else {
                        $msg = "source file $srcFullPath is not readable for file $destFile";
                    }
                    throw new Exception("STY0004", $msg);
                }
                if (false === fwrite($targetHandler, file_get_contents($srcFullPath))) {
                    throw new Exception("STY0005", "data from $srcFullPath could not be written to file $fullTargetPath");
                }
            }
            if (false === fclose($targetHandler)) {
                throw new Exception("STY0005", "$fullTargetPath could not be closed");
            }
        }
    }
    
    class dcpCssTemplateParser implements ICssParser
    {
        
        protected $_srcFiles = null;
        protected $_styleConfig = array();
        /**
         * @param string|string[] $srcFiles path or array of path of source file(s) relative to server root
         * @param array $options array of options
         * @param array $styleConfig full style configuration
         */
        public function __construct($srcFiles, Array $options, Array $styleConfig)
        {
            if (is_array($srcFiles)) {
                $this->_srcFiles = $srcFiles;
            } else {
                $this->_srcFiles = array(
                    $srcFiles
                );
            }
            $this->_styleConfig = $styleConfig;
        }
        /**
         * @param string $destFile destination file path relative to server root (if null, parsed result is returned)
         * @throws Exception
         * @return mixed
         */
        public function gen($destFile = null)
        {
            $template = '';
            $pubDir = DEFAULT_PUBDIR;
            global $action;
            foreach ($this->_srcFiles as $srcPath) {
                $srcFullPath = $pubDir . DIRECTORY_SEPARATOR . $srcPath;
                if (!is_readable($srcFullPath)) {
                    if (!file_exists($srcFullPath)) {
                        $msg = "source file $srcFullPath does not exists for file $destFile";
                    } else {
                        $msg = "source file $srcFullPath is not readable for file $destFile";
                    }
                    throw new Exception("STY0004", $msg);
                }
                $template.= file_get_contents($srcFullPath);
            }
            // prepare target dir
            $fullTargetPath = $pubDir . DIRECTORY_SEPARATOR . $destFile;
            $fullTargetDirname = dirname($fullTargetPath);
            if (!is_dir($fullTargetDirname) && (false === mkdir($fullTargetDirname, 0777, true))) {
                throw new Exception("STY0005", "$fullTargetDirname dir could not be created for file $destFile");
            }
            $explorerP = getExplorerParamtersName();
            foreach ($explorerP as $ep) {
                $action->parent->SetVolatileParam($ep, null);
            }
            $lay = new \Layout("", $action, $template);
            $template = $lay->gen();
            $keyForStyle = preg_replace("/css\//", "", $destFile);
            if (!isset($this->_styleConfig["sty_rules"]["css"][$keyForStyle]["flags"]) || $this->_styleConfig["sty_rules"]["css"][$keyForStyle]["flags"] !== \Style::RULE_FLAG_PARSE_ON_RUNTIME) {
                $subRepositoryLevel = substr_count($destFile, "/");
                $levelToGo = "";
                for ($i = 0; $i < $subRepositoryLevel; $i++) {
                    $levelToGo.= "../";
                }
                $template = preg_replace('/(url\()\s*([\'"]?)\s*(.*?)\s*(\2\s*\))/', "$1$2" . $levelToGo . "$3$4", $template);
            }
            if (false === file_put_contents($fullTargetPath, $template)) {
                throw new Exception("STY0005", "$fullTargetPath could not be written for file $destFile");
            }
        }
    }
    
    class dcpCssCopyDirectory implements ICssParser
    {
        
        protected $_srcFiles = null;
        /**
         * @param string|string[] $srcFiles path or array of path of source file(s) relative to server root
         * @param array $options array of options
         * @param array $styleConfig full style configuration
         */
        public function __construct($srcFiles, Array $options, Array $styleConfig)
        {
            if (is_array($srcFiles)) {
                $this->_srcFiles = $srcFiles;
            } else {
                $this->_srcFiles = array(
                    $srcFiles
                );
            }
        }
        /**
         * @param string $destFile destination file path relative to server root (if null, parsed result is returned)
         * @throws Exception
         * @return mixed
         */
        public function gen($destFile = null)
        {
            $template = '';
            $pubDir = DEFAULT_PUBDIR;
            global $action;
            foreach ($this->_srcFiles as $srcPath) {
                $srcFullPath = $pubDir . DIRECTORY_SEPARATOR . $srcPath;
                if (!is_dir($srcFullPath)) {
                    
                    throw new Exception("STY0009", $srcFullPath);
                }
                if (!is_dir($pubDir . DIRECTORY_SEPARATOR . $destFile)) {
                    $r = mkdir($pubDir . DIRECTORY_SEPARATOR . $destFile);
                    if ($r === false) {
                        throw new Exception("STY0008", $pubDir . DIRECTORY_SEPARATOR . $destFile);
                    }
                }
                $cpCmd = sprintf("cp -r %s/* %s", escapeshellarg($srcFullPath) , escapeshellarg($pubDir . DIRECTORY_SEPARATOR . $destFile));
                $r = shell_exec("$cpCmd 2>&1 && echo 1");
                if ($r === null) {
                    throw new Exception("STY0010", $srcFullPath, $pubDir . DIRECTORY_SEPARATOR . $destFile);
                }
            }
        }
    }
}

