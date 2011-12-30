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
