<?
// ---------------------------------------------------------------------------
// 
// ---------------------------------------------------------------------------
// Anakeen 2000 - yannick.lebriquer@anakeen.com
// ---------------------------------------------------------------------------
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or (at
//  your option) any later version.
//
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
// or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
// for more details.
//
// You should have received a copy of the GNU General Public License along
// with this program; if not, write to the Free Software Foundation, Inc.,
// 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
// ---------------------------------------------------------------------------


// return all ancestor classes
function get_ancestors_class($classname) {
  $father = get_parent_class($classname);

  
  if ($father != "") {

    $ancestors = get_ancestors_class($father);
    $ancestors[] = $father;
  } else {
    $ancestors = array();
  }
  return $ancestors;
}

Class Cache {

  var $isCacheble= true;
  var $cached = false; // use a cached object

  var $max=50; // default value
  var $cacheclass="";
  var $relatedCacheClass= array(); // class must ne cleaned also in case of modify

  function SetCache($id) {    
    global $HTTP_CONNECTION; // use only cache with HTTP
    if ($HTTP_CONNECTION == "")  return false;
   
    $this->Cacheble();

    global $CacheObj;
    if (($this->isCacheble)&& ($id != "")) {

      


      global $core;
      if (is_object($core)) {
	$this->max = $core->GetParam("CORE_MAXOBJCACHE", $this->max);
      }
      if ((!isset( $CacheObj[$this->cacheclass()])) || 
	  (count( $CacheObj[$this->cacheclass()]) < $this->max)) {
	$CacheObj[$this->cacheclass()][$id]=$this;
	
	//		print "SET:".$this->cacheclass().$id."<BR>";
	$this->ReallySetCache();

	return true;
      } else {
	$this->ClearCache(false);
      }
    }
    return false;
  }

  function GetCache($id) {
    global $HTTP_CONNECTION; // use only cache with HTTP
    if ($HTTP_CONNECTION == "")  return false;

    $this->Cacheble();

    global $CacheObj;
    if (($this->isCacheble) && ($id != "")) {


      session_register("CacheObj");
      if (isset($CacheObj[$this->cacheclass()][$id]) && 
	  ((is_object($CacheObj[$this->cacheclass()][$id])) || 
	   (is_array($CacheObj[$this->cacheclass()][$id])))
	  ) {

	$this=$CacheObj[$this->cacheclass()][$id];
	$this->cached = true;
	$this->ReallyGetCache();
	//	print "GET:".$this->cacheclass().$id."<BR>";
	return true;
      }
      
      
    }
    return false;
  }

  function CacheClass() {
      if ($this->cacheclass == "") $this->cacheclass = get_class($this);
      return $this->cacheclass;
  }

  function ClearCache($reallyset = true) {
    global $HTTP_CONNECTION; // use only cache with HTTP
    if ($HTTP_CONNECTION == "")  return false;

    if ($this->isCacheble) {
      global $CacheObj;
      $this->ClearCacheIndex($this->cacheclass(), $reallyset);
      
      if (is_array($CacheObj)) {
	reset($CacheObj);
	while (list($k,$v) = each ($CacheObj)) {
	  // uset all father class also
	  if (is_subclass_of ($this, $k)) {
	    $this->ClearCacheIndex($k, $reallyset);
	  }
	}
      
      
	// unset all related class 
	while (list($k,$v) = each ($this->relatedCacheClass)) {
	  $this->relatedCacheClass[$k] = strtolower($v);
	  $this->ClearCacheIndex($this->relatedCacheClass[$k], $reallyset);

	}

	// unset all childs of related class 
	reset($CacheObj);
	while (list($k,$v) = each ($CacheObj)) {

	  $anc = get_ancestors_class($k);
	  if (count(array_intersect($anc, $this->relatedCacheClass)) > 0) {
	    $this->ClearCacheIndex($k, $reallyset);
	  }
	}
      }
      
    }

  }

  // clear one entry of the object cache
  function ClearCacheIndex($index, $reallyset = true) {
    
      global $CacheObj;
      unset($CacheObj[$index]);
      if ($reallyset) { // to alert other user of modification
	$scache = new SessionCache("", $index);
	$scache->SetTime();
      }
      // reset access last time because it is empty
      // to avoid unnessecessary reinit (by other users)
      global $AccessCacheObj;
      $date = gettimeofday();
      $AccessCacheObj[$index]=$date['sec'];
      
  }

  function Cacheble() {

    // detect if cache enable
    if ($this->isCacheble) {
      global $core;
      if (is_object($core)) {
	$this->isCacheble = ($core->GetParam("CORE_USECACHE","yes") == "yes");
      }
    }

  }
  function ReallySetCache() {
    //just to trace

  }
  function ReallyGetCache() {
    //just to trace  
  }


  // to remove obolete index
  function InitCache() {
    global $HTTP_CONNECTION; // use only cache with HTTP
    if ($HTTP_CONNECTION == "")  return false;

    session_register("AccessCacheObj");
    $accessobject = new QueryDb("","SessionCache");
    $tao= $accessobject->Query(0,0,"TABLE");
    if ($accessobject->nb > 0) {
      global $AccessCacheObj;
      while (list($k,$v) = each ($tao)) {
	if (isset($AccessCacheObj[$v["index"]])) {
	  //print "test cache ".$v["index"].":".$v["lasttime"].">".$AccessCacheObj[$v["index"]]."<BR>";
	  if (intval($v["lasttime"]) > intval($AccessCacheObj[$v["index"]])) {
	    //  print "need update ".$v["index"]."<BR>";
	    $this->ClearCacheIndex($v["index"], false);
	  }
	} else {
	  
	  $date = gettimeofday();
	  $AccessCacheObj[$v["index"]]=$date['sec'];
	} 

      }
    }
  }

  //-------end class
}
?>