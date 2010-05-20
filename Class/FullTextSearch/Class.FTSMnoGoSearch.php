<?php
/**
 * Attribute Document Object Definition
 *
 * @author Anakeen 2002
 * @version $Id: Class.FTSMnoGoSearch.php,v 1.2 2004/10/20 17:10:30 marc Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage 
 */
/**
 */

/**
 * Attribute Document Class
 *
 */
Class FTSMnoGoSearch  {
    
  var $IndexerConf = "/etc/mnogosearch/indexer.conf";
  var $found = false;
  var $rcount = 0;

  var $DEBUG = false;

  function FTSMnoGoSearch() {

  if (!extension_loaded('mnogosearch')) {
    search_error($action, _("mnogosearch php extension not loaded"));
    return;
  }

    $this->ReadConf();
    $this->dbg("MnoGoSearch API Version = ".Udm_Api_Version());
    if ($this->agent = Udm_Alloc_Agent_Array(array($this->db))) {
      $this->SetParam(UDM_PARAM_ISPELL_PREFIXES,UDM_DISABLED);
      $this->SetParam(UDM_PARAM_STOPFILE, $this->stopwordfile);
      $this->SetParam(UDM_PARAM_CROSS_WORDS,$this->crossword);
      $this->SetParam(UDM_PARAM_CHARSET, $this->localcharset);
      $this->SetParam(18, $this->localcharset);
      $this->SetParam(UDM_PARAM_MIN_WORD_LEN, $this->minwordlength);
      $this->SetParam(UDM_PARAM_MAX_WORD_LEN, $this->maxwordlength);
      $this->SetParam(UDM_PARAM_VARDIR, $this->vardir);
      $this->SetParam(UDM_PARAM_CACHE_MODE, UDM_DISABLED);
      $this->SetParam(UDM_PARAM_TRACK_MODE, UDM_DISABLED);
      $this->SetParam(UDM_PARAM_PAGE_NUM, 0);
   }
  }

  function Close() {
     Udm_Free_Res($this->Result);  
     Udm_Free_Agent($this->agent);
  }

  function FTSearch($p, $mode=UDM_MODE_ANY, $wmatch=UDM_MATCH_SUBSTR, $rescount=100, $lang = "") {
    $result = array();
    if ($lan!="") Udm_Add_Search_Limit($this->agent, UDM_LIMIT_LANG, $lang);
    $this->SetParam(UDM_PARAM_PAGE_SIZE, $rescount );
    $this->SetParam(UDM_PARAM_SEARCH_MODE,$mode);
    $this->SetParam(UDM_PARAM_WORD_MATCH,$wmatch);
    $this->SetParam(25,$p);
    $this->dbg("FTSMnoGoSearch::FTSearch [$p]");
    $this->Result = udm_find($this->agent, $p);
    if (Udm_Errno($this->agent)>0) {
      echo "ERREUR";
    } else {
      $this->found = $this->ResultGetParam(UDM_PARAM_FOUND);
      $this->tcount = udm_get_doc_count($this->agent);
      $this->rcount = $this->ResultGetParam(UDM_PARAM_NUM_ROWS);
      $this->searchtime = $this->ResultGetParam(UDM_PARAM_SEARCHTIME);
      for ($i=0; $i<$this->rcount; $i++ ) {
	$result[$i]["file"] = Udm_Get_Res_Field($this->Result,$i,UDM_FIELD_URL);
	$result[$i]["size"] = Udm_Get_Res_Field($this->Result,$i,UDM_FIELD_SIZE);
	$result[$i]["order"] = Udm_Get_Res_Field($this->Result,$i,UDM_FIELD_ORDER);
	$result[$i]["rating"] = Udm_Get_Res_Field($this->Result,$i,UDM_FIELD_RATING);
      }
    }
//     $result[$i]["file"] = "/fichier/test/46.nop";
//     $result[$i]["rating"] = "6.38%";
//     $this->rcount++; $this->found = true;
    return $result;
  }


  /* 
   * Setting parameters
   */
  function SetCharset($c) {
    return $this->SetParam(UDM_PARAM_CHARSET, $c, "UDM_PARAM_CHARSET");
  }
  function SetResultPageCount($n) {
    return $this->SetParam(UDM_PARAM_PAGE_NUM, $n, "UDM_PARAM_PAGE_NUM");
  }
  function SetResultByPage($n) {
    return $this->SetParam(UDM_PARAM_PAGE_SIZE, $n, "UDM_PARAM_PAGE_SIZE");
  }
    


  function SetParam($p, $v, $n="") {
    $st = Udm_Set_Agent_Param($this->agent, $p, $v);
    if ($st) {
      $sts = "Ok";
    } else {
      $sts = "Error";
    }
    $this->dbg("FTSMnoGoSearch::Udm_Set_Agent_Param(".$this->Param2String($p).", &lt;$v&gt;) : ".$sts);
    return $st;
  }
  
  function TotalCount()  { return $this->tcount;  }
  function Found()       { return $this->found; }
  function ResultCount() { return $this->rcount;  }
  function SearchTime()  { return $this->searchtime; } 

  function ResultGetParam($p, $n="") {
    $res = Udm_Get_Res_Param($this->Result, $p);
    if ($res === false) {
      $sts = "Error";
    } else {
      $sts = "Ok";
    }
    $this->dbg("FTSMnoGoSearch::ResultGetParam($n &lt;".$res."&gt;) ".$sts);
    return $res;
  }


  function ReadResult() {
    
  }
  
  function dbg($s) {
    if ($this->DEBUG) echo "<div style=\"width:80%; font-size : 8pt; color:yellow; background-color:black;\">".strftime("%x %X",time())."&lt;".$s."</div>";
  }
    
  function Param2String ($p) {
    switch ($p) {
    case UDM_PARAM_PAGE_NUM : $s = "UDM_PARAM_PAGE_NUM"; break;
    case UDM_PARAM_PAGE_SIZE : $s = "UDM_PARAM_PAGE_SIZE"; break;
    case UDM_PARAM_SEARCH_MODE : $s = "UDM_PARAM_SEARCH_MODE"; break;
    case UDM_PARAM_CACHE_MODE : $s = "UDM_PARAM_CACHE_MODE"; break;
    case UDM_PARAM_TRACK_MODE : $s = "UDM_PARAM_TRACK_MODE"; break;
    case UDM_PARAM_PHRASE_MODE : $s = "UDM_PARAM_PHRASE_MODE"; break;
    case UDM_PARAM_CHARSET : $s = "UDM_PARAM_CHARSET"; break;
    case UDM_PARAM_STOPFILE : $s = "UDM_PARAM_STOPFILE"; break;
    case UDM_PARAM_STOPTABLE : $s = "UDM_PARAM_STOPTABLE"; break;
    case UDM_PARAM_WEIGHT_FACTOR : $s = "UDM_PARAM_WEIGHT_FACTOR"; break;
    case UDM_PARAM_WORD_MATCH : $s = "UDM_PARAM_WORD_MATCH"; break;
    case UDM_PARAM_MIN_WORD_LEN : $s = "UDM_PARAM_MIN_WORD_LEN"; break;
    case UDM_PARAM_MAX_WORD_LEN : $s = "UDM_PARAM_MAX_WORD_LEN"; break;
    case UDM_PARAM_ISPELL_PREFIXES : $s = "UDM_PARAM_ISPELL_PREFIXES"; break;
    case UDM_PARAM_CROSS_WORDS : $s = "UDM_PARAM_CROSS_WORDS"; break;
    case UDM_PARAM_VARDIR : $s = "UDM_PARAM_VARDIR"; break;
    default: $s = "".$p."";
    }     
    return "[$s::$p]";
  }               
  
  function ReadConf() {
    if (!is_readable($this->IndexerConf)) {
      echo "ERROR: Can't open MnoGoSearch indexer configuration <".$this->IndexerConf.">";
      return false;
    }
    $this->ParseConf();
    return true;
  }

  function ParseConf() {
    $conf = file($this->IndexerConf);
    reset($conf);
    while (list($k,$v) = each($conf)) {
      $s = chop($v);
      if (strlen($s)>0) {
	$t = explode(" ",$s);
	switch (strtolower($t[0])) {
	case "dbaddr" : $this->db = $t[1]; $this->dbg("FTMnoGoSearch::Conf: db = [".$this->db."]"); break;
	case "vardir" : $this->vardir = $t[1]; $this->dbg("FTMnoGoSearch::Conf: vardir = [".$this->vardir."]"); break;
	case "localcharset" : $this->localcharset = $t[1]; $this->dbg("FTMnoGoSearch::Conf: localcharset = [".$this->localcharset."]"); break;
	case "crosswords" : $this->crossword = (strtolower($t[1])=="yes"?UDM_ENABLED:UDM_DISABLED); $this->dbg("FTMnoGoSearch::Conf: crossword = [".$this->crossword."]"); break;
	case "stopwordfile" : $this->stopwordfile = $t[1]; $this->dbg("FTMnoGoSearch::Conf: stopwordfile = [".$this->stopwordfile."]"); break;
	case "minwordlength" : $this->minwordlength = $t[1]; $this->dbg("FTMnoGoSearch::Conf: minwordlength = [".$this->minwordlength."]"); break;
	case "maxwordlength" : $this->maxwordlength = $t[1]; $this->dbg("FTMnoGoSearch::Conf: maxwordlength = [".$this->maxwordlength."]"); break;
	}
      }
    }
  }
  
}

?>