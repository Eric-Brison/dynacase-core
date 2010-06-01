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



Class BasicAttribute {
  public $id;
  public $docid;
  public $labelText;
  public $visibility; // W, R, H, O, M, I
  public $options;
  public $docname;
  public $type; // text, longtext, date, file, ...

  function __construct($id, $docid, $label ) {
    $this->id=$id;
    $this->docid=$docid;
    $this->labelText=$label;
  }
   /**
   * return attribute label
   * @return string
   */
  function getLabel() {
    $r=$this->docname.'#'.$this->id;
    $i=_($r);
    if ($i != $r) return $i;
    return $this->labelText;
  }

 /**
   * return value of option $x
   * @return string
   */
  function getOption($x,$def="") {
    if (!isset($this->_topt)) {
      $topt=explode("|",$this->options);
      $this->_topt=array();
      foreach ($topt as $k=>$v) {
	list($vn,$vv)=explode("=",$v);
	$this->_topt[$vn]=$vv;
      }
    }
    $r=$this->docname.'#'.$this->id.'#'.$x;
    $i=_($r);  
    if ($i != $r) return $i;
    $v=$this->_topt[$x];
    return ($v?$v:$def);  
  }
/**
   * return all value of options
   * @return array
   */
  function getOptions() {
    if (!isset($this->_topt)) {
        $this->getOption('a');
    }
    return $this->_topt;
  }
  /**
   * temporary change option
   * @return void
   */
  function setOption($x,$v) {
    if (!isset($this->_topt)) {
      $this->getOption($x);
    }
    $this->_topt[$x]=$v;  
  }
  /**
   * temporary change visibility
   * @param char $vis new visibility : R|H|W|O|I
   * @return void
   */
  function setVisibility($vis) {
    $this->mvisibility=$vis;
  }
  /**
   * temporary change need
   * @param bool $need true means needed, false not needed
   * @return void
   */
  function setNeeded($need) {
    $this->needed=$need;
  }
  /**
   * test if attribute is not a auto created attribute
   */
   function isReal() {
    
   }

  /**
   * to see if an attribute is n item of an array
   * @return boolean
   */
  function inArray() {
    if (get_class($this) == "NormalAttribute") {
      if ($this->fieldSet->type=="array") return true;
    }
    return false;
   }
   /**
    * verify if accept multiple value
    * @return boolean
    */
  function isMultiple() {
  	return ($this->inArray() || ($this->getOption('multiple')=='yes'));
  }
  
  function getXmlSchema() {
      return "<!-- no Schema ".$this->id."-->";
  }
function common_getXmlSchema(&$play) {
   
      $lay=new Layout(getLayoutFile("FDL","infoattribute_schema.xml"));
      $lay->set("aname",$this->id);
      $lay->set("label",$this->labelText);
      $lay->set("type",$this->type);
      $lay->set("visibility",$this->visibility);
      $lay->set("isTitle",$this->isInTitle);
      $lay->set("phpfile",$this->phpfile);
      $lay->set("phpfunc",$this->phpfunc);
      $lay->set("computed",((! $this->phpfile) && (substr($this->phpfunc,0,2)=="::")));
      $lay->set("link",str_replace('&','&amp;',$this->link));
      $lay->set("elink",str_replace('&','&amp;',$this->elink));
      $lay->set("default",false); // TODO : need detect default value
      $lay->set("constraint",$this->phpconstraint);
      $tops=$this->getOptions();
      $t=array();
      foreach ($tops as $k=>$v) {
          if ($k) $t[]=array("key"=>$k,"val"=>$v);
      }
      $lay->setBlockData("options",$t);
      
      $play->set("minOccurs",$this->needed?"1":"0");
      $play->set("maxOccurs",(($this->getOption('multiple')=='yes')?"unbounded":"1"));
      $play->set("aname",$this->id);
      $play->set("appinfos", $lay->gen());
  }
}

