<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: updatetitles.php,v 1.4 2003/08/18 15:47:04 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */
// update title for a classname
// use this only if you have changed title attributes
include_once ("FDL/Class.Doc.php");

$usage = new ApiUsage();

$usage->setDefinitionText("Update titles");
$className = $usage->addRequiredParameter("class", "classname filter"); // classname filter
$famId = $usage->addRequiredParameter("famid", "family filter"); // familly filter
$usage->verify();

$appl = new Application();
$appl->Set("FDL", $core);

$dbaccess = $appl->dbaccess;
if ($dbaccess == "") {
    print "Database not found : appl->dbaccess";
    exit;
}

$query = new QueryDb($dbaccess, "Doc");
$query->AddQuery("locked != -1");

if ($className != "-") $query->AddQuery("classname ~* '$className'");
if ($famId > 0) $query->AddQuery("fromid = $famId");

$table1 = $query->Query();

if ($query->nb > 0) {
    foreach ($table1 as $k => $v) {
        /**
         * @var Doc $v
         */
        print $v->title . "-";
        $v->refreshTitle();
        $v->Modify();
        print $v->title . "\n";
    }
}
