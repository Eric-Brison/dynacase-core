<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Document attribute enumerate
 * @class DocEnum
 */

class DocEnum extends DbObj
{
    
    public $fields = array(
        "famid",
        "attrid",
        "key",
        "label",
        "parentkey",
        "disabled",
        "eorder"
    );
    /**
     * identifier of family of enum
     * @public int
     */
    public $famid;
    /**
     * identifier of family attribute which used enum
     * @public text
     */
    public $attrid;
    /**
     * enum value
     * @public string
     */
    public $key;
    /**
     * default label key
     * @public string
     */
    public $label;
    /**
     * order to display list enum items
     * @public string
     */
    public $eorder;
    /**
     * key of parent enum
     * @public int
     */
    public $parentkey;
    
    public $id_fields = array(
        "famid",
        "attrid",
        "key"
    );
    protected $needChangeOrder = false;
    /**
     * @var bool
     */
    public $disabled;
    public $dbtable = "docenum";
    
    public $sqlcreate = '
create table docenum (
                   famid int not null,
                   attrid text not null,
                   key text,
                   label text,
                   parentkey text,
                   disabled bool,
                   eorder int);
create index if_docenum on docenum(famid, attrid);
create unique index i_docenum on docenum(famid, attrid,  key);
';
    
    public function postUpdate()
    {
        if ($this->needChangeOrder) {
            $this->shiftOrder($this->eorder);
        }
    }
    
    public function postInsert()
    {
        if ($this->needChangeOrder) {
            $this->shiftOrder($this->eorder);
        }
    }
    
    function preUpdate()
    {
        $this->consolidateOrder();
        return '';
    }
    function preInsert()
    {
        $this->consolidateOrder();
        return '';
    }
    /**
     * get last order
     */
    protected function consolidateOrder()
    {
        if (empty($this->eorder) || $this->eorder < 0) {
            $sql = sprintf("select max(eorder) from docenum where famid = '%s' and attrid='%s'", pg_escape_string($this->famid) , pg_escape_string($this->attrid));
            simpleQuery($this->dbaccess, $sql, $newOrder, true, true);
            if ($newOrder > 0) {
                $this->eorder = intval($newOrder) + 1;
            }
        }
    }
    public function shiftOrder($n)
    {
        
        if ($n > 0) {
            $sql = sprintf("update docenum set eorder=eorder + 1 where famid = '%s' and attrid='%s' and key != '%s' and eorder >= %d", pg_escape_string($this->famid) , pg_escape_string($this->attrid) , pg_escape_string($this->key) , $n);
            simpleQuery($this->dbaccess, $sql);
            $seqName = uniqid("tmpseqenum");
            $sql = sprintf("create temporary sequence %s;", $seqName);
            
            $sql.= sprintf("UPDATE docenum SET eorder = neworder from (SELECT *, nextval('%s') as neworder from (select * from docenum where  famid='%s' and attrid = '%s'  order by eorder) as tmpz) as w where w.famid=docenum.famid and w.attrid=docenum.attrid and docenum.key=w.key;", $seqName, pg_escape_string($this->famid) , pg_escape_string($this->attrid));
            
            simpleQuery($this->dbaccess, $sql);
        }
    }
    public function exists()
    {
        if ($this->famid && $this->attrid && $this->key !== null) {
            simpleQuery($this->dbaccess, sprintf("select true from docenum where famid=%d and attrid='%s' and key='%s'", ($this->famid) , pg_escape_string($this->attrid) , pg_escape_string($this->key)) , $r, true, true);
            return $r;
        }
        return false;
    }
    
    public static function getFamilyEnums($famId, $attrid)
    {
        if (!is_numeric($famId)) {
            $famId = getFamIdFromName(getDbAccess() , $famId);
        }
        $attrid = strtolower($attrid);
        $sql = sprintf("select * from docenum where famid=%d and attrid='%s' order by eorder", $famId, pg_escape_string($attrid));
        simpleQuery(getDbAccess() , $sql, $enums);
        return $enums;
    }
    public static function getDisabledKeys($famId, $attrid)
    {
        if (!is_numeric($famId)) {
            $famId = getFamIdFromName(getDbAccess() , $famId);
        }
        $attrid = strtolower($attrid);
        $sql = sprintf("select key from docenum where famid=%d and attrid='%s' and disabled", $famId, pg_escape_string($attrid));
        
        simpleQuery(getDbAccess() , $sql, $dKeys, true);
        return $dKeys;
    }
    
