<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * update list of available style
 *
 * analyze sub-directories presents in STYLE directory
 * @author Anakeen
 * @version $Id: import_style.php,v 1.10 2007/02/21 11:07:12 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage WSH
 */
/**
 */

include_once ("Class.Style.php");
include_once ("Lib.Color.php");

function getStyleInherit($name, &$sty_colorsh, &$sty_consth, &$sty_localsh)
{
    $sty_const = $sty_colors = $sty_local = array();
    if (file_exists(GetParam("CORE_PUBDIR", DEFAULT_PUBDIR) . "/STYLE/{$name}/{$name}.sty")) {
        //  global $sty_desc,$sty_const,$sty_colors,$sty_local;
        include ("STYLE/{$name}/{$name}.sty");
        
        $sty_consth = $sty_const;
        $sty_colorsh = $sty_colors;
        $sty_localsh = $sty_local;
    }
}
$usage = new ApiUsage();

$usage->setText("update list of available style");
$name = $usage->addNeeded("name", "name of style file");
$html = ($usage->addOption("html", "html") != "");

$usage->verify();

$thparam = array(); // array of inherited paramters
$param = new Param();
/**
 * @var Action $action
 */
if (file_exists($action->GetParam("CORE_PUBDIR", DEFAULT_PUBDIR) . "/STYLE/{$name}/{$name}.sty")) {
    //  global $sty_desc,$sty_const,$sty_colors,$sty_local;
    include ("STYLE/{$name}/{$name}.sty");
    // inherit
    if (isset($sty_inherit)) {
        $thparam = $param->GetStyle($sty_inherit, true);
        getStyleInherit($sty_inherit, $sty_colors_inherit, $sty_const_inherit, $sty_local_inherit);
        
        if (isset($sty_const)) {
            foreach ($sty_const_inherit as $k => $v) {
                if (!isset($sty_const[$k])) $sty_const[$k] = $v;
            }
        } else {
            $sty_const = $sty_const_inherit;
        }
        if (!isset($sty_colors)) $sty_colors = $sty_colors_inherit;
    }
    
    $sty = new Style("", $name);
    if (sizeof($sty_desc) > 0) {
        reset($sty_desc);
        while (list($k, $v) = each($sty_desc)) {
            $sty->$k = $v;
        }
        if (!$sty->isAffected()) $sty->Add();
        $sty->Modify();
    }
    // delete first old parameters
    $query = new QueryDb("", "Param");
    $query->AddQuery("type='" . PARAM_STYLE . $name . "'");
    $list = $query->Query();
    if ($query->nb > 0) {
        while (list($k, $v) = each($list)) {
            $v->delete();
        }
    }
    // init param
    foreach ($thparam as $k => $v) {
        $param->Set($v["name"], $v["val"], PARAM_STYLE . $name, 1);
        $action->parent->SetVolatileParam($v["name"], $v["val"]);
    }
    
    if (isset($sty_colors)) {
        // compute all derived color
        $dark = false;
        if (isset($sty_const["COLOR_WHITE"])) {
            $basehsl = srgb2hsl($sty_const["COLOR_WHITE"]);
        }
        $dark = ($basehsl[2] < 0.5);
        
        foreach ($sty_colors as $k => $v) {
            $basecolor = $v;
            if ($basecolor[0] == '#') {
                $r = hexdec(substr($basecolor, 1, 2));
                $g = hexdec(substr($basecolor, 3, 2));
                $b = hexdec(substr($basecolor, 5, 2));
                $basehsl = srgb2hsl($basecolor);
                $h = $basehsl[0];
                $s = $basehsl[1];
                $l = $basehsl[2];
                if ($html) print "<table><tr>";
                if ($dark) $idx = - ($l / 10);
                else $idx = (1 - $l) / 10;
                $il = $l;
                for ($i = 0; $i < 10; $i++) {
                    
                    $rgb = HSL2RGB($h, $s, $il);
                    $pcolor = "COLOR_{$k}{$i}";
                    $param->Set($pcolor, $rgb, PARAM_STYLE . $name, 1);
                    $action->parent->SetVolatileParam($pcolor, $rgb); // to compose css with new paramters
                    $il+= $idx;
                    if ($html) print "<td style='background-color:$rgb'>$pcolor: $rgb</td>\n";
                }
                if ($html) print "</tr></table>";
            }
        }
    }
    
    if (strtolower($sty->parsable) == "y") {
        // delete ISIE, ISCHROME, ... parameters
        initExplorerParam($action->parent, null);
    }
    if (isset($sty_const)) {
        reset($sty_const);
        while (list($k, $v) = each($sty_const)) {
            $vv = $action->getParam($v, $v);
            $param->Set($k, $vv, PARAM_STYLE . $name, 1);
            $action->parent->SetVolatileParam($k, $vv); // to compose css with new paramters
            
        }
    }
    if (isset($sty_local_inherit)) {
        foreach ($sty_local_inherit as $k => $v) {
            $action->parent->SetVolatileParam($k, $action->getParam($v, $v)); // to compose css with new paramters
            
        }
    }
    if (isset($sty_local)) {
        foreach ($sty_local as $k => $v) {
            $action->parent->SetVolatileParam($k, $action->getParam($v, $v)); // to compose css with new paramters
            
        }
    }
    $inputlay = new Layout("STYLE/$name/Layout/$name.css", $action);
    if ($sty_inherit) {
        if ($inputlay->file == "") {
            $inputlay = new Layout("STYLE/$sty_inherit/Layout/$sty_inherit.css", $action);
        } else {
            // concat css
            $inputlayh = new Layout("STYLE/$sty_inherit/Layout/$sty_inherit.css", $action);
            
            if ($inputlayh->file != "") {
                $inputlay->template = $inputlayh->template . "\n" . $inputlay->template;
            }
        }
    }
    
    $out = $inputlay->gen();
    
    if (!is_dir(DEFAULT_PUBDIR . "/STYLE/$name/Layout")) {
        mkdir(DEFAULT_PUBDIR . "/STYLE/$name/Layout");
    }
    file_put_contents(DEFAULT_PUBDIR . "/STYLE/$name/Layout/gen.css", $out);
    // update style list for STYLE parameter definition
    $query = new QueryDb("", "Style");
    $list = $query->Query();
    if ($query->nb > 0) {
        $ndef = "enum(";
        while (list($k, $v) = each($list)) {
            if (substr($v->name, 0, 4) != "SIZE") $ndef.= $v->name . "|";
        }
        $ndef = substr($ndef, 0, -1) . ")";
    } else $ndef = "";
    
    $pdef = new ParamDef("", "STYLE");
    $pdef->kind = $ndef;
    $pdef->modify();
    
    print sprintf(_("%s style updated\n") , $name);
}
?>