<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * Generate worflow graph
 *
 * @author Anakeen 2007
 * @version $Id: wdoc_graphviz.php,v 1.17 2009/01/07 18:04:19 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage
 */
/**
 * @global id Http var : document id to affect
 * @global type Http var : type of graph
 * @global format Http var : file format pnh or svg
 * @global orient Http var :orientation TB (TopBottom)  or LR (LeftRight)
 * @global size Http var : global size of graph
 */
// refreah for a classname
// use this only if you have changed title attributes
include_once ("FDL/Lib.Attr.php");
include_once ("FDL/Class.DocFam.php");

$style = array(
    'autonext-color' => '#006400', // darkgreen
    'arrow-label-font-color' => '#555555', // dark grey
    'arrow-color' => '#00008b', // darkblue
    'condition-color' => '#ffff00', // yellow
    'action-color' => '#ffa500', // orange
    'mail-color' => '#a264d2', // light violet
    'timer-color' => '#64a2d2', // light blue
    'start-color' => '#0000ff', // blue
    
);

$dbaccess = $appl->GetParam("FREEDOM_DB");
if ($dbaccess == "") {
    print "Database not found : param FREEDOM_DB";
    exit;
}

$docid = GetHttpVars("docid", 0); // special docid
$type = GetHttpVars("type"); // type of graph
$orient = GetHttpVars("orient", "LR"); // type of graph
$isize = GetHttpVars("size", "10"); // size of graph
$ratio = GetHttpVars("ratio", "auto"); // ratio of graph
$label = ($type == "complet");
$doc = new_doc($dbaccess, $docid);

$rankdir = $orient;

$fontsize = 13;
if ($isize == "auto") $size = "";
else {
    if ($isize == "A4") {
        $size = "size=\"7.6,11!\";"; // A4 whith 1.5cm margin
        
    } else {
        if (preg_match("/([0-9\.]+),([0-9\.]+)/", $isize, $reg)) {
            
            $fontsize = intval(min($reg[1], $reg[2]) / 2);
            $size = sprintf("size=\"%.2f,%.2f!\";", floatval($reg[1]) / 2.55, floatval($reg[2]) / 2.55);
        } else {
            $isize = sprintf("%.2f", floatval($isize) / 2.55);
            $size = "size=\"$isize,$isize!\";";
        }
    }
}

$statefontsize = $fontsize;
$conditionfontsize = intval($fontsize * 10 / 13);
$labelfontsize = intval($fontsize * 11 / 13);
/*$statefontsize="";
$conditionfontsize="";
$labelfontsize="";*/