    protected function setOrder($beforeThan)
    {
        $sql = sprintf("SELECT count(*) FROM docenum WHERE famid = %d AND attrid = '%s'", $this->famid, pg_escape_string($this->attrid));
        simpleQuery($this->dbaccess, $sql, $count, true, true);
        if ($beforeThan !== null) {
            $sql = sprintf("select eorder from docenum where famid=%d and attrid='%s' and key='%s'", $this->famid, pg_escape_string($this->attrid) , pg_escape_string($beforeThan));
            simpleQuery($this->dbaccess, $sql, $beforeOrder, true, true);
            if ($beforeOrder) {
                $this->eorder = $beforeOrder;
            } else {
                /* If the next key does not exists, then set order to count + 1 */
                $this->eorder = $count + 1;
            }
        } else if (empty($this->eorder)) {
            /*
             * If item has no beforeThan and eorder is not set, then we assume it's the last one
             * (there is nothing after him). So, the order is the number of items + 1
            */
            $this->eorder = $count + 1;
        }
    }
    public static function addEnum($famId, $attrid, EnumStructure $enumStruct)
    {
        if (!is_numeric($famId)) {
            $famId = getFamIdFromName(getDbAccess() , $famId);
        }
        $attrid = strtolower($attrid);
        $enum = new DocEnum("", array(
            $famId,
            $attrid,
            $enumStruct->key
        ));
        if ($enum->isAffected()) {
            throw new \Dcp\Exception(sprintf("Enum %s#%s#%s already exists", $famId, $attrid, $enumStruct->key));
        }
        
        $enum->famid = $famId;
        $enum->attrid = $attrid;
        $enum->key = $enumStruct->key;
        $enum->label = $enumStruct->label;
        $enum->disabled = ($enumStruct->disabled === true);
        $enum->needChangeOrder = true;
        $enum->eorder = $enumStruct->absoluteOrder;
        if ($enumStruct->orderBeforeThan === null) {
            $enum->setOrder(null);
        } else {
            $enum->setOrder($enumStruct->orderBeforeThan);
        }
        $err = $enum->add();
        if ($err) {
            throw new \Dcp\Exception(sprintf("Cannot add enum %s#%s#%s : %s", $famId, $attrid, $enumStruct->key, $err));
        }
        
        if ($enumStruct->localeLabel) {
            foreach ($enumStruct->localeLabel as $lLabel) {
                self::changeLocale($famId, $attrid, $enumStruct->key, $lLabel->lang, $lLabel->label);
            }
        }
    }
    
    public static function modifyEnum($famId, $attrid, EnumStructure $enumStruct)
    {
        if (!is_numeric($famId)) {
            $famId = getFamIdFromName(getDbAccess() , $famId);
        }
        $attrid = strtolower($attrid);
        $enum = new DocEnum("", array(
            $famId,
            $attrid,
            $enumStruct->key
        ));
        if (!$enum->isAffected()) {
            throw new \Dcp\Exception(sprintf("Enum %s#%s#%s not found", $famId, $attrid, $enumStruct->key));
        }
        
        $enum->label = $enumStruct->label;
        $enum->disabled = ($enumStruct->disabled === true);
        if ($enum->eorder != $enumStruct->absoluteOrder) {
            $enum->needChangeOrder = true;
            $enum->eorder = $enumStruct->absoluteOrder;
        }
        if ($enumStruct->orderBeforeThan) {
            $enum->setOrder($enumStruct->orderBeforeThan);
        }
        
        $err = $enum->modify();
        if ($err) {
            throw new \Dcp\Exception(sprintf("Cannot modify enum %s#%s#%s : %s", $famId, $attrid, $enumStruct->key, $err));
        }
        if ($enumStruct->localeLabel) {
            foreach ($enumStruct->localeLabel as $lLabel) {
                self::changeLocale($famId, $attrid, $enumStruct->key, $lLabel->lang, $lLabel->label);
            }
        }
    }
    
