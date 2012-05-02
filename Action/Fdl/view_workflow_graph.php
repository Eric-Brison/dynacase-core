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
function view_workflow_graph(Action &$action)
{
    $usage=new ActionUsage($action);
    $usage->setText("Generate graph image for workflow");
    $docid=$usage->addNeeded("id","workflow id");
    $type=$usage->addOption("type","graph detail level",array("simple","activity","complet","cluster"),"simple");
    $format=$usage->addOption("format","image format",array("png","svg","dot"),"png");
    $orient=$usage->addOption("orient","orientation",array("LR","TB"),"LR");
    $size=$usage->addOption("size","image size",array(),"20");
    $ratio=$usage->addOption("ratio","ration",array("fill","compress","auto","expand"),"fill");
    $tool=$usage->addOption("tool","tool used to generate",array("dot","sfdp","neato","fdp","twopi","circo"),"dot");
    $usage->verify();

    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    if ($type != 'simple' && $type != 'activity' && $type != 'complet' && $type != 'cluster') {
         $action->exitError(sprintf("Invalid type '%s'", htmlspecialchars($type)));
    }
    if ($format != 'dot' && $format != 'png' && $format != 'svg') {
         $action->exitError(sprintf("Invalid format '%s'", htmlspecialchars($format)));
    }
    if ($orient != 'LR' && $orient != 'TB') {
         $action->exitError(sprintf("Invalid orient '%s'", htmlspecialchars($orient)));
    }
    if ($ratio != 'fill' && $ratio != 'compress' && $ratio != 'expand' && $ratio != 'auto') {
         $action->exitError(sprintf("Invalid ratio '%s'", htmlspecialchars($ratio)));
    }
    if ($tool != 'dot' && $tool != 'sfdp' && $tool != 'neato' && $tool != 'fdp' && $tool != 'twopi' && $tool != 'circo') {
         $action->exitError(sprintf("Invalid tool '%s'", htmlspecialchars($tool)));
    }

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
    } else {
        
        $cmd.= sprintf("--api=wdoc_graphviz --size=%s --ratio=%s --type=%s --orient=%s --docid=%d", $size, escapeshellarg($ratio) , escapeshellarg($type) , escapeshellarg($orient) , $doc->id);
        $svgfile = "img-cache/w$type-" . $action->getParam("CORE_LANG") . "-" . $doc->id . ".$format";
        if ($format == "dot") $svgfile.= ".txt"; // conflict with document template
        $dest = DEFAULT_PUBDIR . "/$svgfile";
        if ($format == "dot") $cmd.= sprintf("> %s", escapeshellarg($dest));
        $sed = sprintf("s/%s/../", str_replace('/', '\/', DEFAULT_PUBDIR));
       // if ($format == "svg") $cmd.= sprintf("| %s -T%s | sed -e %s > %s", $tool, escapeshellarg($format) , escapeshellarg($sed) , escapeshellarg($dest));
        //else
        $cmd.= sprintf("| %s -T%s 2>&1  > %s", $tool, escapeshellarg($format) , escapeshellarg($dest));
        

        exec($cmd, $out, $ret);

        if ($ret != 0) $action->exitError(implode("\n",$out));
        //   print_r2( $cmd);
        if ($format == "png") $mime = "image/png";
        elseif ($format == "svg") $mime = "image/svg+xml";
        else $mime = "text/plain";
        Http_DownloadFile($svgfile, sprintf("graph %s.%s", $doc->getTitle() , $format) , $mime, true, false, true);
        exit;
    }
}
