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
//  $Id: Class.QueryObj.php,v 1.1 2002/01/08 12:41:34 eric Exp $
//  $Log: Class.QueryObj.php,v $
//  Revision 1.1  2002/01/08 12:41:34  eric
//  first
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
$CLASS_QUERYOBJ_PHP = '$Id: Class.QueryObj.php,v 1.1 2002/01/08 12:41:34 eric Exp $';

include_once('Class.Out.php');
include_once('Class.Table.php');
include_once('Class.Log.php');
include_once('Class.QueryDb.php');

Class QueryObj extends QueryDb {



var $down="&nbsp;v";
var $up="&nbsp;^";

var $display_alpha="FALSE";
var $alpha_default="NONE";

function QueryObj ($dbaccess,$class) 
    {
      // 
      $this->init("string","");
      $this->init("operator","none");
      $this->init("criteria","");
      $this->init("case","");
      $this->init("order_by","");
      $this->init("casse","NON");
      $this->init("alpha_criteria","");
      $this->init("desc","down");
      $this->init("start",0);
      $this->init("slice",20);

      $this->table = new Table();
      $this->table->start=$this->start;
      $this->table->slice=$this->slice;
      $this->Query($dbaccess,$class);
    }

  function AlphaClause() {
    $out = "";  
    if (($this->display_alpha == "TRUE") && 
        ($this->alpha != "ALL") &&
        ($this->alpha != "NONE")) {
      $out = " upper({$this->alpha_criteria}) like upper('{$this->alpha}%') ";
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

function GenPage($url,$nores,$complete="FALSE",$display_form="YES") {    


  $this->init("alpha",$this->alpha_default);

  $this->table->start=$this->start;
  $this->table->slice=$this->slice;

  $o = new Out();

  $o->cat ($this->GenForm($url,$display_form));
  
  if (($this->display_alpha == "TRUE") && ($this->alpha == "NONE")) {
     return($o->flush());
  }

  if ($complete == "FALSE") {
     $this->table->array = $this->Query($this->start,$this->slice,"TABLE");
  } else {
     $this->table->array = $this->Query($this->start,$this->slice,"LISTC");
     $this->table->arrayobj = "TRUE";
  }

  $this->table->sort_link="javascript:void(0)\\\" onClick=\\\"SortPage(
                           document.QueryObj,'%s');return false";
  $this->table->page_link="javascript:void(0)\\\" onclick=\\\"GoPage(
                           document.QueryObj,%s,%s);return false";

  if (!isset($this->table->headcontent["$this->order_by"])) {
     $this->table->headcontent["$this->order_by"] = $this->order_by;
  }
  $desc = $this->desc;
  $this->table->headcontent["$this->order_by"] .= $this->$desc;
    

  if ($this->nb == 0) {
    $o->cat($nores);
  } else {  
    $o->cat($this->table->get());
  }

  $o->cat('<table border=0 cellspacing=0 cellpadding=0 width="100%">
           <tr><td class="textinv2">Total : '.$this->nb.'</td></tr>
           </table>');

  return ($o->flush());

}

// Generate the Query Form
  function GenForm($url,$display_form="YES")
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

       $ind=0;
       // table of types
       reset($this->basic_elem->criterias);
       while (list($k,$v) = each ($this->basic_elem->criterias)) {
         $o->cat("tab_types[{$ind}]=\"".$v["type"]."\";");
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
  if ($display_form=='YES') {
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
        <form name="QueryObj" method="POST" action="'.$url.'">');
     if ($display_form=="YES") {
          $o->cat('<table bgcolor="#F2F2F2" border="0" cellspacing="0" cellpadding="2" >
            <tr>');

       // Alpha Index
       if ($this->display_alpha == "TRUE") {
         $o->cat('<td><small><select name="p_alpha_criteria">\n');
         reset($this->basic_elem->criterias);
         while (list($k,$v) = each ($this->basic_elem->criterias)) {
           if ($v["type"] == "TXT") {
             if ($this->alpha_criteria==$k) {
               $o->cat("<OPTION SELECTED VALUE=\"{$k}\">".$v["libelle"]."\n");
             } else {
               $o->cat("<OPTION VALUE=\"{$k}\">".$v["libelle"]."\n");
             }
           }
         }
         $o->cat('</select></small></td>');
         $o->cat('<td nowrap colspan="3">');    
           for ($i=ord("A");$i<=ord("Z");$i++) { 
             $c = chr($i);
             $o->cat(' '); 
             if ($this->alpha == $c) { $o->cat('<b>['); }
             $o->cat("<a href=\"javascript:void(0)\" 
                  onClick=\"setAlpha(document.QueryObj,'".$c."');return false\">");
             $o->cat('<font size="-1">'.$c.'</font></a>');
             if ($this->alpha == $c) { $o->cat(']</b>'); }
           }
         $o->cat("</td><td align=\"center\">");
         if ($this->alpha == "ALL") $o->cat('<b>[');
         $o->cat("<a href=\"javascript:void(0)\"
                  onClick=\"setAlpha(document.QueryObj,'ALL');return false\">");
         $o->cat('<font size="-1">Tout</font></a>');
         if ($this->alpha == "ALL") $o->cat(']</b>');
         $o->cat("<br>");
         if ($this->alpha == "NONE") { $o->cat('<b>['); }
         $o->cat("<a href=\"javascript:void(0)\"
                  onClick=\"setAlpha(document.QueryObj,'NONE');return false\">");
         $o->cat("<font size=\"-1\">Rien</font></a>");
         if ($this->alpha == "NONE") { $o->cat(']</b>'); }
         $o->cat("</tr>");
       }

       // Criteria
       $o->cat("<tr>
        <TD><SMALL><SELECT NAME=\"p_criteria\"
                           onChange=\"selFiltre(document.QueryObj,this);\">\n");
       reset($this->basic_elem->criterias);
       while (list($k,$v) = each ($this->basic_elem->criterias)) {
         if ($this->criteria==$k) {
           $o->cat("<OPTION SELECTED VALUE=\"{$k}\">".$v["libelle"]."\n");
         } else {
           $o->cat("<OPTION VALUE=\"{$k}\">".$v["libelle"]."\n");
         }
       }
       $o->cat("</SELECT></SMALL></TD>\n");    

       // Operator
       $o->cat("
        <TD><SMALL><SELECT NAME=\"p_operator\"
                           onChange=\"selFiltre(document.QueryObj,this);\">\n");
       while (list($k,$v) = each ($this->operators)) {
         if ($this->operator==$k) {
           $o->cat("<OPTION SELECTED VALUE=\"{$k}\">".$v["lib"]."\n");
         } else {
           $o->cat("<OPTION VALUE=\"{$k}\">".$v["lib"]."\n");
         }
       }
       $o->cat("</SELECT></SMALL></TD>\n");

       // String (value)
       $o->cat('
        <td><small><input name="p_string" maxlength=15 size=10 class="finput"
                          value="'.$this->string.'">
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
       if ($this->casse=="OUI") { $o->cat(' checked '); }
       $o->cat('name="chk_casse"><font size="-1">Respecter la casse</font></td>'); 

       // TODO

       // Form end
       $o->cat('
        </tr></table>');
     }
        $o->cat('<input type="hidden" name="p_slice" value="'.$this->slice.'">
        <input type="hidden" name="p_start" value="'.$this->start.'">
        <input type="hidden" name="p_order_by" value="'.$this->order_by.'">
        <input type="hidden" name="p_casse" value="'.$this->casse.'">
        <input type="hidden" name="p_desc" value="'.$this->desc.'">
        <input type="hidden" name="p_alpha" value="'.$this->alpha.'">
        </form>');

       return($o->flush());

    }
  
  function init($attr,$default)
    {
      global $HTTP_POST_VARS;
      if (is_array($HTTP_POST_VARS) && isset($HTTP_POST_VARS["p_$attr"])) {
        $this->$attr=$HTTP_POST_VARS["p_$attr"];
      } else { 
        if (isset($this->basic_elem->def[$attr])) {
           $this->$attr=$this->basic_elem->def[$attr];
        } else {
           $this->$attr=$default;
        }
      }
    }

}
?>