$tact = array();
foreach ($doc->cycle as $k => $v) {
    $tmain = '';
    if (isset($doc->autonext[$v["e1"]]) && ($doc->autonext[$v["e1"]] == $v["e2"])) {
        $tmain = sprintf('color="%s",style="setlinewidth(3)",arrowsize=1.0', $style['autonext-color']);
    }
    
    if ($label) {
        $e1 = _($v["e1"]);
        $e2 = _($v["e2"]);
        $act = $doc->getActivity($v["e1"]);
        if ($act && ($tact[$v["e1"]] == "")) {
            $tact[$v["e1"]] = 'act' . $k;
            $line[] = '"' . str_replace(" ", "\\n", 'act' . $k) . '" [ label="' . $act . '", fixedsize=false,shape=box,color="' . $doc->getColor($v["e1"]) . '" ];';
            
            $line[] = sprintf('"%s" -> "%s" [labelfontcolor="%s",decorate=false, color="%s", labelfontname=sans, label="%s"];', str_replace(" ", "\\n", $e1) , str_replace(" ", "\\n", 'act' . $k) , $style['arrow-label-font-color'], $style['arrow-color'], "");
            $e1 = 'act' . $k;
        } elseif ($tact[$v["e1"]] != "") {
            $e1 = $tact[$v["e1"]];
        }
        
        $m1 = $doc->transitions[$v["t"]]["m1"];
        $m2 = $doc->transitions[$v["t"]]["m2"];
        
        $ttrans = array();
        $tm = $doc->getValue($doc->attrPrefix . "_TRANS_TMID" . $v["t"]);
        if ($tm) $ttrans[] = $tm;
        $ttrans = array_merge($ttrans, $doc->getTValue($doc->attrPrefix . "_TRANS_PA_TMID" . $v["t"]));
        $mtrans = $doc->getTValue($doc->attrPrefix . "_TRANS_MTID" . $v["t"]);
        
        if ($m1) {
            //      if ($tmain) $tmain.=",";
            //      $tmain.="taillabel=$m1";
            $line[] = '"' . str_replace(" ", "\\n", $m1 . $k) . '" [ label="' . $m1 . '", fixedsize=false,shape=diamond,color="' . $style['condition-color'] . '" ];';
            
            $line[] = sprintf('"%s" -> "%s" [labelfontcolor="%s",decorate=false, color="%s",  labelfontname=sans, label="%s"];', str_replace(" ", "\\n", $e1) , str_replace(" ", "\\n", $m1 . $k) , $style['arrow-label-font-color'], $style['arrow-color'], _($v["t"]));
            $e1 = $m1 . $k;
        }
        if ($m2) {
            $line[] = '"' . str_replace(" ", "\\n", $m2 . $k) . '" [ label="' . $m2 . '",fixedsize=false,shape=box,color="' . $style['action-color'] . '"];';
            $line[] = sprintf('"%s" -> "%s" [labelfontcolor="%s",decorate=false, color="%s", labelfontname=sans];', str_replace(" ", "\\n", $e1) , str_replace(" ", "\\n", $m2 . $k) , $style['arrow-label-font-color'], $style['arrow-color']);
            $e1 = $m2 . $k;
        }
        
        if (count($mtrans) > 0) {
            $ex = 'mt' . $k;
            $tmlabel = "mail";
            
            $tmlabel = str_replace(array(
                "\n",
                '<BR>'
            ) , "\\n", $doc->getHtmlValue($doc->getAttribute($doc->attrPrefix . "_TRANS_MTID" . $v["t"]) , $doc->_array2val($mtrans) , '_self', false));
            $timgt = ' image="' . DEFAULT_PUBDIR . '/Images/tmail.png"';
            $line[] = '"' . str_replace(" ", "\\n", $ex) . '" [ label="' . $tmlabel . '",fixedsize=false,style=bold,shape=house,color="' . $style['mail-color'] . '"' . $timgt . ' ];';
            $line[] = sprintf('"%s" -> "%s" [labelfontcolor="%s",decorate=false, color="%s",labelfontname=sans];', str_replace(" ", "\\n", $e1) , str_replace(" ", "\\n", $ex) , $style['arrow-label-font-color'], $style['arrow-color']);
            $e1 = $ex;
        }
        
        $aid = strtolower($doc->attrPrefix . "_MTID" . $v["e2"]);
        $mt = $doc->getTValue($aid);
        if (count($mt) > 0) {
            $ex = 'mtf' . $k;
            
            $tmlabel = str_replace(array(
                "\n",
                '<BR>'
            ) , "\\n", $doc->getHtmlValue($doc->getAttribute($doc->attrPrefix . "_MTID" . $v["e2"]) , $doc->_array2val($mt) , '_self', false));
            $timgt = ' image="' . DEFAULT_PUBDIR . '/Images/tmail.png"';
            $line[] = '"' . str_replace(" ", "\\n", $ex) . '" [ label="' . $tmlabel . '",fixedsize=false,style=bold,shape=house,color="' . $style['mail-color'] . '"' . $timgt . ' ];';
            $line[] = sprintf('"%s" -> "%s" [labelfontcolor="%s",decorate=false, color="%s", labelfontname=sans];', str_replace(" ", "\\n", $e1) , str_replace(" ", "\\n", $ex) , $style['arrow-label-font-color'], $style['arrow-color']);
            $e1 = $ex;
        }
        
        $aid = strtolower($doc->attrPrefix . "_TMID" . $v["e2"]);
        $mt = $doc->getTValue($aid);
        if (count($mt) > 0) {
            $ex = 'tmf' . $k;
            $tmlabel = str_replace(array(
                "\n",
                '<BR>'
            ) , "\\n", $doc->getHtmlValue($doc->getAttribute($doc->attrPrefix . "_TMID" . $v["e2"]) , $doc->_array2val($mt) , '_self', false));
            $timgt = ' image="' . DEFAULT_PUBDIR . '/Images/timer.png"';
            $line[] = '"' . str_replace(" ", "\\n", $ex) . '" [ label="' . $tmlabel . '",fixedsize=false,style=bold,shape=octagon,color="' . $style['timer-color'] . '"' . $timgt . ' ];';
            $line[] = sprintf('"%s" -> "%s" [labelfontcolor="%s",decorate=false, color="%s",labelfontname=sans];', str_replace(" ", "\\n", $e1) , str_replace(" ", "\\n", $ex) , $style['arrow-label-font-color'], $style['arrow-color']);
            $e1 = $ex;
        }
        if (count($ttrans) > 0) {
            $ex = 'tm' . $k;
            $tmlabel = "tumer";
            $tmlabel = str_replace(array(
                "\n",
                '<BR>'
            ) , "\\n", $doc->getHtmlValue($doc->getAttribute($doc->attrPrefix . "_TRANS_MTID" . $v["t"]) , $doc->_array2val($ttrans) , '_self', false));
            $timgt = ' image="' . DEFAULT_PUBDIR . '/Images/timer.png"';
            $line[] = '"' . str_replace(" ", "\\n", $ex) . '" [ label="' . $tmlabel . '",fixedsize=false,style=bold,shape=octagon,color="' . $style['timer-color'] . '"' . $timgt . ' ];';
            $line[] = sprintf('"%s" -> "%s" [labelfontcolor="%s",decorate=false, color="%s",labelfontname=sans];', str_replace(" ", "\\n", $e1) , str_replace(" ", "\\n", $ex) , $style['arrow-label-font-color'], $style['arrow-color']);
            $e1 = $ex;
        }
        
        $line[] = sprintf('"%s" -> "%s" [labelfontcolor="%s",decorate=false, color="%s", labelfontname=sans,label="%s" %s];', str_replace(" ", "\\n", $e1) , str_replace(" ", "\\n", $e2) , $style['arrow-label-font-color'], $style['arrow-color'], _($v["t"]) , $tmain);
    } else {
        $e1 = _($v["e1"]);
        $e2 = _($v["e2"]);
        $act = $doc->getActivity($v["e1"]);
        if ($type == "activity") {
            if ($act && ($tact[$v["e1"]] == "")) {
                $tact[$v["e1"]] = 'act' . $k;
                $line[] = '"' . str_replace(" ", "\\n", 'act' . $k) . '" [ label="' . $act . '", fixedsize=false,shape=box,color="' . $doc->getColor($v["e1"]) . '" ];';
                
                $line[] = sprintf('"%s" -> "%s" [labelfontcolor="%s",decorate=false, color="%s", labelfontname=sans, label="%s"];', str_replace(" ", "\\n", $e1) , str_replace(" ", "\\n", 'act' . $k) , $style['arrow-label-font-color'], $style['arrow-color'], "");
                $e1 = 'act' . $k;
            } elseif ($tact[$v["e1"]] != "") {
                $e1 = $tact[$v["e1"]];
            }
        }
        $line[] = sprintf('"%s" -> "%s" [labelfontsize=6,color="%s" %s,labelfontname=sans, label="%s"];', str_replace(" ", "\\n", $e1) , str_replace(" ", "\\n", $e2) , $style['arrow-color'], $tmain, ($type == "activity") ? _($v["t"]) : "");
    }
}
$line[] = '"' . str_replace(" ", "\\n", _($doc->firstState)) . '" [shape = doublecircle,style=filled, width=1.5, fixedsize=true,fontname=sans];';

