<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: FDL_migr_0.2.6.php,v 1.2 2003/08/18 15:47:04 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

// ==========================================================================
// default attribute migration

// Author          Eric Brison	(Anakeen)
// Date            May, 23 2003 - 11:13:08
// Last Update     $Date: 2003/08/18 15:47:04 $
// Version         $Revision: 1.2 $
// ==========================================================================


$ConnId = pg_connect ("dbname=freedom user=anakeen");
$ResId = pg_query ($ConnId,"select attrids,values,fromid from doc where usefor='D'" );

$zou="rognougnou";
print "update docfam set defval='$zou';\n";
while ($row = pg_fetch_array($ResId))
{

  $ta = explode("£",$row["attrids"]);
  $tv = explode("£",$row["values"]);
  $fromid=$row["fromid"];
  while(list($k,$v) = each($ta))   {
    
    if ($v != "") {
      
      print "update docfam set defval=defval||'[$v|".$tv[$k]."]'" . "where  id=$fromid;\n";
    }
  }

} 
print "update docfam set defval='' where defval='$zou';\n";
print " update docfam set defval=str_replace(defval,'$zou','') where defval != ''\n";


pg_close ($ConnId);

// EOF
?>