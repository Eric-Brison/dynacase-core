<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Document Attributes
 *
 * @author Anakeen
 * @version $Id: Class.DocAttribute.php,v 1.47 2008/12/11 10:06:51 eric Exp $
 * @package FDL
 */
/**
 */
/**
 *
 * Generic attribute class
 *
 * @author Anakeen
 */
class BasicAttribute
{
    const hiddenFieldId = Adoc::HIDDENFIELD;
    public $id;
    public $docid;
    public $labelText;
    public $visibility; // W, R, H, O, M, I
    public $mvisibility; ///mask visibility
    public $options;
    public $docname;
    public $type; // text, longtext, date, file, ...
    public $usefor; // = Q if parameters.
    public $ordered; // order to place attributes
    public $format; // subtypepublic
    public $isNormal = null;
    /**
     * @var FieldSetAttribute field set object
     */
    public $fieldSet;
    /**
     * @var array
     */
    public $_topt = null;
    /**
     * Construct a basic attribute
     *
     * @param string $id logical name of the attr
     * @param string $docid
     * @param string $label
     */
    function __construct($id, $docid, $label)
    {
        $this->id = $id;
        $this->docid = $docid;
        $this->labelText = $label;
    }
    /**
     * Return attribute label
     *
     * @return string
     */
    function getLabel()
    {
        $r = $this->docname . '#' . $this->id;
        $i = _($r);
        if ($i != $r) return $i;
        return $this->labelText;
    }
    /**
     * Return value of option $x
     *
     * @param string $x option name
     * @param string $def default value
     *
     * @return string
     */
    function getOption($x, $def = "")
    {
        if (!isset($this->_topt)) {
            $topt = explode("|", $this->options);
            $this->_topt = array();
            foreach ($topt as $k => $v) {
                if ($v) {
                    $v = explode("=", $v, 2);
                    $this->_topt[$v[0]] = isset($v[1]) ? $v[1] : null;
                }
            }
        }
        $r = $this->docname . '#' . $this->id . '#' . $x;
        $i = _($r);
        if ($i != $r) return $i;
        
        $v = (isset($this->_topt[$x]) && $this->_topt[$x] !== '') ? $this->_topt[$x] : $def;
        return $v;
    }
    /**
     * Return all value of options
     *
     * @return array
     */
    function getOptions()
    {
        if (!isset($this->_topt)) {
            $this->getOption('a');
        }
        return $this->_topt;
    }
    /**
     * Temporary change option
     *
     * @param string $x name
     * @param string $v value
     *
     * @return void
     */
    function setOption($x, $v)
    {
        if (!isset($this->_topt)) {
            $this->getOption($x);
        }
        $this->_topt[$x] = $v;
    }
    /**
     * temporary change visibility
     * @param string $vis new visibility : R|H|W|O|I
     * @return void
     */
    function setVisibility($vis)
    {
        $this->mvisibility = $vis;
    }
    /**
     * test if attribute is not a auto created attribute
     *
     * @return boolean
     */
    function isReal()
    {
        return $this->getOption("autocreated") != "yes";
    }
    /**
     * Escape value with xml entities
     *
     * @param string $s value
     * @param bool $quot to encode also quote "
     * @return string
     */
    static function encodeXml($s, $quot = false)
    {
        if ($quot) {
            return str_replace(array(
                '&',
                '<',
                '>',
                '"'
            ) , array(
                '&amp;',
                '&lt;',
                '&gt;',
                '&quot;'
            ) , $s);
        } else {
            return str_replace(array(
                '&',
                '<',
                '>'
            ) , array(
                '&amp;',
                '&lt;',
                '&gt;'
            ) , $s);
        }
    }
    /**
     * to see if an attribute is n item of an array
     *
     * @return boolean
     */
    function inArray()
    {
        return false;
    }
    /**
     * verify if accept multiple value
     *
     * @return boolean
     */
    function isMultiple()
    {
        return ($this->inArray() || ($this->getOption('multiple') === 'yes'));
    }
    /**
     * verify if attribute is multiple value and if is also in array multiple^2
     *
     * @return boolean
     */
    function isMultipleInArray()
    {
        return ($this->inArray() && ($this->getOption('multiple') === 'yes'));
    }
    /**
     * Get tab ancestor
     * false if not found
     *
     * @return FieldSetAttribute|bool
     */
    function getTab()
    {
        if ($this->type == 'tab') {
            return $this;
        }
        if (is_object($this->fieldSet) && method_exists($this->fieldSet, 'getTab') && ($this->fieldSet->id != Adoc::HIDDENFIELD)) {
            return $this->fieldSet->getTab();
        }
        return false;
    }
    /**
     * Export values as xml fragment
     *
     * @param array $la array of DocAttribute
     * @return string
     */
    function getXmlSchema($la)
    {
        return sprintf("<!-- no Schema %s (%s)-->", $this->id, $this->type);
    }
    /**
     * export values as xml fragment
     *
     * @param Doc $doc working doc
     * @param bool|\exportOptionAttribute $opt
     * @deprecated use \Dcp\ExportXmlDocument class instead
     *
     * @return string
     */
    function getXmlValue(Doc & $doc, $opt = false)
    {
        return sprintf("<!-- no value %s (%s)-->", $this->id, $this->type);
    }
    /**
     * Get human readable textual value
     * Fallback method
     *
     * @param Doc $doc current Doc
     * @param int $index index if multiple
     * @param array $configuration value
     *
     * @return string
     */
    public function getTextualValue(Doc $doc, $index = - 1, Array $configuration = array())
    {
        return null;
    }
    /**
     * Generate XML schema layout
     *
     * @param Layout $play
     */
    function common_getXmlSchema(&$play)
    {
        
        $lay = new Layout(getLayoutFile("FDL", "infoattribute_schema.xml"));
        $lay->set("aname", $this->id);
        $lay->set("label", $this->encodeXml($this->labelText));
        $lay->set("type", $this->type);
        $lay->set("visibility", $this->visibility);
        $lay->set("isTitle", false);
        $lay->set("phpfile", false);
        $lay->set("phpfunc", false);
        
        $lay->set("computed", false);
        $lay->set("link", '');
        $lay->set("elink", '');
        $lay->set("default", false);
        $lay->set("constraint", '');
        $tops = $this->getOptions();
        $t = array();
        foreach ($tops as $k => $v) {
            if ($k) $t[] = array(
                "key" => $k,
                "val" => $this->encodeXml($v)
            );
        }
        $lay->setBlockData("options", $t);
        
        $play->set("minOccurs", "0");
        $play->set("isnillable", "true");
        $play->set("maxOccurs", "1");
        $play->set("aname", $this->id);
        $play->set("appinfos", $lay->gen());
    }
}
/**
 * NormalAttribute Class
 * Non structural attribute (all attribute except frame and tab)
 *
 * @author Anakeen
 *
 */
