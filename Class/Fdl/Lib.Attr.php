<?php
/*
 * Generation of PHP Document classes
 * @author Anakeen
 * @package FDL
*/

function activateTrigger($dbaccess, $docid)
{
    Dcp\FamilyImport::activateTrigger($dbaccess, $docid);
}
/**
 * refresh PHP Class & Postgres Table Definition
 * @param string $dbaccess
 * @param int $docid
 * @return string error message
 */
function refreshPhpPgDoc($dbaccess, $docid)
{
    return Dcp\FamilyImport::refreshPhpPgDoc($dbaccess, $docid);
}
/**
 * Extract the main type and the format from a type string
 *
 * @param string $type e.g. 'array("empty")'
 *
 * @return array() struct e.g. array('type' => 'array', 'format' => '"empty"')
 */
function parseType($type)
{
    return Dcp\FamilyImport::parseType($type);
}

