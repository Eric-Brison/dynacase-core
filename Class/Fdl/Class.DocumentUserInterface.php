<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */

class DocumentUserInterface
{
    
    static public function addDocumentFooterZone($index, $zone, $edit)
    {
        $val = array();
        $paramName = $edit ? "FOOTER_ZONE_EDIT" : "FOOTER_ZONE_VIEW";
        simpleQuery(getDbAccess() , sprintf("select val from paramv, application WHERE paramv.appid=application.id and application.name='FDL' and paramv.name='%s'", $paramName) , $val, true, true);
        $val = json_decode($val, true);
        $val[$index] = $zone;
        simpleQuery(getDbAccess() , sprintf("update paramv set val='%s' from application where paramv.appid=application.id and application.name='FDL' and paramv.name='%s'", pg_escape_string(json_encode($val)) , $paramName));
    }
}
