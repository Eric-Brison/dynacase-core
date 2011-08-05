<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * Edition to affect document
 *
 * @author Anakeen 2000
 * @version $Id: view_workflow_graph.php,v 1.8 2008/12/31 16:05:20 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.WDoc.php");
// -----------------------------------

/**
 * Edition to affect document
 * @param Action &$action current action
 * @global id Http var : document id to affect
 * @global type Http var : type of graph
 * @global format Http var : file format pnh or svg
 * @global orient Http var :orientation TB (TopBottom)  or LR (LeftRight)
 * @global size Http var : global size of graph
 */
function view_workflow_graph(&$action)
{
    $docid = GetHttpVars("id");
    $type = GetHttpVars("type", "simple"); // type of graph
    $format = GetHttpVars("format", "png");
    $orient = GetHttpVars("orient", "LR"); // type of graph
    $size = GetHttpVars("size", "10"); // size of graph
    $ratio = GetHttpVars("ratio", "auto"); // ratio of graph
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    if ($type != 'simple' && $type != 'activity' && $type != 'complet') {
        return $action->exitError(sprintf("Invalid type '%s'", htmlspecialchars($type)));
    }
    if ($format != 'dot' && $format != 'png' && $format != 'svg') {
        return $action->exitError(sprintf("Invalid format '%s'", htmlspecialchars($format)));
    }
    if ($orient != 'LR' && $orient != 'TB') {
        return $action->exitError(sprintf("Invalid orient '%s'", htmlspecialchars($orient)));
    }
    if ($ratio != 'fill' && $ratio != 'compress' && $ratio != 'expand' && $ratio != 'auto') {
        return $action->exitError(sprintf("Invalid ratio '%s'", htmlspecialchars($ratio)));
    }
    
    $doc = new_doc($dbaccess, $docid);
    $cmd = getWshCmd(false, $action->user->id);
    
    if (count($doc->cycle) == 0) {
        $action->lay->template = _("no cycle defined");
    } else {
        
        $cmd.= sprintf("--api=wdoc_graphviz --size=%d --ratio=%s --type=%s --orient=%s --docid=%d", $size, escapeshellarg($ratio) , escapeshellarg($type) , escapeshellarg($orient) , $doc->id);
        $svgfile = "img-cache/w$type-" . $action->getParam("CORE_LANG") . "-" . $doc->id . ".$format";
        if ($format == "dot") $svgfile.= ".txt"; // conflict with document template
        $dest = DEFAULT_PUBDIR . "/$svgfile";
        if ($format == "dot") $cmd.= sprintf("> %s", escapeshellarg($dest));
        $sed = sprintf("s/%s/../", str_replace('/', '\/', DEFAULT_PUBDIR));
        if ($format == "svg") $cmd.= sprintf("| dot -T%s | sed -e %s > %s", escapeshellarg($format) , escapeshellarg($sed) , escapeshellarg($dest));
        else $cmd.= sprintf("| dot -T%s > %s", escapeshellarg($format) , escapeshellarg($dest));
        
        system($cmd);
        //   print_r2( $cmd);
        header("location:$svgfile");
        exit;
    }
}