if ($label) {
    $aid = strtolower($doc->attrPrefix . "_TMID" . $doc->firstState);
    $tm = $doc->getTValue($aid);
    $aid = strtolower($doc->attrPrefix . "_MTID" . $doc->firstState);
    $mt = $doc->getTValue($aid);
    $e1 = "D";
    if ((count($tm) > 0) || (count($mt) > 0)) {
        $line[] = '"' . str_replace(" ", "\\n", $e1) . '" [shape = point,style=filled, width=0.3, fixedsize=true,fontname=sans,color="' . $style['start-color'] . '"];';;
    }
    
    if (count($tm) > 0) {
        $e2 = 'tmfirst';
        
        $tmlabel = str_replace(array(
            "\n",
            '<BR>'
        ) , "\\n", $doc->getHtmlValue($doc->getAttribute($doc->attrPrefix . "_TMID" . $doc->firstState) , $doc->_array2val($tm) , '_self', false));
        $timgt = ' image="' . DEFAULT_PUBDIR . '/Images/timer.png"';
        $line[] = '"' . str_replace(" ", "\\n", $e2) . '" [ label="' . $tmlabel . '",fixedsize=false,style=bold,shape=octagon,color="' . $style['timer-color'] . '", fontsize=' . $conditionfontsize . $timgt . ' ];';
        $line[] = sprintf('"%s" -> "%s" [labelfontcolor="%s",decorate=false, color="%s", labelfontname=sans];', str_replace(" ", "\\n", $e1) , str_replace(" ", "\\n", $e2) , $style['arrow-label-font-color'], $style['arrow-color']);
        $e1 = $e2;
    }
    if (count($mt) > 0) {
        $e2 = 'mtfirst';
        $tmlabel = str_replace(array(
            "\n",
            '<BR>'
        ) , "\\n", $doc->getHtmlValue($doc->getAttribute($doc->attrPrefix . "_MTID" . $doc->firstState) , $doc->_array2val($mt) , '_self', false));
        $timgt = ' image="' . DEFAULT_PUBDIR . '/Images/tmail.png"';
        $line[] = '"' . str_replace(" ", "\\n", $e2) . '" [ label="' . $tmlabel . '",fixedsize=false,style=bold,shape=house,color="' . $style['mail-color'] . '", fontsize=' . $conditionfontsize . $timgt . ' ];';
        $line[] = sprintf('"%s" -> "%s" [labelfontcolor="%s",decorate=false, color="%s", labelfontname=sans];', str_replace(" ", "\\n", $e1) , str_replace(" ", "\\n", $e2) , $style['arrow-label-font-color'], $style['arrow-color']);
        $e1 = $e2;
    }
    
    if ($e1 != 'D') {
        //attach to first state
        $e2 = _($doc->firstState);
        $line[] = sprintf('"%s" -> "%s" [labelfontcolor="%s",decorate=false, color="%s", labelfontname=sans];', str_replace(" ", "\\n", $e1) , str_replace(" ", "\\n", $e2) , $style['arrow-label-font-color'], $style['arrow-color']);
    }
}
$states = $doc->getStates();
foreach ($states as $k => $v) {
    $color = $doc->getColor($v);
    $saction = $doc->getActivity($v);
    if ($saction) $tt = ', tooltip="' . $saction . '"';
    else $tt = "";
    
    if ($color) $line[] = '"' . str_replace(" ", "\\n", _($v)) . '" [fillcolor="' . $color . '"' . $tt . '  ];';
    else {
        if ($tt) $tt = substr($tt, 2);
        $line[] = '"' . str_replace(" ", "\\n", _($v)) . '" [' . $tt . '];';
    }
}
#        page=\"11.6,8.2\";
$ft = str_replace(" ", '\n', _($doc->firstState));

$dot = "digraph \"" . $doc->title . "\" {
        ratio=\"$ratio\";
	rankdir=$rankdir;
        $size
        bgcolor=\"transparent\";
        {rank=1; \"$ft\";}
        splines=true;
	node [shape = circle, style=filled, fixedsize=true,width=1.5,   fontname=sans];\n";

$dot.= implode($line, "\n");
$dot.= "\n}";

print $dot;
?>