class NormalAttribute extends BasicAttribute
{
    const _cEnum = "_CACHE_ENUM";
    const _cEnumLabel = "_CACHE_ENUMLABEL";
    const _cParent = "_CACHE_PARENT";
    /**
     * @var bool
     */
    public $needed; // Y / N
    public $format; // C format
    public $eformat; // format for edition : list,vcheck,hcheck
    public $repeat; // true if is a repeatable attribute
    public $isNormal = true;
    /**
     * @var bool
     */
    public $isInTitle;
    /**
     * @var bool
     */
    public $isInAbstract;
    public $link; // hypertext link
    public $phpfile;
    public $phpfunc;
    public $elink; // extra link
    public $phpconstraint; // special constraint set
    
    /**
     * @var bool special use for application interface
     */
    public $isAlone = false;
    /**
     * @var $enum array use for enum attributes
     */
    public $enum;
    /**
     * @var $enumlabel array use for enum attributes
     */
    public $enumlabel;
    /**
     * Array of separator by level of multiplicity for textual export
     * @var array
     */
    protected $textualValueMultipleSeparator = array(
        0 => "\n",
        1 => ", "
    );
    /**
     * @var array
     */
    private static $_cache = array();
    protected $originalPhpfile;
    protected $originalPhpfunc;
    /**
     * Normal Attribute constructor : non structural attribute
     *
     * @param int $id id of the attribute
     * @param int $docid id of the family
     * @param string $label default translate key
     * @param string $type kind of attribute
     * @param string $format format option
     * @param string $repeat is repeteable attr
     * @param int $order display order
     * @param string $link link option
     * @param string $visibility visibility option
     * @param bool $needed is mandotary attribute
     * @param bool $isInTitle is used to compute title
     * @param bool $isInAbstract is used in abstract view
     * @param FieldSetAttribute &$fieldSet parent attribute
     * @param string $phpfile php file used with the phpfunc
     * @param string $phpfunc helpers function
     * @param string $elink eling option
     * @param string $phpconstraint class php function
     * @param string $usefor Attribute or Parameter
     * @param string $eformat eformat option
     * @param string $options option string
     * @param string $docname
     */
    function __construct($id, $docid, $label, $type, $format, $repeat, $order, $link, $visibility, $needed, $isInTitle, $isInAbstract, &$fieldSet, $phpfile, $phpfunc, $elink, $phpconstraint = "", $usefor = "", $eformat = "", $options = "", $docname = "")
    {
        $this->id = $id;
        $this->docid = $docid;
        $this->labelText = $label;
        $this->type = $type;
        $this->format = $format;
        $this->eformat = $eformat;
        $this->ordered = $order;
        $this->link = $link;
        $this->visibility = $visibility;
        $this->needed = $needed;
        $this->isInTitle = $isInTitle;
        $this->isInAbstract = $isInAbstract;
        $this->fieldSet = & $fieldSet;
        $this->phpfile = $phpfile;
        $this->phpfunc = $phpfunc;
        $this->elink = $elink;
        $this->phpconstraint = $phpconstraint;
        $this->usefor = $usefor;
        $this->repeat = $repeat;
        $this->options = $options;
        $this->docname = $docname;
    }
    /**
     * temporary change need
     * @param bool $need true means needed, false not needed
     * @return void
     */
    function setNeeded($need)
    {
        $this->needed = $need;
    }
    /**
     * Parse htmltext and replace id by logicalname for links
     *
     * @param string $value Formated value of attribute
     * @return string Value transformed
     */
    function prepareHtmltextForExport($value)
    {
        if ($this->type == "htmltext") {
            $value = preg_replace_callback('/(data-initid=")([0-9]+)/', function ($matches)
            {
                $name = getNameFromId(getDbAccess() , $matches[2]);
                return $matches[1] . ($name ? $name : $matches[2]);
            }
            , $value);
        }
        return $value;
    }
    /**
     * Generate the xml schema fragment
     *
     * @param array $la array of DocAttribute
     *
     * @return string
     */
    function getXmlSchema($la)
    {
        switch ($this->type) {
            case 'text':
                return $this->text_getXmlSchema($la);
            case 'longtext':
            case 'htmltext':
                return $this->longtext_getXmlSchema($la);
            case 'int':
            case 'integer':
                return $this->int_getXmlSchema($la);
            case 'float':
            case 'money':
                return $this->float_getXmlSchema($la);
            case 'image':
            case 'file':
                return $this->file_getXmlSchema($la);
            case 'enum':
                return $this->enum_getXmlSchema($la);
            case 'thesaurus':
            case 'docid':
            case 'account':
                return $this->docid_getXmlSchema($la);
            case 'date':
                return $this->date_getXmlSchema($la);
            case 'timestamp':
                return $this->timestamp_getXmlSchema($la);
            case 'time':
                return $this->time_getXmlSchema($la);
            case 'array':
                return $this->array_getXmlSchema($la);
            case 'color':
                return $this->color_getXmlSchema($la);
            default:
                return sprintf("<!-- no Schema %s (type %s)-->", $this->id, $this->type);
        }
    }
    /**
     * Generate XML schema layout
     *
     * @param Layout $play
     */
    function common_getXmlSchema(&$play)
    {
        
        $lay = new Layout(getLayoutFile("FDL", "infoattribute_schema.xml"));
        $lay->set("aname", $this->id);
        $lay->set("label", $this->encodeXml($this->labelText));
        $lay->set("type", $this->type);
        $lay->set("visibility", $this->visibility);
        $lay->set("isTitle", $this->isInTitle);
        $lay->set("phpfile", $this->phpfile);
        $lay->set("phpfunc", $this->phpfunc);
        
        if (($this->type == "enum") && (!$this->phpfile) || ($this->phpfile == "-")) {
            $lay->set("phpfile", false);
            $lay->set("phpfunc", false);
        }
        $lay->set("computed", ((!$this->phpfile) && (substr($this->phpfunc, 0, 2) == "::")));
        $lay->set("link", $this->encodeXml($this->link));
        $lay->set("elink", $this->encodeXml($this->elink));
        $lay->set("default", false); // TODO : need detect default value
        $lay->set("constraint", $this->phpconstraint);
        $tops = $this->getOptions();
        $t = array();
        foreach ($tops as $k => $v) {
            if ($k) $t[] = array(
                "key" => $k,
                "val" => $this->encodeXml($v)
            );
        }
        $lay->setBlockData("options", $t);
        
        $play->set("minOccurs", $this->needed ? "1" : "0");
        $play->set("isnillable", $this->needed ? "false" : "true");
        $play->set("maxOccurs", (($this->getOption('multiple') == 'yes') ? "unbounded" : "1"));
        $play->set("aname", $this->id);
        $play->set("appinfos", $lay->gen());
    }
    /**
     * export values as xml fragment
     *
     * @param Doc $doc working doc
     * @param bool|\exportOptionAttribute $opt
     * @deprecated use \Dcp\ExportXmlDocument class intead
     *
     * @return string
     */
    function getXmlValue(Doc & $doc, $opt = false)
    {
        if ($opt->index > - 1) $v = $doc->getMultipleRawValues($this->id, null, $opt->index);
        else $v = $doc->getRawValue($this->id, null);
        //if (! $v) return sprintf("<!-- no value %s -->",$this->id);
        if ($this->getOption("autotitle") == "yes") {
            return sprintf("<!--autotitle %s %s -->", $this->id, $v);
        }
        if (($v === null) && ($this->type != 'array')) {
            if (($this->type == 'file') || ($this->type == 'image')) return sprintf('<%s mime="" title="" xsi:nil="true"/>', $this->id);
            else return sprintf('<%s xsi:nil="true"/>', $this->id);
        }
        switch ($this->type) {
            case 'timestamp':
            case 'date':
                $v = stringDateToIso($v);
                return sprintf("<%s>%s</%s>", $this->id, $v, $this->id);
            case 'array':
                $la = $doc->getAttributes();
                $xmlvalues = array();
                $av = $doc->getArrayRawValues($this->id);
                $axml = array();
                foreach ($av as $k => $col) {
                    $xmlvalues = array();
                    foreach ($col as $aid => $aval) {
                        $oa = $doc->getAttribute($aid);
                        if (empty($opt->exportAttributes[$doc->fromid]) || in_array($aid, $opt->exportAttributes[$doc->fromid])) {
                            $opt->index = $k;
                            $xmlvalues[] = $oa->getXmlValue($doc, $opt);
                        }
                    }
                    $axml[] = sprintf("<%s>%s</%s>", $this->id, implode("\n", $xmlvalues) , $this->id);
                }
                $opt->index = - 1; // restore initial index
                return implode("\n", $axml);
            case 'image':
            case 'file':
                
                if (preg_match(PREGEXPFILE, $v, $reg)) {
                    if ($opt->withIdentifier) {
                        $vid = $reg[2];
                    } else {
                        $vid = '';
                    }
                    $mime = $reg[1];
                    $name = $reg[3];
                    $base = getParam("CORE_EXTERNURL");
                    $href = $base . str_replace('&', '&amp;', $doc->getFileLink($this->id));
                    if ($opt->withFile) {
                        $path = $doc->vault_filename_fromvalue($v, true);
                        
                        if (is_file($path)) {
                            if ($opt->outFile) {
                                return sprintf('<%s vid="%s" mime="%s" title="%s">[FILE64:%s]</%s>', $this->id, $vid, $mime, $name, $path, $this->id);
                            } else {
                                return sprintf('<%s vid="%s" mime="%s" title="%s">%s</%s>', $this->id, $vid, $mime, $name, base64_encode(file_get_contents($path)) , $this->id);
                            }
                        } else {
                            return sprintf('<!-- file not found --><%s vid="%s" mime="%s" title="%s"/>', $this->id, $vid, $mime, $name, $this->id);
                        }
                    } else {
                        return sprintf('<%s vid="%s" mime="%s" href="%s" title="%s"/>', $this->id, $vid, $mime, $href, $this->encodeXml($name));
                    }
                } else {
                    return sprintf("<%s>%s</%s>", $this->id, $v, $this->id);
                }
            case 'thesaurus':
            case 'account':
            case 'docid':
                if (!$v) {
                    return sprintf('<%s xsi:nil="true"/>', $this->id);
                } else {
                    $info = getTDoc($doc->dbaccess, $v, array() , array(
                        "title",
                        "name",
                        "id",
                        "initid",
                        "locked"
                    ));
                    
                    if ($info) {
                        $docid = $info["id"];
                        $latestTitle = ($this->getOption("docrev", "latest") == "latest");
                        if ($latestTitle) {
                            $docid = $info["initid"];
                            if ($info["locked"] == - 1) {
                                $info["title"] = $doc->getLastTitle($docid);
                            }
                        }
                        if ($info["name"]) {
                            if ($opt->withIdentifier) {
                                return sprintf('<%s id="%s" name="%s">%s</%s>', $this->id, $docid, $info["name"], $this->encodeXml($info["title"]) , $this->id);
                            } else {
                                return sprintf('<%s name="%s">%s</%s>', $this->id, $info["name"], $this->encodeXml($info["title"]) , $this->id);
                            }
                        } else {
                            if ($opt->withIdentifier) {
                                return sprintf('<%s id="%s">%s</%s>', $this->id, $docid, $this->encodeXml($info["title"]) , $this->id);
                            } else {
                                
                                return sprintf('<%s>%s</%s>', $this->id, $this->encodeXml($info["title"]) , $this->id);
                            }
                        }
                    } else {
                        if ((strpos($v, '<BR>') === false) && (strpos($v, "\n") === false)) {
                            return sprintf('<%s id="%s">%s</%s>', $this->id, $v, _("unreferenced document") , $this->id);
                        } else {
                            
                            $tids = explode("\n", str_replace('<BR>', "\n", $v));
                            $mName = array();
                            $mId = array();
                            $foundName = false;
                            foreach ($tids as $id) {
                                $lName = getNameFromId($doc->dbaccess, $id);
                                $mName[] = $lName;
                                $mId[] = $id;
                                if ($lName) $foundName = true;
                            }
                            $sIds = '';
                            if ($opt->withIdentifier) {
                                $sIds = sprintf('id="%s"', implode(',', $mId));
                            }
                            $sName = '';
                            if ($foundName) {
                                
                                $sName = sprintf('name="%s"', implode(',', $mName));
                            }
                            return sprintf('<%s %s %s>%s</%s>', $this->id, $sName, $sIds, _("multiple document") , $this->id);
                        }
                    }
                }
            default:
                return sprintf("<%s>%s</%s>", $this->id, $this->encodeXml($v) , $this->id);
            }
    }
    /**
     * custom textual XML schema
     *
     * @return string
     */
    function text_getXmlSchema()
    {
        $lay = new Layout(getLayoutFile("FDL", "textattribute_schema.xml"));
        $this->common_getXmlSchema($lay);
        
        $lay->set("maxlength", false);
        $lay->set("pattern", false);
        return $lay->gen();
    }
    /**
     * enum XML schema
     *
     * @return string
     */
    function enum_getXmlSchema()
    {
        $lay = new Layout(getLayoutFile("FDL", "enumattribute_schema.xml"));
        $this->common_getXmlSchema($lay);
        
        $la = $this->getEnum();
        $te = array();
        foreach ($la as $k => $v) {
            $te[] = array(
                "key" => $k,
                "val" => $this->encodeXml($v)
            );
        }
        $lay->setBlockData("enums", $te);
        return $lay->gen();
    }
    /**
     * docid XML schema
     *
     * @return string
     */
    function docid_getXmlSchema()
    {
        $lay = new Layout(getLayoutFile("FDL", "docidattribute_schema.xml"));
        $this->common_getXmlSchema($lay);
        
        $lay->set("famid", $this->format);
        return $lay->gen();
    }
    /**
     * date XML schema
     *
     * @return string
     */
    function date_getXmlSchema()
    {
        $lay = new Layout(getLayoutFile("FDL", "dateattribute_schema.xml"));
        $this->common_getXmlSchema($lay);
        return $lay->gen();
    }
    /**
     * timeStamp XML schema
     *
     * @return string
     */
    function timestamp_getXmlSchema()
    {
        $lay = new Layout(getLayoutFile("FDL", "timestampattribute_schema.xml"));
        $this->common_getXmlSchema($lay);
        return $lay->gen();
    }
    /**
     * Color XML schema
     *
     * @return string
     */
    function color_getXmlSchema()
    {
        $lay = new Layout(getLayoutFile("FDL", "colorattribute_schema.xml"));
        $this->common_getXmlSchema($lay);
        return $lay->gen();
    }
    /**
     * int XML schema
     *
     * @return string
     */
    function int_getXmlSchema()
    {
        $lay = new Layout(getLayoutFile("FDL", "intattribute_schema.xml"));
        $this->common_getXmlSchema($lay);
        return $lay->gen();
    }
    /**
     * longText XML schema
     *
     * @return string
     */
    function longtext_getXmlSchema()
    {
        $lay = new Layout(getLayoutFile("FDL", "longtextattribute_schema.xml"));
        $this->common_getXmlSchema($lay);
        return $lay->gen();
    }
    /**
     * Float XML schema
     *
     * @return string
     */
    function float_getXmlSchema()
    {
        $lay = new Layout(getLayoutFile("FDL", "floatattribute_schema.xml"));
        $this->common_getXmlSchema($lay);
        return $lay->gen();
    }
    /**
     * Time XML schema
     *
     * @return string
     */
    function time_getXmlSchema()
    {
        $lay = new Layout(getLayoutFile("FDL", "timeattribute_schema.xml"));
        $this->common_getXmlSchema($lay);
        return $lay->gen();
    }
    /**
     * File XML schema
     *
     * @return string
     */
    function file_getXmlSchema()
    {
        $lay = new Layout(getLayoutFile("FDL", "fileattribute_schema.xml"));
        $this->common_getXmlSchema($lay);
        return $lay->gen();
    }
    /**
     * Array XML schema
     * @param BasicAttribute[] &$la
     *
     * @return string
     */
    function array_getXmlSchema(&$la)
    {
        $lay = new Layout(getLayoutFile("FDL", "arrayattribute_schema.xml"));
        $this->common_getXmlSchema($lay);
        $lay->set("minOccurs", "0");
        $lay->set("maxOccurs", "unbounded");
        $tax = array();
        foreach ($la as $k => $v) {
            if ($v->fieldSet && $v->fieldSet->id == $this->id) {
                $tax[] = array(
                    "axs" => $v->getXmlSchema($la)
                );
            }
        }
        $lay->setBlockData("ATTR", $tax);
        return $lay->gen();
    }
    /**
     * Get the textual value of an attribute
     *
     * @param Doc $doc current Doc
     * @param int $index index if multiple
     * @param array $configuration value config array :
     * dateFormat => 'US' 'ISO',
     * decimalSeparator => '.',
     * longtextMultipleBrToCr => ' '
     * multipleSeparator => array(0 => 'arrayLine', 1 => 'multiple')
     *
     * (defaultValue : dateFormat : 'US', decimalSeparator : '.', multiple => array(0 => "\n", 1 => ", "))
     *
     * @return string
     */
    public function getTextualValue(Doc $doc, $index = - 1, Array $configuration = array())
    {
        $decimalSeparator = isset($configuration['decimalSeparator']) ? $configuration['decimalSeparator'] : '.';
        
        if (in_array($this->type, array(
            "int",
            "double",
            "money"
        ))) {
            return $this->getNumberValue($doc, $index, $decimalSeparator);
        }
        $value = $doc->getRawValue($this->id);
        $fc = new \FormatCollection();
        $stripHtmlTags = isset($configuration['stripHtmlTags']) ? $configuration['stripHtmlTags'] : false;
        $fc->stripHtmlTags($stripHtmlTags);
        
        $fc->setDecimalSeparator($decimalSeparator);
        $fc->setDateStyle(\DateAttributeValue::defaultStyle);
        $dateFormat = isset($configuration['dateFormat']) ? $configuration['dateFormat'] : 'US';
        
        if ($dateFormat == 'US') {
            $fc->setDateStyle(\DateAttributeValue::isoWTStyle);
        } elseif ($dateFormat == "ISO") {
            $fc->setDateStyle(\DateAttributeValue::isoStyle);
        } elseif ($dateFormat == 'FR') {
            $fc->setDateStyle(\DateAttributeValue::frenchStyle);
        } else {
            $fc->setDateStyle(\DateAttributeValue::defaultStyle);
        }
        if (isset($configuration['longtextMultipleBrToCr'])) {
            $fc->setLongtextMultipleBrToCr($configuration['longtextMultipleBrToCr']);
        } else {
            $fc->setLongtextMultipleBrToCr(" "); // long text are in a single line
            
        }
        $info = $fc->getInfo($this, $value, $doc);
        if (empty($info)) {
            return '';
        }
        return \FormatCollection::getDisplayValue($info, $this, $index, $configuration);
    }
    
