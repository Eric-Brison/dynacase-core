<?php
/**
 * Query to Database
 *
 * @author Anakeen 2000 
 * @version $Id: Class.QueryDb.php,v 1.10 2005/09/12 16:31:59 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
 /**
 */


include_once('Class.Log.php');

Class QueryDb  {

var $nb=0;
var $LastQuery="";

var $table;

var $operators = array ( 
       "none"    => array( "lib"   => " --", 
                           "oper"  => "",
                           "param" => "NONE"),
       "begin"    => array( "lib"   => "Commence par", 
                           "oper"  => "like",
                           "param" => "SEMIPERCENT"),
       "like"    => array( "lib"   => "Contient", 
                           "oper"  => "like",
                           "param" => "PERCENT"),
       "nlike"   => array( "lib"   => "Ne Contient Pas",
                           "oper"  => "not like",
                           "param" => "PERCENT"),
       "="       => array( "lib"   => "Est égal à", 
                           "oper"  => "=",
                           "param" => "NORMAL"),
       "!="      => array( "lib"   => "Est différent de", 
                           "oper"  => "!=",
                           "param" => "NORMAL"),
       ">"       => array( "lib"   => "Est Supérieur à",
                           "oper"  => ">",
                           "param" => "NORMAL"),
       "<"       => array( "lib"   => "Est Inférieur à", 
                           "oper"  => "<",
                           "param" => "NORMAL"),
       "notn"    => array( "lib"   => "N'est pas Vide", 
                           "oper"  => "is not null",
                           "param" => "NONE"),
       "null"    => array( "lib"   => "Est Vide", 
                           "oper"  => "is null",
                           "param" => "NONE")
        );
var $casse = "NON";

var $criteria="";
var $order_by="";
 var $list= array();

function QueryDb ($dbaccess,$class) 
    {
      // 
      $this->log = new Log("","Query","$class");
      $this->basic_elem = new $class($dbaccess);
      $this->dbaccess = $this->basic_elem->dbaccess;
      $this->class = $class;

    }

// Perform the query : the result can be a table or a list of objects
// depending on the third arg. 
//   the third ARG should be :
//         LIST  : means a table of objects
//         LISTC : means a table of completed objects
//         TABLE : means a table of table fields

  function Query($start=0,$slice=0,$res_type="LIST",$p_query="")
  {
    if ($start=="") $start=0;
    
    if ($p_query=='') { 
      // select construct
      $select="";

      reset($this->basic_elem->fields);
      while (list($k,$v) = each($this->basic_elem->fields)) {
        $select=$select." ".$this->basic_elem->dbtable.".".$v.",";
      }

      reset($this->basic_elem->sup_fields);
      while (list($k,$v) = each($this->basic_elem->sup_fields)) {
        $select=$select." ".$v.",";
      }
      $select=substr($select,0,strlen($select)-1);

      // from
      $from = $this->basic_elem->dbtable;
      reset($this->basic_elem->sup_tables);
      while (list($k,$v) = each($this->basic_elem->sup_tables)) {
        $from = $from.",".$v;
      }

      
      $query="select {$select}
              from {$from} ";

 
      $nb_where=0;
      $where[$nb_where] = $this->CriteriaClause();
      if ($where[$nb_where] != "") { $nb_where++; }
      $where[$nb_where] = $this->AlphaClause();
      if ($where[$nb_where] != "") { $nb_where++; }
      $where[$nb_where] = $this->SupClause();
      if ($where[$nb_where] != "") { $nb_where++; }

      if ($nb_where > 0) {
        $i = 0;
        $query=$query.' where ';
        reset($where);
        while (list($k,$v)=each($where)) {
          if ($v != "") {
            if ($i == 0) {
              $query=$query.$v;
            } else {
              $query=$query.' AND '.$v;
            }
            $i++;
          }
        }
      }
        
         


      // Order by
      if ($this->order_by != "") {
        $query = $query." order by ".$this->order_by;
        if (isset($this->desc) && ($this->desc == "up")) {
          $query = $query." desc";
        }
      }
      $query=$query.';';
      $query=$query.';';
    } else {
      $query=$p_query;
    }

    $this->res_type=$res_type;
    $this->slice=$slice;
    $this->start=$start;
    $this->LastQuery=$query;

    // try cache query first
//     if ($this->basic_elem->isCacheble) {
//       $ocache = new CacheQuery();
      
//       $ocache->cacheclass=strtolower($this->class);
// 	if ($ocache->GetCache($this->cacheId())) {	  
// 	  $this->list = $ocache->list;
// 	  $this->nb = $ocache->nb;

// 	  return $this->list;
// 	}
//     }



      $res = $this->basic_elem->exec_query($query);
    //	print "$query $res_type $p_query<BR>\n";
      if ($res != "") {
         return($res);
      }

      $this->nb = $this->basic_elem->numrows();    
      
      if ($this->nb ==0) {
// 	if ($this->basic_elem->isCacheble) {
// 	  $ocache = new CacheQuery($this);

// 	  $ocache->SetCache($this->cacheId()); // set to the querydb cache 
// 	}
	return FALSE;
      }

      if ($start >= $this->nb) {$start=0;}
      if ($slice == 0) {$slice = $this->nb;}

      if (($start+$slice) >= $this->nb) {
        $end = $this->nb;
      } else {
        $end = $start + $slice;
      }
      for ($c=0; $c<$this->nb;$c++) {
        if (($c >= $start) && ($c<$end)) {
          $result = $this->basic_elem->fetch_array($c);
          if (($res_type == "LIST") || ($res_type == "LISTC")) {
             $this->list[$c] = new $this->class($this->dbaccess,"",$result,$this->basic_elem->dbid);
          } else {
             while (list($k,$v)=each( $result)) {
               $this->list[$c][$k]=$v;
             }
          }
        }
      }
      //      global ${$this->hsql};
      // print "record ".$this->hsql."#".count($this->list)."<BR>";
      

  //     if ($this->basic_elem->isCacheble)
// 	{
// 	  $ocache = new CacheQuery($this);
// 	  $ocache->SetCache($this->cacheId()); // set to the querydb cache 
// 	}
      return($this->list);
    }
      

function cacheId() {
  $hsql = bin2hex(mhash(MHASH_MD5,$this->dbaccess.
			$this->class.$this->res_type.$this->LastQuery.$this->start.'_'.$this->slice));

  
  return $hsql;
}


  function AffectCache($id) {  
    global $CacheObj;
    $CacheObj[$id]=$this->list;
  }
  function RestoreCache($id) {  
    global $CacheObj;
    $this->list=$CacheObj[$id];
  }

  function GetCachePart() {
    return "list";
  }
  function CriteriaClause() {
    $out = "";
    if (isset($this->criteria) && ($this->criteria != "") && ($this->operator != "none")) {
      if ($this->casse == "NON") {
        $out=$out." upper(".$this->criteria.") ". 
                      $this->operators[$this->operator]["oper"];
      } else {
        $out=$out.$this->criteria." ".
                      $this->operators[$this->operator]["oper"];
      }
      $string="";
      switch ($this->operators[$this->operator]["param"]) {
        case "NORMAL" :
          $string=" {$this->string}";
          break;
        case "PERCENT" :
          $string=" '%{$this->string}%'";
          break;
        case "SEMIPERCENT" :
          $string = " '{$this->string}%'";
      }
      if ( ($this->operator!='null') && ($this->operator!='notn')) {
        if ($this->casse == "NON") {
          $out .= " upper({$string})";
        } else {
          $out .= $string;
        }
      }
    }
    return($out);
  }
  
  function AlphaClause() {
  }



  function SupClause() {
    $out = "";
    if (sizeof($this->basic_elem->sup_where)>0) {
      reset($this->basic_elem->sup_where);
      $count=0;
      while(list($k,$v)=each($this->basic_elem->sup_where)) {
        if ($count>0) { 
          $out=$out." AND (".$v.")";
        } else {
          $out="(".$out." ".$v.")";
        }
        $count++;
      }
    }
    return($out);
  }

  function AddQuery($contraint) {
    $this->basic_elem->sup_where[]=$contraint;
  }
  function resetQuery() {
    $this->basic_elem->sup_where=array();
  }
  function AddField($sqlattr, $resultname="") {
    if ($resultname == "")   $this->basic_elem->sup_fields[]=$sqlattr;
    else $this->basic_elem->sup_fields[]="$sqlattr as $resultname";
  }
}

// Class CacheQuery extends Cache {
//   var $list=array();
//   var $nb=0;

//   function CacheQuery($queryobj=NULL) {
//     if ($queryobj != NULL) {
//       $this->list= $queryobj->list;
//       $this->nb = $queryobj->nb;
//       $this->cacheclass=strtolower($queryobj->class);
//     }
//   }
//}
?>