Class NormalAttribute extends BasicAttribute {
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
  function __construct($id, $docid, $label, $type, $format, $repeat, $order, $link,
			   $visibility, $needed,$isInTitle,$isInAbstract,
		       &$fieldSet,$phpfile,$phpfunc,$elink,$phpconstraint="",$usefor="",$eformat="",$options="",$docname="") {
    $this->id=$id;
    $this->docid=$docid;
    $this->labelText=$label;
    $this->type=$type;
    $this->format=$format;
    $this->eformat=$eformat;
    $this->ordered=$order;
    $this->link=$link;
    $this->visibility=$visibility;
    $this->needed=$needed;
    $this->isInTitle =$isInTitle;
    $this->isInAbstract=$isInAbstract;
    $this->fieldSet=&$fieldSet;
    $this->phpfile=$phpfile;
    $this->phpfunc=$phpfunc;
    $this->elink=$elink;
    $this->phpconstraint=$phpconstraint;
    $this->usefor=$usefor;
    $this->repeat=$repeat;
    $this->options=$options;
    $this->docname=$docname;


  }
  
  function getXmlSchema($la) {
      
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
          case 'docid':
              return $this->docid_getXmlSchema($la);
          case 'date':
              return $this->date_getXmlSchema($la);
          case 'time':
              return $this->time_getXmlSchema($la);
          case 'array':
              return $this->array_getXmlSchema($la);
          default:
              return sprintf("<!-- no Schema %s (type %s)-->",$this->id,$this->type);;
      }
  }

 
 function text_getXmlSchema(&$la) {
      $lay=new Layout(getLayoutFile("FDL","textattribute_schema.xml"));
      $this->common_getXmlSchema($lay);
      
      $lay->set("maxlength",false);
      $lay->set("pattern",false);
      return $lay->gen();
  }
