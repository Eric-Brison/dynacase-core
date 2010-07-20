<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Lib.Dir.php,v 1.149 2008/11/13 16:46:48 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */


include_once('FDL/Class.Dir.php');
include_once('FDL/Class.DocSearch.php');
include_once('FDL/Class.DocRead.php');
include_once('FDL/Class.DocFam.php');

function getFirstDir($dbaccess) {
  // query to find first directories
    $qsql= "select id from only doc2  where  (doctype='D') order by id LIMIT 1;";
  
  
  
  $query = new QueryDb($dbaccess,"Doc");
  
  $tableq=$query->Query(0,0,"TABLE",$qsql);
  if ($query->nb > 0)
    {
      
      return $tableq[0]["id"];
    }
  
  
  return(0);
}


function getChildDir($dbaccess, $userid, $dirid, $notfldsearch=false, $restype="LIST") {
  // query to find child directories (no recursive - only in the specified folder)
    
    
    if (!($dirid > 0)) return array();   
  
  // search classid and appid to test privilege
    if ($notfldsearch) {
      // just folder no serach
      return  getChildDoc($dbaccess,$dirid,"0","ALL",array(),$userid,$restype,2,false,"title");
    } else {
      // with folder and searches
      
      return  array_merge(getChildDoc($dbaccess,$dirid,"0","ALL",array("doctype='D'"),$userid,$restype,2,false,"title"),
			  getChildDoc($dbaccess,$dirid,"0","ALL",array("doctype='S'"),$userid,$restype,5,false,"title"));
      
    }
      
}

function isSimpleFilter($sqlfilters) {
  if (! is_array($sqlfilters)) return true;
  static $props=false;

  if (! $props) {
    $d=new Doc();
    $props=$d->fields;
    $props=array_merge($props,$d->sup_fields);
    $props[]="fulltext";
    $props[]="svalues";
  }

  foreach ($sqlfilters as $k=>$v) {
    $tok=ltrim($v,"(");
    $tok=ltrim($tok," ");
    $tok = strtok($tok," !=~@");  
    //if ($tok == "fulltext") return true;
    if (($tok !== false) && ($tok !== "true") && ($tok !== "false") && (!in_array(ltrim($tok,"("),$props))) return false;
  }
  return true;
    

}

