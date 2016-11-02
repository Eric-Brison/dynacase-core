<?php
/*
 * @author Anakeen
 * @package FDL
*/

class XMLSplitter
{
    /**
     * Last error message
     * @var string
     */
    public $errmsg = '';
    /**
     * The xml_parser
     * @var resource
     */
    private $xml_parser = false;
    /**
     * The XML input file pathname
     * @var string
     */
    private $in_file = false;
    /**
     * The XML input file filedescriptor
     * @var resource
     */
    private $in_fh = false;
    /**
     * The current XML output file pathname
     * @var string
     */
    private $out_file = false;
    /**
     * The current XML output file filedescriptor
     * @var resource
     */
    private $out_fh = false;
    /**
     * The current XML node depth
     * @var integer
     */
    private $depth = 0;
    
    private $fileIndex = 0;
    /**
     * By default, escape only '<', '>' and '&' chars,
     * in attribute values, for compatibility with the
     * extractFileFromXmlDocument() function.
     * @var boolean
     */
    private $compatibilityEscape = true;
    /**
     * Split a XML file into smaller XML files: each child node of the root node will be
     * stored into a dedicated file named after the node 'name' or 'id' attribute.
     *
     * @param string $splitdir the directory in which the nodes XML files will be produced
     * @throws Dcp\Exception
     */
    public function __construct($splitdir)
    {
        if (!is_dir($splitdir) || !is_writable($splitdir)) {
            $this->errmsg = sprintf(_("Invalid directory '%s'.") , $splitdir);
            throw new Dcp\Exception($this->errmsg);
        }
        
        $this->splitdir = $splitdir;
        
        $this->xml_parser = xml_parser_create('UTF-8');
        xml_parser_set_option($this->xml_parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($this->xml_parser, XML_OPTION_SKIP_WHITE, 0);
        
        xml_set_object($this->xml_parser, $this);
        xml_set_element_handler($this->xml_parser, "startElement", "endElement");
        xml_set_character_data_handler($this->xml_parser, "characterData");
    }
    /**
     * Parse the given XML input file and produce the XML output files.
     *
     * @param string $file the XML input file
     * @throws Exception
     */
    public function split($file)
    {
        $this->open($file);
        
        try {
            $eof = false;
            while (!$eof) {
                if (($data = fread($this->in_fh, 8192)) === false) {
                    $this->errmsg = sprintf(_("Error reading from file '%s'") , $this->in_file);
                    throw new Dcp\Exception($this->errmsg);
                }
                $eof = feof($this->in_fh);
                if (!xml_parse($this->xml_parser, $data, $eof)) {
                    $this->errmsg = sprintf(_("XML error %s at line %d") , xml_error_string(xml_get_error_code($this->xml_parser)) , xml_get_current_line_number($this->xml_parser));
                    throw new Dcp\Exception($this->errmsg);
                }
            }
        }
        catch(Exception $e) {
            $this->close();
            throw $e;
        }
        $this->close();
    }
    /**
     * Close all files opened by the XML Splitter.
     *
     * @return void
     */
    public function close()
    {
        if ($this->xml_parser !== false) {
            xml_parser_free($this->xml_parser);
        }
        $this->xml_parser = false;
        $this->closeOutputFile();
        $this->closeInputFile();
    }
    /**
     * Escape XML reserved chars into their corresponding entities
     *
     * @param string $str the string to escape
     * @return string the string with reserved characters escaped
     */
    static public function escapeEntities($str)
    {
        $str = str_replace(array(
            '&',
            '"',
            "'",
            '<',
            '>'
        ) , array(
            '&amp;',
            '&quot;',
            '&apos;',
            '&lt;',
            '&gt;'
        ) , $str);
        return $str;
    }
    /**
     * Unescape XML entities into their corresponding char
     *
     * @param string $str the string to unescape
     * @return string the string with the XML entities converted back to their characters
     */
    static public function unescapeEntities($str)
    {
        $str = str_replace(array(
            '&quot;',
            '&apos;',
            '&lt;',
            '&gt;',
            '&amp;'
        ) , array(
            '"',
            "'",
            '<',
            '>',
            '&'
        ) , $str);
        return $str;
    }
    /**
     * Escape only '<', '>' and '&' chars for compatibility
     * with the extractFileFromXmlDocument() function.
     *
     * @param string $str the string to escape
     * @return string the string with reserved characters escaped
     */
    static private function compatibilityEscapeEntities($str)
    {
        $str = str_replace(array(
            '&',
            '<',
            '>'
        ) , array(
            '&amp;',
            '&lt;',
            '&gt;'
        ) , $str);
        return $str;
    }
    /**
     * Open the input XML file that will be splitted
     *
     * @param string $file the XML input file pathname
     * @throws Dcp\Exception
     * @return void
     */
    private function open($file)
    {
        $this->in_file = $file;
        $this->in_fh = fopen($this->in_file, "r");
        if ($this->in_fh === false) {
            $this->errmsg = sprintf(_("Could not open '%s' for reading.") , $this->in_file);
            throw new Dcp\Exception($this->errmsg);
        }
    }
    /**
     * Write a string to the current output file.
     *
     * @param string $str the string to write
     * @throws Dcp\Exception
     * @return void
     */
    private function writeOutputFile($str)
    {
        if ($this->out_fh !== false) {
            $ret = fwrite($this->out_fh, $str);
            if ($ret === false) {
                $this->errmsg = sprintf(_("Error writing to ouput file '%s'.") , $this->out_file);
                throw new Dcp\Exception($this->errmsg);
            }
        }
    }
    /**
     * Write a starting node.
     *
     * @param array $node array structure containing the starting node element
     * @return void
     */
    private function printStartNode(array $node)
    {
        $attrList = array();
        foreach ($node['attrs'] as $name => $value) {
            
            $value = $this::escapeEntities($value);
            
            $attrList[] = sprintf('%s="%s"', $name, $value);
        }
        $str = sprintf("<%s%s%s>", $node['name'], count($attrList) > 0 ? ' ' : '', join(" ", $attrList));
        $this->writeOutputFile($str);
    }
    /**
     * Write an ending node.
     *
     * @param array $node array structure containing the ending node element
     * @return void
     */
    private function printEndNode(array $node)
    {
        $str = sprintf("</%s>\n", $node['name']);
        $this->writeOutputFile($str);
    }
    /**
     * Write data node
     *
     * @param string $data the node data
     * @return void
     */
    private function printData($data)
    {
        $str = $data;
        if ($this->compatibilityEscape) {
            $str = $this::compatibilityEscapeEntities($str);
        } else {
            $str = $this::escapeEntities($str);
        }
        $this->writeOutputFile($str);
    }
    /**
     * XML parser handler for character data
     *
     * @param resource $parser the XML parser resource
     * @param string $data the data extracted by the parser
     * @return void
     */
    /** @noinspection PhpUnusedPrivateMethodInspection */
    private function characterData(&$parser, $data)
    {
        if ($this->depth >= 1) {
            $this->printData($data);
        }
    }
    /**
     * XML Parser handler for start element
     *
     * @param resource $parser the XML parser resource
     * @param string $name the current opening tag name
     * @param array $attrs the current opening tag attributes
     * @throws Dcp\Exception
     * @return void
     */
    /** @noinspection PhpUnusedPrivateMethodInspection */
    private function startElement(&$parser, $name, $attrs)
    {
        $node = array(
            'name' => $name,
            'attrs' => $attrs
        );
        $this->depth++;
        if ($this->depth == 1 && $node['name'] != 'documents') {
            $this->errmsg = sprintf(_("XML Root node is not a '%s' node (root node is '%s').") , 'documents', $node['name']);
            throw new Dcp\Exception($this->errmsg);
        }
        if ($this->depth == 2) {
            $this->openOutputFile($node);
        }
        $this->printStartNode($node);
    }
    /**
     * XML Parser handler for end element
     *
     * @param resource $parser the XML parser resource
     * @param string $name the current closing tag name
     * @return void
     */
    /** @noinspection PhpUnusedPrivateMethodInspection */
    private function endElement(&$parser, $name)
    {
        $node = array(
            'name' => $name
        );
        $this->depth--;
        $this->printEndNode($node);
        if ($this->depth == 1) {
            $this->closeOutputFile();
        }
    }
    /**
     * Create and open an output file for the given starting node
     *
     * The output file name is composed from the 'name' or 'id' attribute.
     * If there are no name/id attributes, then a random string is used.
     *
     * @param array $node the current starting node
     * @throws Dcp\Exception
     * @return void
     */
    private function openOutputFile(array $node)
    {
        if ($this->out_fh !== false) {
            $this->errmsg = sprintf(_("Output file '%s' is already opened.") , $this->out_file);
            error_log($this->errmsg);
            throw new Dcp\Exception($this->errmsg);
        }
        
        if (isset($node['attrs']['name']) && preg_match('/^[a-zA-Z0-9_-]+$/', $node['attrs']['name'])) {
            $fname = $node['attrs']['name'];
        } elseif (isset($node['attrs']['id']) && preg_match('/^[0-9]+$/', $node['attrs']['id'])) {
            $fname = $node['attrs']['id'];
        } else {
            $fname = uniqid("new");
        }
        
        if ($fname == '') {
            $this->errmsg = sprintf(_("Could not generate output file name for node '%s'.") , $node['name']);
            throw new Dcp\Exception($this->errmsg);
        }
        
        $this->out_file = sprintf("%s%s%05d%s.xml", $this->splitdir, DIRECTORY_SEPARATOR, $this->fileIndex++, $fname);
        if (is_file($this->out_file)) {
            $this->errmsg = sprintf(_("Output file '%s' already exists.") , $this->out_file);
            throw new Dcp\Exception($this->errmsg);
        }
        $this->out_fh = fopen($this->out_file, 'wx');
        if ($this->out_fh === false) {
            $this->errmsg = sprintf(_("Xml import : Cannot create file %s") , $this->out_file);
            throw new Dcp\Exception($this->errmsg);
        }
        $this->writeOutputFile("<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
    }
    /**
     * Close the XML input file
     *
     * @return void
     */
    private function closeInputFile()
    {
        if ($this->in_fh !== false) {
            fclose($this->in_fh);
        }
        $this->in_fh = false;
        $this->in_file = false;
    }
    /**
     * Close the current node output file
     *
     * @return void
     */
    private function closeOutputFile()
    {
        if ($this->out_fh !== false) {
            fclose($this->out_fh);
        }
        $this->out_fh = false;
        $this->out_file = false;
    }
}
