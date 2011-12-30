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
    
    private $visibilities = array(
        'I',
        'H',
        'R',
        'W',
        'O',
        'S',
        'U'
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
        $this->syntaxOrder();
        $this->syntaxVisibility();
        
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
                list($code, $err) = $v;
                $terr[] = sprintf("{%s} %s:%s.", $code, $k, $err);
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
            $this->errKey('FA001', 'is empty');
        } elseif (strlen($id) > 63) {
            $this->errKey('FA002', 'too long (max 64 characters)');
        }
    }
    /**
     * test syntax order
     * must be an integer
     * @return void
     */
    public function syntaxOrder()
    {
        $order = $this->setKey('order');
        if ($this->isNodeNeedOrder()) {
            
            if (empty($order)) {
                $this->errKey('FA003', 'is empty');
            } elseif (!is_numeric($order)) {
                $this->errKey('FA004', sprintf('%s not a number', $order));
            }
        }
    }
    /**
     * test syntax order
     * must be an integer
     * @return void
     */
    public function syntaxVisibility()
    {
        $vis = $this->setKey('visibility');
        if (empty($vis)) {
            $this->errKey('FA005', 'is empty');
        } elseif (!in_array($vis, $this->visibilities)) {
            $this->errKey('FA006', sprintf('%s is not valid', $vis));
        }
    }
    /**
     * Test parent structure attribute needed when is not a tab or a frame
     * @return void
     */
    public function syntaxSet()
    {
        $key = $this->setKey('setid');
        if ($this->isNodeNeedSet()) {
            if (empty($key)) {
                $this->errKey('FA007', 'is empty');
            } elseif (strlen($key) > 63) {
                $this->errKey('FA008', 'too long (max 64 characters)');
            }
        } else {
            if ($key) {
                if (strlen($key) > 63) {
                    $this->errKey('FA008', 'too long (max 64 characters)');
                }
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
            $this->errKey('FA009', 'is empty');
        } elseif (!in_array($key, $this->types)) {
            if (preg_match('/([a-z]+)\(["\'].+["\']\)/i', $key, $reg)) {
                $type = $reg[1];
                if (!in_array($type, $this->types)) {
                    $this->errKey('FA010', sprintf('%s unrecognized in %s', $type, $key));
                }
            } else {
                $this->errKey('FA011', sprintf('%s syntax error', $key));
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
    private function isNodeNeedSet()
    {
        $type = $this->getType();
        return (($type != "tab") && ($type != "frame") && ($type != "menu") && ($type != "action"));
    }
    
    private function isNodeNeedOrder()
    {
        $type = $this->getType();
        return (($type != "tab") && ($type != "frame"));
    }
    
    private function setKey($key)
    {
        $this->cKey = $key;
        return $this->struct->$key;
    }
    
    private function errKey($code, $err)
    {
        
        $this->syntaxResult[$this->cKey] = array(
            $code,
            $err
        );
    }
}
