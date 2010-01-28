<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Class.TableLayout.php,v 1.2 2003/08/18 15:46:42 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package WHAT
 * @subpackage CORE
 */
 /**
 */

// ---------------------------------------------------------------------------
// $Id: Class.TableLayout.php,v 1.2 2003/08/18 15:46:42 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Class/Layout/Class.TableLayout.php,v $
// ---------------------------------------------------------------
// $Log: Class.TableLayout.php,v $
// Revision 1.2  2003/08/18 15:46:42  eric
// phpdoc
//
// Revision 1.1  2002/01/08 12:41:34  eric
// first
//
// Revision 1.13  2001/02/26 15:10:32  yannick
// Optimization
//
// Revision 1.12  2001/02/09 13:53:26  yannick
// Release 0.3.0
//
// Revision 1.11  2001/02/07 16:41:22  yannick
// Gestion des header et tris
//
// Revision 1.10  2001/02/07 13:16:17  yannick
// Retour à la page 0 sur tri
//
// Revision 1.9  2001/02/07 11:30:44  yannick
// Traitement résultat vide, 1 seule page
//
// Revision 1.8  2001/02/07 09:22:17  yannick
// Nombre de pages arrondi
//
// Revision 1.7  2001/02/06 16:23:28  yannick
// QueryGen : first release
//
// Revision 1.6  2001/01/15 11:29:25  marianne
// Parametrage du nom de la table
//
// Revision 1.5  2000/10/26 08:10:50  yannick
// Nettoyage
//
// Revision 1.4  2000/10/24 17:44:55  yannick
// Ajout du download
//
// Revision 1.3  2000/10/19 16:49:20  marc
// TableLayout utilise un lay fournit
//
// Revision 1.2  2000/10/19 10:58:54  yannick
// Gestion des utilisateurs
//
// Revision 1.1  2000/10/13 14:21:41  yannick
// Création
//
//
//
// ---------------------------------------------------------------------------
//

include_once('Class.Log.php');


class TableLayout 
{

// ---------------------------------------------------------------------------
// Public var
//

  var $table_name;         
  var $array;              // THE Array (2 dimensionnal) or
                           // array of objects

  // Fields attributes
  var $fields;             // array of the field names to show
  var $order_by;           // the current ordering field
  var $desc="";		   // the ordering =up or down
  var $sort_link;          // the URL used to perform a reordering of the table
                           // this URL is a format string with a %s where we 
                           // should give the column name

  // Header attributes
  var $headcontent;        // the content of the header
                           // if not set, the field string is used
  var $headsortfields;     // column with sorting capabilities

  // Footer attributes
  var $footcontent;        // content of the footer
                           // if not set, the field string is used

  // Hyperlinks
  var $links;              // array of links associated with fields, each link 
                           // is a composed with a dynamic url + an array of
                           // value that should replace %s format tag
                           // in the url using an sprintff function

  // Paging attributes 
  var $start = 0;          // the start index
  var $slice = 20;          // the slice size, zero means all
  var $page_numbering = 0; // if true a page number is displayed
  var $prev = "prev";  // the text (can be <img...) used to link to the
                           // previous page
  var $next = "next";       // the text (can be <img...) used to link to the
                           // next page
  var $first = "first";  // the text (can be <img...) used to link to the
                           // first page
  var $last = "last";       // the text (can be <img...) used to link to the
                           // last page
  var $page_link;          // the URL used to turn pages. This URL is a format 
                           // string with two %s in it the first on gives the
                           // index of the page start, the second gives the
                           // page size (number of elements in the page
  var $nb_tot=0;           // Total number of elements

  

  // ---------------------------------------------------------------------------
  // Private var

  var $row = 0;            // index of the current displayed row
  var $out;                // the output string
  var $paging_zone;
  var $header_zone;
  var $table_zone;
  var $footer_zone;

  //
  // ---------------------------------------------------------------------------
  // Public methods
  // ---------------------------------------------------------------------------
  //

  function TableLayout(&$lay,$table_name='TABLE') {
    $this->table_name=$table_name;
    $this->log = new Log("","TableLayout","");  
    $this->lay = &$lay;
  }

  //
  // ---------------------------------------------------------------------------
  // Private methods
  // ---------------------------------------------------------------------------
  //

  function Set() 
  {
    if ($this->start=="") $this->start=0;
    // check the table
    if (!is_array($this->array)) {
      return;
    }
      
    // init the fields to display
    $this->SelectColnames();

    // show the table
      
    $this->GenPaging();
    $this->GenHeader();
    $this->GenTable();
    $this->GenFooter();
    
  }
  