/**
 * compose query to serach document
 *
 * @param string $dbaccess database specification
 * @param array  $dirid the array of id or single id of folder where search document (0 => in all DB)
 * @param string $fromid for a specific familly (0 => all familly) (<0 strict familly)
 * @param array $sqlfilters array of sql filter
 * @param bool $distinct
 * @param bool $latest set false if search in all revised doc
 * @param string $trash (no|only|also) search in trash or not
 * @param bool $simplesearch set false if search is about specific attributes
*/
function getSqlSearchDoc($dbaccess, 
			 $dirid, 
			 $fromid, 
			 $sqlfilters=array(),
			 $distinct=false,// if want distinct without locked
			 $latest=true,// only latest document
			 $trash="",
			 $simplesearch=false,
			 $folderRecursiveLevel=2) {
  if (($fromid!="") && (! is_numeric($fromid))) $fromid=getFamIdFromName($dbaccess,$fromid);
  $table="doc";$only="";
  if ($trash=="only") $distinct=true;
  if ($fromid == -1) $table="docfam";
  elseif ($simplesearch) $table="docread";
  elseif ($fromid < 0) {
    $only="only" ;$fromid=-$fromid;
    $table="doc$fromid";
  } else {
    if ($fromid != 0) {
      if ( isSimpleFilter($sqlfilters) && (familyNeedDocread($dbaccess,$fromid))) {
	$table="docread";
	$fdoc=new_doc($dbaccess, $fromid);
	$sqlfilters[-4] = GetSqlCond(array_merge(array($fromid),array_keys($fdoc->GetChildFam())),"fromid",true);
      } else {
	$table="doc$fromid";
      }
    } elseif ($fromid == 0) {    
      if (isSimpleFilter($sqlfilters)) 	$table="docread";
    }
  }

  

  if ($distinct) {
    $selectfields =  "distinct on (initid) $table.*";
  } else {
    $selectfields =  "$table.*"; 
    $sqlfilters[-2] = "doctype != 'T'";
    ksort($sqlfilters);

  }

  $sqlcond="true";
  ksort($sqlfilters);
  if (count($sqlfilters)>0)    $sqlcond = " (".implode(") and (", $sqlfilters).")";


  if ($dirid == 0) {
    //-------------------------------------------
    // search in all Db
    //-------------------------------------------
      if (strpos(implode(",",$sqlfilters),"archiveid")===false)  $sqlfilters[-4] = "archiveid is null";
      
    if ($trash=="only") {
      $sqlfilters[-3] = "doctype = 'Z'";    
    } elseif ($trash=="also") ;
    else if (!$fromid) $sqlfilters[-3] = "doctype != 'Z'";

    if (($latest) && (($trash=="no")||(!$trash))) $sqlfilters[-1] = "locked != -1";
    ksort($sqlfilters);
    if (count($sqlfilters)>0)    $sqlcond = " (".implode(") and (", $sqlfilters).")";
    $qsql= "select $selectfields ".
      "from $only $table  ".
      "where  ".
      $sqlcond;
  } else {

    //-------------------------------------------
    // in a specific folder
    //-------------------------------------------

    
    if (! is_array($dirid))    $fld = new_Doc($dbaccess, $dirid);
    if ((is_array($dirid)) || ( $fld->defDoctype != 'S'))  {
        $hasFilters=false;
        if ($fld && method_exists($fld,"getSpecificFilters")) {
            $specFilters=$fld->getSpecificFilters();
            if (is_array($specFilters) && (count($specFilters) > 0)) {
                $sqlfilters=array_merge($sqlfilters,$specFilters);
                $hasFilters=true;
            }
        }
      if (strpos(implode(",",$sqlfilters),"archiveid")===false)  $sqlfilters[-4] = "archiveid is null";
        

      //if ($fld->getValue("se_trash")!="yes") $sqlfilters[-3] = "doctype != 'Z'";
    
      if ($trash=="only") $sqlfilters[-1] = "locked = -1";
      elseif ($latest) $sqlfilters[-1] = "locked != -1";
      ksort($sqlfilters);
      if (count($sqlfilters)>0)    $sqlcond = " (".implode(") and (", $sqlfilters).")";
      
      if (is_array($dirid)) {
	$sqlfld=GetSqlCond($dirid,"dirid",true);
	$qsql= "select $selectfields ".
	  "from (select childid from fld where $sqlfld) as fld2 inner join $table on (initid=childid)  ".
	  "where  $sqlcond ";
      } else {
	$sqlfld="dirid=$dirid and qtype='S'";
	if ($fromid==2) $sqlfld.= " and doctype='D'";
	if ($fromid==5) $sqlfld.= " and doctype='S'";
	if ($hasFilters) {
	     $sqlcond = " (".implode(") and (", $sqlfilters).")";
             $qsql= "select $selectfields from $only $table where $sqlcond "; 
	} else {
	    $q = new QueryDb($dbaccess,"QueryDir");
	    $q->AddQuery($sqlfld);
	    $tfld=$q->Query(0,0,"TABLE");
	    if ($q->nb > 0) {
	        foreach ($tfld as $onefld) {
	            $tfldid[]=$onefld["childid"];
	        }
	        if (count($tfldid) > 1000) {
	            $qsql= "select $selectfields ".
	      "from $table where initid in (select childid from fld where $sqlfld)  ".
	      "and  $sqlcond ";
	        } else {
	            $sfldids=implode(",",$tfldid);
	            if ($table=="docread") {
	                /*$qsql= "select $selectfields ".
	                 "from $table where initid in (select childid from fld where $sqlfld)  ".
	                 "and  $sqlcond ";	*/
	                $qsql= "select $selectfields ".
	      "from $table where initid in ($sfldids)  ".
	      "and  $sqlcond ";	
	            } else {
	                /*$qsql= "select $selectfields ".
	                 "from (select childid from fld where $sqlfld) as fld2 inner join $table on (initid=childid)  ".
	                 "where  $sqlcond ";*/
	                $qsql= "select $selectfields ".
	      "from $only $table where initid in ($sfldids)  ".
	      "and  $sqlcond ";	
	            }
	        }
	    }
	}
	//$qsql= "select $selectfields "."from $table where $dirid = any(fldrels) and  "."  $sqlcond ";
      }      
    } else {
      //-------------------------------------------
      // search familly
      //-------------------------------------------
      $docsearch = new QueryDb($dbaccess,"QueryDir");
      $docsearch ->AddQuery("dirid=$dirid");
      $docsearch ->AddQuery("qtype = 'M'");
      $ldocsearch = $docsearch ->Query(0,0,"TABLE");      
      
      // for the moment only one query search
      if (($docsearch ->nb) > 0) {
	switch ($ldocsearch[0]["qtype"]) {
	 
	case "M": // complex query
	    
	  // $sqlM=$ldocsearch[0]["query"];

	  $fld=new_Doc($dbaccess,$dirid);
	  if ($trash) $fld->setValue("se_trash",$trash);
	  else $trash=$fld->getValue("se_trash");
	  $fld->folderRecursiveLevel=$folderRecursiveLevel;
	  $tsqlM=$fld->getQuery();	
	  foreach ($tsqlM as $sqlM) {
	    if ($sqlM != false) {
	      if (! preg_match("/doctype[ ]*=[ ]*'Z'/",$sqlM,$reg)) {
		if (($trash != "also")&&($trash != "only"))  $sqlfilters[-3] = "doctype != 'Z'"; // no zombie if no trash
		ksort($sqlfilters);
		foreach ($sqlfilters as $kf=>$sf) { // suppress doubles
		  if (strstr ($sqlM , $sf )) {		   
		    unset($sqlfilters[$kf]);
		  }
		}
		if (count($sqlfilters)>0)    $sqlcond = " (".implode(") and (", $sqlfilters).")";
		else $sqlcond="";
	      }
	      if ($fromid > 0) $sqlM=str_replace("from doc ","from $only $table ",$sqlM);
	      if ($sqlcond)     $qsql[]= $sqlM ." and " . $sqlcond;
	      else $qsql[]= $sqlM;
	    }
	  }
	  break;
	}
      } else {
	return false; // no query avalaible
      }
    }

  }
  if (is_array($qsql)) return $qsql;
  return array($qsql);
}
/**
 * get possibles errors before request of getChildDoc
 * @param string $dbaccess database specification
 * @param array  $dirid the array of id or single id of folder where search document 
 * @return array error codes
 */
