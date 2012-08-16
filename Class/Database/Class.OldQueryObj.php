<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: Class.OldQueryObj.php,v 1.2 2003/08/18 15:46:42 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage CORE
 */
/**
 */
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
//  $Id: Class.OldQueryObj.php,v 1.2 2003/08/18 15:46:42 eric Exp $
//  $Log: Class.OldQueryObj.php,v $
//  Revision 1.2  2003/08/18 15:46:42  eric
//  phpdoc
//
//  Revision 1.1  2002/01/08 12:41:34  eric
//  first
//
//  Revision 1.1  2001/01/25 03:21:38  marianne
//  initialisation
//
// ---------------------------------------------------------------------------
// This class is designed to perform query constructs on objects
// It returns a table of objects that match the query
//
// It gives the HTML/JScript element for the gui
// and it gives the result of the query
$CLASS_OLDQUERYOBJ_PHP = '$Id: Class.OldQueryObj.php,v 1.2 2003/08/18 15:46:42 eric Exp $';

include_once ('Class.Out.php');
include_once ('Class.Table.php');
include_once ('Class.Log.php');

class OldQueryObj
{
    
    var $nb = 0;
    var $LastQuery = "";
    
    var $table;
    
    var $operators = array(
        "none" => array(
            "lib" => " --",
            "oper" => "",
            "param" => "NONE"
        ) ,
        "begin" => array(
            "lib" => "Commence par",
            "oper" => "like",
            "param" => "SEMIPERCENT"
        ) ,
        "like" => array(
            "lib" => "Contient",
            "oper" => "like",
            "param" => "PERCENT"
        ) ,
        "nlike" => array(
            "lib" => "Ne Contient Pas",
            "oper" => "not like",
            "param" => "PERCENT"
        ) ,
        "=" => array(
            "lib" => "Est égal à",
            "oper" => "=",
            "param" => "NORMAL"
        ) ,
        "!=" => array(
            "lib" => "Est différent de",
            "oper" => "!=",
            "param" => "NORMAL"
        ) ,
        ">" => array(
            "lib" => "Est Supérieur à",
            "oper" => ">",
            "param" => "NORMAL"
        ) ,
        "<" => array(
            "lib" => "Est Inférieur à",
            "oper" => "<",
            "param" => "NORMAL"
        ) ,
        "notn" => array(
            "lib" => "N'est pas Vide",
            "oper" => "is not null",
            "param" => "NONE"
        ) ,
        "null" => array(
            "lib" => "Est Vide",
            "oper" => "is null",
            "param" => "NONE"
        )
    );
    
    var $down = "&nbsp;v";
    var $up = "&nbsp;^";
    
    var $display_alpha = "FALSE";
    var $alpha_default = "NONE";
    var $distinct = "distinct";
    
