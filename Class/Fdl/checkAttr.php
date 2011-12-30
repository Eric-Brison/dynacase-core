<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Checking application accesses
 * @class CheckAttr
 * @brief Check application accesses when importing definition
 * @see ErrorCodeATTR
 */
class CheckAttr extends CheckData
{
    /**
     * @var StructAttribute
     */
    private $structAttr = null;
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
    
    private $postgreSqlWords = array(
        'all',
        'analyse',
        'analyze',
        'and',
        'any',
        'array',
        'as',
        'asc',
        'asymmetric',
        'both',
        'case',
        'cast',
        'check',
        'collate',
        'column',
        'constraint',
        'create',
        'current_date',
        'current_role',
        'current_time',
        'current_timestamp',
        'current_user',
        'default',
        'deferrable',
        'desc',
        'distinct',
        'do',
        'else',
        'end',
        'except',
        'false',
        'for',
        'foreign',
        'from',
        'grant',
        'group',
        'having',
        'in',
        'initially',
        'intersect',
        'into',
        'leading',
        'limit',
        'localtime',
        'localtimestamp',
        'new',
        'not',
        'null',
        'off',
        'offset',
        'old',
        'on',
        'only',
        'or',
        'order',
        'placing',
        'primary',
        'references',
        'returning',
        'select',
        'session_user',
        'some',
        'symmetric',
        'table',
        'then',
        'to',
        'trailing',
        'true',
        'union',
        'unique',
        'user',
        'using',
        'when',
        'where',
        'with'
    );
    /**
     * the attribute identificator
     * @var string
     */
    private $attrid;
    /**
     * analyze an attribute structure
     * @param array $data
     * @param mixed $extra
     * @return CheckAttr
     */
    public function check(array $data, &$extra = null)
    {
        $this->structAttr = new StructAttribute($data);
        $this->attrid = strtolower($this->structAttr->id);
        
        $this->syntaxId();
        $this->syntaxSet();
        $this->syntaxType();
        return $this;
    }
    /**
     * test syntax for document's identificator
     * @return void
     */
    public function syntaxId()
    {
        if (empty($this->attrid)) {
            $this->addError(ErrorCode::getError('ATTR0102'));
        } elseif (!$this->checkAttrSyntax($this->attrid)) {
            $this->addError(ErrorCode::getError('ATTR0100', $this->attrid));
        } else {
            if (in_array($this->attrid, $this->postgreSqlWords)) {
                $this->addError(ErrorCode::getError('ATTR0101', $this->attrid));
            } else {
                $doc = new Doc();
                if (in_array($this->attrid, $doc->fields)) {
                    $this->addError(ErrorCode::getError('ATTR0103', $this->attrid));
                }
            }
        }
    }
    /**
     * test syntax for document's identificator
     * @return void
     */
    public function syntaxSet()
    {
        $setId = strtolower($this->structAttr->setid);
        if ($setId && ($this->attrid == $setId)) {
            $this->addError(ErrorCode::getError('ATTR0202', $setId, $this->attrid));
        }
        if ($this->isNodeNeedSet()) {
            if (empty($setId)) {
                $this->addError(ErrorCode::getError('ATTR0201', $this->attrid));
            } elseif (!$this->checkAttrSyntax($setId)) {
                $this->addError(ErrorCode::getError('ATTR0200', $setId, $this->attrid));
            }
        } elseif ($setId) {
            if (!$this->checkAttrSyntax($setId)) {
                $this->addError(ErrorCode::getError('ATTR0200', $setId, $this->attrid));
            }
        }
    }
    /**
     * test attribute type is a recognized type
     * @return void
     */
    public function syntaxType()
    {
        
        $type = $this->structAttr->type;
        if (!$type) {
            $this->addError(ErrorCode::getError('ATTR0600', $this->attrid));
        } elseif (!in_array($type, $this->types)) {
            $basicType = $this->getType();
            if (!$basicType) {
                $this->addError(ErrorCode::getError('ATTR0602', $type, $this->attrid));
            } elseif (!in_array($basicType, $this->types)) {
                $this->addError(ErrorCode::getError('ATTR0601', $basicType, $this->attrid, implode(', ', $this->types)));
            }
        }
    }
    /**
     * @param string $attrid
     * @return bool
     */
    private function checkAttrSyntax($attrid)
    {
        if (preg_match("/^[A-Z_0-9]{1,63}$/i", $attrid)) {
            return true;
        }
        return false;
    }
    private function getType()
    {
        $type = trim($this->structAttr->type);
        $rtype = '';
        if (preg_match('/^([a-z]+)\(["\'].+["\']\)$/i', $type, $reg)) {
            $rtype = $reg[1];
        } elseif (preg_match('/^([a-z]+)$/i', $type, $reg)) {
            $rtype = $reg[1];
        }
        return $rtype;
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
    private $dataOrder = array(
        "id",
        "setid",
        "label",
        "istitle",
        "isabstract",
        "type",
        "order",
        "visibility",
        "isneeded",
        "link",
        "phpfile",
        "phpfunc",
        "elink",
        "constraint",
        "options"
    );
    
    public function __construct(array $data = array())
    {
        if (count($data) > 0) $this->set($data);
    }
    
    public function set(array $data)
    {
        $cid = 1;
        foreach ($this->dataOrder as $key) {
            $this->$key = trim($data[$cid]);
            $cid++;
        }
    }
}