function getChildDocError($dbaccess, 
			 $dirid) { // in a specific folder (0 => in all DB)

  $terr=array();


  if ($dirid == 0) {
    //-------------------------------------------
    // search in all Db
    //-------------------------------------------
   
  } else {

    //-------------------------------------------
    // in a specific folder
    //-------------------------------------------

    if (! is_array($dirid))    $fld = new_Doc($dbaccess, $dirid);

    if ($fld->getValue("se_phpfunc") != "") return $terr;

    if ((is_array($dirid)) || ( $fld->defDoctype != 'S'))  {


    } else {
      //-------------------------------------------
      // search familly
      //-------------------------------------------
      $docsearch = new QueryDb($dbaccess,"QueryDir");
      $err=$docsearch ->AddQuery("dirid=$dirid");
      if ($err!="") {
	global $action;
	$action->AddWarningMsg($err);
      }
      $docsearch ->AddQuery("qtype = 'M'");
      $ldocsearch = $docsearch ->Query(0,0,"TABLE");
      
      
      
      // for the moment only one query search
      if (($docsearch ->nb) > 0) {
	switch ($ldocsearch[0]["qtype"]) {
	 
	case "M": // complex query
	    

	  $fld=new_Doc($dbaccess,$dirid);
	  $tsqlM=$fld->getQuery();
	  foreach ($tsqlM as $sqlM) {

	    if ($sqlM == false) $terr[$dirid]=_("uncomplete request"); // uncomplete
	    
	  }
	  break;
	}
      } else {
	$terr[$dirid]=_("request not found"); // not found
      }
    }

  }
  return $terr;
}
/**
 * return array of documents
 *
 * @param string $dbaccess database specification
 * @param array  $dirid the array of id or single id of folder where search document 
 * @param string $start the start index 
 * @param string $slice the maximum number of returned document
 * @param array $sqlfilters array of sql filter
 * @param int $userid the current user id
 * @param string $qtype LIST|TABLE the kind of return : list of object or list or values array
 * @param int $fromid identificator of family document
 * @param bool $distinct if true all revision of the document are returned else only latest
 * @param string $orderby field order
 * @param bool $latest if true only latest else all revision
 * @param string $trash (no|only|also) search in trash or not
 * @return array/Doc
 */