  function GenHeader() {

    if (!isset($this->headcontent)) return;
    reset($this->headcontent);
    while(list($k,$v) = each($this->headcontent))  {

      
      /* link ? */
      if (isset($this->headsortfields[$k])) {
        $value[0]=$this->headsortfields[$k];
        $value[1]="down";
        $value[2]=0;
        if ($this->order_by==$this->headsortfields[$k]) {
          $value[2]=0;
          if ($this->desc == "down") {
            $value[1]="up";
          }
        } 
        $v = $this->create_link($this->sort_link,$value,$v);
      }
      $this->lay->set("$k",$v);
    }

  }



    
  // ----------------------------------------------
  function GenTable() {
    $ind = 0; 
    reset($this->array);
    $tmparray="";
    while(list($key,$val) = each($this->array)) {
      if ($ind > $this->slice) break;
      if ((!is_array ($val)) && (!is_object ($val))) continue;
      
      reset($this->fields);
      while(list($k,$v) = each ($this->fields)) {
        if (is_object($val)) {
          $curval = $val->$v;
        } else {
          if (isset($val[$v])) {
            $curval = $val[$v];
          } else {
            $curval = "";
          }
        }
	if(!isset($this->links[$v])) {
          $tmparray[$ind][$v]=$curval;
	} else {
	  reset($this->links[$v][1]);
	  while(list($kk,$var) = each($this->links[$v][1])) {
            if (is_object($val)) {
              $value[$kk]=$val->$var;
            } else {
              if (isset($val[$var])) {
                $value[$kk]=$val[$var];
              } else {
                $value[$kk]="";
              }
            }
	  }
	  $link = $this->create_link($this->links[$v][0],$value,$curval);
          $tmparray[$ind][$v]=$link;
	}
      }
      $ind++;
    }
    reset($this->fields);
    while(list($k,$v) = each ($this->fields)) {
      $this->lay->SetBlockCorresp($this->table_name."BODY",$v,$v);
    }

    $this->lay->SetBlockData($this->table_name."BODY",$tmparray);
  }
  
  // ----------------------------------------------
  function GenFooter() {
    reset($this->fields);
    while(list($k,$v) = each($this->fields)) {
      if (isset($this->footcontent)) {
        if (isset($this->footcontent[$v])) {
          $val=$this->footcontent[$v];
        } else {
          continue;
        }
      } else {
        $val=$v;
      }
	
      $this->lay->set($v,$val);
    }
    return;
  }

   
  // ----------------------------------------------
  function GenPaging() {
    
    
    $link_first="";
    $link_last="";
    $link_next="";
    $link_prev="";
    $page_num=1;
    $page_tot=1;
    // Next/Prev pages
    if ($this->slice && 
        ($this->slice < $this->nb_tot) &&
        isset($this->page_link)) {
 
      $page_tot=(ceil(($this->nb_tot/$this->slice)*$this->slice)==$this->nb_tot)
        ?ceil($this->nb_tot/$this->slice)
        :ceil($this->nb_tot/$this->slice+1);
      $page_num=(int) ($this->start/$this->slice)+1;


      $values_first[0]=0;
      $values_first[1]=$this->slice;
      $values_last[0]=$this->nb_tot-($this->nb_tot -(($page_tot-1)*($this->slice)));
      $values_last[1]=$this->slice;
      if ($this->start-$this->slice >= 0) {
        $value[0]=$this->start-$this->slice;
        $value[1]=$this->slice;
        $link_first = $this->create_link($this->page_link,$values_first,$this->first);
        $link_prev = $this->create_link($this->page_link,$value,$this->prev);
      }
      if ($this->start+$this->slice < $this->nb_tot) {
        $value[0]=$this->start+$this->slice;
        $value[1]=$this->slice;
        $link_next = $this->create_link($this->page_link,$value,$this->next);
        $link_last = $this->create_link($this->page_link,$values_last,$this->last);
      }
    }  
    $this->lay->set($this->table_name."_PREV",$link_prev);
    $this->lay->set($this->table_name."_NEXT",$link_next);
    $this->lay->set($this->table_name."_FIRST",$link_first);
    $this->lay->set($this->table_name."_LAST",$link_last);
    $this->lay->set($this->table_name."_NUM",$page_num);
    $this->lay->set($this->table_name."_NB",$page_tot);
    
  }
    
    
  // ----------------------------------------------
  // Used if fields are not provided
  function SelectColnames() {
    if (isset($this->fields)) return;
    reset($this->array);
    list($key, $val) = each($this->array);
    if (is_object($val)) $val = get_object_vars($val);
    reset($val);
    while (list($k,$v) = each($val)) {
      $this->fields[]= $k;
    }
  }
  
  ////////////////////////////////////////////////////////////////
  // create_link : should be usefull for other classes
  //  this function is here because we don't know where we should put it
  //  so !!
  //
  function create_link($template,$values,$text) {
    $link = "<a href=\"".$template."\">";
    for ($i=0;$i<9;$i++) {
      if (!isset($values[$i])) $values[$i]="";
    }
    $link=sprintf($link,
                  $values[0],
                  $values[1],
                  $values[2],
                  $values[3],
                  $values[4],
                  $values[5],
                  $values[6],
                  $values[7],
                  $values[8]
                 );
    $link=$link.$text."</a>";
    return($link);
  }


}
?>
