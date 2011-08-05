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
 * @version $Id: usercard_csv2vcard.php,v 1.6 2008/08/14 09:59:14 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */
// remove all tempory doc and orphelines values
include_once ("FDL/Class.Doc.php");
include_once ("FDL/Class.UsercardVcard.php");

$fimport = GetHttpVars("ifile"); // file to convert
$fvcf = GetHttpVars("ofile", "php://stdin"); // output file
$appl = new Application();
$appl->Set("USERCARD", $core);

$dbaccess = $appl->GetParam("FREEDOM_DB");
if ($dbaccess == "") {
    print "Database not found : param FREEDOM_DB";
    exit;
}

$doc = new_Doc($dbaccess, getFamIdFromName($dbaccess, "USER"));

$lattr = $doc->GetNormalAttributes();
$format = "DOC;" . $doc->id . ";<special id>;<special dirid>; ";

while (list($k, $attr) = each($lattr)) {
    $format.= $attr->getLabel() . " ;";
}
//print_r( $lattr);;
$usercard = new UsercardVcard();

$fdoc = fopen($fimport, "r");

$deffam = $action->GetParam("DEFAULT_FAMILY", getFamIdFromName($dbaccess, "USER"));

$usercard->open($fvcf, "w");
while ($data = fgetcsv($fdoc, 1000, ";")) {
    $num = count($data);
    if ($data[0] != "DOC") continue;
    if ($data[1] != $deffam) continue;
    
    $attr = array();
    reset($data);
    //array_shift($data);array_shift($data);array_shift($data);array_shift($data);
    while (list($k, $v) = each($data)) {
        if ($k > 3) $attr[$lattr[$k - 4]->id] = $v;
    }
    
    $usercard->WriteCard($attr["us_lname"] . " " . $attr["us_fname"], $attr);
}
?>