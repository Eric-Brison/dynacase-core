<?php
// ---------------------------------------------------------------------------
// anakeen 2000 - Yannick Le Briquer
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
//  $Id: Class.QueryDb.php,v 1.3 2002/02/18 10:55:16 eric Exp $
//  $Log: Class.QueryDb.php,v $
//  Revision 1.3  2002/02/18 10:55:16  eric
//  modif id_fields de objectcontrol : cause pas unique
//
//  Revision 1.2  2002/01/25 14:31:37  eric
//  gestion de cache objet - variable de session
//
//  Revision 1.1  2002/01/08 12:41:34  eric
//  first
//
//  Revision 1.5  2001/07/05 10:37:22  eric
//  ajout fonction AggQuery au niveau de querydb
//
//  Revision 1.4  2001/06/14 14:32:30  eric
//  correction parenthesage condition SQL sur SupClause
//
//  Revision 1.3  2000/10/24 17:44:55  yannick
//  Ajout du download
//
//  Revision 1.2  2000/10/13 15:53:36  yannick
//  Erreur Layout
//
//  Revision 1.1.1.1  2000/10/05 17:29:10  yannick
//  Importation
//
//  Revision 1.17  2000/09/15 07:48:23  marianne
//  Modif libelle N'est pad Vide
//
//  Revision 1.16  2000/09/15 07:47:27  marianne
//  Correction bug sur 'null' et 'not null'
//
//  Revision 1.15  2000/09/04 14:50:24  marianne
//  Ajout booleen dans GenPage pour ne pas afficher le filtre
//
//  Revision 1.14  2000/09/01 12:37:45  marianne
//  suppression d'un /input en trop et modif classe Total
//
//  Revision 1.13  2000/09/01 12:35:58  marianne
//  celspacing=>cellspacing
//
//  Revision 1.12  2000/08/03 14:11:55  marianne
//  initialisation de dtart et slice
//
//  Revision 1.11  2000/07/29 08:39:10  marc
//  Mise en forme formulaire de recherche
//
//  Revision 1.10  2000/07/09 08:00:55  yannick
//  Mise au point V2.0
//
//  Revision 1.9  2000/07/07 10:11:08  yannick
//  Mise au point
//
//  Revision 1.8  2000/07/03 13:30:35  yannick
//  Ajout Last Query
//
//  Revision 1.7  2000/06/30 12:45:46  yannick
//  Retourne faux si le DbId n'existe pas
//
//  Revision 1.6  2000/06/16 16:04:36  yannick
//  mise au point
//
//  Revision 1.5  2000/06/07 14:33:54  yannick
//  RAZ num page sur changement de lettre
//
//  Revision 1.4  2000/06/07 14:17:55  yannick
//  QueryObj
//
//  Revision 1.3  2000/06/05 16:13:55  yannick
//  Fonction tournepage OK
//
//  Revision 1.2  2000/06/05 13:58:27  yannick
//  Mise au point
//
//  Revision 1.1  2000/05/30 15:03:32  yannick
//  Nouveau
//
//  Revision 1.4  2000/05/30 09:33:50  xavier
//  mise à jour du 05 30
//
//  Revision 1.1  2000/05/19 09:56:15  admin
//  init
//
// ---------------------------------------------------------------------------
// This class is designed to perform query constructs on objects
// It returns a table of objects that match the query
//
// It gives the HTML/JScript element for the gui
// and it gives the result of the query
$CLASS_QUERYDB_PHP = '$Id: Class.QueryDb.php,v 1.3 2002/02/18 10:55:16 eric Exp $';

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
      if ($this->basic_elem->cached) print "BIG ERROR";
      $this->dbaccess = $dbaccess;
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
    if ($this->basic_elem->isCacheble) {
      $ocache = new CacheQuery();
      
      $ocache->cacheclass=strtolower($this->class);
	if ($ocache->GetCache($this->cacheId())) {	  
	  $this->list = $ocache->list;
	  $this->nb = $ocache->nb;

	  return $this->list;
	}
    }



      $res = $this->basic_elem->exec_query($query);
    //	print "$query $res_type $p_query<BR>\n";
      if ($res != "") {
         return($res);
      }

      $this->nb = $this->basic_elem->numrows();    
      
      if ($this->nb ==0) {
	if ($this->basic_elem->isCacheble) {
	  $ocache = new CacheQuery($this);

	  $ocache->SetCache($this->cacheId()); // set to the querydb cache 
	}
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
      

      if ($this->basic_elem->isCacheble)
	{
	  $ocache = new CacheQuery($this);
	  $ocache->SetCache($this->cacheId()); // set to the querydb cache 
	}
      return($this->list);
    }
      

function cacheId() {
  $hsql = bin2hex(mhash(MHASH_MD5,
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
}

Class CacheQuery extends Cache {
  var $list=array();
  var $nb=0;

  function CacheQuery($queryobj=NULL) {
    if ($queryobj != NULL) {
      $this->list= $queryobj->list;
      $this->nb = $queryobj->nb;
      $this->cacheclass=strtolower($queryobj->class);
    }
  }
}
?>