function getChildDoc($dbaccess, 
		     $dirid, 
		     $start="0", $slice="ALL", $sqlfilters=array(), 
		     $userid=1, 
		     $qtype="LIST", $fromid="",$distinct=false, $orderby="title",$latest=true,$trash="",&$debug=null,$folderRecursiveLevel=2) {
  
  global $action;

  // query to find child documents          
  if (($fromid!="") && (! is_numeric($fromid))) $fromid=getFamIdFromName($dbaccess,$fromid);
  if ($fromid==0) $fromid="";
  if (($fromid=="") && ($dirid!=0)&&($qtype=="TABLE")) {

    $fld = new_Doc($dbaccess, $dirid);

    // In case of full text search, execute specific code
    if ($fld->fromid == getFamIdFromName($dbaccess,"FTEXTSEARCH")) 
      return $fld->GetFullTextResultDocs($dbaccess, $dirid, $start, $slice, $sqlfilters, 
					 $userid, $qtype, $fromid, $distinct, $orderby, $latest);


    if ($fld->fromid == getFamIdFromName($dbaccess,"SSEARCH")) 
      return $fld->getDocList($start, $slice, $qtype,$userid);
    
    if ($fld->defDoctype != 'S' ) {
      // try optimize containt of folder
      if (!$fld->hasSpecificFilters() ) {
          $td=getFldDoc($dbaccess,$dirid,$sqlfilters);
          if (is_array($td)) return $td;
      }
    } else {
      if ($fld->getValue("se_famid")) $fromid=$fld->getValue("se_famid");
    }
  } elseif ($dirid!=0) {
    $fld = new_Doc($dbaccess, $dirid);
    if (( $fld->defDoctype == 'S') && ($fld->getValue("se_famid"))) $fromid=$fld->getValue("se_famid");
  }
  if ($trash=="only") $distinct=true;
 
  //   xdebug_var_dump(xdebug_get_function_stack());
 
  $tqsql=getSqlSearchDoc($dbaccess,$dirid,$fromid,$sqlfilters,$distinct,$latest,$trash,false,$folderRecursiveLevel);

  $tretdocs=array();
  if ($tqsql) {
    foreach ($tqsql as $k=>&$qsql) {
      if ($qsql == false) unset($tqsql[$k]);
    }
    $isgroup=(count($tqsql) > 1);
    foreach ($tqsql as &$qsql) {
	if ($fromid!=-1) { // not families
	  if ($fromid!=0) {
	    $fdoc=createDoc($dbaccess,abs($fromid),false,false);	    
	    if (preg_match("/from\s+docread/",$qsql) || $isgroup) $fdoc=new DocRead($dbaccess);
	  } else $fdoc=new DocRead($dbaccess);
	  $sqlfields=implode(", ",array_merge($fdoc->fields,$fdoc->sup_fields));
	  if ($userid > 1) { // control view privilege
	    $qsql .= " and (profid <= 0 or hasviewprivilege($userid, profid))";
	    // and get permission
	    $qsql = str_replace("* from ","$sqlfields ,getuperm($userid,profid) as uperm from ",$qsql);
	  } else {
	  
	    $qsql = str_replace("* from ","$sqlfields  from ",$qsql);
	  }

	  if ((!$distinct) && strstr($qsql,"distinct")) $distinct=true;
	  if ($start == "") $start="0";
	  if ($distinct) {
	      $qsql .= " ORDER BY initid, id desc";
	      if (! $isgroup) $qsql .= " LIMIT $slice OFFSET $start";
	  }
	  else  {
	    if (($fromid == "") && $orderby=="") $orderby="title";
	    elseif (substr($qsql,0,12)  == "select doc.*") $orderby="title";
	    if ($orderby==""  && (! $isgroup) ) $qsql .= "  LIMIT $slice OFFSET $start;";
	    else {
	      if ($orderby[0]=='-') $orderby=substr($orderby,1)." desc";
	       if (! $isgroup) $qsql .= " ORDER BY $orderby LIMIT $slice OFFSET $start;";
	    }
	  }   
	} else {
	  // families
	  if ($userid > 1) { // control view privilege
	    $qsql .= " and (profid <= 0 or hasviewprivilege($userid, profid))";
	    // and get permission
	    $qsql = str_replace("* from ","* ,getuperm($userid,profid) as uperm from ",$qsql);
	  } 
	  $qsql .= " ORDER BY $orderby LIMIT $slice OFFSET $start;";
	}
	if ($fromid != "") {
	  if ($fromid == -1) {
	    include_once "FDL$GEN/Class.DocFam.php";
	    $fromid="Fam";
	  } else {
	    $fromid=abs($fromid);
	    if ($fromid > 0) {
	      $GEN=getGen($dbaccess);
	      include_once "FDL$GEN/Class.Doc$fromid.php";
	    }
	  }
	}
      }
     
      if (count($tqsql) > 0) {
          if (count($tqsql) == 1) {
              $query = new QueryDb($dbaccess,"Doc$fromid");
              $mb=microtime();
              $tableq=$query->Query(0,0,$qtype,$tqsql[0]);
          } else {            
              $usql=implode($tqsql," union ");
              if ($orderby) $usql .= " ORDER BY $orderby LIMIT $slice OFFSET $start;";
              else $usql .= " LIMIT $slice OFFSET $start;";
             
              $query = new QueryDb($dbaccess,"Doc");
              $mb=microtime();
              $tableq=$query->Query(0,0,$qtype,$usql);
          }

          if ($query->nb > 0) {
              if ($qtype=="ITEM") {
                  $tretdocs[]=$tableq;
              } else $tretdocs=array_merge($tretdocs,$tableq);
          }
          //		 print "<HR><br><div style=\"border:red 1px inset;background-color:lightyellow;color:black\">".$query->LastQuery; print " - $qtype<B> [".$query->nb.']'.sprintf("%.03fs",microtime_diff(microtime(),$mb))."</B><b style='color:red'>".$query->basic_elem->msg_err."</b></div>";
          if ($query->basic_elem->msg_err!="") {
              addLogMsg($query->basic_elem->msg_err,200);
              addLogMsg(array("query"=>$query->LastQuery,"err"=>$query->basic_elem->msg_err));
              // print_r2(array_pop(debug_backtrace()));
          }
          if ($debug!==null) {
              $debug["count"]=$query->nb;
              $debug["query"]=$query->LastQuery;
              $debug["error"]=$query->basic_elem->msg_err;
              $debug["delay"]=sprintf("%.03fs",microtime_diff(microtime(),$mb));
              addLogMsg($query->basic_elem->msg_err,200);
              addLogMsg($debug);
          } elseif ($query->basic_elem->msg_err!="") {
              $debug["query"]=$query->LastQuery;
              $debug["error"]=$query->basic_elem->msg_err;
          }
      }

  }
    

  
  
  reset($tretdocs);
  
  return($tretdocs);
}