    function OldQueryObj($dbaccess, $class)
    {
        //
        $this->log = new Log("", "Query", "$class");
        $this->log->debug("Nouveau QueryObj $dbaccess/$class");
        $this->basic_elem = new $class($dbaccess);
        
        $this->dbaccess = $dbaccess;
        $this->class = $class;
        $this->init("string", "");
        $this->init("operator", "none");
        $this->init("criteria", "");
        $this->init("case", "");
        $this->init("order_by", "");
        $this->init("casse", "NON");
        $this->init("alpha_criteria", "");
        $this->init("desc", "down");
        $this->init("start", 0);
        $this->init("slice", 20);
        
        $this->table = new Table();
        $this->table->start = $this->start;
        $this->table->slice = $this->slice;
    }
    // Perform the query : the result can be a table or a list of objects
    // depending on the third arg.
    //   the third ARG should be :
    //         LIST  : means a table of objects
    //         LISTC : means a table of completed objects
    //         TABLE : means a table of table fields
    function Query($start = 0, $slice = 0, $res_type = "LIST", $p_query = "")
    {
        if ($p_query == '') {
            // select construct
            $select = "";
            reset($this->basic_elem->fields);
            while (list($k, $v) = each($this->basic_elem->fields)) {
                $select = $select . " " . $this->basic_elem->dbtable . "." . $v . ",";
            }
            
            reset($this->basic_elem->sup_fields);
            while (list($k, $v) = each($this->basic_elem->sup_fields)) {
                $select = $select . " " . $v . ",";
            }
            $select = substr($select, 0, strlen($select) - 1);
            // from
            $from = $this->basic_elem->dbtable;
            reset($this->basic_elem->sup_tables);
            while (list($k, $v) = each($this->basic_elem->sup_tables)) {
                $from = $from . "," . $v;
            }
            
            $query = "select " . $this->distinct . " {$select}
              from {$from} ";
            
            $nb_where = 0;
            $where[$nb_where] = $this->CriteriaClause();
            if ($where[$nb_where] != "") {
                $nb_where++;
            }
            $where[$nb_where] = $this->AlphaClause();
            if ($where[$nb_where] != "") {
                $nb_where++;
            }
            $where[$nb_where] = $this->SupClause();
            if ($where[$nb_where] != "") {
                $nb_where++;
            }
            
            if ($nb_where > 0) {
                $i = 0;
                $query = $query . ' where ';
                reset($where);
                while (list($k, $v) = each($where)) {
                    if ($v != "") {
                        if ($i == 0) {
                            $query = $query . $v;
                        } else {
                            $query = $query . ' AND ' . $v;
                        }
                        $i++;
                    }
                }
            }
            // Order by
            if ($this->order_by != "") {
                $query = $query . " order by " . $this->order_by;
                if ($this->desc == "up") {
                    $query = $query . " desc";
                }
            }
            $query = $query . ';';
            $query = $query . ';';
        } else {
            $query = $p_query;
        }
        $this->dbid = pg_connect("{$this->dbaccess}");
        ### echo $this->dbaccess;
        $chrono = microtime();
        ###$this->log->debug("Requète : $query / $chrono");
        $this->LastQuery = $query;
        ### echo "<br>$query";
        $res = pg_exec($this->dbid, $query);
        
        if (!$res) return FALSE;
        
        $this->nb = pg_numrows($res);
        
        if ($this->nb == 0) return FALSE;
        $chrono = microtime();
        $this->log->debug("Résultats : {$this->nb} / $chrono");
        
        if ($start >= $this->nb) {
            $start = 0;
        }
        if ($slice == 0) {
            $slice = $this->nb;
        }
        
        if (($start + $slice) >= $this->nb) {
            $end = $this->nb;
        } else {
            $end = $start + $slice;
        }
        $this->log->debug("Extraction de " . $start . " à " . $end);
        for ($c = 0; $c < $this->nb; $c++) {
            if (($c >= $start) && ($c < $end)) {
                $result = pg_fetch_array($res, $c);
                $this->log->debug("Affectation de " . $c);
                if (($res_type == "LIST") || ($res_type == "LISTC")) {
                    $this->list[$c] = new $this->class($this->dbaccess, "", $result, $this->dbid);
                } else {
                    while (list($k, $v) = each($result)) {
                        $this->list[$c][$k] = $v;
                    }
                }
            } else {
                $this->list[$c] = "";
            }
        }
        $chrono = microtime();
        $this->log->debug("Affectation du tableau : $chrono");
        return ($this->list);
    }
    
    function CriteriaClause()
    {
        $out = "";
        if (($this->criteria != "") && ($this->operator != "none")) {
            if ($this->casse == "NON") {
                $out = $out . " upper(" . $this->criteria . ") " . $this->operators[$this->operator]["oper"];
            } else {
                $out = $out . $this->criteria . " " . $this->operators[$this->operator]["oper"];
            }
            $string = "";
            switch ($this->operators[$this->operator]["param"]) {
                case "NORMAL":
                    $string = " '{$this->string}'";
                    break;

                case "PERCENT":
                    $string = " '%{$this->string}%'";
                    break;

                case "SEMIPERCENT":
                    $string = " '{$this->string}%'";
            }
            if (($this->operator != 'null') && ($this->operator != 'notn')) {
                if ($this->casse == "NON") {
                    $out.= " upper({$string})";
                } else {
                    $out.= $string;
                }
            }
        }
        return ($out);
    }
    
