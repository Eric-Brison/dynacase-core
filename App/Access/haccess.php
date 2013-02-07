<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * return account documents
 * @param string $filterName title filter key
 * @param int $limit max account returned
 * @return array
 */
function accessGetAccounts($accountType, $filterName = '', $limit = 15)
{
    $tout = array();
    $condaTs = GetSqlCond(explode('|', $accountType) , "accounttype");
    
    if ($filterName) {
        $name = pg_escape_string(mb_strtolower($filterName));
        $cond = sprintf(" and (login~'%s' or lower(lastname) ~ '%s' or lower(firstname) ~ '%s')", $name, $name, $name);
        $condaTs.= $cond;
    }
    $sql = sprintf("select id, login, firstname, lastname, accounttype from users where $condaTs order by lastname");
    
    simpleQuery(getDbAccess() , $sql, $result);
    $t = array();
    
    foreach ($result as $aAccount) {
        if ($aAccount["accounttype"] == 'U') {
            $dn = trim(sprintf("%s %s (%s)", ($aAccount["lastname"]) , $aAccount["firstname"], $aAccount["login"]));
        } else {
            $dn = trim(sprintf("%s %s", ($aAccount["lastname"]) , $aAccount["firstname"]));
        }
        $t[] = array(
            $dn,
            $aAccount["id"],
            $dn
        );
    }
    if ((count($t) == 0) && ($filterName != '')) return sprintf(_("no account match '%s'") , $filterName);
    return $t;
}

function accessGetApps($filterName = '', $limit = 35)
{
    $condaTs = "app.id = acl.id_application";

    if ($filterName) {
        $name = pg_escape_string(mb_strtolower($filterName));
        $cond = sprintf("and (lower(app.name) ~'%s' or lower(app.short_name) ~ '%s' )", $name, $name);
        $condaTs .= $cond;
    }
    $sql = sprintf("select app.id, app.name, app.short_name from application as app, acl as acl WHERE $condaTs group by app.id, app.name, app.short_name order by app.name;");

    simpleQuery(getDbAccess(), $sql, $result);
    $t = array();
    //$t[]=array($sql,'g','f');
    foreach ($result as $aAccount) {
        $dn = trim(sprintf("%s (%s)", ($aAccount["name"]) , $aAccount["short_name"]));
        $t[] = array(
            $dn,
            $aAccount["id"],
            $dn
        );
    }
    if ((count($t) == 0) && ($filterName != '')) return sprintf(_("no application match '%s'") , $filterName);
    return $t;
}
