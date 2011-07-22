<?php
/**
 * Family Object Definition
 *
 * @author Anakeen 2009
 * @version $Id: $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 */
include_once("DATA/Class.Document.php");

/**
 * Family Class
 *
 */
class Fdl_Family extends Fdl_Document {

  /**
   * Add a new attribute in family
   */
  function addAttribute($id,$label,$order,$type,$parent='',$visibility='W',$intitle=false,$inabstract=false,$needed=false,$link='',$elink='',$phpfile="",$phpfunc='',$constraint='',$options=null) {
    if ($this->doc) {
      $err=$this->doc->lock(true);
      if ($err) {
	$this->setError($err);
	return null;
      }
      $id=strtolower($id);
      if ($id=="") {	
	$this->setError(_("attribute identificator is null"));
	$err=$this->doc->unlock(true);
	return null;
      }
      if (! preg_match("/^[a-z0-9_]+$/",$id)) {	
	$this->setError(sprintf(_("attribute [%s] identificator syntax is unavalaible. Must have only alphanumeric characters and must not includes spaces"),$id));
	$err=$this->doc->unlock(true);
	return null;
      }

      if (! preg_match("/^[H|R|W|S|O|I|U]$/",$visibility)) {	
	$this->setError(sprintf(_("attribute [%s] visibility must be one of H,R,W,S,O,I,U"),$visibility));
	$err=$this->doc->unlock(true);
	return null;
      }
      $oattr=new DocAttr($this->dbaccess,array($this->doc->initid,$id));  
      if ($oattr->isAffected()) {
	$this->setError(sprintf(_("attribute [%s] already exists : cannot add it"),$id));
	$err=$this->doc->unlock(true);
	return null;
      }
            
      if ($parent) {
	$oap=$this->doc->getAttribute($parent);
	if (!$oap) {
	  $this->setError(sprintf(_("parent attribute [%s] not exists : cannot add it"),$parent));
	  $err=$this->doc->unlock(true);
	  return null;
	}
      }
      
      if ($this->completeAttributes($oattr,$id,$label,$order,$type,$parent,$visibility,$intitle,$inabstract,
				    $needed,$link,$elink,$phpfile,$phpfunc,$constraint,$options)) {            
	$err=$oattr->add();
	if ($err) $this->setError($err);
	else {
	  $wsh = getWshCmd();
	  $cmd = $wsh . " --api=fdl_adoc --docid=".intval($this->doc->initid);
	  $log = exec($cmd, $out, $ret); 
	  if ($err) $this->setError($err);
	  else return true;
	}
      }
      $err=$this->doc->unlock(true);
            

    }
    return null;
  }
  function modifyAttribute($id,$label,$order,$type,$parent='',$visibility='W',$intitle=false,$inabstract=false,$needed=false,$link='',$elink='',$phpfile="",$phpfunc='',$constraint='',$options=null) {
    if ($this->doc) {
      $err=$this->doc->lock(true);
      if ($err) {
	$this->setError($err);
	return null;
      }
      $id=strtolower($id);
      if ($id=="") {	
	$this->setError(_("attribute identificator is null"));
	$err=$this->doc->unlock(true);
	return null;
      }
     

      $oa=$this->doc->getAttribute($id);
      if (! $oa) {
	$this->setError(sprintf(_("attribute [%s] not exists : cannot modify it"),$id));
	$err=$this->doc->unlock(true);
	return null;
      }

      $oattr=new DocAttr($this->dbaccess,array($oa->docid,$id));
      if (! $oattr->isAffected()) $oattr=new DocAttr($this->dbaccess,array($oa->docid,':'.$id));
      if (! $oattr->isAffected()) {
	$this->setError(sprintf(_("attribute [%s] not exists : cannot modify it"),$id));
	$err=$this->doc->unlock(true);
	return null;
      }
      if ($this->completeAttributes($oattr,$oattr->id,$label,$order,$type,$parent,$visibility,$intitle,$inabstract,
				    $needed,$link,$elink,$phpfile,$phpfunc,$constraint,$options)) {            
	$err=$oattr->modify();
	if ($err) $this->setError($err);
	else {
	  $wsh = getWshCmd();
	  $cmd = $wsh . " --api=fdl_adoc --docid=".intval($oa->docid);
	  $log= exec($cmd, $out, $ret);
	  if ($err) $this->setError($err);
	  else return true;
	}
      }
      
      $err=$this->doc->unlock(true);
      
    }
    return null;
  }

  function removeAttribute($id) {
    if ($this->doc) {
      $err=$this->doc->lock(true);
      if ($err) {
	$this->setError($err);
	return null;
      }
      $id=strtolower($id);
      if ($id=="") {	
	$this->setError(_("attribute identificator is null"));
	$err=$this->doc->unlock(true);
	return null;
      }
     

      $oattr=new DocAttr($this->dbaccess,array($this->doc->initid,$id));  
      if (! $oattr->isAffected()) {
	$this->setError(sprintf(_("attribute [%s] not exists : cannot delete it"),$id));
	$err=$this->doc->unlock(true);
	return null;
      }
      $err=$oattr->delete();
      if ($err) $this->setError($err);
      else {
	$wsh = getWshCmd();
	$cmd = $wsh . "--api=fdl_adoc --docid=".intval($this->doc->initid);
	$log= exec($cmd, $out, $ret);
	if ($err) $this->setError($err);
	else return true;
      }
            
      $err=$this->doc->unlock(true);
      
    }
    return null;
  }

