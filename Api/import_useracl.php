<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * import USER login and acl
 *
 * @param string $filename the file which contain new login or ACLs
 * @author Anakeen
 * @version $Id: import_useracl.php,v 1.2 2003/08/18 15:46:41 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage WSH
 */
/**
 */
// ---------------------------------------------------------------
// $Id: import_useracl.php,v 1.2 2003/08/18 15:46:41 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Api/import_useracl.php,v $
// ---------------------------------------------------------------
include_once ("Lib.Http.php");
include_once ("ACCESS/upload.php");

$usage = new ApiUsage();

$usage->setDefinitionText("import USER login and acl");
$filename = $usage->addRequiredParameter("filename", "File name");

$usage->verify();

$content = file($filename);

$tnewacl = array();
while (list($k, $v) = each($content)) {
    switch (substr($v, 0, 1)) {
        case "U":
            changeuser($action, substr($v, 2) , true);
            break;

        case "A":
            changeacl($action, substr($v, 2) , true);
            break;
    }
}
?>