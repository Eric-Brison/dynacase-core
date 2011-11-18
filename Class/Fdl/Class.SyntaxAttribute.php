<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */

class SyntaxAttribute
{
    /**
     * @var StructAttribute
     */
    private $struct = array();
    
    private $syntaxResult = array();
    /**
     * current key
     * @var string
     */
    private $cKey = '';
    
    private $types = array(
        "text",
        "longtext",
        "image",
        "file",
        "frame",
        "enum",
        "date",
        "integer",
        "int",
        "double",
        "money",
        "password",
        "ifile",
        "xml",
        "thesaurus",
        "idoc",
        "tab",
        "time",
        "timestamp",
        "array",
        "color",
        "menu",
        "action",
        "docid",
        "htmltext"
    );
    /**
     * a struct is
     * @param StructAttribute $attributeStruct
     */
    public function __construct(StructAttribute $attributeStruct = null)
    {
        $this->struct = $attributeStruct;
    }
    /**
     * analyze an attribute structure
     * @param null|StructAttribute $attributeStruct
     * @return string the syntax error, empty string if no error detected
     */
    public function analyze(StructAttribute $attributeStruct = null)
    {
        if ($attributeStruct) $this->struct = $attributeStruct;
        $this->syntaxResult = array();
        $this->syntaxId();
        $this->syntaxSet();
        $this->syntaxType();
        
        return $this->getResultError();
    }
    /**
     * return error message if syntax error detected after analyze
     * @return string
     */
    public function getResultError()
    {
        $terr = array();
        foreach ($this->syntaxResult as $k => $v) {
            if ($v) {
                $terr[] = sprintf("%s:%s.", $k, $v);
            }
        }
        return implode("\n", $terr);
    }
    /**
     * test syntax for document's identificator
     * @return void
     */
    public function syntaxId()
    {
        $id = $this->setKey('id');
        if (empty($id)) {
            $this->errKey('is empty');
        } elseif (strlen($id) > 63) {
            $this->errKey('too long (max 64 characters)');
        }
    }
    /**
     * test syntax order
     * must be an integer
     * @return void
     */
    public function syntaxOrder()
    {
        $id = $this->setKey('order');
        if ($this->isNodeNoNeedOrder()) {
        }
        if (empty($id)) {
            $this->errKey('is empty');
        } elseif (!is_integer($id)) {
            $this->errKey('not a number');
        }
    }
    /**
     * Test parent structure attribute needed when is not a tab or a frame
     * @return void
     */
    public function syntaxSet()
    {
        $key = $this->setKey('setid');
        if ($this->isNodeNoNeedSet()) {
            if ($key) {
                if (strlen($key) > 63) {
                    $this->errKey('too long (max 64 characters)');
                }
            }
        } else {
            if (empty($key)) {
                $this->errKey('is empty');
            } elseif (strlen($key) > 63) {
                $this->errKey('too long (max 64 characters)');
            }
        }
    }
    /**
     * test attribute type is a recognized type
     * @return void
     */
    public function syntaxType()
    {
        $key = $this->setKey('type');
        if (!$key) {
            $this->errKey('is empty');
        } elseif (!in_array($key, $this->types)) {
            if (preg_match('/([a-z]+)\(["\'].+["\']\)/i', $key, $reg)) {
                $type = $reg[1];
                if (!in_array($type, $this->types)) {
                    $this->errKey(sprintf('%s unrecognized in %s', $type, $key));
                }
            } else {
                $this->errKey(sprintf('%s syntax error', $key));
            }
        }
    }
    
    private function getType()
    {
        $type = $this->struct->type;
        if (preg_match('/([a-z]+)\(["\'].+["\']\)/i', $type, $reg)) {
            $type = $reg[1];
        }
        return $type;
    }
    private function isNodeNoNeedSet()
    {
        $type = $this->getType();
        return (($type == "tab") || ($type == "frame") || ($type == "menu") || ($type == "action"));
    }
    
    private function isNodeNoNeedOrder()
    {
        $type = $this->getType();
        return (($type == "tab") || ($type == "frame"));
    }
    
    private function setKey($key)
    {
        $this->cKey = $key;
        return $this->struct->$key;
    }
    
    private function errKey($err)
    {
        $this->syntaxResult[$this->cKey] = $err;
    }
}

class StructAttribute
{
    public $id;
    public $setid;
    public $label;
    public $istitle;
    public $isabstract;
    public $type;
    public $order;
    public $visibility;
    public $isneeded;
    public $link;
    public $phpfile;
    public $phpfunc;
    public $elink;
    public $constraint;
    public $options;
}