    public function getNumberValue(Doc $doc, $index = - 1, $decimalSeparator = ".")
    {
        
        if ($index >= 0) {
            $numberValue = $doc->getMultipleRawValues($this->id, "", $index);
            if ($this->format) {
                $numberValue = sprintf($this->format, $numberValue);
            }
        } elseif ($this->isMultiple() && $this->format) {
            $cellValues = $doc->getMultipleRawValues($this->id);
            foreach ($cellValues as & $cell) {
                $cell = sprintf($this->format, $cell);
            }
            $numberValue = implode("\n", $cellValues);
        } else {
            $numberValue = $doc->getRawValue($this->id);
            if ($this->format) {
                $numberValue = sprintf($this->format, $numberValue);
            }
        }
        
        if (!empty($decimalSeparator)) {
            $numberValue = str_replace(".", $decimalSeparator, $numberValue);
        }
        return $numberValue;
    }
    /**
     * to see if an attribute is n item of an array
     *
     * @return boolean
     */
    function inArray()
    {
        return ($this->fieldSet && $this->fieldSet->type === "array");
    }
    /**
     * Return array of enumeration definition
     * the array's keys are the enum key and the values are the labels
     *
     * @param bool $returnDisabled if false disabled enum are not returned
     * @return array
     */
    function getEnum($returnDisabled = true)
    {
        $cached = self::_cacheFetch(self::_cEnum, array(
            $this->docid,
            $this->id
        ) , null, $returnDisabled);
        if ($cached !== null) {
            return $cached;
        }
        
        if (($this->type == "enum") || ($this->type == "enumlist")) {
            // set the enum array
            $this->enum = array();
            $this->enumlabel = array();
            $br = $this->docname . '#' . $this->id . '#'; // id i18n prefix
            if ($this->originalPhpfile && $this->originalPhpfunc) {
                $this->phpfile = $this->originalPhpfile;
                $this->phpfunc = $this->originalPhpfunc;
            }
            if (($this->phpfile != "") && ($this->phpfile != "-")) {
                // for dynamic  specification of kind attributes
                if (!include_once ("EXTERNALS/$this->phpfile")) {
                    /**
                     * @var Action $action
                     */
                    global $action;
                    $action->exitError(sprintf(_("the external pluggin file %s cannot be read") , $this->phpfile));
                }
                if (preg_match('/(.*)\((.*)\)/', $this->phpfunc, $reg)) {
                    $args = explode(",", $reg[2]);
                    if (preg_match('/linkenum\((.*),(.*)\)/', $this->phpfunc, $dreg)) {
                        $br = $dreg[1] . '#' . strtolower($dreg[2]) . '#';
                    }
                    if (function_exists($reg[1])) {
                        $this->originalPhpfile = $this->phpfile;
                        $this->originalPhpfunc = $this->phpfunc;
                        $this->phpfile = "";
                        $this->phpfunc = call_user_func_array($reg[1], $args);
                        
                        EnumAttributeTools::flatEnumNotationToEnumArray($this->phpfunc, $this->enum, $this->enumlabel, $br);
                    } else {
                        AddWarningMsg(sprintf(_("function [%s] not exists") , $this->phpfunc));
                        $this->phpfunc = "";
                    }
                } else {
                    AddWarningMsg(sprintf(_("invalid syntax for [%s] for enum attribute [%s]") , $this->phpfunc, $this->id));
                }
                self::_cacheStore(self::_cEnum, array(
                    $this->docid,
                    $this->id
                ) , $this->enum);
                self::_cacheStore(self::_cEnumLabel, array(
                    $this->docid,
                    $this->id
                ) , $this->enumlabel);
            } else {
                // static enum
                $famId = $this->_getRecursiveParentFamHavingAttribute($this->docid, $this->id);
                
                $cached = self::_cacheFetch(self::_cEnum, array(
                    $famId,
                    $this->id
                ) , null, $returnDisabled);
                if ($cached !== null) {
                    return $cached;
                }
                
                $sql = sprintf("select * from docenum where famid=%d and attrid='%s' order by eorder", $famId, pg_escape_string($this->id));
                
                simpleQuery('', $sql, $enums);
                
                foreach ($enums as $k => $item) {
                    $enums[$k]["keyPath"] = str_replace('.', '\\.', $item["key"]);
                }
                foreach ($enums as $item) {
                    $enumKey = $item["key"];
                    $enumPath = $item["keyPath"];
                    $translatedEnumValue = _($br . $enumKey);
                    if ($translatedEnumValue != $br . $enumKey) {
                        $enumLabel = $translatedEnumValue;
                    } else {
                        $enumLabel = $item["label"];
                    }
                    if ($item["parentkey"] !== null) {
                        $this->enum[$this->getCompleteEnumKey($enumKey, $enums) ] = $enumLabel;
                        $enumCompleteLabel = $this->getCompleteEnumlabel($enumKey, $enums, $br);
                        $this->enumlabel[$enumKey] = $enumCompleteLabel;
                    } else {
                        $this->enum[$enumPath] = $enumLabel;
                        $this->enumlabel[$enumKey] = $enumLabel;
                    }
                }
                self::_cacheStore(self::_cEnum, array(
                    $famId,
                    $this->id
                ) , $this->enum);
                self::_cacheStore(self::_cEnumLabel, array(
                    $famId,
                    $this->id
                ) , $this->enumlabel);
            }
        }
        if (!$returnDisabled) {
            return self::_cacheFetch(self::_cEnum, array(
                $this->docid,
                $this->id
            ) , null, $returnDisabled);
        }
        return $this->enum;
    }
    
