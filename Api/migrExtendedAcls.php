<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Generate Php Document Classes
 *
 * @author Anakeen
 * @version $Id: fdl_adoc.php,v 1.20 2008/10/30 17:34:31 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */
// refreah for a classname
// use this only if you have changed title attributes
include_once ("FDL/Lib.Attr.php");
include_once ("FDL/Class.DocFam.php");

$appl = new Application();
$appl->Set("FDL", $core);

$dbaccess = $appl->GetParam("FREEDOM_DB");
if ($dbaccess == "") {
    print "Database not found : param FREEDOM_DB";
    exit;
}
$usage = new ApiUsage();
$usage->setText("Migrate old Acls for workflow and CV to extended acls");
$usage->verify();
/**
 * @var Action $action
 */
// First Part: Workflow
print "\t === migrate workflows ===\n";
$s = new SearchDoc($action->dbaccess, "WDoc");
$s->setObjectReturn(true);
$s->search();
/**
 * @var WDoc $wdoc
 */
while ($wdoc = $s->nextDoc()) {
    print ("\n" . $wdoc->getTitle() . " : #" . $wdoc->id);
    $tr = $wdoc->transitions;
    if (is_array($tr)) {
        // old code compatibility
        $ka = POS_WF;
        
        foreach ($tr as $k => $trans) {
            $wdoc->dacls[$k] = array(
                "pos" => $ka,
                "description" => _($k)
            );
            $ka++;
        }
        
        $sql = sprintf("select * from docperm where docid=%d", $wdoc->id);
        simpleQuery($action->dbaccess, $sql, $acls);
        foreach ($tr as $tid => $v) {
            /**
             * @var $aAcl array
             */
            foreach ($acls as $aAcl) {
                if ($wdoc->controlUp($aAcl["upacl"], $tid) == "") {
                    print "\n\tAdd acl $tid to " . Account::getDisplayName($aAcl["userid"]);
                    $wdoc->addControl($aAcl["userid"], $tid);
                }
            }
        }
    }
    print "\n";
}
// Second Part: View Control
print "\n\t === migrate view controller === \n";
$s = new SearchDoc($action->dbaccess, "CVDoc");
$s->setObjectReturn(true);
$s->search();
/**
 * @var CVDoc $cvdoc
 */
while ($cvdoc = $s->nextDoc()) {
    print ("\n" . $cvdoc->getTitle() . " : #" . $cvdoc->id);
    // old code compatibility
    $ka = POS_WF;
    $ti = $cvdoc->getTValue("CV_IDVIEW");
    $tl = $cvdoc->getTValue("CV_LVIEW");
    
    $ka = POS_WF;
    foreach ($ti as $k => $v) {
        if ($ti[$k] == "") $cvk = "CV$k";
        else $cvk = $ti[$k];
        $cvdoc->dacls[$cvk] = array(
            "pos" => $ka,
            "description" => $tl[$k]
        );
        $cvdoc->acls[] = $cvk;
        $ka++;
    }
    
    $sql = sprintf("select * from docperm where docid=%d", $cvdoc->id);
    simpleQuery($action->dbaccess, $sql, $acls);
    foreach ($ti as $k => $tid) {
        /**
         * @var $aAcl array
         */
        foreach ($acls as $aAcl) {
            if ($cvdoc->controlUp($aAcl["upacl"], $tid) == "") {
                
                $err = $cvdoc->addControl($aAcl["userid"], $tid);
                if ($err) print $err;
                else print "\n\tAdd acl $tid to " . Account::getDisplayName($aAcl["userid"]);
            } else {
                //print "no $tid for ".Account::getDisplayName($aAcl["userid"]);
                
            }
        }
    }
}
print "\n";
