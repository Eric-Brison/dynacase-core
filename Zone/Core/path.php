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
 * @version $Id: path.php,v 1.3 2004/03/22 15:21:40 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage CORE
 */
/**
 */
// ---------------------------------------------------------------
// $Id: path.php,v 1.3 2004/03/22 15:21:40 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Zone/Core/path.php,v $
// ---------------------------------------------------------------
// $Log: path.php,v $
// Revision 1.3  2004/03/22 15:21:40  eric
// change HTTP variable name to put register_globals = Off
//
// Revision 1.2  2003/08/18 15:46:42  eric
// phpdoc
//
// Revision 1.1  2002/01/08 12:41:34  eric
// first
//
// Revision 1.5  2000/10/22 14:22:51  marc
// Gestion des langues
//
// Revision 1.4  2000/10/11 16:31:33  yannick
// Nouvelle gestion de l'init
//
// Revision 1.3  2000/10/10 19:08:37  marc
// Mise au point
//
// Revision 1.2  2000/10/06 19:38:34  marc
// Mise en oeuvre
//
//
// ---------------------------------------------------------------
include_once ("Class.Action.php");

function path(&$action)
{
    global $_GET;
    $app = $_GET["app"];
    $act = $_GET["action"];
    
    if ($app != "CORE") {
        
        $appcalled = new Application();
        $appcalled->Set($app, $action->parent);
        
        $c = new Action();
        $c->Set($act, $appcalled);
        $ito = 0;
        do {
            $continue = 0;
            // Add current action
            $toc[$ito]["base"] = $c->parent->Getparam("CORE_BASEURL");
            $toc[$ito]["appl"] = $c->parent->name;
            $toc[$ito]["name"] = $c->name;
            $toc[$ito]["style"] = "tocitem0on";
            if (substr($c->short_name, 0, 1) == '&') {
                $sn = substr($c->short_name, 1, strlen($c->short_name));
                $toc[$ito]["descr"] = $c->text($sn);
            } else {
                $toc[$ito]["descr"] = $c->short_name;
            }
            $ito++;
            $action->log->debug("Current node : {$c->name}, father id {$c->father}");
            if ($c->father > 0) {
                $query = new QueryDb($action->dbaccess, "Action");
                $query->basic_elem->sup_where = array(
                    "id='" . $c->father . "'"
                );
                $query->Query(0, 0, "TABLE");
                if ($query->nb > 0) {
                    $c = new Action();
                    $c->Set($query->list[0]->name, $appcalled);
                    $continue = 1;
                }
            }
        } while ($continue);
        // Add application
        $toc[$ito]["base"] = $appcalled->Getparam("CORE_BASEURL");
        $toc[$ito]["appl"] = $appcalled->name;
        $toc[$ito]["name"] = "";
        $toc[$ito]["style"] = "tocitem0on";
        $toc[$ito]["descr"] = $appcalled->name;
        $ito++;
    }
    
    if (isset($toc)) {
        $toc2 = array_reverse($toc);
        $action->lay->SetBlockCorresp("PATH", "BASE", "base");
        $action->lay->SetBlockCorresp("PATH", "APPL", "appl");
        $action->lay->SetBlockCorresp("PATH", "NAME", "name");
        $action->lay->SetBlockCorresp("PATH", "STYLE", "style");
        $action->lay->SetBlockCorresp("PATH", "DESCR", "descr");
        $action->lay->SetBlockData("PATH", $toc2);
        $action->lay->SetBlockData("PATHEMPTY", NULL);
    } else {
        $action->lay->SetBlockData("PATH", NULL);
        $action->lay->SetBlockCorresp("PATHEMPTY", "NOPCAR", "nopcar");
        $action->lay->SetBlockData("PATHEMPTY", array(
            array(
                "nopcar" => " "
            )
        ));
    }
}
?>
