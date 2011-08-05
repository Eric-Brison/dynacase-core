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
 * @version $Id: clearcache.php,v 1.2 2003/08/18 15:46:41 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage CORE
 */
/**
 */
// $Id: clearcache.php,v 1.2 2003/08/18 15:46:41 eric Exp $
// $Log: clearcache.php,v $
// Revision 1.2  2003/08/18 15:46:41  eric
// phpdoc
//
// Revision 1.1  2002/04/15 14:19:59  eric
// ajout clear cache objet
//

function clearcache(&$action)
{
    //  session_unset();
    session_unregister("CacheObj");
    
    redirect($action, "CORE", "HEAD", $action->GetParam("CORE_STANDURL"));
}
?>