  private function completeAttributes(&$oattr,$id,$label,$order,$type,$parent='',$visibility='W',$intitle=false,$inabstract=false,$needed=false,$link='',$elink='',$phpfile="",$phpfunc='',$constraint='',$options=null) {
    if ($this->doc) {
      $id=strtolower($id);
      if ($id=="") {	
	$this->setError(_("attribute identificator is null"));	
	return null;
      }
      if (! preg_match("/^[a-z0-9_:]+$/",$id)) {	
	$this->setError(sprintf(_("attribute [%s] identificator syntax is unavalaible. Must have only alphanumeric characters and must not includes spaces"),$id));
	return null;
      }

      if ($visibility!==null) {
	if (! preg_match("/^[H|R|W|S|O|I|U]$/",$visibility)) {	
	  $this->setError(sprintf(_("attribute [%s] visibility must be one of H,R,W,S,O,I,U"),$visibility));
	  return null;
	}
      }
                 
      if ($parent) {
	$oap=$this->doc->getAttribute($parent);
	if (!$oap) {
	  $this->setError(sprintf(_("parent attribute [%s] not exists : cannot add it"),$parent));
	  $err=$this->doc->unlock(true);
	  return null;
	}
      }
      

      if ($order && (! is_numeric($order))) {
	// upto or downto
	if (preg_match("/^(upto|downto) ([a-z0-9_]+)$/",$order,$reg)) {
	  $oar=$this->doc->getAttribute($reg[2]);
	  if ($oar) {
	    $reforder=$oar->ordered;
	    $updown=$reg[1];
	    if ($updown=='upto') $order=$reforder-1;
	    else $order=$reforder+1;
	    uasort($this->doc->attributes->attr,"tordered"); 
	    $la=$this->doc->getAttributes();

	    // find the previous
	    $oaprev=null;
	    reset($la);
	    while (list($k, $v) = each($la)) {
	      if ($k == $oar->id) {
		$oaprev=$_oaprev;
		break;
	      }
	    }
	    prev($la); // here we are in upto element
	    $needdown=array();
	    if ($updown=='upto') {
	      while (($v=prev($la)) !== false) {
		if ($v->ordered < $order) {
		  break;
		}
		if ($v->id!=$id) $needdown[]=$v;
	      }
	    } else {
	      while (($v=next($la)) !== false) {
		if ($v->ordered > $order) {
		  break;
		}
		if ($v->id!=$id) $needdown[]=$v;
	      }
	    }	   
	    $famtoupdate=array();
	    foreach ($needdown as $k=>$v) {
	      $attr=new DocAttr($this->dbaccess,array($v->docid,$v->id));	      
	      if (! $attr->isAffected()) $attr=new DocAttr($this->dbaccess,array($v->docid,':'.$v->id));
	      if ($attr->isAffected()) {
		if ($updown=='upto') $attr->ordered=$v->ordered-1;
		else $attr->ordered=$v->ordered+1;
		$err=$attr->modify();
		if ($v->docid != $this->doc->id) {
		  $famtoupdate[$v->docid]=$v->docid;
		}
	      }
	    }
	    
	    foreach ($famtoupdate as $k=>$v) {
	      $wsh = getWshCmd();
	      $cmd = $wsh . " --api=fdl_adoc --docid=".intval($k);
	      $log = exec($cmd, $out, $ret); 
	    }
	  } else $order=null;
	} else {
	  $this->setError(sprintf(_("syntax error for order %s. May be an integer or a relative upto or downto identificator"),$order));	
	  return null;
	}
      }
      if (! $oattr->id) {
	$oattr->id=$id;
	$oattr->docid=$this->doc->id;
      }
      if ($label!==null) $oattr->labeltext=$label;      
      if ($intitle!==null) $oattr->title=$intitle?'Y':'N';
      if ($inabtract!==null)$oattr->abstract=$inabtract?'Y':'N';
      if ($needed!==null)$oattr->needed=$needed?'Y':'N';
      if ($type!==null)$oattr->type=$type;	    
      if ($parent!==null)$oattr->frameid=$parent?$parent:"0";
      if ($order!==null)$oattr->ordered=intval($order);
      if ($visibility!==null)$oattr->visibility=$visibility;
      if ($link!==null)$oattr->link=$link;
      if ($phpfile!==null)$oattr->phpfile=$phpfile;
      if ($phpfunc!==null)$oattr->phpfunc=$phpfunc;
      if ($elink!==null)$oattr->elink=$elink;
      if ($constraint!==null)$oattr->phpconstraint=$constraint;

      if ($options!==null) {
	$opt=array();
	foreach ($options as $k=>$v) {
	  $opt[]="$k=$v";
	}
	$oattr->options=implode("|",$opt);
      }

      $oattr->usefor='N';      
            
      return true;
    }
    return null;
  }
}

?>