<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * importation of documents
 *
 * @author Anakeen
 * @version $Id: freedom_import.php,v 1.9 2008/11/13 16:49:16 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage WSH
 */
/**
 */

global $action;
// refreah for a classname
// use this only if you have changed title attributes
include_once ("FDL/Class.Doc.php");
include_once ("FDL/Class.SearchDoc.php");

$usage = new ApiUsage();
$usage->setText("Init view privilege ");
$accountOnly = $usage->addOption("reset-account", "reset account members of", array(
    "yes",
    "no"
) , "no");

$usage->verify();
$accountOnly = ($accountOnly == "yes");

$dbaccess = $action->getParam("FREEDOM_DB");
if ($dbaccess == "") {
    print "Database not found : param FREEDOM_DB";
    exit();
}

ini_set("max_execution_time", "-1"); // no limit
if ($accountOnly) {
    $err = simpleQuery($dbaccess, "select * from users order by id", $tusers);
} else {
    $err = simpleQuery($dbaccess, "select * from users where memberof is null", $tusers);
}
$card = count($tusers);
printf("\n%d user privilege to update.\n", $card);

$u = new Account($dbaccess);
foreach ($tusers as $tu) {
    printf("%d) %s \n", $card, $tu["login"]);
    $u->affect($tu);
    $u->updateMemberOf();
    $card--;
}

if (!$accountOnly) {
    $tfam[] = 3;
    $err == simpleQuery($dbaccess, "select name from docfam order by id", $tfams, true);
    $tfam = array_merge($tfam, $tfams);
    $tfam[] = - 1;
    foreach ($tfam as $famid) {
        $s = new SearchDoc($dbaccess, $famid);
        $s->addFilter("views is null");
        $s->addFilter("profid > 0");
        $s->setObjectReturn();
        $s->latest = false;
        $s->search();
        $card = $s->count();
        if ($card > 0) {
            printf("\n%s) %d records\n", $famid, $card);
            while ($doc = $s->nextDoc()) {
                $doc->setViewProfil();
                print '.';
            }
        }
    }
    print "update empty profil...";
    $err == simpleQuery($dbaccess, "update doc set views='{0}' where (profid=0 or profid is null)");
}
print "Finish profiling.";
?>