function enum_getXmlSchema(&$la) {
      $lay=new Layout(getLayoutFile("FDL","enumattribute_schema.xml"));
      $this->common_getXmlSchema($lay);
     $la=$this->getEnum();
     $te=array();
      foreach ($la as $k=>$v) {        
            $te[]=array("key"=>$k,"val"=>$v);        
      }
      $lay->setBlockData("enums",$te);
      return $lay->gen();
  }
  function docid_getXmlSchema(&$la) {
      $lay=new Layout(getLayoutFile("FDL","docidattribute_schema.xml"));
      $this->common_getXmlSchema($lay);
      
      $lay->set("famid",$this->format);
      return $lay->gen();
  }
  function date_getXmlSchema(&$la) {
      $lay=new Layout(getLayoutFile("FDL","dateattribute_schema.xml"));  
      $this->common_getXmlSchema($lay);
            return $lay->gen();
  }

  function int_getXmlSchema(&$la) {
      $lay=new Layout(getLayoutFile("FDL","intattribute_schema.xml"));  
      $this->common_getXmlSchema($lay);
            return $lay->gen();
  }
  function longtext_getXmlSchema(&$la) {
      $lay=new Layout(getLayoutFile("FDL","longtextattribute_schema.xml"));  
      $this->common_getXmlSchema($lay);
            return $lay->gen();
  }
  function float_getXmlSchema(&$la) {
      $lay=new Layout(getLayoutFile("FDL","floatattribute_schema.xml"));  
      $this->common_getXmlSchema($lay);
            return $lay->gen();
  }
  function time_getXmlSchema(&$la) {
      $lay=new Layout(getLayoutFile("FDL","timeattribute_schema.xml"));  
      $this->common_getXmlSchema($lay);
            return $lay->gen();
  }
  function file_getXmlSchema(&$la) {
      $lay=new Layout(getLayoutFile("FDL","fileattribute_schema.xml"));  
      $this->common_getXmlSchema($lay);
            return $lay->gen();
  }
  function array_getXmlSchema(&$la) {
      $lay=new Layout(getLayoutFile("FDL","arrayattribute_schema.xml"));       
      $this->common_getXmlSchema($lay);
            $lay->set("minOccurs","0");
      $lay->set("maxOccurs","unbounded");
      $tax=array();
      foreach ($la as $k=>$v) {        
        if ($v->fieldSet && $v->fieldSet->id==$this->id) {
            $tax[]=array("axs"=>$v->getXmlSchema($la));
        }
      }      
      $lay->setBlockData("ATTR",$tax);
      return $lay->gen();
  }
  function getEnum() {   
    global $__tenum; // for speed optimization
    global $__tlenum;

    if (isset($__tenum[$this->id])) return $__tenum[$this->id]; // not twice
 
    if (($this->type == "enum") || ($this->type == "enumlist")) {
      // set the enum array
      $this->enum=array();
      $this->enumlabel=array();
      $br=$this->docname.'#'.$this->id.'#';// id i18n prefix
      if (($this->phpfile != "") && ($this->phpfile != "-")) {
	// for dynamic  specification of kind attributes
	if (! include_once("EXTERNALS/$this->phpfile")) {
	  global $action;
	  $action->exitError(sprintf(_("the external pluggin file %s cannot be read"), $this->phpfile));
	}
	if (preg_match("/(.*)\((.*)\)/", $this->phpfunc, $reg)) {	 
	  $args=explode(",",$reg[2]); 
	  if (preg_match("/linkenum\((.*),(.*)\)/",$this->phpfunc,$dreg))  $br=$dreg[1].'#'.strtolower($dreg[2]).'#';
	  if (function_exists($reg[1])) {
	  $this->phpfunc = call_user_func_array($reg[1],$args);	 
	  } else {
	      AddWarningMsg(sprintf(_("function [%s] not exists"),$this->phpfunc));
	      $this->phpfunc="";
	  }
	} else {
	  AddWarningMsg(sprintf(_("invalid syntax for [%s] for enum attribute"),$this->phpfunc));
	}
      }

      $sphpfunc = str_replace("\\.", "-dot-",$this->phpfunc); // to replace dot & comma separators
      $sphpfunc  = str_replace("\\,", "-comma-",$sphpfunc);
      if ($sphpfunc!="") {
	$tenum = explode(",",$sphpfunc);
	foreach($tenum as $k=>$v) {
	  list($n,$text) = explode("|",$v);
	  list($n1,$n2) = explode(".",$n);
	  $r=$br.str_replace(array('-dot-','-comma-'),array('.',','),$n);
	  
	  $i=_($r);
	  if ($i != $r) $text=$i;
	  else {
	    $text=str_replace( "-dot-",".",$text);
	    $text=str_replace( "-comma-",",",$text);
	  }
	  $n=str_replace( "-dot-",".",$n);
	  $n=str_replace( "-comma-",",",$n);
	  $n1=str_replace( "-dot-",".",$n1);
	  $n1=str_replace( "-comma-",",",$n1);

	  if ($n != " ") $n=trim($n);
	  if ($n2 != "") $this->enum[$n]=$this->enum[$n1]."/".$text;
	  else $this->enum[$n]=$text;
	  if ($n2 != "") $this->enumlabel[substr($n,strrpos($n,'.')+1)]=$this->enum[$n];
	  else $this->enumlabel[$n]=$this->enum[$n];
	}
      } 
    }
    $__tenum[$this->id]=$this->enum;
    $__tlenum[$this->id]=$this->enumlabel;
    return $this->enum;
  }

  function getEnumLabel($enumid="") {  
    global $__tlenum;

    $this->getEnum();

    $implode=false;
    if (isset($__tlenum[$this->id])) { // is set
      if ($enumid=="") return $__tlenum[$this->id]; 
      if (strstr($enumid,"\n")) {
	$enumid=explode("\n",$enumid);
	$implode=true;
      }
      if (is_array($enumid)) {
	$tv=array();
	foreach ($enumid as $v) {
	  if (isset($__tlenum[$this->id][$v])) $tv[]= $__tlenum[$this->id][$v];
	  else $tv[]=$enumid;
	}
	if ($implode) return implode("\n",$tv);
	return $tv;
      } else {
	if (isset($__tlenum[$this->id][$enumid])) return $__tlenum[$this->id][$enumid];
	else return $enumid;
      }
    }    
  }

  /**
   * add new item in enum list items
   *
   * @param int $famid family identificator
   * @param string $key database key
   * @param string $label human label
   * @return string error message (empty means ok)
   */
  function addEnum($dbaccess,$key,$label) {
    if ($key =="") return "";

    $a=new DocAttr($dbaccess, array($this->docid,$this->id));
    if ($a->isAffected()) {
      $tenum=$this->getEnum();
      $key=str_replace(array('|'),array('_'),$key);
      $label=str_replace(array('|'),array('_'),$label);
      $tkey=array_keys($tenum);
      if (! in_array($key,$tkey)) {


      $tenum[$key]=$label;
      global $__tenum; // modify cache 
      global $__tlenum;
      $__tenum[$this->id][$key]=$label;
      $__tlenum[$this->id][$key]=$label;
      // convert array to string
      $tsenum=array();
      foreach ($tenum as $k=>$v) {
	$v=str_replace(array(',','|'),array('\,','_'),$v);
	$k=str_replace(array(',','|'),array('\,','_'),$k);
	$tsenum[]="$k|$v";
      }
      $senum=implode($tsenum,',');
      $a->phpfunc=$senum;
      $err=$a->modify();
      if ($err=="") {
	include_once("FDL/Lib.Attr.php");
	refreshPhpPgDoc($dbaccess,$this->docid);
      }
      }
    } else {
      $err=sprintf(_("unknow attribute %s (family %s)"),$this->id,$this->docid);
    }
  
    return $err;
  }

  function existEnum($key) {
     if ($key =="") return false;
     $this->getEnum();
     if (isset($this->enum[$key])) return true;
     return false;
  }
}


