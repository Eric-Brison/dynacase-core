<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Functions used for edition help of USER, GROUP & SOCIETY Family
 *
 * @author Anakeen
 * @version $Id: USERCARD_external.php,v 1.20 2008/11/06 10:16:24 jerome Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Dir.php");
include_once ("FDL/Lib.Dir.php");
include_once ("EXTERNALS/fdl.php");
/**
 * society list
 *
 * the SOCIETY documents and the SITE documents wich doesn't have society father
 * @param string $dbaccess database specification
 * @param string $name string filter on the title
 * @return array/string*3 array of (title, identifier, title)
 * see lfamilly()
 */
function lsociety($dbaccess, $name)
{
    //'lsociety(D,US_SOCIETY):US_IDSOCIETY,US_SOCIETY,
    global $action;
    $dirid = 0;
    
    $societies = lfamilly($dbaccess, 124, $name, $dirid, array(
        "fromid=124"
    ));
    
    $societies+= lfamilly($dbaccess, 126, $name, $dirid, array(
        "si_idsoc isnull"
    ));
    
    return $societies;
}
/**
 * site list
 *
 * all the SITE documents
 * @param string $dbaccess database specification
 * @param string $name string filter on the title
 * @return array/string*3 array of (title, identifier, title)
 * see lfamilly()
 */
function lsite($dbaccess, $name)
{
    //'lsociety(D,US_SOCIETY):US_IDSOCIETY,US_SOCIETY,
    $dirid = 0;
    
    return lfamilly($dbaccess, 124, $name, $dirid);
}
// liste des société
function laddrsoc($dbaccess, $idc)
{
    //'laddrsoc(D,US_IDSOCIETY):US_SOCADDR,US_WORKADDR,US_WORKTOWN,US_WORKPOSTALCODE,US_WORKWEB,US_CEDEX,US_COUNTRY
    $doc = new_Doc($dbaccess, $idc);
    $tr = array();
    if ($doc->isAffected()) {
        $tr[] = array(
            "adresse société",
            "yes",
            $doc->getValue("SI_ADDR") ,
            $doc->getValue("SI_TOWN") ,
            $doc->getValue("SI_POSTCODE") ,
            $doc->getValue("SI_WEB") ,
            $doc->getValue("SI_CEDEX") ,
            $doc->getValue("SI_COUNTRY") ,
            $doc->getValue("SI_PHONE") ,
            $doc->getValue("SI_FAX")
        );
    }
    //   $tr[] = array("adresse propre",
    // 		  " ",
    // 		  "?",
    // 		  "?",
    // 		  "?",
    // 		  "?",
    // 		  "?",
    // 		  "?",
    // 		  "?",
    // 		  "?");
    return $tr;
}
function getSphone($dbaccess, $idc)
{
    $doc = new_Doc($dbaccess, $idc);
    
    $tr = array();
    if ($doc->isAlive()) {
        $tr[] = array(
            $doc->getValue("SI_PHONE") ,
            $doc->getValue("SI_PHONE")
        );
    }
    return $tr;
}
function getSFax($dbaccess, $idc)
{
    $doc = new_Doc($dbaccess, $idc);
    
    $tr = array();
    if ($doc->isAlive()) {
        $tr[] = array(
            $doc->getValue("SI_FAX") ,
            $doc->getValue("SI_FAX")
        );
    }
    return $tr;
}
// liste des personnes d'une société
function lpersonnesociety($dbaccess, $idsociety, $name = "")
{
    // 'lpersonnesociety(D,CMF_IDSFUR,CMF_PFUR):CMF_IDPFUR,CMF_PFUR,CMF_AFUR,CMF_MFUR,CMF_TFUR,CMF_FFUR,CMF_SFUR,CMF_IDSFUR
    $filter = array();
    
    if ($idsociety > 0) $filter[] = "us_idsociety = '$idsociety'";
    
    if ($name != "") $filter[] = "title ~* '$name'";
    
    $tinter = getChildDoc($dbaccess, 0, 0, 100, $filter, getUserId() , "TABLE", getFamIdFromName($dbaccess, "USER"));
    
    $tr = array();
    
    while (list($k, $v) = each($tinter)) {
        
        $sidfur = getv($v, "us_idsociety");
        
        $sfur = getv($v, "us_society");
        $afur = getv($v, "us_workaddr") . "\n" . getv($v, "us_workpostalcode") . " " . getv($v, "us_worktown") . " " . getv($v, "us_workcedex");
        if (getv($v, "us_country") != "") $afur.= "\n" . getv($v, "us_country");
        $tfur = getv($v, "us_phone");
        $ffur = getv($v, "us_fax");
        $mfur = getv($v, "us_mail");
        
        $tr[] = array(
            $v["title"],
            $v["id"],
            $v["title"],
            $afur,
            $mfur,
            $tfur,
            $ffur,
            $sfur,
            $sidfur
        );
    }
    return $tr;
}
// identification société
function gsociety($dbaccess, $idc)
{
    //gsociety(D,US_IDSOCIETY):US_SOCIETY
    $doc = new_Doc($dbaccess, $idc);
    $cl = array(
        $doc->title
    );
    
    return ($cl);
}
// get enum list from society document
function enumscatg()
{
    $dbaccess = getParam("FREEDOM_DB");
    $soc = new_Doc($dbaccess, 124);
    
    if ($soc->isAffected()) {
        /**
         * @var NormalAttribute $a
         */
        $a = $soc->getAttribute("si_catg");
        return $a->phpfunc;
    }
    return "";
}
/**
 * search all members (and in sub groups) from a group
 * @param string $dbaccess database access
 * @param int $groupid group document identificator
 * @param string $name the key filter
 * @param string $sort the sort column default is lastname (available : firstname, mail)
 * @param boolean $searchinmail if true search also in mail address
 * @param int $limit max responses returned (default 50)
 */
