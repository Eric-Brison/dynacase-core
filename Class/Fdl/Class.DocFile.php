<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: Class.DocFile.php,v 1.11 2006/04/03 14:56:26 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 */
// ---------------------------------------------------------------
// $Id: Class.DocFile.php,v 1.11 2006/04/03 14:56:26 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Fdl/Class.DocFile.php,v $
// ---------------------------------------------------------------
$CLASS_DOCFILE_PHP = '$Id: Class.DocFile.php,v 1.11 2006/04/03 14:56:26 eric Exp $';

include_once ("FDL/Class.PDoc.php");

class DocFile extends PDoc
{
    
    var $defDoctype = 'F';
    var $defClassname = 'DocFile';
    // suppress no numeric characters
    function suppressNotNum($s)
    {
        $i = 0;
        while (($i < strlen($s)) && ($s[$i] >= '0') && ($s[$i] <= '9')) $i++;
        return substr($s, 0, $i);
    }
}
?>