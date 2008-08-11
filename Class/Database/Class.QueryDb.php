<?php
/**
 * Query to Database
 *
 * @author Anakeen 2000 
 * @version $Id: Class.QueryDb.php,v 1.16 2008/08/11 10:03:29 eric Exp $
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


 private function initQuery($start=0,$slice=0,$p_query="",$onlycont=false) {
    if ($start=="") $start=0;
     if ($p_query=='') { 
      // select construct
      $select="";
      if (!$onlycont) {
	foreach($this->basic_elem->fields as $k=>$v) {
	  $select=$select." ".$this->basic_elem->dbtable.".".$v.",";
	}

	foreach($this->basic_elem->sup_fields as $k=>$v) {
	  $select=$select." ".$v.",";
	}
	$select=substr($select,0,strlen($select)-1);
      } else {
	$select='count(*)';
      }
      // from
      $from = $this->basic_elem->dbtable;
      foreach($this->basic_elem->sup_tables as $k=>$v) {
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
      if (($this->order_by != "")&&(!$onlycont)) {
        $query = $query." order by ".$this->order_by;
        if (isset($this->desc) && ($this->desc == "up")) {
          $query = $query." desc";
        }
      }
      if ($slice > 0) $query.= " limit $slice";   
      if ($start > 0) $query.= " offset $start";     
      $query.=';';

    } else {
      $query=$p_query;
    }

    $this->slice=$slice;
    $this->start=$start;

    $this->LastQuery=$query;
    return $query;
 }


 /**
  * Perform the query : the result can be a table or a list of objects
  * depending on the third arg. 
  *   the third ARG should be :
  *        LIST  : means a table of objects
  *        LISTC : means a table of completed objects
  *        TABLE : means a table of table fields
  *        ITEM  : means a ressource to step by step use table field rows
  */
 function Query($start=0,$slice=0,$res_type="LIST",$p_query="")  {
      
   $query=$this->initQuery($start,$slice,$p_query);
   $this->res_type=$res_type;	
   $err = $this->basic_elem->exec_query($query);
   //	print "$query $res_type $p_query<BR>\n";
   if ($err != "") return($err);      

   $this->nb = $this->basic_elem->numrows();    
      
   if ($this->nb ==0) {
     return FALSE;
   }
   if ($res_type == "ITEM") {
     $this->cindex=0; // current index row
     return $this->basic_elem->res;
   }
   if ($start >= $this->nb) {$start=0;}
   if ($slice == 0) {$slice = $this->nb;}

   if (($start+$slice) >= $this->nb) {
     $end = $this->nb;
   } else {
     $end = $start + $slice;
   }
   if ($res_type == "TABLE") {
     $this->list=pg_fetch_all($this->basic_elem->res);
   } else {
     for ($c=0; $c<$this->nb;$c++) {        
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
   return($this->list);
 }
      
 /**
  * Perform the query : return only the count fo rows returned 
  */
  function Count($start=0,$slice=0)  {
      
    $query=$this->initQuery($start,$slice,"",true);
    $this->res_type="TABLE";
    $err = $this->basic_elem->exec_query($query);

    //	print "$query $res_type $p_query<BR>\n";
    if ($err != "") return($err);      

    $result = $this->basic_elem->fetch_array(0);
    return ($result["count"]);    
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
    unset($this->list);
  }
  function AddField($sqlattr, $resultname="") {
    if ($resultname == "")   $this->basic_elem->sup_fields[]=$sqlattr;
    else $this->basic_elem->sup_fields[]="$sqlattr as $resultname";
  }
}

?>