    private function getCompleteEnumKey($key, array & $enums)
    {
        foreach ($enums as $item) {
            if ($item["key"] === $key) {
                if ($item["parentkey"] !== null) {
                    
                    return sprintf("%s.%s", $this->getCompleteEnumKey($item["parentkey"], $enums) , $item["keyPath"]);
                } else {
                    return $item["keyPath"];
                }
            }
        }
        return '';
    }
    private function getCompleteEnumLabel($key, array & $enums, $prefix)
    {
        foreach ($enums as $item) {
            if ($item["key"] === $key) {
                $translatedEnumValue = _($prefix . $key);
                if ($translatedEnumValue != $prefix . $key) {
                    $label = $translatedEnumValue;
                } else {
                    $label = $item["label"];
                }
                if ($item["parentkey"] !== null) {
                    
                    return sprintf("%s/%s", $this->getCompleteEnumLabel($item["parentkey"], $enums, $prefix) , $label);
                } else {
                    return $label;
                }
            }
        }
        return '';
    }
    /**
     * reset Enum cache
     */
    public static function resetEnum()
    {
        self::_cacheFlush(self::_cEnum);
        self::_cacheFlush(self::_cEnumLabel);
        self::_cacheFlush(self::_cParent);
    }
    /**
     * return array of enumeration definition
     * the array'skeys are the enum single key and the values are the complete labels
     *
     * @param string $enumid the key of enumerate (if no parameter all labels are returned
     * @param bool $returnDisabled if false disabled enum are not returned
     * @return array|string|null
     */
    function getEnumLabel($enumid = null, $returnDisabled = true)
    {
        $implode = false;
        $this->getEnum($returnDisabled);
        
        $cached = self::_cacheFetch(self::_cEnumLabel, array(
            $this->docid,
            $this->id
        ) , null, $returnDisabled);
        if ($cached === null) {
            $famId = $this->_getRecursiveParentFamHavingAttribute($this->docid, $this->id);
            if ($famId !== $this->docid) {
                $cached = self::_cacheFetch(self::_cEnumLabel, array(
                    $famId,
                    $this->id
                ) , null, $returnDisabled);
            }
        }
        if ($cached !== null) {
            if ($enumid === null) {
                return $cached;
            }
            if (strstr($enumid, "\n")) {
                $enumid = explode("\n", $enumid);
                $implode = true;
            }
            if (is_array($enumid)) {
                $tv = array();
                foreach ($enumid as $v) {
                    $tv[] = (isset($cached[$v])) ? $cached[$v] : $v;
                }
                if ($implode) {
                    return implode("\n", $tv);
                }
                return $tv;
            } else {
                return (array_key_exists($enumid, $cached)) ? $cached[$enumid] : $enumid;
            }
        }
        
        return null;
    }
    /**
     * add new item in enum list items
     *
     * @param string $dbaccess dbaccess string
     * @param string $key database key
     * @param string $label human label
     *
     * @return string error message (empty means ok)
     */
    function addEnum($dbaccess, $key, $label)
    {
        $err = '';
        if ($key == "") return "";
        
        $famId = $this->docid;
        $attrId = $this->id;
        
        $a = new DocAttr($dbaccess, array(
            $famId,
            $attrId
        ));
        if (!$a->isAffected()) {
            /* Search attribute in parents */
            $a = $this->_getDocAttrFromParents($dbaccess, $famId, $attrId);
            if ($a === false) {
                $err = sprintf(_("unknow attribute %s (family %s)") , $attrId, $famId);
                return $err;
            }
        }
        if ($a->isAffected()) {
            $famId = $a->docid;
            $oe = new DocEnum($dbaccess, array(
                $famId,
                $attrId,
                $key
            ));
            $this->getEnum();
            
            $key = str_replace(array(
                '|'
            ) , array(
                '_'
            ) , $key);
            $label = str_replace(array(
                '|'
            ) , array(
                '_'
            ) , $label);
            if (!$oe->isAffected()) {
                $oe->attrid = $attrId;
                $oe->famid = $famId;
                $oe->key = $key;
                $oe->label = $label;
                /* Store enum in database */
                $err = $oe->add();
                if ($err == '') {
                    /* Update cache */
                    $cachedEnum = self::_cacheFetch(self::_cEnum, array(
                        $famId,
                        $this->id
                    ) , array());
                    $cachedEnumLabel = self::_cacheFetch(self::_cEnumLabel, array(
                        $famId,
                        $this->id
                    ) , array());
                    $cachedEnum[$key] = $label;
                    $cachedEnumLabel[$key] = $label;
                    self::_cacheStore(self::_cEnum, array(
                        $famId,
                        $this->id
                    ) , $cachedEnum);
                    self::_cacheStore(self::_cEnumLabel, array(
                        $famId,
                        $this->id
                    ) , $cachedEnumLabel);
                }
            }
        } else {
            $err = sprintf(_("unknow attribute %s (family %s)") , $attrId, $famId);
        }
        return $err;
    }
    private function _getRecursiveParentFamHavingAttribute($famId, $attrId)
    {
        $cached = self::_cacheFetch(self::_cParent, array(
            $famId,
            $attrId
        ));
        if ($cached !== null) {
            return $cached;
        }
        $sql = <<<'SQL'
WITH RECURSIVE parent_attr(fromid, docid, id) AS (
    SELECT
        docfam.fromid,
        docattr.docid,
        docattr.id
    FROM
        docattr,
        docfam
    WHERE
        docattr.docid = docfam.id
        AND
        docattr.docid = %d

    UNION

    SELECT
        docfam.fromid,
        docattr.docid,
        docattr.id
    FROM
        docattr,
        docfam,
        parent_attr
    WHERE
        docattr.docid = parent_attr.fromid
        AND
        parent_attr.fromid = docfam.id
)
SELECT docid FROM parent_attr WHERE id = '%s' LIMIT 1;
SQL;
        $sql = sprintf($sql, pg_escape_string($famId) , pg_escape_string($attrId));
        $parentFamId = false;
        simpleQuery('', $sql, $parentFamId, true, true);
        if ($parentFamId !== false) {
            self::_cacheStore(self::_cParent, array(
                $famId,
                $attrId
            ) , $parentFamId);
        }
        return $parentFamId;
    }
    private function _getDocAttrFromParents($dbaccess, $famId, $attrId)
    {
        $parentFamId = $this->_getRecursiveParentFamHavingAttribute($famId, $attrId);
        if ($parentFamId === false) {
            return false;
        }
        $a = new DocAttr($dbaccess, $parentFamId, $attrId);
        return $a;
    }
    /**
     * Test if an enum key exists
     *
     * @param string $key enumKey
     * @param bool $completeKey if true test complete key with path else without path
     * @return bool
     */
    function existEnum($key, $completeKey = true)
    {
        if ($key == "") {
            return false;
        }
        
        if ($completeKey) {
            $enumKeys = $this->getEnum();
        } else {
            $enumKeys = $this->getEnumLabel();
        }
        return isset($enumKeys[$key]);
    }
    /**
     * Construct a string key
     *
     * @param mixed $k key
     * @return string
     */
    private static function _cacheKey($k)
    {
        if (is_scalar($k)) {
            return $k;
        } else if (is_array($k)) {
            return implode(':', $k);
        }
        return serialize($k);
    }
    /**
     * Check if an entry exists for the given key
     *
     * @param string $cacheId cache Id
     * @param string $k key
     * @return bool true if it exists, false if it does not exists
     */
    private static function _cacheExists($cacheId, $k)
    {
        $k = self::_cacheKey($k);
        return isset(self::$_cache[$cacheId][$k]);
    }
    /**
     * Add (or update) a key/value
     *
     * @param string $cacheId cache Id
     * @param string $k key
     * @param mixed $v value
     * @return bool true on success, false on failure
     */
    private static function _cacheStore($cacheId, $k, $v)
    {
        $k = self::_cacheKey($k);
        self::$_cache[$cacheId][$k] = $v;
        return true;
    }
    /**
     * Fetch the key's value
     *
     * @param string $cacheId cache Id
     * @param string $k key
     * @param mixed $onCacheMiss value returned on cache miss (default is null)
     * @param bool $returnDisabled if false unreturn disabled enums
     * @return null|mixed null on failure, mixed value on success
     */
    private static function _cacheFetch($cacheId, $k, $onCacheMiss = null, $returnDisabled = true)
    {
        if (self::_cacheExists($cacheId, $k)) {
            $ks = self::_cacheKey($k);
            if (!$returnDisabled) {
                $famId = $k[0];
                $attrid = $k[1];
                $disabledKeys = DocEnum::getDisabledKeys($famId, $attrid);
                if (!empty($disabledKeys)) {
                    $cached = self::$_cache[$cacheId][$ks];
                    foreach ($disabledKeys as $dKey) {
                        unset($cached[$dKey]);
                    }
                    return $cached;
                }
            }
            
            return self::$_cache[$cacheId][$ks];
        }
        return $onCacheMiss;
    }
    /**
     * Remove a key and it's value from the cache
     *
     * @param string $cacheId cache Id
     * @param string $k key
     * @return bool true on success, false on failure
     */
    private static function _cacheRemove($cacheId, $k)
    {
        if (self::_cacheExists($cacheId, $k)) {
            $k = self::_cacheKey($k);
            unset(self::$_cache[$cacheId][$k]);
        }
        return true;
    }
    /**
     * Flush the cache contents
     *
     * @param string|null $cacheId cache Id or null (default) to flush all caches
     * @return void
     */
    private static function _cacheFlush($cacheId = null)
    {
        if ($cacheId === null) {
            self::$_cache = array();
        } else {
            self::$_cache[$cacheId] = array();
        }
    }
}
/**
 * Structural attribute (attribute that contain other attribute : tab, frame)
 *
 * @author Anakeen
 *
 */
