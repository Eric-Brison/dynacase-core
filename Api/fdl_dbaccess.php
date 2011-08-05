<?php
/**
 * Get database coordonate for freedom access by psql
 *
 * @author Anakeen 2000 
 * @version $Id: fdl_dbaccess.php,v 1.2 2006/02/03 16:03:13 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

$dbaccess=getParam("FREEDOM_DB");
if ($dbaccess != "") {
  if (preg_match('/dbname=[ ]*([a-z_0-9]*)/',$dbaccess,$reg)) {  
      $dbname=$reg[1];
    }
    if (preg_match('/host=[ ]*([a-z_0-9\.]*)/',$dbaccess,$reg)) {  
      $dbhost=$reg[1];
    }
    if (preg_match('/port=[ ]*([a-z_0-9]*)/',$dbaccess,$reg)) {  
      $dbport=$reg[1];
    }
    $dbpsql="";
    if ($dbhost != "")  $dbpsql.= "--host $dbhost ";
    if ($dbport != "")  $dbpsql.= "--port $dbport ";
    $dbpsql.= "--username anakeen --dbname $dbname ";
}

print $dbpsql;

?>