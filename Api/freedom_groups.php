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
 * @version $Id: freedom_groups.php,v 1.16 2009/01/16 08:51:42 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage
 */
/**
 */
// refreah for a classname
// use this only if you have changed title attributes
include_once ("FDL/Class.Doc.php");
include_once ("Lib.Common.php");

$appl = new Application();
$appl->Set("FDL", $core);

$dbaccess = $appl->GetParam("FREEDOM_DB");
if ($dbaccess == "") {
    print "Database not found : param FREEDOM_DB";
    exit;
}

$doc = new_Doc($dbaccess);

$pgservice_core = getServiceCore();
$pgservice_freedom = getServiceFreedom();

$big = false; // need to set to true when table group count > 20000
if ($pgservice_core == $pgservice_freedom) {
    system(sprintf("PGSERVICE=%s psql -c 'delete from docperm where upacl=0 and unacl=0;update docperm set cacl=0 where cacl != 0;'", escapeshellarg($pgservice_freedom)));
} else {
    if ($big) system(sprintf("PGSERVICE=%s psql -c 'DROP INDEX groups_idx2;DROP INDEX groups_idx1;'", escapeshellarg($pgservice_freedom)));
    system(sprintf("PGSERVICE=%s psql -c 'delete from groups;delete from docperm where upacl=0 and unacl=0;update docperm set cacl=0 where cacl != 0;'", escapeshellarg($pgservice_freedom)));
    system(sprintf("PGSERVICE=%s pg_dump -a --disable-triggers -t groups | PGSERVICE=%s psql", escapeshellarg($pgservice_core) , escapeshellarg($pgservice_freedom)));
    
    if ($big) system(sprintf("PGSERVICE=%s psql -c 'CREATE unique INDEX groups_idx2 on groups(iduser,idgroup);CREATE INDEX groups_idx1 on  groups(iduser);'", escapeshellarg($pgservice_freedom)));
}

system("PGSERVICE=\"$pgservice_core\" psql -c 'DELETE FROM permission WHERE computed = TRUE;'");
//system("echo 'vacuum  docperm;vacuum  groups' | psql $dbfree");
//system("echo 'select getuperm(userid, docid) from docperm' | psql freedom anakeen");

?>