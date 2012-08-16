<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Generate family relation graph
 *
 * @author Anakeen
 * @version $Id: family_graphviz.php,v 1.1 2008/07/11 13:17:31 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
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
$q = new QueryDb($dbaccess, "DocAttr");
//$q->AddQuery("type='docid'");
$ta = $q->Query(0, 0, "TABLE");

$q = new QueryDb($dbaccess, "DocFam");
$tf = $q->Query(0, 0, "TABLE");
foreach ($tf as $k => $v) {
    if ($v["name"] != '') {
        
        $tname[$v["id"]] = $v["name"];
    }
}

$fampattern = '/,' . implode($tname, '[,\)]|,') . '[,\)]/';
//print $fampattern;
foreach ($ta as $k => $v) {
    if ($v['phpfunc'][0] != ':') {
        if (preg_match($fampattern, $v['phpfunc'], $match)) {
            if ((count($match) > 0) && ($match[0] != "")) {
                $rel[] = array(
                    "aid" => $v["id"],
                    "alabel" => $v["labeltext"],
                    "x" => $v["phpfunc"],
                    "sourcefam" => $tname[$v["docid"]],
                    "ciblefam" => str_replace(array(
                        ',',
                        ')'
                    ) , '', $match[0])
                );
            }
        }
    }
}
//print_r2($rel);

$rankdir = $orient;
if ($isize == "auto") $size = "";
else {
    if ($isize == "A4") {
        $size = "size=\"7.6,11!\";"; // A4 whith 1.5cm margin
        
    } else {
        if (preg_match("/([0-9\.]+),([0-9\.]+)/", $isize, $reg)) {
            $size = sprintf("size=\"%.2f,%.2f!\";", floatval($reg[1]) / 2.54, floatval($reg[2]) / 2.54);
        } else {
            $isize = sprintf("%.2f", floatval($isize) / 2.54);
            $size = "size=\"$isize,$isize!\";";
        }
    }
}
$statefontsize = 13;
$conditionfontsize = 12;
$labelfontsize = 11;

foreach ($rel as $k => $v) {
    
    $line[] = sprintf('"%s" -> "%s" [labelfontsize=6,color=darkblue label="%s"];', str_replace(" ", "\\n", ($v["sourcefam"])) , str_replace(" ", "\\n", ($v["ciblefam"])) , $v["aid"]);
    //  $line[]='"'.utf8_encode(_($v["e1"])).'" -> "'.utf8_encode(_($v["e2"])).' [label="'..'";';
    
}
//$line[]='"'.str_replace(" ","\\n",_($doc->firstState)).'" [shape = doublecircle,style=filled, width=1.5, fixedsize=true,fontsize='.$statefontsize.',fontname=sans];';;
$title = "reference";
$dot = "digraph \"" . $title . "\" {
        ratio=\"$ratio\";
	rankdir=$rankdir;
        $size
        bgcolor=\"transparent\";
        {rank=1; \"$ft\";}
        splines=false;
	node [shape = circle, style=filled, fixedsize=true,width=1.5,  fontsize=$statefontsize, fontname=sans];\n";

$dot.= implode($line, "\n");
$dot.= "\n}";

print $dot;
?>