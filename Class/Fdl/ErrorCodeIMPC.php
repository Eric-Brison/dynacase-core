<?php
/*
 * @author Anakeen
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
    /**
     * @errorCode Filename's title must not contain the directory separator char (e.g. '/')
     * @see Dcp\Core\ImportXml::extractFileFromXmlDocument
     */
    const IMPC0005 = "Directory separator char ('%s') not allowed in filename '%s'.";
    /**
     * @errorCode Could not create the MediaIndex directory (possible causes: path already exists, missing free disk space, etc.)
     * @see Dcp\Core\ImportXml::extractFileFromXmlDocument
     */
    const IMPC0006 = "Error creating MediaIndex directory '%s': %s";
    /**
     * @errorCode The MediaDir exists but is not adirectory (possible causes: path already exists but is a file (race-condition?), etc.)
     * @see Dcp\Core\ImportXml::extractFileFromXmlDocument
     */
    const IMPC0007 = "MediaIndex directory '%s' is not a directory.";
    /**
     * @errorCode The output file could not be opended for writing (possible causes: missing free disk space, erroneous right/ownership on parent dir, etc.)
     * @see Dcp\Core\ImportXml::extractFileFromXmlDocument
     */
    const IMPC0008 = "Error opening file '%s' for writing.";
    /**
     * @errorCode
     * @see Dcp\Core\ImportXml::extractFileFromXmlDocument
     */
    const IMPC0009 = "Error opening file '%s' for reading.";
    /**
     * @errorCode
     * @see Dcp\Core\ImportXml::extractFileFromXmlDocument
     */
    const IMPC0010 = "Error opening file '%s' for writing.";
    /**
     * @errorCode
     * @see Dcp\Core\ImportXml::extractFileFromXmlDocument
     */
    const IMPC0011 = "Error renaming '%s' to '%s'.";
    /**
     * @errorCode
     * @see Dcp\Core\ImportXml::extractFileFromXmlDocument
     */
    const IMPC0012 = "Error writing content to '%s'.";
}
