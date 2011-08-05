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
 * @version $Id: migr_histo.php,v 1.2 2006/08/10 08:45:36 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */
// refreah for a classname
// use this only if you have changed title attributes
include_once ("FDL/Lib.Dir.php");

function converthisto($dbaccess, $id, $initid, $comment)
{
    
    $tc = explode("\n", $comment);
    
    $h = new DocHisto($dbaccess);
    $h->id = $id;
    $h->level = HISTO_INFO;
    $h->initid = $initid;
    $h->uid = "0";
    
    foreach ($tc as $vc) {
        if (preg_match("/([^\[]*)\[([^]]*)\](.*)/", $vc, $reg)) {
            $kc++;
            if (preg_match("|([0-9]{1,2})/([0-9]{1,2})/([0-9]{1,4}) ([0-2]{0,1}[0-9]):([0-5]{0,1}[0-9])|", $reg[1], $regt)) {
                $stime = strftime("%d-%m-%Y %H:%M", mktime($regt[4], $regt[5], $regt[6], $regt[2], $regt[1], $regt[3]));
            } else $stime = $reg[1];
            
            $h->date = $stime;
            $h->uname = $reg[2];
            $h->comment = $reg[3];
            
            $err = $h->Add();
            if ($err != "") return $err;
        } else {
            print "\t bizarre $is : $comment\n";
        }
    };
    return $err;
}
$dbaccess = GetParam("FREEDOM_DB");
$filter[] = "comment is not null";

$ldoc = getChildDoc($dbaccess, 0, 0, "ALL", $filter, 1, "ITEM", 0, false, "", false, "also");

$reste = countDocs($ldoc);

while ($doc = getNextDoc($dbaccess, $ldoc)) {
    print "$reste)" . $doc->title . "\n";
    converthisto($dbaccess, $doc->id, $doc->initid, $doc->comment);
    $reste--;
}
?>