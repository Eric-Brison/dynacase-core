<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: Class.Form.php,v 1.2 2003/08/18 15:46:42 eric Exp $
 * @package FDL
 * @subpackage CORE
 */
/**
 */
// ---------------------------------------------------------------------------
// anakeen 2000 - Marianne Le Briquer
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
//  $Id: Class.Form.php,v 1.2 2003/08/18 15:46:42 eric Exp $
//  $Log: Class.Form.php,v $
//  Revision 1.2  2003/08/18 15:46:42  eric
//  phpdoc
//
//  Revision 1.1  2002/01/08 12:41:34  eric
//  first
//
//  Revision 1.2  2001/02/11 10:53:33  marianne
//  Ajout Img + maj diverses
//
//  Revision 1.9  2000/08/21 10:55:13  marianne
//  Ajout Newline et Hr
//
//  Revision 1.8  2000/07/29 10:27:15  marc
//  Ajout attr wrap dans textarea
//
//  Revision 1.7  2000/06/21 18:10:37  yannick
//  Pas de lignes blanches
//
//  Revision 1.6  2000/06/06 15:12:42  marianne
//  Nouvelles fonctions...
//
//  Revision 1.1  2000/05/29 08:36:51  yannick
//  Creation
//
// ---------------------------------------------------------------------------
$CLASS_FORM_PHP = '$Id';

class Form
{
    
    var $cell_class;
    var $label_class;
    var $titre_class;
    
    function Form($ClassLabel = "label3", $ClassTitre = "titre3", $ClassCell = "tab1")
    {
        $this->cell_class = $ClassCell;
        $this->label_class = $ClassLabel;
        $this->titre_class = $ClassTitre;
        return TRUE;
    }
    
    function Hidden($name, $value = '')
    {
        $src = "<INPUT TYPE=\"hidden\" NAME=\"$name\" VALUE=\"$value\">\n";
        return $src;
    }
    function Img($src, $alt = "", $border = 0, $vspace = 0, $hspace = 0)
    {
        $walt = "";
        if ($alt != "") $walt = "ALT=\"$alt\"";
        $src = "<IMG SRC=\"$src\" BORDER=\"$border\" $walt VSPACE=$vspace HSPACE=$hspace>\n";
        return $src;
    }
    function Table($border = 0, $width = "100%", $align = "", $space = 0, $padd = 0)
    {
        $walign = "";
        if ($align != "") $walign = "ALIGN=\"$align\"";
        $src = "<TABLE BORDER=\"$border\" WIDTH=\"$width\" $walign CELLSPACING=\"$space\" CELLPADDING=\"$padd\">\n";
        return $src;
    }
    
    function Hr($width = "100%", $align = "")
    {
        $walign = "";
        if ($align != "") $walign = "ALIGN=\"$align\"";
        $src = "<HR WIDTH=\"$width\" $walign>\n";
        return $src;
    }
    
    function NewLine($class = "")
    {
        if ($class == "") {
            return "</TR>\n<TR>\n";
        } else {
            return "</TR>\n<TR CLASS=\"$class\" >\n";
        }
        return $src;
    }
    
    function LibCell($libelle, $value, $class = "", $align = "LEFT", $span = 1)
    {
        $src = "\t<TD CLASS=\"{$this->label_class}\" ALIGN=\"$align\" COLSPAN=$span>$libelle\n";
        if ($class == "") {
            $src.= "\t<DIV CLASS=\"{$this->titre_class}\" >&nbsp;$value</DIV></TD>\n";
        } else {
            $src.= "\t<DIV CLASS=\"$class\" >&nbsp;$value</DIV></TD>\n";
        }
        return $src;
    }
    function LibCells($libelle, $value, $classval = "", $classlib = "", $align = "LEFT")
    {
        if ($classlib == "") $classlib = $this->label_class;
        if ($classval == "") $classval = $this->cell_class;
        $src = "\t<TD CLASS=\"$classlib\" ALIGN=\"$align\">$libelle</TD>\n";
        $src.= "\t<TD CLASS=\"$classval\" ALIGN=\"$align\">$value</TD>\n";
        return $src;
    }
    function Cell($value, $class = "", $align = "LEFT", $span = 1)
    {
        if ($align == "") {
            $align = "LEFT";
        }
        if ($class == "") {
            $src = "\t<TD CLASS=\"{$this->cell_class}\" ALIGN=\"$align\" COLSPAN=$span>&nbsp;$value</TD>\n";
        } else {
            $src = "\t<TD CLASS=\"$class\" ALIGN=\"$align\" COLSPAN=$span>&nbsp;$value</TD>\n";
        }
        return $src;
    }
    
