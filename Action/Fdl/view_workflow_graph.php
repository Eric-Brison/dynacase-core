<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Edition to affect document
 *
 * @author Anakeen
 * @version $Id: view_workflow_graph.php,v 1.8 2008/12/31 16:05:20 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.WDoc.php");
// -----------------------------------

/**
 * Edition to affect document
 * @param Action &$action current action
 * @global string $id Http var : document id to affect
 * @global string $type Http var : type of graph
 * @global string $format Http var : file format pnh or svg
 * @global string $orient Http var :orientation TB (TopBottom)  or LR (LeftRight)
 * @global string $size Http var : global size of graph
 * @return void
 */
function view_workflow_graph(Action & $action)
{
    $usage = new ActionUsage($action);
    $usage->setDefinitionText("Generate graph image for workflow");
    $docid = $usage->addRequiredParameter("id", "workflow id");
    $type = $usage->addOptionalParameter("type", "graph detail level", array(
        "justactivity",
        "simple",
        "activity",
        "complet",
        "cluster"
    ) , "justactivity");
    $format = $usage->addOptionalParameter("format", "image format", array(
        "png",
        "svg",
        "dot"
    ) , "png");
    $orient = $usage->addOptionalParameter("orient", "orientation", array(
        "LR",
        "TB"
    ) , "LR");
    $size = $usage->addOptionalParameter("size", "image size", array() , "auto");
    $ratio = $usage->addOptionalParameter("ratio", "ration", array(
        "fill",
        "compress",
        "auto",
        "expand"
    ) , "fill");
    $tool = $usage->addOptionalParameter("tool", "tool used to generate", array(
        "dot",
        "sfdp",
        "neato",
        "fdp",
        "twopi",
        "circo"
    ) , "dot");
    $usage->verify();
    
    $dbaccess = $action->dbaccess;
    
    if ($tool == "sfdp") {
        $tool.= "  -Goverlap=prism";
    } elseif ($tool == "neato") {
        $tool.= " -Goverlap=prism";
    } elseif ($tool == "twopi") {
        $tool.= " -Granksep=3.0 -Gnodesep=2.0 -Goverlap=false";
    }
    /**
     * @var WDoc $doc
     */
    $doc = new_doc($dbaccess, $docid);
    $cmd = getWshCmd(false, $action->user->id);
    
    if (count($doc->cycle) == 0) {
        $action->lay->template = _("no cycle defined");
        $action->lay->noparse = true;
    } else {
        
        $cmd.= sprintf("--api=wdoc_graphviz --size=%s --ratio=%s --type=%s --orient=%s --docid=%d", escapeshellarg($size) , escapeshellarg($ratio) , escapeshellarg($type) , escapeshellarg($orient) , $doc->id);
        $svgfile = "var/cache/image/w$type-" . $action->getParam("CORE_LANG") . "-" . $doc->id . ".$format";
        if ($format == "dot") $svgfile.= ".txt"; // conflict with document template
        $dest = DEFAULT_PUBDIR . "/$svgfile";
        if ($format == "dot") {
            $cmd.= sprintf(" > %s", escapeshellarg($dest));
        } else {
            $cmd.= sprintf(" | %s -T%s -o%s 2>&1", $tool, escapeshellarg($format) , escapeshellarg($dest));
        }
        
        exec($cmd, $out, $ret);
        
        if ($ret != 0) $action->exitError(implode("\n", $out));
        //   print_r2( $cmd);
        if ($format == "png") $mime = "image/png";
        elseif ($format == "svg") $mime = "image/svg+xml";
        else $mime = "text/plain";
        Http_DownloadFile($svgfile, sprintf("graph %s.%s", $doc->getTitle() , $format) , $mime, true, false, true);
        exit;
    }
}
