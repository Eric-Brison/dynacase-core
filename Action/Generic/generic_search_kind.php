<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: generic_search_kind.php,v 1.15 2008/08/14 09:59:14 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage
 */
/**
 */
// ---------------------------------------------------------------
// $Id: generic_search_kind.php,v 1.15 2008/08/14 09:59:14 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Generic/generic_search_kind.php,v $
// ---------------------------------------------------------------

include_once ("FDL/Class.DocSearch.php");
include_once ("FDL/freedom_util.php");
include_once ("GENERIC/generic_util.php");
// -----------------------------------
function generic_search_kind(&$action)
{
    // -----------------------------------
    
    // Get all the params
    $kid = GetHttpVars("kid"); // kind id to search
    $aid = GetHttpVars("aid"); // attribute to search
    $dirid = GetHttpVars("catg"); // folder or research to search
    
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    $famid = getDefFam($action);
    $fdoc = new_Doc($dbaccess, $famid);
    
    $attr = $fdoc->getAttribute($aid);
    $enum = $attr->getEnum();
    $kindname = $enum[$kid];
    
    $dir = new_Doc($dbaccess, $dirid);
    
    $sdoc = createTmpDoc($dbaccess, 5); //new DocSearch($dbaccess);
    $sdoc->title = sprintf(_("search %s") , $keyword);
    if (($dirid == 0) || ($dir->id == getDefFld($action))) $sdoc->title = sprintf(_("search %s is %s") , $attr->getLabel() , $kindname);
    else $sdoc->title = sprintf(_("search %s is %s in %s") , $attr->getLabel() , $kindname, $dir->gettitle());
    
    $sdoc->Add();
    
    $searchquery = "";
    $sdirid = 0;
    if ($dir->defDoctype == 'S') { // case of search in search doc
        $sdirid = $dir->id;
        $pds = $dir->urlWhatEncodeSpec("");
        //print "<hr>pds=$pds";
        
    } else { // case of search in folder
        if ($dir->id != getDefFld($action)) $sdirid = $dirid;
    }
    
    $kid = str_replace('\.', '-dot-', $kid);
    if (strrpos($kid, '.') !== false) $kid = substr($kid, strrpos($kid, '.') + 1); // last reference
    // clear key
    $action->parent->param->Set("GENE_LATESTTXTSEARCH", setUkey($action, $famid, $keyword) , PARAM_USER . $action->user->id, $action->parent->id);
    
    $sqlfilter[] = "locked != -1";
    //  $sqlfilter[]= "doctype='F'";
    //  $sqlfilter[]= "usefor != 'D'";
    
    // searches for all fathers kind
    $a = $fdoc->getAttribute($aid);
    $enum = $a->getEnum();
    $tkids = array();;
    foreach ($enum as $k => $v) {
        $k = str_replace('\.', '-dot-', $k);
        if (in_array($kid, explode(".", $k))) {
            $tkids[] = pg_escape_string(str_replace('-dot-', '.', substr($k, strrpos("." . $k, '.'))));
        }
    }
    if ($a->type == "enum") {
        if ($a->repeat) {
            $sqlfilter[] = "in_textlist($aid,'" . implode("') or in_textlist($aid,'", $tkids) . "')";
        } else {
            $sqlfilter[] = "$aid='" . implode("' or $aid='", $tkids) . "'";
        }
    }
    
    $query = getSqlSearchDoc($dbaccess, $sdirid, $famid, $sqlfilter);
    
    $sdoc->AddQuery($query);
    
    redirect($action, GetHttpVars("app") , "GENERIC_LIST$pds&famid=$famid&dirid=" . $sdoc->id . "&catg=" . $dirid);
}
?>