/** 
 * optimization for getChildDoc
 * @param int $limit if -1 no limit
 * @param bool $reallylimit if false don't return false if limit is reached
 */
function getFldDoc($dbaccess,$dirid,$sqlfilters=array(),$limit=100,$reallylimit=true) {
 
  if (is_array($dirid)) {
    $sqlfld=GetSqlCond($dirid,"dirid",true);
  } else {
    $sqlfld = "fld.dirid=$dirid";
  }
  
  $mc=microtime();
  
  $q = new QueryDb($dbaccess,"QueryDir");
  $q->AddQuery($sqlfld);
  $q->AddQuery("qtype='S'");

  if ($limit > 0) {
      $tfld=$q->Query(0,$limit+1,"TABLE");
      // use always this mode because is more quickly
      if (($reallylimit) && ($q->nb > $limit)) return false;
  } else {
      $tfld=$q->Query(0,$limit+1,"TABLE");
  }
  $t=array();
  if ($q->nb > 0) {
      foreach ($tfld as $k=>$v) {                     
          $t[$v["childid"]]=getLatestTDoc($dbaccess,$v["childid"],$sqlfilters,($v["doctype"]=="C")?-1:$v["fromid"]);

          if ($t[$v["childid"]] == false) unset($t[$v["childid"]]);
          elseif ($t[$v["childid"]]["archiveid"]) unset($t[$v["childid"]]);
          else {
              if (($t[$v["childid"]]["uperm"] & (1 << POS_VIEW)) == 0) { // control view
                  unset($t[$v["childid"]]);
              }
          }  
      }
  }
  uasort($t,"sortbytitle");
  //  print "<HR><br><div style=\"border:red 1px inset;background-color:orange;color:black\">"; print " - getFldDoc $dirid [nbdoc:".count($tfld)."]<B>".microtime_diff(microtime(),$mc)."</B></div>";
  return $t;
}
function sortbytitle($td1,$td2) {
  return strcasecmp($td1["title"],$td2["title"]);
}
/** 
 * optimization for getChildDoc in case of grouped searches
 * not used
 */
