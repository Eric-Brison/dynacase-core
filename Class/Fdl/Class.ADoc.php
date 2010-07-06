<?php
/**
 * Attribute Document Object Definition
 *
 * @author Anakeen 2002
 * @version $Id: Class.ADoc.php,v 1.13 2006/04/03 14:56:26 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 */
/**
 */

include_once("FDL/Class.DocAttribute.php");
/**
 * Attribute Document Class
 *
 */
Class ADoc  {

	function __construct () {
		$this->attr["FIELD_HIDDENS"] = new FieldSetAttribute("FIELD_HIDDENS",0, "hiddens");
	}

	function getAttr($id) {
		if (isset($this->attr[$id])) return $this->attr[$id];
		if (isset($this->attr[strtolower($id)])) return $this->attr[$id];
		 
		return false;
	}
	/**
	 * get attributes ids
	 */
	function getAttrIds($id) {
		return array_keys($this->attr);
	}
	function GetNormalAttributes($onlyopt=false)  {
		$tsa=array();
		if (isset($this->attr)) {
			foreach($this->attr as $k=>$v) {
				if ((get_class($v) == "NormalAttribute") &&
				((($v->usefor != "Q")&&(!$onlyopt)) || (($v->usefor == "O")&&($onlyopt))))   $tsa[$v->id]=$v;
			}
		}
		return $tsa;
	}

	public function GetActionAttributes() {
		$tsa=array();
		 
		foreach($this->attr as $k=>$v) {
			if (get_class($v) == "ActionAttribute")  $tsa[$v->id]=$v;
		}
		return $tsa;
	}
	 
	function getParamAttributes() {
		$tsa=array();
		if (isset($this->attr)) {
			reset($this->attr);
			while (list($k,$v) = each($this->attr)) {
				if ((get_class($v) == "NormalAttribute") && ($v->usefor == "Q")) $tsa[$v->id]=$v;
			}
		}
		return $tsa;
	}
	
	function getArrayElements($id) {
		$a = $this->getAttr($id);
		if ($a && ($a->type == "array")) {
			if ($a->usefor != "Q") {
				$tsa=$this->GetNormalAttributes();
			} else {
				$tsa=$this->getParamAttributes();
			}
			$ta=array();
			foreach ($tsa as $k=>$v) {
				if ($v->fieldSet->id == $id) $ta[$v->id]=$v;
			}
			return $ta;
			 
		}
		return false;
	}

	 
}
?>
