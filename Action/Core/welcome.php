<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */

function welcome(&$action)
{
    
    $action->parent->AddCssRef("CORE:welcome.css", true);
    $action->lay->set("thisyear", strftime("%Y", time()));
    $action->lay->set("version", $action->GetParam("VERSION"));
    $action->lay->set("userRealName", $action->user->firstname . " " . $action->user->lastname);
    $action->lay->set("userDomain", getParam("CORE_CLIENT"));
    // 3rd parties developpement
    $fext = $action->getLayoutFile("externals-dev.xml");
    $action->lay->set("HAVE_EXTERNALS", false);
    $trd = array();
    if (file_exists($fext)) {
        $fdata = file($fext);
        foreach ($fdata as $k => $v) {
            $action->lay->set("HAVE_EXTERNALS", true);
            $ds = explode("#", $v);
            $trd[] = array(
                "site" => $ds[0],
                "name" => $ds[1],
                "license" => $ds[2]
            );
        }
    }
    $action->lay->setBlockData("EXTERNALS", $trd);
    // retrieve installed applications
    $query = new QueryDb($action->dbaccess, "Application");
    $query->basic_elem->sup_where = array(
        "available='Y'",
        "displayable='Y'",
        "tag!='CORE' or tag is null"
    );
    $list = $query->Query(0, 0, "TABLE");
    $ap = 0;
    $tab = array();
    if ($query->nb > 0) {
        $i = 0;
        $j = 0;
        $lapps = array();
        foreach ($list as $k => $appli) {
            if ($appli["access_free"] == "N") {
                $action->log->debug("Access not free for :" . $appli["name"]);
                if (isset($action->user)) {
                    if ($action->user->id != 1) { // no control for user Admin
                        // search  acl for root action
                        $queryact = new QueryDb($action->dbaccess, "Action");
                        $queryact->AddQuery("id_application=" . $appli["id"]);
                        $queryact->AddQuery("root='Y'");
                        $listact = $queryact->Query(0, 0, "TABLE");
                        $root_acl_name = $listact[0]["acl"];
                        if (!$action->HasPermission($root_acl_name, $appli["id"])) continue;
                    }
                }
            } else {
                continue;
            }
            $lapps[$ap]["name"] = $appli["name"];
            $lapps[$ap]["desc"] = $action->text($appli["description"]); // translate
            $lapps[$ap]["sname"] = $action->text($appli["short_name"]); // translate
            $lapps[$ap]["icon"] = $action->GetImageUrl($appli["icon"]);
            if ($lapps[$ap]["icon"] == "CORE/Images/noimage.png") $lapps[$ap]["icon"] = $appli["name"] . "/Images/" . $appli["icon"];
            $ap++;
        }
    }
    $action->lay->set("appsdev", count($lapps) > 0);
    $action->lay->setBlockData("apps", $lapps);
}
?>