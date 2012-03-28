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
    $condaTs = "access_free != 'Y'";
    
    if ($filterName) {
        $name = pg_escape_string(mb_strtolower($filterName));
        $cond = sprintf(" and (lower(name) ~'%s' or lower(short_name) ~ '%s' )", $name, $name);
        $condaTs.= $cond;
    }
    $sql = sprintf("select id, name, short_name from application where $condaTs order by name");
    
    simpleQuery(getDbAccess() , $sql, $result);
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