class FieldSetAttribute extends BasicAttribute
{
    /**
     * Constructor
     *
     * @param string $id $docid famid
     * @param string $docid
     * @param string $label default translation key
     * @param string $visibility visibility option
     * @param string $usefor Attr or Param usage
     * @param string $type kind of
     * @param FieldSetAttribute $fieldSet parent field
     * @param string $options option string
     * @param string $docname
     */
    function __construct($id, $docid, $label, $visibility = "", $usefor = "", $type = "frame", &$fieldSet = null, $options = "", $docname = "")
    {
        $this->id = $id;
        $this->docid = $docid;
        $this->labelText = $label;
        $this->visibility = $visibility;
        $this->usefor = $usefor;
        $this->type = $type;
        $this->fieldSet = & $fieldSet;
        $this->options = $options;
        $this->docname = $docname;
    }
    /**
     * Generate the xml schema fragment
     *
     * @param BasicAttribute[] $la
     *
     * @return string
     */
    function getXmlSchema($la)
    {
        $lay = new Layout(getLayoutFile("FDL", "fieldattribute_schema.xml"));
        $lay->set("aname", $this->id);
        $this->common_getXmlSchema($lay);
        
        $lay->set("minOccurs", "0");
        $lay->set("maxOccurs", "1");
        $lay->set("notop", ($this->fieldSet->id != '' && $this->fieldSet->id != Adoc::HIDDENFIELD));
        $tax = array();
        foreach ($la as $k => $v) {
            if ($v->fieldSet && $v->fieldSet->id == $this->id) {
                $tax[] = array(
                    "axs" => $v->getXmlSchema($la)
                );
            }
        }
        
        $lay->setBlockData("ATTR", $tax);
        return $lay->gen();
    }
    /**
     * export values as xml fragment
     *
     * @param Doc $doc working doc
     * @param exportOptionAttribute $opt
     * @deprecated use \Dcp\ExportXmlDocument class instead
     *
     * @return string
     */
    function getXmlValue(Doc & $doc, $opt = false)
    {
        $la = $doc->getAttributes();
        $xmlvalues = array();
        foreach ($la as $k => $v) {
            /**
             * @var NormalAttribute $v
             */
            if ($v->fieldSet && $v->fieldSet->id == $this->id && (empty($opt->exportAttributes[$doc->fromid]) || in_array($v->id, $opt->exportAttributes[$doc->fromid]))) {
                $value = $v->getXmlValue($doc, $opt);
                if ($v->type == "htmltext" && $opt !== false) {
                    $value = $v->prepareHtmltextForExport($value);
                    if ($opt->withFile) {
                        $value = preg_replace_callback('/(&lt;img.*?)src="(((?=.*docid=(.*?)&)(?=.*attrid=(.*?)&)(?=.*index=(-?[0-9]+)))|(file\/(.*?)\/[0-9]+\/(.*?)\/(-?[0-9]+))).*?"/', function ($matches) use ($opt)
                        {
                            if (isset($matches[7])) {
                                $docid = $matches[8];
                                $attrid = $matches[9];
                                $index = $matches[10] == "-1" ? 0 : $matches[10];
                            } else {
                                $docid = $matches[4];
                                $index = $matches[6] == "-1" ? 0 : $matches[6];
                                $attrid = $matches[5];
                            }
                            $doc = new_Doc(getDbAccess() , $docid);
                            $attr = $doc->getAttribute($attrid);
                            $tfiles = $doc->vault_properties($attr);
                            $f = $tfiles[$index];
                            if (is_file($f["path"])) {
                                if ($opt->outFile) {
                                    return sprintf('%s title="%s" src="data:%s;base64,[FILE64:%s]"', "\n" . $matches[1], unaccent($f["name"]) , $f["mime_s"], $f["path"]);
                                } else {
                                    return sprintf('%s title="%s" src="data:%s;base64,%s"', "\n" . $matches[1], unaccent($f["name"]) , $f["mime_s"], base64_encode(file_get_contents($f["path"])));
                                }
                            } else {
                                return sprintf('%s title="%s" src="data:%s;base64,file not found"', "\n" . $matches[1], unaccent($f["name"]) , $f["mime_s"]);
                            }
                        }
                        , $value);
                    }
                }
                $xmlvalues[] = $value;
            }
        }
        if ($opt->flat) return implode("\n", $xmlvalues);
        else return sprintf("<%s>%s</%s>", $this->id, implode("\n", $xmlvalues) , $this->id);
    }
}