    function AlphaClause()
    {
        $out = "";
        if (($this->display_alpha == "TRUE") && ($this->alpha != "ALL") && ($this->alpha != "NONE")) {
            $out = " upper({$this->alpha_criteria}) like upper('{$this->alpha}%') ";
        }
        return ($out);
    }
    
    function SupClause()
    {
        $out = "";
        $this->log->debug("Supplementaires");
        if (sizeof($this->basic_elem->sup_where) > 0) {
            reset($this->basic_elem->sup_where);
            $count = 0;
            while (list($k, $v) = each($this->basic_elem->sup_where)) {
                if ($count > 0) {
                    $out = $out . " AND " . $v;
                } else {
                    $out = $out . " " . $v;
                }
                $count++;
            }
        }
        return ($out);
    }
    // Create a complete page with the form, the table, the links...
    //  url = url called back on next/prev/start/end button (the calling page)
    //  nores = HTML message displayed when the query result is empty
    //  complete = boolean : say TRUE if you need that the object list is completely
    //   initiated (means that some attributes of the object are checked out from
    //   different tables). say FALSE if all the fields you need are checked out in
    //   the query. If you use complete on large results, it may take a while and
    //   a lot of memory.
    function GenPage($url, $nores, $complete = "FALSE", $display_form = "YES")
    {
        
        $this->init("alpha", $this->alpha_default);
        
        $this->table->start = $this->start;
        $this->table->slice = $this->slice;
        
        $o = new Out();
        
        $o->cat($this->GenForm($url, $display_form));
        
        if (($this->display_alpha == "TRUE") && ($this->alpha == "NONE")) {
            return ($o->flush());
        }
        
        if ($complete == "FALSE") {
            $this->table->array = $this->Query($this->start, $this->slice, "TABLE");
        } else {
            $this->table->array = $this->Query($this->start, $this->slice, "LISTC");
            $this->table->arrayobj = "TRUE";
        }
        
        $this->table->sort_link = "javascript:void(0)\\\" onClick=\\\"SortPage(
                           document.QueryObj,'%s');return false";
        $this->table->page_link = "javascript:void(0)\\\" onclick=\\\"GoPage(
                           document.QueryObj,%s,%s);return false";
        
        if (!isset($this->table->headcontent["$this->order_by"])) {
            $this->table->headcontent["$this->order_by"] = $this->order_by;
        }
        $desc = $this->desc;
        $this->table->headcontent["$this->order_by"].= $this->$desc;
        
        if ($this->nb == 0) {
            $o->cat($nores);
        } else {
            $o->cat($this->table->get());
        }
        
        $o->cat('<table border=0 cellspacing=0 cellpadding=0 width="100%">
           <tr><td class="textinv2">Total : ' . $this->nb . '</td></tr>
           </table>');
        
        return ($o->flush());
    }
    // Generate the Query Form
    function GenForm($url, $display_form = "YES")
    {
        
        if (sizeof($this->basic_elem->criterias) == 0) return ("");
        $this->log->debug("Nouvelle forme");
        
        $o = new Out();
        // JavaScript Construct
        $o->cat('
        <script language="javascript">
          function selFiltre(form_cour, champ) {
            var oper_sel=form_cour.p_operator[form_cour.p_operator.selectedIndex].value;
            var crit_sel=form_cour.p_criteria[form_cour.p_criteria.selectedIndex].value;
            var crit_idx=form_cour.p_criteria.selectedIndex;
            tab_types = new Array();');
        
        $ind = 0;
        // table of types
        reset($this->basic_elem->criterias);
        while (list($k, $v) = each($this->basic_elem->criterias)) {
            $o->cat("tab_types[{$ind}]=\"" . $v["type"] . "\";");
            $ind++;
        }
        
        $o->cat('
            if ((tab_types[crit_idx]=="NUM") && (oper_sel=="nlike")) {
              alert ("Un champ numérique ne peut-être recherché avec \'Ne Contient Pas\'!!");
              form_cour.p_operator.options[0].selected=true;
            }
            if ((tab_types[crit_idx]=="NUM") && (oper_sel=="like")) {
              alert ("Un champ numérique ne peut-être recherché avec \'Contient\'!!");
              form_cour.p_operator.options[0].selected=true;
            }
            if ((crit_sel=="") && (champ.name=="p_criteria")) {
              form_cour.p_string.value = "";
              form_cour.p_operator.options[0].selected=true;
            }
           }');
        
        $o->cat('
         function checkFiltre(form_cour) {
    ');
        if ($display_form == 'YES') {
            $o->cat('
           var ok=true;
           var oper_sel=form_cour.p_operator[form_cour.p_operator.selectedIndex].value;
           var crit_sel=form_cour.p_criteria[form_cour.p_criteria.selectedIndex].value;
           var casse=form_cour.chk_casse.checked;
           if (!casse) { 
             form_cour.p_casse.value="NON";
           } else { 
             form_cour.p_casse.value="OUI";
           }
           if ((crit_sel=="") && (oper_sel!="")) { 
             ok=false;
             alert ("Vous devez choisir un critere!!"); }
           if ((crit_sel!="") && (oper_sel=="")) { 
             ok=false;
             alert ("Vous devez choisir un operator!!"); }
           return(ok);
         }
       ');
        } else {
            $o->cat('  var ok=true;  return(ok); }');
        }
        $o->cat(' 

         function queryFiltre(form_cour) {

           ok = checkFiltre(form_cour);
           if (ok) {
              form_cour.p_start.value=0;
              form_cour.submit();
           }
         }

         function resetFiltre(form_cour) {
           form_cour.p_criteria.selectedIndex=0;
           form_cour.p_operator.selectedIndex=0;
           form_cour.p_start.value=0;
           form_cour.p_string.value="";
           form_cour.submit(); 
         }

         function GoPage(form_cour,start,slice) {
           form_cour.p_start.value = start;
           form_cour.p_slice.value = slice;
           ok=checkFiltre(form_cour);
           if (ok) {form_cour.submit();}
         }

         function SortPage(form_cour,order) {
           if (form_cour.p_order_by.value == order) {
             if (form_cour.p_desc.value == "down") {
               form_cour.p_desc.value = "up"; 
             } else {
               form_cour.p_desc.value = "down";
             }
           } else {
             form_cour.p_order_by.value = order;
             form_cour.p_desc.value = "down";
           }
           ok=checkFiltre(form_cour);
           if (ok) {form_cour.submit();}
         }

         function setAlpha(form_cour,car) {
           form_cour.p_alpha.value = car;
           form_cour.p_start.value = 0;
           ok=checkFiltre(form_cour);
           if (ok) {form_cour.submit();}
         }

          </script>');
        // Form Construct
        $o->cat('
        <form name="QueryObj" method="POST" action="' . $url . '">');
        if ($display_form == "YES") {
            $o->cat('<table bgcolor="#F2F2F2" border="0" cellspacing="0" cellpadding="2" >
            <tr>');
            // Alpha Index
            if ($this->display_alpha == "TRUE") {
                $o->cat('<td><small><select name="p_alpha_criteria">\n');
                reset($this->basic_elem->criterias);
                while (list($k, $v) = each($this->basic_elem->criterias)) {
                    if ($v["type"] == "TXT") {
                        if ($this->alpha_criteria == $k) {
                            $o->cat("<OPTION SELECTED VALUE=\"{$k}\">" . $v["libelle"] . "\n");
                        } else {
                            $o->cat("<OPTION VALUE=\"{$k}\">" . $v["libelle"] . "\n");
                        }
                    }
                }
                $o->cat('</select></small></td>');
                $o->cat('<td nowrap colspan="3">');
                for ($i = ord("A"); $i <= ord("Z"); $i++) {
                    $c = chr($i);
                    $o->cat(' ');
                    if ($this->alpha == $c) {
                        $o->cat('<b>[');
                    }
                    $o->cat("<a href=\"javascript:void(0)\" 
                  onClick=\"setAlpha(document.QueryObj,'" . $c . "');return false\">");
                    $o->cat('<font size="-1">' . $c . '</font></a>');
                    if ($this->alpha == $c) {
                        $o->cat(']</b>');
                    }
                }
                $o->cat("</td><td align=\"center\">");
                if ($this->alpha == "ALL") $o->cat('<b>[');
                $o->cat("<a href=\"javascript:void(0)\"
                  onClick=\"setAlpha(document.QueryObj,'ALL');return false\">");
                $o->cat('<font size="-1">Tout</font></a>');
                if ($this->alpha == "ALL") $o->cat(']</b>');
                $o->cat("<br>");
                if ($this->alpha == "NONE") {
                    $o->cat('<b>[');
                }
                $o->cat("<a href=\"javascript:void(0)\"
                  onClick=\"setAlpha(document.QueryObj,'NONE');return false\">");
                $o->cat("<font size=\"-1\">Rien</font></a>");
                if ($this->alpha == "NONE") {
                    $o->cat(']</b>');
                }
                $o->cat("</tr>");
            }
            // Criteria
            $o->cat("<tr>
        <TD><SMALL><SELECT NAME=\"p_criteria\"
                           onChange=\"selFiltre(document.QueryObj,this);\">\n");
            reset($this->basic_elem->criterias);
            while (list($k, $v) = each($this->basic_elem->criterias)) {
                if ($this->criteria == $k) {
                    $o->cat("<OPTION SELECTED VALUE=\"{$k}\">" . $v["libelle"] . "\n");
                } else {
                    $o->cat("<OPTION VALUE=\"{$k}\">" . $v["libelle"] . "\n");
                }
            }
            $o->cat("</SELECT></SMALL></TD>\n");
            // Operator
            $o->cat("
        <TD><SMALL><SELECT NAME=\"p_operator\"
                           onChange=\"selFiltre(document.QueryObj,this);\">\n");
            while (list($k, $v) = each($this->operators)) {
                if ($this->operator == $k) {
                    $o->cat("<OPTION SELECTED VALUE=\"{$k}\">" . $v["lib"] . "\n");
                } else {
                    $o->cat("<OPTION VALUE=\"{$k}\">" . $v["lib"] . "\n");
                }
            }
            $o->cat("</SELECT></SMALL></TD>\n");
            // String (value)
            $o->cat('
        <td><small><input name="p_string" maxlength=15 size=10 class="finput"
                          value="' . $this->string . '">
        </td>');
            // Query buttons
            $o->cat('
        <td><a href="javascript:void(0)" 
               onClick="queryFiltre(document.QueryObj);return false">
            <font size="-1">Recherche</font></a>
            <a href="javascript:void(0)"
               onClick="resetFiltre(document.QueryObj);return false">     
            <font size="-1">Réinitialiser</font></a>
        </td>');
            // Case sensitivity
            $o->cat('
        <td nowrap><input type="checkbox"');
            if ($this->casse == "OUI") {
                $o->cat(' checked ');
            }
            $o->cat('name="chk_casse"><font size="-1">Respecter la casse</font></td>');
            // TODO
            // Form end
            $o->cat('
        </tr></table>');
        }
        $o->cat('<input type="hidden" name="p_slice" value="' . $this->slice . '">
        <input type="hidden" name="p_start" value="' . $this->start . '">
        <input type="hidden" name="p_order_by" value="' . $this->order_by . '">
        <input type="hidden" name="p_casse" value="' . $this->casse . '">
        <input type="hidden" name="p_desc" value="' . $this->desc . '">
        <input type="hidden" name="p_alpha" value="' . $this->alpha . '">
        </form>');
        
        return ($o->flush());
    }
    
    function init($attr, $default)
    {
        global $HTTP_POST_VARS;
        if (is_array($HTTP_POST_VARS) && isset($HTTP_POST_VARS["p_$attr"])) {
            $this->$attr = $HTTP_POST_VARS["p_$attr"];
        } else {
            if (isset($this->basic_elem->def[$attr])) {
                $this->$attr = $this->basic_elem->def[$attr];
            } else {
                $this->$attr = $default;
            }
        }
    }
}
?>
