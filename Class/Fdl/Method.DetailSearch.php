<?php
/**
 * Detailled search
 *
 * @author Anakeen 2000
 * @version $Id: Method.DetailSearch.php,v 1.73 2009/01/08 17:52:54 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage GED
 */
/**
 */


/**
 * @begin-method-ignore
 * this part will be deleted when construct document class until end-method-ignore
 */
Class _DSEARCH extends DocSearch {
	/*
	 * @end-method-ignore
	 */
	var $defaultedit= "FREEDOM:EDITDSEARCH";#N_("include") N_("equal") N_("equal") _("not equal") N_("is empty") N_("is not empty") N_("one value equal")
	var $defaultview= "FREEDOM:VIEWDSEARCH"; #N_("not include") N_("begin by") N_("not equal") N_("&gt; or equal") N_("&lt; or equal") N_("one word equal") N_("content file word") N_("content file expression")

	/**
	 * return sql query to search wanted document
	 */
	function ComputeQuery($keyword="",$famid=-1,$latest="yes",$sensitive=false,$dirid=-1, $subfolder=true) {

		if ($dirid > 0) {

			if ($subfolder)  $cdirid = getRChildDirId($this->dbaccess, $dirid);
			else $cdirid=$dirid;

		} else $cdirid=0;;

		$filters=$this->getSqlGeneralFilters($keyword,$latest,$sensitive);

		$cond=$this->getSqlDetailFilter();
		
		if ($cond === false) return array(false);
		$distinct=false;
		if ($latest=="lastfixed") $distinct=true;
		if ($cond != "") $filters[]=$cond;
		if ($this->getValue("se_famonly")=="yes") {
			if (! is_numeric($famid)) $famid=getFamIdFromName($this->dbaccess,$famid);
			$famid=- abs($famid);
		}
		$query = getSqlSearchDoc($this->dbaccess, $cdirid, $famid, $filters,$distinct,$latest=="yes",$this->getValue("se_trash"),false);

		return $query;
	}
	/**
	 * Change queries when use filters objects instead of declarative criteria
	 * @see DocSearch#getQuery()
	 */
	function getQuery() {
		if (count($this->getTvalue("se_filter")) > 0) {
			$queries=array();
			$filters=$this->getTValue("se_filter");
			foreach ($filters as $filter) {
				$q=$this->getSqlXmlFilter($filter);
				if ($q) $queries[]=$q;
			}
			return $queries;
		} else {
			return parent::getQuery();
		}
	}
	/**
	 * return a query from on filter object
	 * @param string $xml xml filter object
	 * @return string the query
	 */
	function getSqlXmlFilter($xml) {
		$root = simplexml_load_string($xml);
		// trasnform XmlObject to StdClass object
		$std=$this->simpleXml2StdClass($root);		
		$this->object2SqlFilter($std,$famid,$sql);
		
		$filters[]=$sql;
		$cdirid=0;
		$q=getSqlSearchDoc($this->dbaccess, $cdirid, $famid, $filters);
		if (count($q) == 1) {
			$q0=$q[0]; // need a tempo variable : don't know why
			return ($q0);
		}
		
		return false;
	}

	/**
	 * cast SimpleXMLElment to stdClass
	 * @param SimpleXMLElement $xml
	 * @return stdClass return  object or value if it is a leaf
	 */
	public function simpleXml2StdClass(SimpleXMLElement $xml) {
	    $std=null;
	    if ($xml->count() == 0) {
	        return current($xml);
	    } else {
	        foreach ($xml as $k=>$se) {
	            if (isset($std->$k)) {
	                if (! is_array($std->$k)) $std->$k=array($std->$k);
	                array_push($std->$k,$this->simpleXml2StdClass($se));
	            } else {
	                $std->$k=$this->simpleXml2StdClass($se);
	            }
	        }
	    }
	    return $std;
	}
	
	function preConsultation() {
		if (count($this->getTvalue("se_filter")) > 0) {
			$this->defaultview="FDL:VIEWBODYCARD";
		}
	}
	function preEdition() {
		if (count($this->getTvalue("se_filter")) > 0) {
			$this->defaultedit="FDL:EDITBODYCARD";
			$this->getAttribute('se_t_filters',$oa);
			$oa->setVisibility('W');
				
			$this->getAttribute('se_filter',$oa);
			$oa->setVisibility('W');
		}
	}
	/**
	 * return error if query filters are not compatibles
	 * verify parenthesis
	 * @return string error message , empty if no errors
	 */
	function getSqlParseError() {
		$err="";
		$tlp = $this->getTValue("SE_LEFTP");
		$tlr = $this->getTValue("SE_RIGHTP");
		$clp=0;
		$clr=0;
		//if (count($tlp) > count($tlr)) $err=sprintf(_("left parenthesis is not closed"));
		if ($err=="") {
			foreach ($tlp as $lp) if ($lp == "yes") $clp++;
			foreach ($tlr as $lr) if ($lr == "yes") $clr++;
			if ($clp != $clr) $err=sprintf(_("parenthesis number mismatch : %d left, %d right"),$clp,$clr);
		}
		return $err;
	}
	/**
	 * return sql part from operator
	 * @param string $col a column : property or attribute name
	 * @param string $op one of this ::top keys : =, !=, >, ....
	 * @param string $val value use for test
	 * @param string $val2 second value use for test with >< operator
	 * @return string the sql query part
	 */
	function getSqlCond($col,$op,$val="",$val2="",&$err="") {

		if ((! $this->searchfam) || ($this->searchfam->id != $this->getValue("se_famid"))) {
			$this->searchfam=new_doc($this->dbaccess,$this->getValue("se_famid"));
		}
		$col=trim(strtok($col,' ')); // a col is one word only (prevent injection)
		// because for historic reason revdate is not a date type
		if (($col=="revdate") && ($val!='') && (! is_numeric($val))) {
			$val=stringdatetounixts($val);
		}
		$atype='';
		$oa=$this->searchfam->getAttribute($col);
		
		if ($oa) $atype=$oa->type;
		else if ($this->infofields[$col]) $atype=$this->infofields[$col]["type"];
		if (($atype=="date" || $atype=="timestamp")) {
		    if ($col=='revdate') {
		        if ($op=="=") {
                            $val2=$val+85399; // tonight 
		            $op="><";
		        }
		    } else {
		        if (($atype=="timestamp")){
		            $pos = strpos($val,' ');
		            $hms = '';
		            if($pos != false){
		                $hms = substr($val,$pos + 1);
		            }
		        }
		        	
		        $cfgdate=getLocaleConfig();
		        if ($val) $val=stringDateToIso($val,$cfgdate['dateFormat']);
		        if ($val2) $val2=stringDateToIso($val2,$cfgdate['dateFormat']);

		        if (($atype=="timestamp") && ($op=="=")) {

		            $val=trim($val);
		            if (strlen($val)==10) {
		                if($hms == ''){
		                    $val2=$val." 23:59:59";
		                    $val.=" 00:00:00";
		                    $op="><";
		                } elseif (strlen($hms) == 2){
		                    $val2=$val.' '.$hms.":59:59";
		                    $val.=' '.$hms.":00:00";
		                    $op="><";
		                } elseif (strlen($hms) == 5){
		                    $val2=$val.' '.$hms.":59";
		                    $val.=' '.$hms.":00";
		                    $op="><";
		                } else {
		                    $val .= ' '.$hms ;
		                }

		            }
		        }
		    }
		}
		switch($op) {
			case "is null":
				
				switch ($atype) {
					case "int":
					case "uid":
					case "double":
					case "money":
						$cond = sprintf(" (%s is null or %s = 0) ",$col,$col);
						break;
					case "date":
					case "time":
						$cond = sprintf(" (%s is null) ",$col);
						break;
					default:
						$cond = sprintf(" (%s is null or %s = '') ",$col,$col);
				}
				
				break;
			case "is not null":
				$cond = " ".$col." ".trim($op)." ";
				break;
			case "~*":
				if (trim($val) != "") $cond = " ".$col." ".trim($op)." ".$this->_pg_val($val)." ";
				break;
			case "~^":
				if (trim($val) != "") $cond = " ".$col."~* '^".pg_escape_string(trim($val))."' ";
				break;
			case "~y":
				if (! is_array($val)) $val=$this->_val2array($val);
				if (count($val) > 0) $cond = " ".$col." ~ '\\\\y(".pg_escape_string(implode('|',$val)).")\\\\y' ";

				break;
			case "><":
				if ((trim($val) != "")&&(trim($val2) != "")) {
					$cond = sprintf("%s >= %s and %s <= %s",$col,
					$this->_pg_val($val),$col,
					$this->_pg_val($val2));
				}
				break;
			case "=~*":
				switch ($atype) {
				    case "uid":
				        $err=simpleQuery(getDbAccessCore(),
				        sprintf("select id from users where firstname ~* '%s' or lastname ~* '%s'",
				        pg_escape_string($val),pg_escape_string($val)),
				        $ids,
				        true);
				        if ($err=="") {
				            if (count($ids)==0) $cond="false";
				            elseif (count($ids)==1) {
				                $cond = " ".$col." = ".intval($ids[0])." ";
				            } else {
				                $cond = " ".$col." in (".implode(',',$ids).") ";
				            }
				        }
				        break;
				    case "docid":
				        if ($oa) {
				            $otitle=$oa->getOption("doctitle");
				            if (! $otitle) {
				                $fid=$oa->format;
				                if (! $fid) $err=sprintf(_("no compatible type with operator %s"),$op);
				                else {
				                    if (! is_numeric($fid)) $fid=getFamidFromName($this->dbaccess,$fid);
				                    $err=simpleQuery($this->dbaccess,
				                    sprintf("select id from doc%d where title ~* '%s'",$fid,
				                    pg_escape_string($val)),
				                    $ids,
				                    true);
				                    if ($err=="") {
				                        if (count($ids)==0) $cond="false";
				                        elseif (count($ids)==1) {
				                            $cond = " ".$col." = '".intval($ids[0])."' ";
				                        } else {
				                            $cond = " ".$col." in ('".implode("','",$ids)."') ";
				                        }
				                    }
				                }
				            } else {
				                if ($otitle=="auto") $otitle=$oa->id."_title";
				                $oat=$this->searchfam->getAttribute($otitle);
				                if ($oat) {
				                    $cond = " ".$oat->id." ~* '".pg_escape_string(trim($val))."' ";
				                } else {
				                    $err=sprintf(_("attribute %s : cannot detect title attribute"),$col);
				                }
				            }
				        } elseif ($col == "fromid") {
				            $err=simpleQuery($this->dbaccess,
            				            sprintf("select id from docfam where title ~* '%s'",
            				            pg_escape_string($val)),
            				            $ids,
            				            true);
				            if ($err=="") {
				                if (count($ids)==0) $cond="false";
				                elseif (count($ids)==1) {
				                    $cond = " ".$col." = ".intval($ids[0])." ";
				                } else {
				                    $cond = " ".$col." in (".implode(",",$ids).") ";
				                }
				            }
				        }
				        break;
				    default:
				        if ($atype) $err=sprintf(_("attribute %s : %s type is not allowed with %s operator"),$col, $atype,$op);
				        else $err=sprintf(_("attribute %s not found [%s]"),$col, $atype);
				}
				break;
			case "~@":
				if (trim($val) != "") {
					$cond = " ".$col.'_txt'." ~ '".strtolower($val)."' ";
				}
				break;
			case "=@":
			case "@@":
				if (trim($val) != "") {
					$tstatickeys=explode(' ',$val);
					if (count($tstatickeys) > 1) {
						$keyword.= str_replace(" ","&",trim($val));
					} else {
						$keyword=trim($val);
					}
					if ($op=="@@") $cond= " ".$col.'_vec'." @@ to_tsquery('french','.".unaccent(strtolower($keyword))."') ";
					else if ($op=="=@") $cond= "fulltext @@ to_tsquery('french','".unaccent(strtolower($keyword))."') ";
				}
				break;
			default:
				
				switch ($atype) {
					case "enum":
						$enum = $oa->getEnum();
						if (strrpos($val,'.') !== false)   $val = substr($val,strrpos($val,'.')+1);
						$tkids=array();;
						foreach($enum as $k=>$v) {
							if (in_array($val,explode(".",$k))) {
								$tkids[] = substr($k,strrpos(".".$k,'.'));
							}
						}

						if ($op=='=') {
							if ($oa->repeat) {
								$cond= " ".$col." ~ '\\\\y(".pg_escape_string(implode('|',$tkids)).")\\\\y' ";
							} else {
								$cond= " $col='". implode("' or $col='",$tkids)."'";
							}
						} elseif ($op=='!=') {
							if ($oa->repeat) {
								$cond1 = " ".$col." !~ '\\\\y(".pg_escape_string(implode('|',$tkids)).")\\\\y' ";

							} else {
								$cond1 = " $col !='". implode("' and $col != '",$tkids)."'";
							}
							$cond= " (($cond1) or ($col is null))";
						}

						break;
					case "docid":
						if (! is_numeric($val)) $val=getIdFromName($this->dbaccess,$val);
					default:
						$cond1 = " ".$col." ".trim($op).$this->_pg_val($val)." ";
						if (($op=='!=')||($op=='!~*')) {
							$cond= "(($cond1) or ($col is null))";
						} else $cond=$cond1;
				}
		}

		if (!$cond) $cond="true";
		return $cond;
	}

	private static function _pg_val($s) {
		if (substr($s,0,2)==':@') {
			return " ".trim(strtok(substr($s,2)," \t"))." ";
		} else return " '".pg_escape_string(trim($s))."' ";
	}

	/**
	 * return array of sql filter needed to search wanted document
	 */
	function getSqlDetailFilter() {
		$ol = $this->getValue("SE_OL");
		$tkey = $this->getTValue("SE_KEYS");
		$taid = $this->getTValue("SE_ATTRIDS");
		$tf = $this->getTValue("SE_FUNCS");
		$tlp = $this->getTValue("SE_LEFTP");
		$tlr = $this->getTValue("SE_RIGHTP");
		$tols = $this->getTValue("SE_OLS");

		if ($ol == "") {
			// try in old version
			$ols=$this->getTValue("SE_OLS");
			$ol=$ols[1];
			if ($ol) {
				$this->setValue("SE_OL",$ol);
				$this->modify();
			}
		}
		if ($ol == "") $ol="and";
		$cond="";
		if (! $this->searchfam) {
			$this->searchfam=new_doc($this->dbaccess,$this->getValue("se_famid"));
		}
		if ((count($taid) > 1) || ($taid[0] != "")) {
			// special loop for revdate
			foreach($tkey as $k=>$v) {
				if (strtolower(substr($v,0,5))=="::get") { // only get method allowed
					// it's method call
					$workdoc=$this->getSearchFamilyDocument();
					if ($workdoc) $rv = $workdoc->ApplyMethod($v);
					else $rv = $this->ApplyMethod($v);
					$tkey[$k]=$rv;
				}
				if (substr($v,0,1)=="?") {
					// it's a parameter
					$rv = getHttpVars(substr($v,1),"-");
					if ($rv == "-") return (false);
					if ($rv==="" || $rv===" ") unset($taid[$k]);
                                        else $tkey[$k]=$rv;                                       
				}
				if ($taid[$k] == "revdate") {
					list($dd,$mm,$yyyy) = explode("/",$tkey[$k]);
					if ($yyyy > 0) $tkey[$k]=mktime (0,0,0,$mm,$dd,$yyyy);
				}
			}
			foreach ($taid as $k=>$v) {
				$cond1=$this->getSqlCond($taid[$k],trim($tf[$k]),$tkey[$k]);
				if ($cond == "") {
					if ($tlp[$k]=="yes") $cond='('.$cond1." ";
					else $cond=$cond1." ";
					if ($tlr[$k]=="yes") $cond.=')';
				} elseif ($cond1!="") {
					if ($tols[$k]!="") $ol1=$tols[$k];
					else $ol1=$ol;
					if ($tlp[$k]=="yes") $cond.=$ol1.' ('.$cond1." ";
					else $cond.=$ol1." ".$cond1." ";
					if ($tlr[$k]=="yes") $cond.=') ';
				}

			}
		}
		if (trim($cond)=="") $cond="true";
		return $cond;
	}

	/**
	 * return true if the search has parameters
	 */
	function isParameterizable() {
		$tkey = $this->getTValue("SE_KEYS");

		if ((count($tkey) > 1) || ($tkey[0] != "")) {

			foreach ($tkey as $k=>$v) {
					
				if ($v[0]=='?') {
					return true;
					//if (getHttpVars(substr($v,1),"-") == "-") return true;
				}

			}
		}
		return false;
	}
	/**
	 * return true if the search need parameters
	 */
	function needParameters() {
		$tkey = $this->getTValue("SE_KEYS");

		if ((count($tkey) > 1) || ($tkey[0] != "")) {

			foreach ($tkey as $k=>$v) {
					
				if ($v[0]=='?') {
					if (getHttpVars(substr($v,1),"-") == "-") return true;
				}

			}
		}
		return false;
	}
	/**
	 * Add parameters
	 */
	function urlWhatEncodeSpec($l) {
		$tkey = $this->getTValue("SE_KEYS");

		if ((count($tkey) > 1) || ($tkey[0] != "")) {

			foreach ($tkey as $k=>$v) {
					
				if ($v[0]=='?') {
					if (getHttpVars(substr($v,1),"-") != "-") {
						$l.='&'.substr($v,1)."=".getHttpVars(substr($v,1));
					}
				}

			}
		}

		return $l;
	}

	/**
	 * add parameters in title
	 */
	function getSpecTitle() {
		$tkey = $this->getTValue("SE_KEYS");
                $taid = $this->getTValue("SE_ATTRIDS");
		$l="";
		if ((count($tkey) > 1) || ($tkey[0] != "")) {
			$tl=array();
			foreach ($tkey as $k=>$v) {
					
				if ($v[0]=='?') {
					$vh=getHttpVars(substr($v,1),"-");
					if (($vh != "-") && ($vh != "")) {
					    
					    if (is_numeric($vh)) {
					        $fam=$this->getSearchFamilyDocument();
					        if ($fam) {
					            $oa=$fam->getAttribute($taid[$k]);
					            if ($oa && $oa->type=="docid") {
					                $vh=$this->getTitle($vh);
					            }
					        }
					    }
					    $tl[]= $vh;
					}
				}

			}
			if (count($tl)> 0) {
				$l=" (".implode(", ",$tl).")";
			}
		}
		return $this->getValue("ba_title").$l;
	}

	function viewdsearch($target="_self",$ulink=true,$abstract=false) {
		// Compute value to be inserted in a  layout
		$this->viewattr();
		//-----------------------------------------------
		// display already condition written

		$tkey = $this->getTValue("SE_KEYS");
		$taid = $this->getTValue("SE_ATTRIDS");
		$tf = $this->getTValue("SE_FUNCS");

		if ((count($taid) > 1) || ($taid[0] != "")) {

			$fdoc=new_Doc($this->dbaccess, $this->getValue("SE_FAMID",1));
			$zpi=$fdoc->GetNormalAttributes();
			$zpi["state"]=new BasicAttribute("state",$this->fromid,_("state"));
			$zpi["title"]=new BasicAttribute("title",$this->fromid,_("doctitle"));
			$zpi["revdate"]=new BasicAttribute("revdate",$this->fromid,_("revdate"));
			$zpi["cdate"]=new BasicAttribute("cdate",$this->fromid,_("cdate"),'W','','','date');
			$zpi["revision"]=new BasicAttribute("cdate",$this->fromid,_("revision"));
			$zpi["owner"]=new BasicAttribute("owner",$this->fromid,_("owner"));
			$zpi["locked"]=new BasicAttribute("owner",$this->fromid,_("locked"));
			$zpi["allocated"]=new BasicAttribute("owner",$this->fromid,_("allocated"));
			$zpi["svalues"]=new BasicAttribute("svalues",$this->fromid,_("any values"));


			foreach ($taid as $k=>$v) {
				$label=$zpi[$taid[$k]]->getLabel();
				if ($label=="") $label=$taid[$k];
				$tcond[]["condition"]=sprintf("%s %s %s",
				$label,$this->getOperatorLabel($tf[$k],$zpi[$taid[$k]]->type),
				($tkey[$k]!="")?_($tkey[$k]):$tkey[$k]);
				if ($tkey[$k][0]=='?') {
					$tparm[substr($tkey[$k],1)]=$taid[$k];
				}
			}
			$this->lay->SetBlockData("COND", $tcond);
		}
		$this->lay->Set("ddetail", "");

	}

	/**
	 * return true if the sqlselect is writted by hand
	 * @return bool
	 */
	function isStaticSql() {
		return ($this->getValue("se_static") != "");
	}
	/**
	 * return family use for search
	 * @return Doc
	 */
	private function getSearchFamilyDocument() {
	    static $fam=null;
	    if (! $fam) $fam=createTmpDoc($this->dbaccess,$this->getValue("SE_FAMID",1));
	    return $fam;
	}
	
	function paramdsearch($target="_self",$ulink=true,$abstract=false) {
		// Compute value to be inserted in a  layout
		$this->viewattr();
		//-----------------------------------------------
		// display already condition written

		$tkey = $this->getTValue("SE_KEYS");
		$taid = $this->getTValue("SE_ATTRIDS");
		$tf = $this->getTValue("SE_FUNCS");

		if ((count($taid) > 1) || ($taid[0] != "")) {

			$fdoc=new_Doc($this->dbaccess, $this->getValue("SE_FAMID",1));
			$zpi=$fdoc->GetNormalAttributes();
			$zpi["state"]=new BasicAttribute("state",$this->fromid,_("state"));
			$zpi["title"]=new BasicAttribute("title",$this->fromid,_("doctitle"));
			$zpi["revdate"]=new BasicAttribute("revdate",$this->fromid,_("revdate"));
			$zpi["cdate"]=new BasicAttribute("cdate",$this->fromid,_("cdate"),'W','','','date');
			$zpi["revision"]=new BasicAttribute("cdate",$this->fromid,_("revision"));
			$zpi["owner"]=new BasicAttribute("owner",$this->fromid,_("owner"));
			$zpi["locked"]=new BasicAttribute("owner",$this->fromid,_("locked"));
			$zpi["allocated"]=new BasicAttribute("owner",$this->fromid,_("allocated"));
			$zpi["svalues"]=new BasicAttribute("svalues",$this->fromid,_("any values"));

			foreach ($taid as $k=>$v) {
				if ($tkey[$k][0]=='?') {
					$tparm[substr($tkey[$k],1)]=$taid[$k];
                                        $toperator[substr($tkey[$k],1)]=$tf[$k];
				}
			}
			$this->lay->SetBlockData("COND", $tcond);
		}

		$this->lay->Set("ddetail", "");
		if (count($tparm) > 0) {
			include_once("FDL/editutil.php");
			global $action;
			editmode($action);

			$doc= $this->getSearchFamilyDocument();
			$inputset=array();
			$ki=0; // index numeric
			foreach ($tparm as $k=>$v) {
				if (isset($inputset[$v])) {
				    // need clone when use several times the same attribute
				    $vz=$v."Z".$ki;
				    $zpi[$vz]=$zpi[$v];
				    $zpi[$vz]->id=$vz;
				    $v=$vz;
				}
				if ($zpi[$v]->fieldSet->type=='array') $zpi[$v]->fieldSet->type='frame'; // no use array configuration for help input
				$ki++;
				$inputset[$v]=true;
				
				$ttransfert[]=array("idi"=>$v,
			 "idp"=>$k,
			 "value"=>getHttpVars($k));
				$tinputs[$k]["label"]=$zpi[$v]->getLabel();
                                $tinputs[$k]["operator"]=$this->getOperatorLabel($toperator[$k],$zpi[$v]->type);
                                if (($toperator[$k]=="=~*" || $toperator[$k]=="~*") && $zpi[$v]->type=="docid") $zpi[$v]->type="text"; // present like a search when operator is text search
                                
                                if ($zpi[$v]->visibility=='R') $zpi[$v]->mvisibility='W';
				if ($zpi[$v]->visibility=='S') $zpi[$v]->mvisibility='W';
				if (isset($zpi[$v]->id)) {
					$zpi[$v]->isAlone=true;
					$tinputs[$k]["inputs"]=getHtmlInput($doc,$zpi[$v],getHttpVars($k));
				} else {
					$aotxt=new BasicAttribute($v,$doc->id,"eou");
					if ($v=="revdate") $aotxt->type="date";
					$tinputs[$k]["inputs"]=getHtmlInput($doc,$aotxt,getHttpVars($k));
				}
			}
			$this->lay->setBlockData("PARAM",$tinputs);
			$this->lay->setBlockData("TRANSFERT",$ttransfert);
			$this->lay->setBlockData("PINPUTS",$ttransfert);
			$this->lay->Set("ddetail", "none");
			$this->lay->set("stext",_("send search"));
			$this->lay->set("saction",getHttpVars("saction","FREEDOM_VIEW"));
			$this->lay->set("sapp",getHttpVars("sapp","FREEDOM"));
			$this->lay->set("sid",getHttpVars("sid","dirid"));
			$this->lay->set("starget",getHttpVars("starget",""));
			$this->lay->set("icon",$this->getIcon());
		}
	}
	// -----------------------------------

	function editdsearch() {
		global $action;

		$famid = GetHttpVars("sfamid",$this->getValue("SE_FAMID",1));
		$onlysubfam = GetHttpVars("onlysubfam"); // restricy to sub fam of
		$dirid = GetHttpVars("dirid");
		$this->lay->set("ACTION",$action->name);
		$tclassdoc=array();
		$action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDL/Layout/edittable.js");
		$action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FREEDOM/Layout/editdsearch.js");


		if ($dirid > 0) {
			$dir = new_Doc($this->dbaccess, $dirid);
			if (method_exists($dir,"isAuthorized")) {
				if ($dir->isAuthorized($classid)) {
					// verify if classid is possible
					if ($dir->hasNoRestriction()) {
						$tclassdoc=GetClassesDoc($this->dbaccess, $action->user->id,$classid,"TABLE");
						$tclassdoc[]=array("id"=>0,
			     "title"=>_("any families"));

					} else {
						$tclassdoc=$dir->getAuthorizedFamilies();
						$this->lay->set("restrict",true);
					}
				} else  {
					$tclassdoc=$dir->getAuthorizedFamilies();
					$first = current($tclassdoc);
					$famid1 = ($first["id"]);
					$this->lay->set("restrict",true);
					$tfamids=array_keys($tclassdoc);
					if (! in_array($famid,$tfamids)) $famid=$famid1;
				}
			}
			else {
				$tclassdoc = GetClassesDoc($this->dbaccess, $action->user->id,$classid,"TABLE");
			}
		} else {
			if ($onlysubfam) {
				$alsosub=true;
				if (! is_numeric($onlysubfam))  $onlysubfam = getFamIdFromName($this->dbaccess,$onlysubfam);
				$cdoc = new_Doc($this->dbaccess,$onlysubfam);
				$tsub=$cdoc->GetChildFam($cdoc->id,false);
				if ($alsosub) {
					$tclassdoc[$classid] = array("id"=>$cdoc->id ,
				     "title"=>$cdoc->title);
					$tclassdoc = array_merge($tclassdoc,$tsub);
				} else {
					$tclassdoc=$tsub;
				}
				$first = current($tclassdoc);
				if ($classid=="") $classid = $first["id"];

			} else  {
				$tclassdoc = GetClassesDoc($this->dbaccess, $action->user->id,$classid,"TABLE");
				$tclassdoc[]=array("id"=>0,
			 "title"=>_("any families"));
			}
		}


		$this->lay->set("onlysubfam",$onlysubfam);
		$selfam=false;
		foreach ($tclassdoc as $k=>$cdoc) {
			$selectclass[$k]["idcdoc"]=$cdoc["id"];
			$selectclass[$k]["classname"]=$cdoc["title"];
			if (abs($cdoc["id"]) == abs($famid)) {
				$selfam=true;
				$selectclass[$k]["selected"]="selected";
				if ($famid < 0)	$this->lay->set("selfam",$cdoc["title"]." "._("(only)"));
				else $this->lay->set("selfam",$cdoc["title"]);
			} else $selectclass[$k]["selected"]="";
		}
		if (! $selfam) {
		    $famid=abs($this->getValue("se_famid"));
		    if ($this->id && $famid) {
                        $selectclass[]=array("idcdoc"=>$famid,
                                             "classname"=>$this->getTitle($famid),
                                             "selected"=>"selected");
		        
		    } else {
			reset($tclassdoc);
			$first = current($tclassdoc);
			$famid = $first["id"];
		    }
		}
		$this->lay->Set("dirid",$dirid);
		$this->lay->Set("classid",$this->fromid);
		$this->lay->SetBlockData("SELECTCLASS", $selectclass);
		$this->setFamidInLayout();



		// display attributes
		$tattr=array();
		$internals=array("title" => _("doctitle"),
		   "revdate" => _("revdate"),
		   "cdate" => _("cdate"),
		   "revision" => _("revision"),
		   "owner" => _("id owner"),
		   "locked" => _("id locked"),
		   "allocated" => _("id allocated"),
		   "svalues"=> _("any values"));

                foreach($internals as $k=>$v) {
			if ($k=="revdate") $type="date";
			else if ($k=="owner") $type="docid";
			else if ($k=="locked") $type="docid";
			else if ($k=="allocated") $type="docid";
			else if ($k=="cdate") $type="date";
			else if ($k=="revision") $type="int";
			else if ($k=="state") $type="docid";
			else $type="text";
			$tattr[]=array("attrid"=> $k,
		   "attrtype"=>$type,
		   "attrname" => $v);
		}

		$fdoc=new_Doc($this->dbaccess, abs($famid));
		$zpi=$fdoc->GetNormalAttributes();
		foreach($zpi as $k=>$v) {
			if ($v->type == "array") continue;
			if ($v->isMultiple() && ($v->type!='file')) $type="array";
			else $type=$v->type;
			$tattr[]=array("attrid"=> $v->id,
		   "attrtype"=>$type,
		   "attrname" => $v->getLabel());
		}
		$this->lay->SetBlockData("ATTR", $tattr);

		foreach($this->top as $k=>$v) {
			$display='';
			if (isset($v["type"])) {
				$ctype=implode(",",$v["type"]);
				if (! in_array('text',$v["type"])) $display='none'; // first is title
			} else $ctype="";


			$tfunc[]=array("funcid"=> $k,
		   "functype"=>$ctype,
		   "funcdisplay"=>$display,
		   "funcname" => _($v["label"]));
		}
		$this->lay->SetBlockData("FUNC", $tfunc);
		foreach ($tfunc as $k=>$v) {
			if (($v["functype"]!="") && (strpos($v["functype"],"enum")===false)) unset($tfunc[$k]);
		}
		$this->lay->SetBlockData("FUNCSTATE", $tfunc);
		$this->lay->Set("icon",$fdoc->getIcon());


		if ($this->getValue("SE_LATEST") == "no")     $this->lay->Set("select_all","selected");
		else $this->lay->Set("select_all","");


		//-----------------------------------------------
		// display state
		if ($fdoc->wid > 0) {
			$wdoc=new_Doc($this->dbaccess, $fdoc->wid);
			$states=$wdoc->getStates();

			$tstates=array();
			while(list($k,$v) = each($states)) {
				$tstates[] = array("stateid"=>$v,
			 "statename"=>_($v));
			}
			$this->lay->SetBlockData("STATE",$tstates );
			$this->lay->Set("dstate","inline" );
		} else {
			$this->lay->Set("dstate","none" );
		}

		//-----------------------------------------------
		// display already condition written
		$tol = $this->getTValue("SE_OLS");
		$tkey = $this->getTValue("SE_KEYS");
		$taid = $this->getTValue("SE_ATTRIDS");
		$tf = $this->getTValue("SE_FUNCS");
		$tlp = $this->getTValue("SE_LEFTP");
		$trp = $this->getTValue("SE_RIGHTP");

		$cond="";
		$tcond=array();

		if ((count($taid) > 1) || ($taid[0] != "")) {
			foreach($taid as $k=>$va) {
				$docid_aid = 0;
				$v=$tkey[$k];
				$oa=$fdoc->getAttribute($taid[$k]);
				$tcond[$k]= array("OLCOND"   => "olcond$k",
			"ATTRCOND" => "attrcond$k",
			"FUNCCOND" => "funccond$k",
			"ISENUM" => (($taid[$k]=="state")||($oa->type=="enum")),
			"SSTATE" => "sstate$k",
  			"ols_and_selected"=> ($tol[$k]=="and")?"selected":"",
 			"ols_or_selected"=> ($tol[$k]=="or")?"selected":"",
  			"leftp_none_selected"=> ($tlp[$k]!="yes")?"selected":"",
 			"leftp_open_selected"=> ($tlp[$k]=="yes")?"selected":"",
  			"rightp_none_selected"=> ($trp[$k]!="yes")?"selected":"",
 			"rightp_open_selected"=> ($trp[$k]=="yes")?"selected":"",
			"key" => $v);

				$tattr=array();
				if ($taid[$k]=="state") {
					$tstates=array();
					$stateselected=false;
					foreach($states as $ks=>$vs) {
						$tstates[] = array("sstateid"=>$vs,
			     "sstate_selected" => ($vs==$v)?"selected":"",
			     "sstatename"=>_($vs));
						if ($vs==$v) $stateselected=true;
					}
					if (! $stateselected) $tcond[$k]["ISENUM"]=false;
					$this->lay->SetBlockData("sstate$k",$tstates );
					$tattr[]=array("attr_id"=> $taid[$k],
							 "attr_type"=>"docid",
		       "attr_selected" => "selected",
		       "attr_name" => _("state"));
				} else {
					if ($oa->type=="enum") {
						$te=$oa->getEnum();
						$tstates=array();
						$enumselected=false;
						foreach ($te as $ks=>$vs) {
							$tstates[] = array("sstateid"=>$ks,
			       "sstate_selected" => ($ks==$v)?"selected":"",
			       "sstatename"=>$vs);
							if ($ks==$v) $enumselected=true;
						}
						$this->lay->SetBlockData("sstate$k",$tstates );
						if (! $enumselected) $tcond[$k]["ISENUM"]=false;
					}

					foreach($internals as $ki=>$vi) {
						if ($ki=="revdate") $type="date";
						else if ($ki=="owner") $type="docid";
						else $type="text";
						$tattr[]=array("attr_id"=> $ki,
							 "attr_type"=>$type,
							 "attr_selected" => ($taid[$k]==$ki)?"selected":"",
							 "attr_name" => $vi);
					}
					foreach($zpi as $ki=>$vi) {
						if ($vi->inArray() && ($vi->type!='file')) $type="array";
						else $type=$vi->type;
						$tattr[]=array("attr_id"=> $vi->id,
			 				"attr_type"=>$type,
			 "attr_selected" => ($taid[$k]==$vi->id)?"selected":"",
			 "attr_name" => $vi->getLabel());
					}
				}
				$this->lay->SetBlockData("attrcond$k", $tattr);

				$tfunc=array();

				foreach($this->top as $ki=>$vi) {
					$oa=$fdoc->getAttribute($taid[$k]);
					$type=$oa->type;
					if ($type=="") {
						if ($taid[$k]=="title") $type="text";
						elseif ($taid[$k]=="cdate") $type="date";
						elseif ($taid[$k]=="revision") $type="int";
						elseif ($taid[$k]=="allocated") $type="docid";
						elseif ($taid[$k]=="locked") $type="docid";
						elseif ($taid[$k]=="revdate") $type="date";
						elseif ($taid[$k]=="owner") $type="docid";
						elseif ($taid[$k]=="svalues") $type="text";
						elseif ($taid[$k]=="state") $type="enum";
					} else {
						if ($oa->inArray() && ($oa->type!='file')) $type="array";
					}
					$display='';
					$ctype='';
					if (isset($vi["type"])) {
						if (! in_array($type,$vi["type"])) $display='none';
						$ctype=implode(",",$vi["type"]);
					}
					if($tf[$k]==$ki && $type == 'docid' && $display == '' && ($ki == '=' || $ki == '!=')) {
						$docid_aid = $taid[$k];
					}
					$tfunc[]=array("func_id"=> $ki,
		       "func_selected" => ($tf[$k]==$ki)?"selected":"",
		       "func_display"=>$display,
		       "func_type"=>$ctype,
		       "func_name" => _($vi["label"]));
				}
				$this->lay->SetBlockData("funccond$k", $tfunc);

				$tols=array();
				foreach($this->tol as $ki=>$vi) {
					$tols[]=array("ol_id"=> $ki,
		      "ol_selected" => ($tol[$k]==$ki)?"selected":"",
		      "ol_name" => _($vi));
				}
				$this->lay->SetBlockData("olcond$k", $tols);

				if(is_numeric($v) && isset($docid_aid) && !empty($docid_aid)) {
					$tcond[$k]["ISENUM"] = false;
					$tcond[$k]["ISDOCID"] = true;
					$tcond[$k]["DOCID_AID"] = $docid_aid;
					$tcond[$k]["DOCID_TITLE"] = $this->getTitle($v);
					$tcond[$k]["FAMID"] = abs($famid);
				}
				else {
					$tcond[$k]["ISDOCID"] = false;
					$tcond[$k]["DOCID_AID"] = 0;
					$tcond[$k]["DOCID_TITLE"] = '';
					$tcond[$k]["FAMID"] = abs($famid);
				}


			}
		}
		if (count($tcond) > 0)  $this->lay->SetBlockData("CONDITIONS", $tcond);
		// Add select for enum attributes

		$tenums=array();
		foreach($zpi as $k=>$v) {
			if (($v->type == "enum")|| ($v->type == "enumlist")) {
				$tenums[]=array("SELENUM"=>"ENUM$k",
		      "attrid"=>$v->id);
				$tenum=$v->getEnum();
				$te=array();
				foreach ($tenum as $ke=>$ve) {
					$te[]=array("enumkey"=>$ke,
		    "enumlabel"=>$ve);
				}
				$this->lay->setBlockData("ENUM$k",$te);
			}
		}

		$this->lay->setBlockData("ENUMS",$tenums);

		$this->lay->Set("id", $this->id);
		$this->editattr();

	}
	/**
	* @begin-method-ignore
	* this part will be deleted when construct document class until end-method-ignore
	*/
}

/*
 * @end-method-ignore
 */

?>