function members($dbaccess, $groupid, $name = "", $sort = 'lastname', $searchinmail = false, $limit = 50)
{
    $tr = array();
    
    $doc = new_Doc($dbaccess, $groupid);
    if (!$doc->isAlive()) return sprintf(_("no valid document group : %s") , $groupid);
    $name = trim($name);
    $gid = $doc->getValue("us_whatid");
    if ($gid) {
        $u = new Account("", $gid);
        if (!$u->isAffected()) return sprintf(_("no valid group : %s") , $gid);
        $g = new Group();
        $lg = $g->getChildsGroupId($u->id);
        $lg[] = $u->id;
        $cond = getSqlCond($lg, "idgroup", true);
        if (!$cond) $cond = "true";
        $condname = "";
        if ($name) {
            $tname = explode(' ', $name);
            $name = pg_escape_string($name);
            $condmail = '';
            if ($searchinmail) $condmail = sprintf("or (mail ~* '%s')", $name);
            if (count($tname) > 1) {
                $condname = sprintf("and (coalesce(firstname,'') || ' ' || coalesce(lastname,'') ~* '%s' $condmail)", $name);
            } else {
                $condname = sprintf("and (firstname ~* '%s' or lastname ~* '%s' $condmail)", $name, $name);
            }
        }
        if ($sort) $sort = pg_escape_string($sort);
        else $sort = 'lastname';
        $sql = sprintf("SELECT distinct on (%s, users.fid) users.firstname , users.lastname, users.mail,users.fid from users, groups where %s and (groups.iduser=users.id) %s and isgroup != 'Y' order by %s limit %d", $sort, $cond, $condname, $sort, $limit);
        $err = simpleQuery($doc->dbaccess, $sql, $result);
        if ($err != "") return $err;
        foreach ($result as $k => $v) {
            $mail = $v["mail"] ? (' (' . $v["mail"] . ')') : '';
            $tr[] = array(
                $v["firstname"] . " " . $v["lastname"] . $mail,
                $v["fid"],
                $v["lastname"] . " " . $v["firstname"]
            );
        }
    } else {
        $oa = $doc->getAttribute("grp_ruser");
        if (!$oa) return sprintf(_("document %s is not a group") , $doc->getTitle());
        $tmembers = $doc->getTvalue("GRP_RUSER");
        $tmembersid = $doc->getTvalue("GRP_IDRUSER");
        
        $pattern_name = preg_quote($name);
        foreach ($tmembersid as $k => $v) {
            if (($name == "") || (preg_match("/$pattern_name/i", $tmembers[$k]))) $tr[] = array(
                $tmembers[$k],
                $v,
                $tmembers[$k]
            );
        }
    }
    
    return $tr;
}
//return my groups
function mygroups($name = "")
{
    $dbaccess = GetParam("FREEDOM_DB");
    $docuid = doc::getUserId();
    $tr = array();
    $doc = new_Doc($dbaccess, $docuid);
    $grid = $doc->getTValue("us_idgroup");
    $grname = $doc->getTValue("us_group");
    
    $pattern_name = preg_quote($name);
    foreach ($grid as $k => $v) {
        if (($name == "") || (preg_match("/$pattern_name/i", $grname[$k]))) $tr[] = array(
            $grname[$k],
            $v,
            $grname[$k]
        );
    }
    return $tr;
}
?>