    public static function getMoFilename($famId, $lang)
    {
        $fam = new_Doc("", $famId);
        
        $moFile = sprintf("%s/locale/%s/LC_MESSAGES/customFamily_%s.mo", DEFAULT_PUBDIR, substr($lang, 0, 2) , $fam->name);
        return $moFile;
    }
    /**
     * @param $famId
     * @param $attrid
     * @param $enumId
     * @param $lang
     * @param $label
     * @throws Dcp\Exception
     */
    public static function changeLocale($famId, $attrid, $enumId, $lang, $label)
    {
        setLanguage($lang);
        $fam = new_Doc("", $famId);
        $oa = $fam->getAttribute($attrid);
        if (!$oa) {
            throw new \Dcp\Exception(sprintf("Locale : Enum %s#%s#%s not found", $famId, $attrid, $enumId));
        }
        /**
         * @var NormalAttribute $oa
         */
        $oa->resetEnum();
        //$curLabel = $oa->getEnumLabel($enumId);
        if ($label !== null) {
            
            $moFile = self::getMoFilename($fam->name, $lang);
            $poFile = sprintf("%s.po", (substr($moFile, 0, -3)));
            $msgInit = '';
            $msgInit = sprintf('msgid ""
msgstr ""
"Project-Id-Version: Custom enum for %s\n"
"Language: %s\n"
"PO-Revision-Date: %s"
"MIME-Version: 1.0\n"
"Content-Type: text/plain; charset=UTF-8\n"
"Content-Transfer-Encoding: 8bit\n"', $fam->name, substr($lang, 0, 2) , date('Y-m-d H:i:s'));
            if (file_exists($moFile)) {
                // Just test mo validity
                $cmd = sprintf("(msgunfmt %s > %s) 2>&1", escapeshellarg($moFile) , escapeshellarg($poFile));
                
                exec($cmd, $output, $ret);
                if ($ret) {
                    throw new \Dcp\Exception(sprintf("Locale : Enum %s#%s#%s error : %s", $famId, $attrid, $enumId, implode(',', $output)));
                }
            } else {
                file_put_contents($poFile, $msgInit);
            }
            // add new entry
            $localeKey = sprintf("%s#%s#%s", $fam->name, $oa->id, $enumId);
            $msgEntry = sprintf('msgid "%s"' . "\n" . 'msgstr "%s"', str_replace('"', '\\"', $localeKey) , str_replace('"', '\\"', $label));
            $content = file_get_contents($poFile);
            // fuzzy old entry
            $match = sprintf('msgid "%s"', $localeKey);
            $content = str_replace($match, "#, fuzzy\n$match", $content);
            // delete previous header
            $content = str_replace('msgid ""', "#, fuzzy\nmsgid \"- HEADER DELETION -\"", $content);
            
            file_put_contents($poFile, $msgInit . $msgEntry . "\n\n" . $content);
            $cmd = sprintf("(msguniq --use-first %s | msgfmt - -o %s; rm -f %s) 2>&1", escapeshellarg($poFile) , escapeshellarg($moFile) , escapeshellarg($poFile));
            exec($cmd, $output, $ret);
            if ($ret) {
                print $cmd;
                throw new \Dcp\Exception(sprintf("Locale : Enum %s#%s#%s error : %s", $famId, $attrid, $enumId, implode(',', $output)));
            }
        }
    }
}
class EnumStructure
{
    /**
     * @var string enum key
     */
    public $key;
    public $label;
    /**
     * @var bool
     */
    public $disabled;
    /**
     *  @var int enum order
     *  first order is 1
     * last order is -1 (or 0)
     */
    public $absoluteOrder;
    public $orderBeforeThan;
    /**
     * @var EnumLocale[]
     */
    public $localeLabel;
    public function affect(array $o)
    {
        $this->key = null;
        $this->label = null;
        $this->disabled = false;
        $this->relativeOrder = null;
        $this->orderBeforeThan = null;
        $this->localeLabel = array();
        foreach ($o as $k => $v) {
            if ($k != "localeLabel") {
                $this->$k = $v;
            } else {
                foreach ($v as $locale) {
                    $this->localeLabel[] = new EnumLocale($locale["lang"], $locale["label"]);
                }
            }
        }
    }
}
class EnumLocale
{
    public $lang;
    public $label;
    public function __construct($lang, $label)
    {
        $this->lang = $lang;
        $this->label = $label;
    }
}

