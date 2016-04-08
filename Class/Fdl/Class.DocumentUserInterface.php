<?php
/*
 * @author Anakeen
 * @package FDL
 */

class DocumentUserInterface
{
    /**
     * Add/replace a zone for document html footer
     *
     * @param string $index index of zone to add/replace
     * @param string $zone [APP:ZONE] zone reference
     * @param bool $edit set to true for form view, false for consultation
     * @throws \Dcp\Db\Exception
     */
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