    function TCell($value, $span = 1, $class = "", $align = "LEFT", $width = "")
    {
        $wwidth = "";
        if ($width != "") {
            $wwidth = " width=\"$width\"";
        }
        if ($class == "") {
            $src = "\t<TD NOWRAP $wwidth CLASS=\"{$this->titre_class}\" COLSPAN=$span>$value</TD>\n";
        } else {
            $src = "\t<TD NOWRAP CLASS=\"$class\" COLSPAN=$span>$value</TD>\n";
        }
        return $src;
    }
    
    function CellTextArea($CREATION, $libelle, $rows = 5, $cols = 55, $name, $entite, $span = 1)
    {
        if (!$CREATION) {
            $value = $entite->$name;
        } else {
            $value = '';
        };
        $src = "\t<TD CLASS=\"{$this->label_class}\" COLSPAN=$span>$libelle\n";
        $src.= "<DIV CLASS=\"finput\"><FONT SIZE=\"-1\">" . "<TEXTAREA WRAP=\"virtual\" ROWS=$rows COLS=$cols NAME=\"p_$name\">";
        if (!$CREATION) {
            $src.= "$value";
        }
        $src.= "</TEXTAREA></FONT></TD>\n";
        return $src;
    }
    
    function CellInput($CREATION, $libelle, $max, $size, $name, $entite, $span = 1)
    {
        if (!$CREATION) {
            $value = $entite->$name;
        } else {
            $value = '';
        };
        $src = "\t<TD CLASS=\"{$this->label_class}\" COLSPAN=$span>$libelle\n";
        $src.= "<DIV CLASS=\"finput\"><FONT SIZE=\"-1\">" . "<INPUT MAXLENGTH=$max SIZE=$size NAME=\"p_$name\"";
        if ($CREATION) {
            $src.= ">\n";
        } else {
            $src.= " VALUE=\"$value\">\n";
        }
        $src.= "</FONT></TD>\n";
        return $src;
    }
    function CellInputId($CREATION, $libelle, $max, $size, $name, $id, $entite, $span = 1)
    {
        if (!$CREATION) {
            $value = $entite->$id;
        } else {
            $value = '';
        };
        $src = "\t<TD CLASS=\"{$this->label_class}\" COLSPAN=$span>$libelle\n";
        $src.= "<FONT SIZE=\"-1\">" . "<INPUT MAXLENGTH=$max SIZE=$size NAME=\"p_$name\"";
        if ($CREATION) {
            $src.= ">\n";
        } else {
            $src.= " VALUE=\"$value\">\n";
        }
        $src.= "</FONT></TD>\n";
        return $src;
    }
    function CellInputNoDiv($CREATION, $libelle, $max, $size, $name, $entite, $span = 1)
    {
        if (!$CREATION) {
            $value = $entite->$name;
        } else {
            $value = '';
        };
        $src = "\t<TD CLASS=\"{$this->label_class}\" COLSPAN=$span>$libelle\n";
        $src.= "<FONT SIZE=\"-1\">" . "<INPUT MAXLENGTH=$max SIZE=$size NAME=\"p_$name\"";
        if ($CREATION) {
            $src.= ">\n";
        } else {
            $src.= " VALUE=\"$value\">\n";
        }
        $src.= "</FONT></TD>\n";
        return $src;
    }
    
    function Input($CREATION, $libelle, $max, $size, $name, $entite, $span = 1)
    {
        if (!$CREATION) {
            $value = $entite->$name;
        } else {
            $value = '';
        };
        $src = "<DIV CLASS=\"finput\"><FONT SIZE=\"-1\">" . "<INPUT MAXLENGTH=$max SIZE=$size NAME=\"p_$name\"";
        if ($CREATION) {
            $src.= ">\n";
        } else {
            $src.= " VALUE=\"$value\">\n";
        }
        $src.= "</FONT>\n";
        return $src;
    }
    function InputSimple($max, $size, $name, $value, $span = 1)
    {
        $src = "<FONT SIZE=\"-1\">" . "<INPUT MAXLENGTH=$max SIZE=$size NAME=\"p_$name\"";
        $src.= " VALUE=\"$value\">\n";
        $src.= "</FONT>\n";
        return $src;
    }
    
