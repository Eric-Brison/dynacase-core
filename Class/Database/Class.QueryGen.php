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
//  $Id: Class.QueryGen.php,v 1.3 2003/08/11 15:41:37 eric Exp $
//
// ---------------------------------------------------------------------------
// This class is designed to design easily pages with query/order elements
//
$CLASS_QUERYGEN_PHP = '$Id: Class.QueryGen.php,v 1.3 2003/08/11 15:41:37 eric Exp $';

include_once('Class.QueryDb.php');
include_once('Class.TableLayout.php');
include_once('Class.SubForm.php');

Class QueryGen {


var $table;
var $start;
var $slice;
var $order_by;
var $desc;
var $criteria=array();
var $operator;
var $value;
var $connector;
var $level;
var $fulltext="";
var $fulltextfields=array();
var $freedata="";

var $fulltextform='
<form name="fulltext" method="post" 
                      action="javascript:set_form_par(\'query\',\'fulltext\',self.document.fulltext.text.value,0);set_form_par(\'query\',\'start\',0,0);set_form_par(\'query\',\'all\',\'\',1);"
                      onreset="javascript:set_form_par(\'query\',\'fulltext\',\'\',0);set_form_par(\'query\',\'start\',0,0);set_form_par(\'query\',\'all\',\'\',1);">
  <input name="text" type="text" value="%s" size="10">
</form>';

var $up="&nbsp;^";
var $down="&nbsp;v";

function QueryGen  ($dbaccess,$class,&$action) {
  // 
  $this->log = new Log("","QueryGen","$class");
  $this->query = new QueryDb($dbaccess,$class);

  $this->dbaccess = $dbaccess;

  $this->action =& $action;

  $this->action_name = GetHttpVars("sact",$action->name);
  $this->app_name = GetHttpVars("sapp",$action->parent->name);

  if ($this->action_name != $action->name) {
     $app = new Application($action->dbaccess);
     $app->Set($this->app_name,$action->parent);
     $this->action = new Action($action->dbaccess);
     $this->action->Set($this->action_name,$app); 
     
  }
  // Init all query params

  $this->Init("order_by",$this->query->basic_elem->order_by);
  $this->Init("desc","down");
  $this->Init("start",0);
  $this->Init("slice",10);
  $this->Init("fulltext","");
  $this->Init("freedata","");
  $this->fulltextfields=$this->query->basic_elem->fulltextfields;

  $i=0;
  while ($i<$this->slice) {
    $this->Init("criteria","",$i);
    if (($this->criteria=="") || ($this->criteria[$i]=="")) break;
    $this->Init("operator","",$i);
    $this->Init("value","",$i);
    $this->Init("connector","",$i);
    $this->Init("level","",$i);
    $i++;
  }

  // Init the query form (can be overlay by user)
  $this->baseurl=$action->GetParam("CORE_BASEURL");
  $this->action->lay->set("QUERY_FORM",
    $this->GenMainForm("query",0,0,
                       $this->baseurl."app=CORE&action=SETACTPAR&sole=Y")
                         );

  // Add Js Code that will be used to manage param modification
  $this->action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/query_paging.js");

  // Init a table layout (default. user can use its one)
  $this->table = new TableLayout($this->action->lay);
  $this->table->start = $this->start;
  $this->table->slice= $this->slice;

}

 function SetFullTextForm($text)
   {
     $this->table->lay->set("FULLTEXTFORM",sprintf($this->fulltextform,$text));
   }

function GenMainForm($name,$height,$width,$mainurl,$suburl="") {
   $this->form = new SubForm($name,$height,$width,$mainurl,$suburl);

   $this->form->SetParam("key","");
   $this->form->SetParam("start",$this->start);
   $this->form->SetParam("slice",$this->slice);
   $this->form->SetParam("order_by",$this->order_by);
   $this->form->SetParam("desc",$this->desc);
   $this->form->SetParam("fulltext",$this->fulltext);
   $this->form->SetParam("sapp",$this->action->parent->name);
   $this->form->SetParam("sact",$this->action->name);
   $this->form->SetParam("freedata",$this->freedata);

   $i=0;
   while ($i<$this->slice) {
     if (!isset($this->criteria[$i]) || $this->criteria[$i] == "") break;
     $this->form->SetParam("criteria_$i",$this->criteria[$i]);
     $this->form->SetParam("operator_$i",$this->operator[$i]);
     $this->form->SetParam("value_$i",$this->value[$i]);
     $this->form->SetParam("connector_$i",$this->connector[$i]);
     $this->form->SetParam("level_$i",$this->level[$i]);
     $i++;
   }
   return($this->form->GetMainForm());
}

 function Query($type="TABLE") {

   $this->query->order_by=$this->order_by;
   $this->query->desc=$this->desc;
   $this->AddFulltextQuery();
   $this->table->array = $this->query->Query($this->start,$this->slice,$type);
   $this->table->nb_tot = $this->query->nb;
   if ($this->table->nb_tot == 0) {
     $this->table->array=array();
   }

   // Layout elements
   $this->table->page_link= "javascript:set_form_par('query','start','%s',1);";
   $this->table->sort_link= "javascript:set_form_par('query','order_by','%s',0);set_form_par('query','desc','%s',0);set_form_par('query','start','%s',0);set_form_par('query','all','',1);";
   $this->table->desc=$this->desc;
   $this->table->order_by=$this->order_by;
   $this->table->slice=$this->slice;
   $this->table->prev=$this->action->GetIcon("prev.png","prev");
   $this->table->next=$this->action->GetIcon("next.png","next");
   $this->table->first=$this->action->GetIcon("first.png","first");
   $this->table->last=$this->action->GetIcon("last.png","last");
   $this->up=$this->action->GetIcon("up.png","up");
   $this->down=$this->action->GetIcon("down.png","down");
   $this->SetFullTextForm($this->fulltext);

   // color row table
   $this->table->fields[]="CLASS";
   reset ($this->table->array);
   while(list($k,$v) = each($this->table->array)) {
     $this->table->array[$k]["CLASS"]=($k%2)?"TABBackground":"";
   }
  
   reset ($this->table->array);
   if (($this->order_by != "") && ($this->desc != "")) {
     $desc=$this->desc;
     reset($this->table->headsortfields);
     while(list($k,$v)=each($this->table->headsortfields)) {
       if ($this->order_by == $v) {
         $this->table->headcontent[$k] .= $this->$desc;
       }
     }
   }
 }


function AddFulltextQuery()
{
  if (($this->fulltext != "") && (sizeof($this->fulltextfields)>0)) {
    reset ($this->fulltextfields);
    $sql="(";
    while (list($k,$v)=each ($this->fulltextfields)) {
      $sql .= " upper($v) like '%".strtoupper($this->fulltext)."%' OR";
    }
    $sql =substr($sql,0,-2).")";
    $this->AddQuery($sql);    
  }
}

function Init($key,$defval,$ind="") {

   if ($ind == "") {
     $this->$key = GetHttpVars("$key",$this->action->ActRead($key,$defval));
     $this->action->ActRegister("$key",$this->$key);
   } else {
     $this->$key[$ind] = GetHttpVars("$key_$ind",$this->action->ActRead("$key_$ind",$defval));
     $this->action->ActRegister("$key_$ind",$this->$key[$ind]);
   }

}
    

  function AddQuery($contraint) {
    $this->query->AddQuery($contraint);
  }


}
?>
