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


Class Cache {

  var $isCacheble= true;
  var $cached = false; // use a cached object

  var $max=50; // default value
  var $cacheclass="";
 

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
    if ($this->isCacheble) {
      if ($reallyset) {


	global $clearedClass;
	if (! isset($clearedClass[$this->cacheclass()])) {
	  $query = new QueryDb("", "Session");
	  $query -> AddQuery("userid > 0");
	  $tablesession = $query->Query(0,0,"TABLE");
	  if ($query->nb > 0) {
	    while (list($k,$v) = each ($tablesession)) {

	      global $CacheObj;
	      unset($CacheObj[$this->cacheclass()]);
	      //global $HTTP_VERSION_VARS;
	      //  unset($HTTP_VERSION_VARS["CacheObj"][$this->cacheclass()]);
	      session_write_close();
	  
	      session_id($v["id"]);
	      @session_start();
	    }
	  }
	  $clearedClass[$this->cacheclass()]=1;
	}
      }
    } else {
      global $CacheObj;
      unset($CacheObj[$this->cacheclass()]);
    }
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

}

?>