function getMSearchDoc($dbaccess,$dirid,
		       $start="0", $slice="ALL",$sqlfilters=array(), 
		       $userid=1, 
		       $qtype="LIST", $fromid="",$distinct=false, $orderby="title",$latest=true) {
 
  $sdoc= new_Doc($dbaccess, $dirid);

  $tidsearch=$sdoc->getTValue("SEG_IDCOND");
  $tdoc=array();
  foreach ($tidsearch as $k=>$v) {
    $tdoc=array_merge(getChildDoc($dbaccess,$v,
				  $start, $slice,$sqlfilters, 
				  $userid, 
				  $qtype, $fromid,$distinct, $orderby,$latest),
		      $tdoc);
  }
  return $tdoc;
    
}







/**
 * return array of documents
 *
 * based on {@see getChildDoc()} it return document with enum attribute condition
 * return document which the $aid attribute has the value $kid 
 *
 * @param string $dbaccess database specification
 * @param string $famname internal name of family document
 * @param string $aid the attribute identificator
 * @param string $kid the key for enum value to search
 * @param string $name additionnal filter on the title
 * @param array $sqlfilters array of sql filter
 * @param int $limit max document returned
 * @param string $qtype LIST|TABLE the kind of return : list of object or list or values array
 * @param int $userid the current user id
 * @return array/Doc
 */
function getKindDoc($dbaccess, 
		    $famname,
		    $aid, 
		    $kid, 
		    $name="", // filter on title
		    $sqlfilter=array(), 
		    $limit=100,
		    $qtype="TABLE",
		    $userid=0) {

  global $action;

  if ($userid==0) $userid=$action->user->id;
  
  $famid= getFamIdFromName($dbaccess,$famname);
  $fdoc = new_Doc($dbaccess, $famid);

  // searches for all fathers kind
  $a = $fdoc->getAttribute($aid);
  if ($a) {
    $tkids=array();;
    $enum = $a->getEnum();
    while (list($k, $v) = each($enum)) {
      if (in_array($kid,explode(".",$k))) {
	$tkids[] = substr($k,strrpos(".".$k,'.'));
      }
    }
 
    if ($a->type == "enum") {
      if ($a->repeat) {
	$sqlfilter[] = "in_textlist($aid,'".
	  implode("') or in_textlist($aid,'",$tkids)."')";
      } else {
	$sqlfilter[] = "$aid='".
	  implode("' or $aid='",$tkids)."'";    
      }
    }
  }

  if ($name != "")  $sqlfilter[]="title ~* '$name'";

  return getChildDoc($dbaccess, 
		     0,0,$limit,$sqlfilter ,$userid,"TABLE",
		     getFamIdFromName($dbaccess,$famname),false,"title");
}
function sqlval2array($sqlvalue) {
  // return values in comprehensive structure
    
    $rt = array();
  if ($sqlvalue != "") {
    $vals = explode("][", substr($sqlvalue,1,-1));
    while(list($k1,$v1) = each($vals)) {
      list($aname,$aval) = explode(";;", $v1);
      $rt[$aname]=$aval;
    }
    
  }
  return $rt;
}


/**
 * query to find child directories (no recursive - only in the specified folder)
 * @param string $dbaccess database specification
 * @param int  $dirid the id of folder where search subfolders 
 */
function getChildDirId($dbaccess, $dirid) {       
  $tableid = array();
  
  $tdir=getChildDoc($dbaccess,$dirid,"0","ALL",array(),$userid,"TABLE",2);

  while(list($k,$v) = each($tdir)) {
    $tableid[] = $v["id"];
  }
  
  
  return($tableid);
}
// --------------------------------------------------------------------

/**
 * return array of subfolder id until sublevel 2 (RECURSIVE)
 *
 * @param string $dbaccess database specification
 * @param int  $dirid the id of folder where search subfolders 
 * @param array $rchilds use for recursion (dont't set anything)
 * @param int  $level use for recursion (dont't set anything)
 * @param int  $levelmax max recursion level (default 2)
 * @return array/int
 * @see getChildDir()
 */
