<?php
/**
 * Search Document
 *
 * @author Anakeen 2008
 * @version $Id: Class.SearchDoc.php,v 1.8 2008/08/14 14:20:25 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 */
/**
 */


include_once("FDL/Lib.Dir.php");


Class SearchDoc {  
  /**
   * family identificator filter
   * @public string
   */
  public $fromid;
  /**
   * folder identificator filter
   * @public int
   */
  public $dirid=0;
  /**
   * recursive search for folders
   * @public boolean
   */
  public $recursiveSearch=false;
  /**
   * max recursive level
   * @public int
   */
  public $folderRecursiveLevel=2;
  /**
   * number of results : set "ALL" if no limit
   * @public int
   */
  public $slice="ALL";
  /**
   * index of results begins
   * @public int
   */
  public $start=0;
  /**
   * sql filters
   * @public array
   */
  public $filters=array();
 
  /**
   * search in sub-families set false if restriction to top family
   * @public bool
   */
  public $only=false;
  /**
   * 
   * @public bool
   */
  public $distinct=false;
  /**
   * order of result : like sql order
   * @public string
   */
  public $orderby="title";
  /**
   * to search in trash : [no|also|only]
   * @public string
   */
  public $trash="";
  /**
   * restriction to latest revision
   * @public bool
   */
  public $latest=true;
  /**
   * user identificator : set to current user by default
   * @public int
   */
  public $userid=0; 
  /**
   * debug mode : to view query and delay
   * @public bool
   */
  private $debug=false;
  private $debuginfo="";

  /**
   * result type [ITEM|TABLE]
   * @private string
   */
  private $mode="TABLE";
  private $count=-1;
  private $index=0;
  private $result;

  /**
   * initialize with family
   *
   * @param string $dbaccess database coordinate
   * @param string $fromid family identificator to filter
   * 
   */
  public function __construct($dbaccess, $fromid=0) {
    $this->dbaccess=$dbaccess;
    $this->fromid=$fromid;
    $this->orderby='title';
    $this->userid=getUserId();
  }
  /**
   * count results without return data
   * 
   * @return int 
   */
  public function onlyCount() {
      if (! $this->result) {
          $fld = new_Doc($this->dbaccess, $this->dirid);
          $userid=$fld->userid;
          if ($fld->fromid != getFamIdFromName($this->dbaccess,"SSEARCH")) {
              $this->mode="ITEM";
              if ($this->debug) $debuginfo=array();
              else $debuginfo=null;
              $tqsql=getSqlSearchDoc($this->dbaccess,$this->dirid,$this->fromid,
                                     $this->filters,$this->distinct,$this->latest,$this->trash);
              $this->debuginfo["query"]=$tqsql[0];
              $count=0;
              foreach ($tqsql as $sql) {
                  if ($sql) {
                      $sql=preg_replace("/select\s+(.*)\s+from\s/","select count(id) from ",$sql);
          
                      if ($userid != 1) $sql.=" and (profid <= 0 or hasviewprivilege($userid, profid))";
                      $dbid=getDbid($this->dbaccess);
                      $mb=microtime(true);
                      $q=pg_query($dbid,$sql);
                      $result = pg_fetch_array ($q,0,PGSQL_ASSOC);
                      $count+=$result["count"];
                      $this->debuginfo["delay"]=sprintf("%.03fs",microtime(true)-$mb);
                  }
              }
            return $count;
          }
      } else $this->count();
      return $this->count;
  }
  /**
   * return original sql query before test permissions
   * 
   * @return string 
   */
  public function getOriginalQuery() {
   
      $tqsql=getSqlSearchDoc($this->dbaccess,$this->dirid,$this->fromid,
			     $this->filters,$this->distinct,$this->latest,$this->trash);

      return $tqsql[0];
  }
  /**
   * count results
   * ::search must be call before
   *
   * @return int 
   * 
   */
  public function count() {
    if ($this->count==-1) {
      if ($this->searchmode=="ITEM") {
	$this->count=countDocs($this->result);
      } else {
	$this->count=count($this->result);
      }
    }
    return $this->count;
  }  
  /* reset results to use another search
   *
   * @return void
   * 
   */
  public function reset() {
    $this->result=false;
  }
  /**
   * send search
   *
   * @return array of documents
   * 
   */
  public function search() {
      if ($this->fromid) {
          if (! is_numeric($this->fromid))  {
              $fromid=getFamIdFromName($this->dbaccess,$this->fromid);     
          } else {
              if ($this->fromid != -1) {
                  // test if it is a family
                  if ($this->fromid < -1) {
                      $this->only=true;
                  }
                  $err=simpleQuery($this->dbaccess,sprintf("select doctype from docfam where id=%d",abs($this->fromid)),$doctype,true,true);
                  if ($doctype!='C') $fromid=0;
                  else $fromid=$this->fromid;
              } else $fromid=$this->fromid;
          }
          if ($fromid == 0) {
              $this->debuginfo["error"]=sprintf("%s is not a family",$this->fromid);
              if ($this->mode=="ITEM") return null;
              else return array();
          }
          if ($this->only) $this->fromid=-(abs($fromid));
          else $this->fromid=$fromid;          
      }
    if ($this->recursiveSearch && $this->dirid) {
        $tmps=createTmpDoc($this->dbaccess,"SEARCH");
        $tmps->setValue("se_idfld",$this->dirid);
        $tmps->setValue("se_latest","yes");
        $err=$tmps->add();
        if ($err=="") {
            $tmps->addQuery($tmps->getQuery()); // compute internal sql query
            $this->dirid=$tmps->id;
        }
    }
    $this->index=0;
    $this->searchmode=$this->mode;
    if ($this->mode=="ITEM") {
      // change search mode because ITEM mode not supported for Specailized searches
      $fld = new_Doc($this->dbaccess, $this->dirid);
      if ($fld->fromid == getFamIdFromName($this->dbaccess,"SSEARCH")) $this->searchmode="TABLE";      
    }
    if ($this->debug) $debuginfo=array();
    else $debuginfo=null;
    $this->result = getChildDoc($this->dbaccess, 
				$this->dirid,
				$this->start,
				$this->slice, $this->filters,$this->userid,$this->searchmode,
				$this->fromid,$this->distinct,$this->orderby, $this->latest, $this->trash,$debuginfo,$this->folderRecursiveLevel);
    if ($this->searchmode=="TABLE") $this->count=count($this->result); // memo cause array is unset by shift
    $this->debuginfo=$debuginfo;
    if (($this->searchmode=="TABLE") && ($this->mode=="ITEM")) $this->mode="TABLEITEM";

    return $this->result;
  }
  /**
   * 
   */
  public function searchError() {
      return ($this->debuginfo["error"]);
  }
  /**
   * 
   */
  public function getError() {
      if ($this->debuginfo) return $this->debuginfo["error"];
      return "";
  }

  /**
   * do the search in debug mode, you can after the search get infrrmation with getDebugIndo()
   * @param boolean $debug set to true search in debug mode
   * @return void
   */
  public function setDebugMode($debug=true) {
    $this->debug=$debug;
  }
  
 /**
   * set recursive mode for folder searches
   *
   * @return void
   */
  public function setRecursiveSearch($b=true) {
    $this->recursiveSearch=$b;
  }
  /**
   * return debug info if debug mode enabled
   *
   * @return array of info
   */
  public function getDebugInfo() {
    return $this->debuginfo;
  }
  /**
   * can, be use in 
   * ::search must be call before
   *
   * @return Doc or null if this is the end
   */
  public function nextDoc() {
    if ($this->mode=="ITEM") {
      return getNextDoc($this->dbaccess,$this->result);
    } elseif ($this->mode=="TABLEITEM") {
      $t=array_shift($this->result);
      if (! is_array($t)) return false;
      return getDocObject($this->dbaccess,$t);
    } else return array_shift($this->result);
     
  }  

  /**
   * add a condition in filters
   * @param string $filter the filter string
   * @param string $args arguments of the filter string (arguments are escaped to avoid sql injection)
   * @return void
   */
  public function addFilter($filter) {
      
    if ($filter != "") {
        $args=func_get_args();
        if (count($args) > 1) {
            $fs[0]=$args[0];
            for ($i=1;$i<count($args);$i++) {
                $fs[]=pg_escape_string($args[$i]);
            }
            $filter=call_user_func_array("sprintf", $fs);
        }
        $this->filters[]=$filter;
    }
  }  

  static function sqlcond($values, $column, $integer=false) {
    $sql_cond="true";
  if (count($values) > 0) {
      if ($integer) { // for integer type 
	$sql_cond = "$column in (";      
	$sql_cond .= implode(",",$values);
	$sql_cond .= ")";
      } else {// for text type 
          foreach ($values as &$v) $v=pg_escape_string($v);
	$sql_cond = "$column in ('";      
	$sql_cond .= implode("','",$values);
	$sql_cond .= "')";
      }
    }

  return $sql_cond;
}

  /**
   * add a condition in filters
   *
   * @return void
   */
  public function noViewControl() {
    $this->userid=1;
  }
  /**
   * add a slice
   * @param string $s the slice number or "ALL" if unlimied
   * @return void
   */
  public function setSlice($s) {
    $this->slice=parseInt($s);
  }
  /**
   * the return of ::search will be array of document's object
   *
   * @return void
   */
  public function setObjectReturn($returnobject=true) {
  	if ($returnobject) $this->mode="ITEM";
  	else $this->mode="TABLE";
  }
  /**
   * the return of ::search will be array of values
   * @deprecated
   * @return void
   */
  public function setValueReturn() {
    $this->mode="TABLE";
  }


}


?>