Class FieldSetAttribute extends BasicAttribute {

  public $fieldSet; // field set object
  function __construct($id, $docid, $label, $visibility="",$usefor="",$type="frame", &$fieldSet=null,$options="",$docname="" ) {
    $this->id=$id;
    $this->docid=$docid;
    $this->labelText=$label;
    $this->visibility=$visibility;
    $this->usefor=$usefor;
    $this->type=$type;
    $this->fieldSet=&$fieldSet;
    $this->options=$options;
    $this->docname=$docname;
  }
  
  function getXmlSchema(&$la) {
      $lay=new Layout(getLayoutFile("FDL","fieldattribute_schema.xml"));
      $lay->set("aname",$this->id);
      $this->common_getXmlSchema($lay);
      
      $lay->set("minOccurs","0");
      $lay->set("maxOccurs","1");
      $lay->set("notop",($this->fieldSet->id != '' && $this->fieldSet->id != 'FIELD_HIDDENS'));
      $tax=array();
      foreach ($la as $k=>$v) {        
        if ($v->fieldSet && $v->fieldSet->id==$this->id) {
            $tax[]=array("axs"=>$v->getXmlSchema($la));
        }
      }
      
      $lay->setBlockData("ATTR",$tax);
      return $lay->gen();
  }
}

Class MenuAttribute extends BasicAttribute {
  public $link; // hypertext link
  public $ordered;
  public $precond; // pre-condition to activate menu

  function __construct($id, $docid, $label, $order, $link, $visibility="", $precond="",$options="",$docname="") {
    $this->id=$id;
    $this->docid=$docid;
    $this->labelText=$label;
    $this->ordered=$order;
    $this->link=$link;
    $this->visibility=$visibility;
    $this->options=$options;
    $this->precond=$precond;
    $this->type="menu";
    $this->docname=$docname;

  }

}

Class ActionAttribute extends BasicAttribute {

  public $wapplication; // the what application name
  public $waction; // the what action name
  public $ordered;
  public $precond; // pre-condition to activate action
  function __construct($id, $docid, $label, $order,$visibility="",$wapplication="",$waction="", $precond="",$options="",$docname="" ) {
    $this->id=$id;
    $this->docid=$docid;
    $this->labelText=$label;
    $this->visibility=$visibility;
    $this->ordered=$order;
    $this->waction=$waction;
    $this->wapplication=$wapplication;
    $this->options=$options;
    $this->precond=$precond;
    $this->type="action";
    $this->docname=$docname;
  }
  function getLink($docid) {
    $l=getParam("CORE_STANDURL");
    $batch=($this->getOption("batchfolder")=="yes");
    if ($batch) {
      $l.="&app=FREEDOM&action=BATCHEXEC&sapp=".$this->wapplication;
      $l.="&saction=".$this->waction;
      $l.="&id=".$docid;
    } else {
      $l.="&app=".$this->wapplication;
      $l.="&action=".$this->waction;
      if (! stristr($this->waction,"&id=")) $l.="&id=".$docid;
    }
    return $l;
  }
}
?>