class MenuAttribute extends BasicAttribute
{
    public $link; // hypertext link
    public $precond; // pre-condition to activate menu
    function __construct($id, $docid, $label, $order, $link, $visibility = "", $precond = "", $options = "", $docname = "")
    {
        $this->id = $id;
        $this->docid = $docid;
        $this->labelText = $label;
        $this->ordered = $order;
        $this->link = $link;
        $this->visibility = $visibility;
        $this->options = $options;
        $this->precond = $precond;
        $this->type = "menu";
        $this->docname = $docname;
    }
}

class ActionAttribute extends BasicAttribute
{
    
    public $wapplication; // the what application name
    public $waction; // the what action name
    public $precond; // pre-condition to activate action
    function __construct($id, $docid, $label, $order, $visibility = "", $wapplication = "", $waction = "", $precond = "", $options = "", $docname = "")
    {
        $this->id = $id;
        $this->docid = $docid;
        $this->labelText = $label;
        $this->visibility = $visibility;
        $this->ordered = $order;
        $this->waction = $waction;
        $this->wapplication = $wapplication;
        $this->options = $options;
        $this->precond = $precond;
        $this->type = "action";
        $this->docname = $docname;
    }
    function getLink($docid)
    {
        $l = getParam("CORE_STANDURL");
        $batch = ($this->getOption("batchfolder") == "yes");
        if ($batch) {
            $l.= "&app=FREEDOM&action=BATCHEXEC&sapp=" . $this->wapplication;
            $l.= "&saction=" . $this->waction;
            $l.= "&id=" . $docid;
        } else {
            $l.= "&app=" . $this->wapplication;
            $l.= "&action=" . $this->waction;
            if (!stristr($this->waction, "&id=")) $l.= "&id=" . $docid;
        }
        return $l;
    }
}

