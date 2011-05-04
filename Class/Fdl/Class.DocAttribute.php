<?php
/**
 * Document Attributes
 *
 * @author Anakeen 2000
 * @version $Id: Class.DocAttribute.php,v 1.47 2008/12/11 10:06:51 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 */
/**
 */
/**
 *
 * Generic attribute class
 *
 * @author anakeen
 */
class BasicAttribute
{
    public $id;
    public $docid;
    public $labelText;
    public $visibility; // W, R, H, O, M, I
    public $options;
    public $docname;
    public $type; // text, longtext, date, file, ...


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
        if ($i != $r)
            return $i;
        return $this->labelText;
    }

    /**
     * Return value of option $x
     *
     * @param $x option name
     * @param $def default value
     *
     * @return string
     */
    function getOption($x, $def = "")
    {
        if (!isset($this->_topt)) {
            $topt = explode("|", $this->options);
            $this->_topt = array();
            foreach ( $topt as $k => $v ) {
                list($vn, $vv) = explode("=", $v, 2);
                $this->_topt[$vn] = $vv;
            }
        }
        $r = $this->docname . '#' . $this->id . '#' . $x;
        $i = _($r);
        if ($i != $r)
            return $i;
        $v = $this->_topt[$x];
        return ($v ? $v : $def);
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
     * @param $x name
     * @param $v value
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
     * @param char $vis new visibility : R|H|W|O|I
     * @return void
     */
    function setVisibility($vis)
    {
        $this->mvisibility = $vis;
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
     * test if attribute is not a auto created attribute
     *
     * @return void
     */
    function isReal()
    {
        return $this->getOption("autocreated") != "yes";
    }
    /**
     * Escape value with xml entities
     *
     * @param string $s value
     *
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
            ), array(
                '&amp;',
                '&lt;',
                '&gt;',
                '&quot;'
            ), $s);
        } else {
            return str_replace(array(
                '&',
                '<',
                '>'
            ), array(
                '&amp;',
                '&lt;',
                '&gt;'
            ), $s);
        }
    }
    /**
     * to see if an attribute is n item of an array
     *
     * @return boolean
     */
    function inArray()
    {
        if (get_class($this) == "NormalAttribute") {
            if ($this->fieldSet->type == "array")
                return true;
        }
        return false;
    }
    /**
     * verify if accept multiple value
     *
     * @return boolean
     */
    function isMultiple()
    {
        return ($this->inArray() || ($this->getOption('multiple') == 'yes'));
    }
    /**
     * Get tab ancestor
     *
     * @return FieldSetAttribute
     */
    function getTab()
    {
        if ($this->type == 'tab')
            return $this;
        if ($this->fieldSet && ($this->fieldSet->id != 'FIELD_HIDDENS'))
            return $this->fieldSet->getTab();
        return false;
    }
    /**
     * Export values as xml fragment
     *
     * @return string
     */
    function getXmlSchema()
    {
        return sprintf("<!-- no Schema %s (%s)-->", $this->id, $this->type);
    }

    /**
     * Export values as xml fragment
     *
     * @return string
     */
    function getXmlValue()
    {
        return sprintf("<!-- no value %s (%s)-->", $this->id, $this->type);
    }
    /**
     * Generate XML schema layout
     *
     * @param unknown_type $play
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

        if (($this->ype == "enum") && (!$this->phpfile) || ($this->phpfile == "-")) {
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
        foreach ( $tops as $k => $v ) {
            if ($k)
                $t[] = array(
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
}

/**
 * NormalAttribute Class
 * Non structural attribute (all attribute except frame and tab)
 *
 * @author anakeen
 *
 */
class NormalAttribute extends BasicAttribute
{
    public $needed; // Y / N
    public $format; // C format
    public $eformat; // format for edition : list,vcheck,hcheck
    public $repeat; // true if is a repeatable attribute
    public $isInTitle;
    public $isInAbstract;
    public $fieldSet; // field set object
    public $link; // hypertext link
    public $phpfile;
    public $phpfunc;
    public $elink; // extra link
    public $ordered;
    public $phpconstraint; // special constraint set
    public $usefor; // = Q if parameters
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
     * @param char $needed is mandotary attribute
     * @param char $isInTitle is used to compute title
     * @param char $isInAbstract is used in abstract view
     * @param string $fieldSet parent attribute
     * @param string $phpfile php file used with the phpfunc
     * @param string $phpfunc helpers function
     * @param string $elink eling option
     * @param string $phpconstraint class php function
     * @param string $usefor Attribute or Parameter
     * @param string $eformat eformat option
     * @param string $options option string
     * @param unknown_type $docname
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
        $this->fieldSet = &$fieldSet;
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
     * Generate the xml schema fragment
     *
     * @param array $la array of DocAttribute
     *
     * @return string
     */
    function getXmlSchema($la)
    {

        switch ($this->type) {
        case 'text' :
            return $this->text_getXmlSchema($la);
        case 'longtext' :
        case 'htmltext' :
            return $this->longtext_getXmlSchema($la);
        case 'int' :
        case 'integer' :
            return $this->int_getXmlSchema($la);
        case 'float' :
        case 'money' :
            return $this->float_getXmlSchema($la);
        case 'image' :
        case 'file' :
            return $this->file_getXmlSchema($la);
        case 'enum' :
            return $this->enum_getXmlSchema($la);
        case 'thesaurus' :
        case 'docid' :
            return $this->docid_getXmlSchema($la);
        case 'date' :
            return $this->date_getXmlSchema($la);
        case 'timestamp' :
            return $this->timestamp_getXmlSchema($la);
        case 'time' :
            return $this->time_getXmlSchema($la);
        case 'array' :
            return $this->array_getXmlSchema($la);
        case 'color' :
            return $this->color_getXmlSchema($la);
        default :
            return sprintf("<!-- no Schema %s (type %s)-->", $this->id, $this->type);
            ;
        }
    }

    /**
     * export values as xml fragment
     *
     * @param Doc $doc working doc
     * @param StdObject $opt
     *
     * @return string
     */
    function getXmlValue(Doc &$doc, $opt = false)
    {
        if ($opt->index > -1)
            $v = $doc->getTvalue($this->id, '', $opt->index);
        else $v = $doc->getValue($this->id);
        //if (! $v) return sprintf("<!-- no value %s -->",$this->id);


        if ($this->getOption("autotitle") == "yes")
            return sprintf("<!--autotitle %s %s -->", $this->id, $v);
        if ((!$v) && ($this->type != 'array')) {
            if (($this->type == 'file') || ($this->type == 'image'))
                return sprintf('<%s mime="" title="" xsi:nil="true"/>', $this->id);
            else return sprintf('<%s xsi:nil="true"/>', $this->id);
        }
        switch ($this->type) {
        case 'timestamp' :
        case 'date' :
            $v = stringDateToIso($v);
            return sprintf("<%s>%s</%s>", $this->id, $v, $this->id);
        case 'array' :
            $la = $doc->getAttributes();
            $xmlvalues = array();
            $av = $doc->getAvalues($this->id);
            $axml = array();
            foreach ( $av as $k => $col ) {
                $xmlvalues = array();
                foreach ( $col as $aid => $aval ) {
                    $oa = $doc->getAttribute($aid);
                    if (empty($opt->exportAttributes[$doc->fromid]) || in_array($aid, $opt->exportAttributes[$doc->fromid])) {
                        $opt->index = $k;
                        $xmlvalues[] = $oa->getXmlValue($doc, $opt);
                    }
                }
                $axml[] = sprintf("<%s>%s</%s>", $this->id, implode("\n", $xmlvalues), $this->id);
            }
            $opt->index = -1; // restore initial index
            return implode("\n", $axml);
        case 'image' :
        case 'file' :
            if (preg_match(PREGEXPFILE, $v, $reg)) {
                if ($opt->withIdentificator)
                    $vid = $reg[2];
                else $vid = '';
                $mime = $reg[1];
                $name = $reg[3];
                $base = getParam("CORE_EXTERNURL");
                $href = $base . str_replace('&', '&amp;', $doc->getFileLink($this->id));
                if ($opt->withFile) {
                    $path = $doc->vault_filename_fromvalue($v, true);

                    if (is_file($path)) {
                        if ($opt->outFile) {
                            return sprintf('<%s vid="%d" mime="%s" title="%s">[FILE64:%s]</%s>', $this->id, $vid, $mime, $name, $path, $this->id);
                        } else {
                            return sprintf('<%s vid="%d" mime="%s" title="%s">%s</%s>', $this->id, $vid, $mime, $name, base64_encode(file_get_contents($path)), $this->id);
                        }
                    } else {
                        return sprintf('<!-- file not found --><%s vid="%d" mime="%s" title="%s"/>', $this->id, $vid, $mime, $name, $this->id);
                    }
                } else {
                    return sprintf('<%s vid="%d" mime="%s" href="%s" title="%s"/>', $this->id, $vid, $mime, $href, $this->encodeXml($name));
                }
            } else {
                return sprintf("<%s>%s</%s>", $this->id, $v, $this->id);
            }

        case 'docid' :
            $info = getTDoc($doc->dbaccess, $v, array(), array(
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
                    if ($info["locked"] == -1) {
                        $info["title"] = $doc->getLastTitle($docid);
                    }
                }
                if ($info["name"]) {
                    if ($opt->withIdentificator) {
                        return sprintf('<%s id="%s" name="%s">%s</%s>', $this->id, $docid, $info["name"], $this->encodeXml($info["title"]), $this->id);
                    } else {
                        return sprintf('<%s name="%s">%s</%s>', $this->id, $info["name"], $this->encodeXml($info["title"]), $this->id);
                    }
                } else {
                    if ($opt->withIdentificator) {
                        return sprintf('<%s id="%s">%s</%s>', $this->id, $docid, $this->encodeXml($info["title"]), $this->id);
                    } else {

                        return sprintf('<%s>%s</%s>', $this->id, $this->encodeXml($info["title"]), $this->id);
                    }
                }
            } else {
                if ((strpos($v, '<BR>') === false) && (strpos($v, "\n") === false)) {
                    return sprintf('<%s id="%s">%s</%s>', $this->id, $v, _("unreferenced document"), $this->id);
                } else {
                    return sprintf('<%s id="%s">%s</%s>', $this->id, str_replace(array(
                        "\n",
                        '<BR>'
                    ), ',', $v), _("multiple document"), $this->id);
                }
            }
        default :
            return sprintf("<%s>%s</%s>", $this->id, $this->encodeXml($v), $this->id);
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
        foreach ( $la as $k => $v ) {
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
        foreach ( $la as $k => $v ) {
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
     * Return array of enumeration definition
     * the array's keys are the enum key and the values are the labels
     *
     * @return array
     */
    function getEnum()
    {
        global $__tenum; // for speed optimization
        global $__tlenum;

        if (isset($__tenum[$this->id]))
            return $__tenum[$this->id]; // not twice


        if (($this->type == "enum") || ($this->type == "enumlist")) {
            // set the enum array
            $this->enum = array();
            $this->enumlabel = array();
            $br = $this->docname . '#' . $this->id . '#'; // id i18n prefix
            if (($this->phpfile != "") && ($this->phpfile != "-")) {
                // for dynamic  specification of kind attributes
                if (!include_once ("EXTERNALS/$this->phpfile")) {
                    global $action;
                    $action->exitError(sprintf(_("the external pluggin file %s cannot be read"), $this->phpfile));
                }
                if (preg_match("/(.*)\((.*)\)/", $this->phpfunc, $reg)) {
                    $args = explode(",", $reg[2]);
                    if (preg_match("/linkenum\((.*),(.*)\)/", $this->phpfunc, $dreg)) {
                        $br = $dreg[1] . '#' . strtolower($dreg[2]) . '#';
                    }
                    if (function_exists($reg[1])) {
                        $this->phpfunc = call_user_func_array($reg[1], $args);
                    } else {
                        AddWarningMsg(sprintf(_("function [%s] not exists"), $this->phpfunc));
                        $this->phpfunc = "";
                    }
                } else {
                    AddWarningMsg(sprintf(_("invalid syntax for [%s] for enum attribute"), $this->phpfunc));
                }
            }

            $sphpfunc = str_replace("\\.", "-dot-", $this->phpfunc); // to replace dot & comma separators
            $sphpfunc = str_replace("\\,", "-comma-", $sphpfunc);
            if ($sphpfunc != "") {
                $tenum = explode(",", $sphpfunc);
                foreach ( $tenum as $k => $v ) {
                    list($enumKey, $enumValue) = explode("|", $v);
                    $treeKeys = explode(".", $enumKey);
                    $enumKey = trim($enumKey);
                    if (strlen($enumKey) == 0)
                        $enumKey = " ";
                    $enumValue = trim($enumValue);
                    $translatedEnumValue = _($br . $enumValue);
                    if ($translatedEnumValue != $br . $enumValue) {
                        $enumValue = $translatedEnumValue;
                    }

                    $n = count($treeKeys);
                    if ($n <= 1) {
                        $enumValue = str_replace(array(
                            '-dot-',
                            '-comma-'
                        ), array(
                            '\.',
                            ','
                        ), $enumValue);
                        $this->enum[str_replace(array(
                            '-dot-',
                            '-comma-'
                        ), array(
                            '\.',
                            ','
                        ), $enumKey)] = $enumValue;
                        $this->enumlabel[str_replace(array(
                            '-dot-',
                            '-comma-'
                        ), array(
                            '.',
                            ','
                        ), $enumKey)] = $enumValue;
                    } else {
                        $enumlabelKey = '';
                        $tmpKey = '';
                        $previousKey = '';
                        foreach ( $treeKeys as $i => $treeKey ) {
                            $enumlabelKey = $treeKey;

                            if ($i < $n - 1) {
                                if ($i > 0) {
                                    $tmpKey .= '.';
                                }
                                $tmpKey .= $treeKey;
                            }

                        }
                        $tmpKey = str_replace(array(
                            '-dot-',
                            '-comma-'
                        ), array(
                            '\.',
                            ','
                        ), $tmpKey);
                        $enumlabelValue = $this->enum[$tmpKey] . '/' . $enumValue;
                        $enumlabelValue = str_replace(array(
                            '-dot-',
                            '-comma-'
                        ), array(
                            '\.',
                            ','
                        ), $enumlabelValue);
                        $this->enum[str_replace(array(
                            '-dot-',
                            '-comma-'
                        ), array(
                            '\.',
                            ','
                        ), $enumKey)] = $enumValue;
                        $this->enumlabel[str_replace(array(
                            '-dot-',
                            '-comma-'
                        ), array(
                            '.',
                            ','
                        ), $enumlabelKey)] = $enumlabelValue;
                    }
                }
            }
        }
        $__tenum[$this->id] = $this->enum;
        $__tlenum[$this->id] = $this->enumlabel;
        return $this->enum;
    }
    /**
     * return array of enumeration definition
     * the array'skeys are the enum single key and the values are the complete labels
     *
     * @param string $enumid the key of enumerate (if no parameter all labels are returned
     *
     * @return array
     */
    function getEnumLabel($enumid = null)
    {
        global $__tlenum;

        $this->getEnum();

        $implode = false;
        if (isset($__tlenum[$this->id])) { // is set
            if ($enumid === null)
                return $__tlenum[$this->id];
            if (strstr($enumid, "\n")) {
                $enumid = explode("\n", $enumid);
                $implode = true;
            }
            if (is_array($enumid)) {
                $tv = array();
                foreach ( $enumid as $v ) {
                    if (isset($__tlenum[$this->id][$v]))
                        $tv[] = $__tlenum[$this->id][$v];
                    else $tv[] = $enumid;
                }
                if ($implode)
                    return implode("\n", $tv);
                return $tv;
            } else {
                if (isset($__tlenum[$this->id][$enumid]))
                    return $__tlenum[$this->id][$enumid];
                else return $enumid;
            }
        }
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
        if ($key == "")
            return "";

        $a = new DocAttr($dbaccess, array(
            $this->docid,
            $this->id
        ));
        if ($a->isAffected()) {
            $tenum = $this->getEnum();

            $key = str_replace(array(
                '|'
            ), array(
                '_'
            ), $key);
            $label = str_replace(array(
                '|'
            ), array(
                '_'
            ), $label);
            if (!array_key_exists($key, $tenum)) {
                $tenum[$key] = $label;
                global $__tenum; // modify cache
                global $__tlenum;
                $__tenum[$this->id][$key] = $label;
                $__tlenum[$this->id][$key] = $label;
                // convert array to string
                $tsenum = array();
                foreach ( $tenum as $k => $v ) {
                    $v = str_replace(array(
                        ',',
                        '|'
                    ), array(
                        '\,',
                        '_'
                    ), $v);
                    $k = str_replace(array(
                        ',',
                        '|'
                    ), array(
                        '\,',
                        '_'
                    ), $k);
                    $tsenum[] = "$k|$v";
                }
                $senum = implode($tsenum, ',');
                $a->phpfunc = $senum;
                $err = $a->modify();
                if ($err == "") {
                    include_once ("FDL/Lib.Attr.php");
                    refreshPhpPgDoc($dbaccess, $this->docid);
                }
            }
        } else {
            $err = sprintf(_("unknow attribute %s (family %s)"), $this->id, $this->docid);
        }

        return $err;
    }

    /**
     * Test if an enum key existe
     *
     * @param string $key enumKey
     *
     * @return boolean
     */
    function existEnum($key)
    {
        if ($key == "")
            return false;
        $this->getEnum();
        if (isset($this->enum[$key]))
            return true;
        return false;
    }

}

/**
 * Structural attribute (attribute that contain other attribute : tab, frame)
 *
 * @author anakeen
 *
 */
class FieldSetAttribute extends BasicAttribute
{

    public $fieldSet; // field set object
    /**
     * Constructor
     *
     * @param string $id logical name
     * @param id $docid famid
     * @param string $label default translation key
     * @param string $visibility visibility option
     * @param string $usefor Attr or Param usage
     * @param string $type kind of
     * @param string $fieldSet parent attribute
     * @param string $options option string
     * @param unknown_type $docname
     */
    function __construct($id, $docid, $label, $visibility = "", $usefor = "", $type = "frame", &$fieldSet = null, $options = "", $docname = "")
    {
        $this->id = $id;
        $this->docid = $docid;
        $this->labelText = $label;
        $this->visibility = $visibility;
        $this->usefor = $usefor;
        $this->type = $type;
        $this->fieldSet = &$fieldSet;
        $this->options = $options;
        $this->docname = $docname;
    }
    /**
     * Generate the xml schema fragment
     *
     * @param array $la array of DocAttribute
     *
     * @return string
     */
    function getXmlSchema(&$la)
    {
        $lay = new Layout(getLayoutFile("FDL", "fieldattribute_schema.xml"));
        $lay->set("aname", $this->id);
        $this->common_getXmlSchema($lay);

        $lay->set("minOccurs", "0");
        $lay->set("maxOccurs", "1");
        $lay->set("notop", ($this->fieldSet->id != '' && $this->fieldSet->id != 'FIELD_HIDDENS'));
        $tax = array();
        foreach ( $la as $k => $v ) {
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
     * @param StdObject $opt
     *
     * @return string
     */
    function getXmlValue(Doc &$doc, $opt)
    {
        $la = $doc->getAttributes();
        $xmlvalues = array();
        foreach ( $la as $k => $v ) {
            if ($v->fieldSet && $v->fieldSet->id == $this->id && (empty($opt->exportAttributes[$doc->fromid]) || in_array($v->id, $opt->exportAttributes[$doc->fromid]))) {
                $xmlvalues[] = $v->getXmlValue($doc, $opt);
            }
        }
        if ($opt->flat)
            return implode("\n", $xmlvalues);
        else return sprintf("<%s>%s</%s>", $this->id, implode("\n", $xmlvalues), $this->id);
    }
}

class MenuAttribute extends BasicAttribute
{
    public $link; // hypertext link
    public $ordered;
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
    public $ordered;
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
            $l .= "&app=FREEDOM&action=BATCHEXEC&sapp=" . $this->wapplication;
            $l .= "&saction=" . $this->waction;
            $l .= "&id=" . $docid;
        } else {
            $l .= "&app=" . $this->wapplication;
            $l .= "&action=" . $this->waction;
            if (!stristr($this->waction, "&id="))
                $l .= "&id=" . $docid;
        }
        return $l;
    }
}
?>