    function Select($libelle, $name, $name_id, $name_lib, $entite, $liste, $vide = "vide")
    {
        reset($liste);
        if (isset($entite->$name) && ($entite->$name == "")) unset($entite->$name);
        $src = "\t<TD CLASS=\"{$this->label_class}\">$libelle\n";
        $src.= "<DIV CLASS=\"{$this->titre_class}\">\n";
        $src.= "<SELECT NAME=\"p_$name\" SIZE=1>\n";
        if ($vide == "vide") {
            if (!isset($entite->$name)) {
                $src.= "<OPTION SELECTED VALUE=\"\">-- vide --\n";
            } else {
                $src.= "<OPTION VALUE=\"\">-- vide --\n";
            }
        }
        if (sizeof($liste) > 0) {
            foreach ($liste as $k => $v) {
                if ((isset($entite->$name)) && ($entite->$name == $v->$name_id)) {
                    $src.= "<OPTION SELECTED VALUE=\"" . $v->$name_id . "\">" . $v->$name_lib . "\n";
                } else {
                    if (($vide != "vide") && (!isset($entite->$name)) && ($vide == $v->$name_lib)) {
                        $src.= "<OPTION SELECTED VALUE=\"" . $v->$name_id . "\">" . $v->$name_lib . "\n";
                    } else {
                        $src.= "<OPTION VALUE=\"" . $v->$name_id . "\">" . $v->$name_lib . "\n";
                    }
                }
            }
        }
        $src.= "</SELECT></DIV></TD>  \n";
        return $src;
    }
    
    function SelectConcat($libelle, $name, $name_id, $name_lib1, $name_lib2, $entite, $liste, $vide = "vide")
    {
        reset($liste);
        if (isset($entite->$name) && ($entite->$name == "")) unset($entite->$name);
        $src = "\t<TD CLASS=\"{$this->label_class}\">$libelle\n";
        $src.= "<DIV CLASS=\"{$this->titre_class}\">\n";
        $src.= "<SELECT NAME=\"p_$name\" SIZE=1>\n";
        if (($vide == "vide") && (!isset($entite->$name))) {
            $src.= "<OPTION SELECTED VALUE=\"\">-- vide --\n";
        }
        if (sizeof($liste) > 0) {
            foreach ($liste as $k => $v) {
                if ((isset($entite->$name)) && ($entite->$name == $v->$name_id)) {
                    $src.= "<OPTION SELECTED VALUE=\"" . $v->$name_id . "\">" . $v->$name_lib1 . " " . $v->$name_lib2 . "\n";
                } else {
                    if (($vide != "vide") && (!isset($entite->$name)) && ($vide == $v->$name_lib1)) {
                        $src.= "<OPTION SELECTED VALUE=\"" . $v->$name_id . "\">" . $v->$name_lib1 . " " . $v->$name_lib2 . "\n";
                    } else {
                        $src.= "<OPTION VALUE=\"" . $v->$name_id . "\">" . $v->$name_lib1 . " " . $v->$name_lib2 . "\n";
                    }
                }
            }
        }
        $src.= "</SELECT></DIV></TD>  \n";
        return $src;
    }
    
    function SelectListeSimple($libelle, $name, $entite, $liste, $vide = "OUI")
    {
        
        reset($liste);
        $src = "\t<TD CLASS=\"{$this->label_class}\">$libelle\n";
        $src.= "<DIV CLASS=\"{$this->titre_class}\">\n";
        $src.= "<SELECT NAME=\"p_$name\" SIZE=1>\n";
        if ($vide == "OUI") {
            if (!isset($entite->$name)) {
                $src.= "<OPTION SELECTED VALUE=\"\">-- vide --\n";
            }
            if ((isset($entite->$name)) && ($entite->$name == '')) {
                $src.= "<OPTION SELECTED VALUE=\"\">-- vide --\n";
            }
        }
        if (sizeof($liste) > 0) {
            foreach ($liste as $k => $v) {
                if ((isset($entite->$name)) && ($entite->$name == $v)) {
                    $src.= "<OPTION SELECTED VALUE='$v'>$v\n";
                } else {
                    $src.= "<OPTION VALUE='$v'>$v\n";
                }
            }
        }
        $src.= "</SELECT></DIV></TD>  \n";
        return $src;
    }
}
?>