class exportOptionAttribute
{
    /**
     * @var array
     */
    public $exportAttributes;
    /**
     * @var bool
     */
    public $flat;
    /**
     * @var bool
     */
    public $withIdentifier;
    /**
     * @var bool
     */
    public $withFile;
    /**
     * @var string
     */
    public $outFile;
    /**
     * @var int
     */
    public $index;
}

class EnumAttributeTools
{
    /**
     * convert enum flat notation to an array of item (key/label).
     * @param string $phpfunc the flat notation
     * @param array $theEnum [out] the enum array converted
     * @param array $theEnumlabel [out] the enum array converted - with complete labels in case of levels
     * @param string $locale the prefix key for locale values (if empty no locale are set)
     * @return array
     */
    public static function flatEnumNotationToEnumArray($phpfunc, array & $theEnum, array & $theEnumlabel = array() , $locale = '')
    {
        
        if (!$phpfunc) return array();
        if (preg_match('/^\[[a-z]*\](.*)/', $phpfunc, $reg)) {
            //delete old enum format syntax
            $phpfunc = $reg[1];
        }
        // set the enum array
        $theEnum = array();
        $theEnumlabel = array();
        
        $sphpfunc = str_replace("\\.", "-dot-", $phpfunc); // to replace dot & comma separators
        $sphpfunc = str_replace("\\,", "-comma-", $sphpfunc);
        if ($sphpfunc == "-") $sphpfunc = ""; // it is recorded
        if ($sphpfunc != "") {
            $tenum = explode(",", $sphpfunc);
            foreach ($tenum as $k => $v) {
                list($enumKey, $enumValue) = explode("|", $v, 2);
                $treeKeys = explode(".", $enumKey);
                $enumKey = trim($enumKey);
                if (strlen($enumKey) == 0) $enumKey = " ";
                $enumValue = trim($enumValue);
                
                $n = count($treeKeys);
                if ($n <= 1) {
                    $enumValue = str_replace(array(
                        '-dot-',
                        '-comma-'
                    ) , array(
                        '\.',
                        ','
                    ) , $enumValue);
                    
                    if ($locale) {
                        $translatedEnumValue = _($locale . $enumKey);
                        if ($translatedEnumValue != $locale . $enumKey) {
                            $enumValue = $translatedEnumValue;
                        }
                    }
                    
                    $theEnum[str_replace(array(
                        '-dot-',
                        '-comma-'
                    ) , array(
                        '\.',
                        ','
                    ) , $enumKey) ] = $enumValue;
                    $theEnumlabel[str_replace(array(
                        '-dot-',
                        '-comma-'
                    ) , array(
                        '.',
                        ','
                    ) , $enumKey) ] = $enumValue;
                } else {
                    $enumlabelKey = '';
                    $tmpKey = '';
                    $previousKey = '';
                    foreach ($treeKeys as $i => $treeKey) {
                        $enumlabelKey = $treeKey;
                        
                        if ($i < $n - 1) {
                            if ($i > 0) {
                                $tmpKey.= '.';
                            }
                            $tmpKey.= $treeKey;
                        }
                    }
                    $tmpKey = str_replace(array(
                        '-dot-',
                        '-comma-'
                    ) , array(
                        '\.',
                        ','
                    ) , $tmpKey);
                    
                    if ($locale) {
                        $translatedEnumValue = _($locale . $enumlabelKey);
                        if ($translatedEnumValue != $locale . $enumlabelKey) {
                            $enumValue = $translatedEnumValue;
                        }
                    }
                    $enumlabelValue = $theEnum[$tmpKey] . '/' . $enumValue;
                    $enumlabelValue = str_replace(array(
                        '-dot-',
                        '-comma-'
                    ) , array(
                        '\.',
                        ','
                    ) , $enumlabelValue);
                    $theEnum[str_replace(array(
                        '-dot-',
                        '-comma-'
                    ) , array(
                        '\.',
                        ','
                    ) , $enumKey) ] = $enumValue;
                    $theEnumlabel[str_replace(array(
                        '-dot-',
                        '-comma-'
                    ) , array(
                        '.',
                        ','
                    ) , $enumlabelKey) ] = $enumlabelValue;
                }
            }
        }
        
        return $theEnum;
    }
    
