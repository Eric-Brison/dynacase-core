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
 * @version $Id: Class.SessionVar.php,v 1.3 2003/08/18 15:46:42 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package WHAT
 * @subpackage CORE
 */
/**
 */
// ---------------------------------------------------------------------------
// Marc Claverie (marc.claverie@anakeen.com)- anakeen 2000
// ---------------------------------------------------------------------------
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or (at
//  your option) any later version.
//
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
// or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
// for more details.
//
// You should have received a copy of the GNU General Public License along
// with this program; if not, write to the Free Software Foundation, Inc.,
// 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
// ---------------------------------------------------------------------------
// $Id: Class.SessionVar.php,v 1.3 2003/08/18 15:46:42 eric Exp $
//
// $Log: Class.SessionVar.php,v $
// Revision 1.3  2003/08/18 15:46:42  eric
// phpdoc
//
// Revision 1.2  2002/01/25 14:31:37  eric
// gestion de cache objet - variable de session
//
// Revision 1.1  2002/01/08 12:41:34  eric
// first
//
// Revision 1.2  2000/10/11 12:18:41  yannick
// Gestion des sessions
//
// Revision 1.1  2000/10/09 10:44:02  yannick
// Déplacement des sessions
//
// Revision 1.1.1.1  2000/10/05 17:29:10  yannick
// Importation
//
// Revision 1.1  2000/06/30 15:24:49  marc
// Mise en conf, version initiale
//
//
// ---------------------------------------------------------------------------
$DROITS_CLASS_SESSIONVAR_PHP = '$Id: Class.SessionVar.php,v 1.3 2003/08/18 15:46:42 eric Exp $';

include_once ('Class.DbObj.php');

Class SessionVar extends DbObj
{
    
    var $fields = array(
        "session",
        "key",
        "val"
    );
    
    var $id_fields = array(
        "session",
        "key"
    );
    
    var $dbtable = "session_vars";
    
    var $sqlcreate = "create table session_vars ( session varchar(100), 
			    key	    varchar(50),
			    val	    varchar(200));
create index session_vars_idx on session_vars(session,key);";
    var $isCacheble = false;
}
?>