function getRChildDirId($dbaccess, $dirid, $rchilds=array(), $level=0,$levelmax=2) { 
  global $action;

  
  if ($level > $levelmax) {
    // $action->addWarningMsg("getRChildDirId::Max dir deep [$level levels] reached");
    return ($rchilds);
  }

  $rchilds[] = $dirid;

  $childs = getChildDirId($dbaccess, $dirid, true);

  if (count($childs) > 0) {
    while(list($k,$v) = each($childs)) {
      if (!in_array($v,$rchilds)) {
	$t = array_merge($rchilds, getRChildDirId($dbaccess,$v,$rchilds,$level+1,$levelmax));
	if (is_array($t)) $rchilds = array_values(array_unique($t));
      }
    }
  } 
  return($rchilds);
}

function isInDir($dbaccess, $dirid, $docid) {
  // return true id docid is in dirid
    
    
    $query = new QueryDb($dbaccess,"QueryDir");
  $query -> AddQuery("dirid=".$dirid);
  $query -> AddQuery("childid=".$docid);
  
  $query->Query(0,0,"TABLE");
  return ($query->nb > 0);
}

/** 
 * return true if dirid has one or more child dir
 * @param string $dbaccess database specification
 * @param int $dirid folder id
 * @return bool
 */
function hasChildFld($dbaccess, $dirid,$issearch=false) {
    
  if ($issearch) {
    $query = new QueryDb($dbaccess,"QueryDir");  
    $query->AddQuery("qtype='M'");
    $query->AddQuery("dirid=$dirid");
    $list=$query->Query(0,1,"TABLE");

    if ($list) {
      $oquery=$list[0]["query"];
      if (preg_match("/select (.+) from (.+)/",$oquery,$reg)) {
	if (preg_match("/doctype( *)=/",$reg[2],$treg)) return false; // do not test if special doctype searches
	$nq=sprintf("select count(%s) from %s and ((doctype='D')or(doctype='S')) limit 1",$reg[1],$reg[2]);
	$count=$query->Query(0,0,"TABLE",$nq);
	if (($query->nb > 0) && (is_array($count)) && ($count[0]["count"] > 0)) return true;
      }
      
    }
  } else {
    $qfld = new QueryDb($dbaccess,"QueryDir");  
    $qfld->AddQuery("qtype='S'");
    $qfld->AddQuery("fld.dirid=$dirid");
    $qfld->AddQuery("doctype='D' or doctype='S'");
    $lq=$qfld->Query(0,1,"TABLE");

    $qids=array();
    if (! is_array($lq)) return false;
    return ($qfld->nb > 0);
    
  }
  return false;
}




/**
 * return families with the same usefor
 * @param string $dbaccess database specification
 * @param int $userid identificator of the user
 * @param int $classid the reference family to find by usefor (if 0 all families) can be an array of id
 * @param string $qtype  [TABLE|LIST] use TABLE if you can because LIST cost too many memory
 * @return array the families
 */
function GetClassesDoc($dbaccess,$userid,$classid=0,$qtype="LIST")
     // --------------------------------------------------------------------
{
  $query = new QueryDb($dbaccess,"DocFam");
  
  $query->AddQuery("doctype='C'");
  
  if (is_array($classid)) {
    foreach ($classid as $fid) {
      $tcdoc = getTDoc($dbaccess, $fid);
      $use[]=$tcdoc["usefor"];          
    }
    $query->AddQuery(GetSqlCond($use,"usefor"));  
  } else  if ($classid >0 ) {
    $cdoc = new DocFam($dbaccess, $classid);
    $query->AddQuery("usefor = '".$cdoc->usefor."'");
  }
  
  
  if ($userid > 1) $query->AddQuery("hasviewprivilege(".$userid.",docfam.profid)");

  if ($qtype=="TABLE") {
    $t=$query->Query(0,0,$qtype);
    foreach ($t as $k=>$v) {
      $t[$k]["title"]=ucfirst(getFamTitle($v));
    }
    usort($t,"cmpfamtitle");
    return $t;
  } else {
    $query->order_by="lower(title)";
    return $query->Query(0,0,$qtype);
  }
}
function cmpfamtitle($a, $b) {
  return strcasecmp(unaccent($a["title"]),unaccent($b["title"]));
}
 /**
 * return array of possible profil for profile type
 *
 * @param string $dbaccess database specification
 * @param int  $famid the id of family document
 * @return array/Doc
 * @see getChildDir()
 */
