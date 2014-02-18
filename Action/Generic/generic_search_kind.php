<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: generic_search_kind.php,v 1.15 2008/08/14 09:59:14 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
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
function generic_search_kind(Action & $action)
{
    // -----------------------------------
    // Get all the params
    $kid = $action->getArgument("kid"); // kind id to search
    $aid = $action->getArgument("aid"); // attribute to search
    $dirid = $action->getArgument("catg"); // folder or research to search
    $onefamOrigin = $action->getArgument("onefam"); // onefam origin
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    $famid = getDefFam($action);
    $fdoc = new_Doc($dbaccess, $famid);
    /**
     * @var NormalAttribute $attr
     */
    $attr = $fdoc->getAttribute($aid);
    $enum = $attr->getEnum();
    $kindname = $enum[$kid];
    
    $dir = new_Doc($dbaccess, $dirid);
    /**
     * @var DocSearch $sdoc
     */
    $sdoc = createTmpDoc($dbaccess, 5); //new DocSearch($dbaccess);
    $sdoc->title = sprintf(_("search %s") , $kid);
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
    $action->parent->param->Set("GENE_LATESTTXTSEARCH", setUkey($action, $famid, '') , Param::PARAM_USER . $action->user->id, $action->parent->id);
    
    $s = new SearchDoc($action->dbaccess, $famid);
    $s->addFilter("locked != -1");
    // searches for all fathers kind
    $a = $fdoc->getAttribute($aid);
    /**
     * @var NormalAttribute $a
     */
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
            $s->addFilter("in_textlist($aid,'" . implode("') or in_textlist($aid,'", $tkids) . "')");
        } else {
            $s->addFilter("$aid='" . implode("' or $aid='", $tkids) . "'");
        }
    }
    
    $query = $s->getQueries();
    $sdoc->AddQuery($query);
    
    redirect($action, $action->getArgument("app") , "GENERIC_LIST&onefam=$onefamOrigin&famid=$famid&dirid=" . $sdoc->id . "&catg=" . $dirid);
}
?>