    private static function getEnumHierarchy($key, $parents)
    {
        if (isset($parents[$key])) {
            return array_merge(self::getEnumHierarchy($parents[$key], $parents) , array(
                $key
            ));
        } else {
            return array(
                $key
            );
        }
    }
    /**
     * return flat notation from docenum database table
     * @param int $famid family identifier
     * @param string $attrid attribute identifier
     * @return string ftat enum
     */
    public static function getFlatEnumNotation($famid, $attrid)
    {
        $sql = sprintf("select * from docenum where famid='%s' and attrid='%s' and (disabled is null or not disabled) order by eorder", pg_escape_string($famid) , pg_escape_string($attrid));
        simpleQuery('', $sql, $results);
        $tItems = array();
        $hierarchy = array();
        foreach ($results as $item) {
            if ($item["parentkey"] !== null) {
                $hierarchy[$item["key"]] = $item["parentkey"];
            }
        }
        foreach ($results as $item) {
            $key = $item["key"];
            $label = $item["label"];
            if ($item["parentkey"] !== null) {
                $parents = self::getEnumHierarchy($key, $hierarchy);
                foreach ($parents as & $pKey) {
                    $pKey = str_replace(".", '-dot-', $pKey);
                }
                $key = implode('.', $parents);
                $key = str_replace('-dot-', '\\.', $key);
            } else {
                $key = str_replace('.', '\\.', $key);
            }
            $tItems[] = sprintf("%s|%s", str_replace(',', '\\,', $key) , str_replace(',', '\\,', $label));
        }
        return implode(",", $tItems);
    }
}
?>