function GetProfileDoc($dbaccess,$docid,$defProfFamId="")
{
  global $action;
  $filter=array();
  
  $doc=new_Doc($dbaccess,$docid);
  $chdoc=$doc->GetFromDoc();
  if ($defProfFamId=="") $defProfFamId=$doc->defProfFamId;
  
  $cond = GetSqlCond($chdoc,"dpdoc_famid");
  if ($cond != "") $filter[]="dpdoc_famid is null or (".GetSqlCond($chdoc,"dpdoc_famid").")";
  else $filter[]="dpdoc_famid is null";
  $filter[]="fromid=".$defProfFamId;
  $tcv = getChildDoc($dbaccess,
		     0,0,"ALL",$filter,$action->user->id,"TABLE",$defProfFamId);
  
  return $tcv;
}

/**
 * get array of family id that the user can create interactivaly
 *
 * @param string $dbaccess database specification
 * @param int $uid user identificator
 * @param array restriction of this set of family id
 * @return array of family identificators
 */
function getFamilyCreationIds($dbaccess,$uid,$tfid=array()) {
  
  $query = new QueryDb($dbaccess,"DocFam");
  if (count($tfid) > 0) {    
    $query->AddQuery(GetSqlCond($tfid,"id"));  
  }
  $perm=(2<<(POS_CREATE-1))+(2<<(POS_ICREATE-1));

  $query->AddQuery("((profid = 0) OR hasdocprivilege($uid, profid, $perm))");
  
  $l= $query->Query(0,0,"TABLE");

  $lid=array();
  if ($query->nb > 0) {
    foreach ($l as $k=>$v) {
      $lid[]=$v["id"];
    }
  }
  return $lid;

}
/**
 * get array of document values from array od document id
 * @param string $dbaccess database specification
 */
function getDocsFromIds($dbaccess,$ids,$userid=0) {
  $tdoc=array();
  foreach ($ids as $k=>$id) {
    $tdoc1=getTDoc($dbaccess,$id);
    if ((($userid==1) || controlTdoc($tdoc1,"view"))&&($tdoc1["doctype"]!='Z'))   $tdoc[$id]=$tdoc1;
  }
  return $tdoc;
}
/**
 * get array of document values from array od document id
 * @param string $dbaccess database specification
 */
function getLatestDocsFromIds($dbaccess,$ids,$userid=0) {
  $tdoc=array();
  foreach ($ids as $k=>$id) {
    $tdoc1=getLatestTDoc($dbaccess,$id);
    if ((($userid==1) || controlTdoc($tdoc1,"view"))&&($tdoc1["doctype"]!='Z'))   $tdoc[$id]=$tdoc1;
  }
  return $tdoc;
}
/**
 * get array of document values from array od document id
 * @param string $dbaccess database specification
 * @param string $ids array of init id -only initid-
 * @param string $userid the user where search visibility
 */
function getVisibleDocsFromIds($dbaccess,$ids,$userid) {

  $query = new QueryDb($dbaccess,"DocRead");
  $query->AddQuery("initid in (".implode(",",$ids).')');
  $query->AddQuery("locked != -1");
  if ($userid > 1) $query->AddQuery("hasviewprivilege(".$userid.",profid)");
  
  $tdoc=$query->Query(0,0,"TABLE");
  
  return $tdoc;
}
/**
 * return true for optimization select
 * @param string $dbaccess database specification
 * @param int $id identificator of the document family
 * 
 * @return int false if error occured
 */
function familyNeedDocread($dbaccess, $id) {
  if (! is_numeric($id)) $id=getFamIdFromName($dbaccess,$id);
  $id=abs(intval($id));
  if ($id == 0) return false;
  $dbid=getDbid($dbaccess);   
  $fromid=false;
  $result = pg_query($dbid,"select id from docfam where id=$id and usedocread=1");
  if (pg_numrows ($result) > 0) {   
    $result = pg_query($dbid,"select fromid from docfam where fromid=$id;");
    if (pg_numrows ($result) > 0) {
      return true;
    }
  }
  
  return false;    
} 
?>
