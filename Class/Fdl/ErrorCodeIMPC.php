<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Errors code used when import Documents
 * @class ErrorCodeIMPC
 * @brief List all error for import documents
 * @see ErrorCode
 */
class ErrorCodeIMPC
{
    /**
     * @errorCode Only valid format can be used to parameter export
     * @see Dcp\Core\ImportXml::extractFileFromXmlDocument
     */
    const IMPC0001 = 'IMPORT : import Xml extract : file "%s" not found.';
    /**
     * @errorCode Only valid format can be used to parameter export
     * @see Dcp\Core\ImportXml::importSingleXmlFile
     */
    const IMPC0002 = 'IMPORT : Cannot create directory "%s" for xml import.';
    /**
     * @errorCode Only valid format can be used to parameter export
     * @see Dcp\Core\ImportXml::importZipFile
     */
    const IMPC0003 = 'IMPORT : Cannot create directory "%s" for xml zip import.';
    /**
     * @errorCode Only valid format can be used to parameter export
     * @see Dcp\Core\ImportXml::importZipFile
     */
    const IMPC0004 = 'IMPORT : Cannot unzip file "%s" : %s.';
}
