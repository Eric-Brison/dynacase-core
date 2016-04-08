<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Errors code used when export Documents
 * @class ErrorCodeEXPC
 * @brief List all error for export documents
 * @see ErrorCode
 */
class ErrorCodeEXPC
{
    /**
     * @errorCode Only valid format can be used to parameter export
     * @see Dcp\ExportCollection::setOutputFormat
     */
    const EXPC0001 = 'EXPORT : invalid out format "%s", valid are %s.';
    /**
     * @errorCode Need set file output before export collection
     * @see Dcp\ExportCollection::setOutputFilePath
     */
    const EXPC0002 = 'EXPORT : Output file must be set';
    /**
     * @errorCode The output file must be writable
     * @see Dcp\ExportCollection::setOutputFilePath
     */
    const EXPC0003 = 'EXPORT : Output file "%s" is not writable';
    /**
     * @errorCode Need set document collection to export
     * @see Dcp\ExportCollection::setDocumentList
     */
    const EXPC0004 = 'EXPORT : Collection must be set';
    /**
     * @errorCode The output file must be writable
     * @see Dcp\ExportCollection::setOutputFilePath
     */
    const EXPC0005 = 'EXPORT : Output file "%s" cannot be opened in write mode';
    /**
     * @errorCode XML format export need to create sub directory
     * @see Dcp\ExportCollection::export
     */
    const EXPC0006 = 'EXPORT : Can create temporay directory "%s" for XML exports';
    /**
     * @errorCode The output file must be writable
     * @see Dcp\ExportCollection::export
     */
    const EXPC0007 = 'EXPORT : Output XML file "%s" is not writable';
    /**
     * @errorCode  XML format export need to goto sub directory where XML files are produced
     * @see Dcp\ExportCollection::export
     */
    const EXPC0008 = 'EXPORT : Cannot change dir to  "%s" : XML export aborted';
    /**
     * @errorCode XML format export need to restore original directory
     * @see Dcp\ExportCollection::export
     */
    const EXPC0009 = 'EXPORT : Cannot change dir to  "%s" : XML export aborted';
    /**
     * @errorCode XML format cannot append data to end of file
     * @see Dcp\ExportCollection::export
     */
    const EXPC0010 = 'EXPORT : Ouput file  "%s" cannot be completed';
    /**
     * @errorCode XML format cannot append last line to output file
     * @see Dcp\ExportCollection::export
     */
    const EXPC0011 = 'EXPORT : Ouput file  "%s" cannot be finished';
    /**
     * @errorCode XML archive format : output file cannot be produced
     * @see Dcp\ExportCollection::export
     */
    const EXPC0012 = 'EXPORT : Xml archive "%s" cannot be created';
    /**
     * @errorCode When use csv with file option
     * @see Dcp\ExportCollection::setOutputFilePath
     */
    const EXPC0013 = 'EXPORT : Work output file "%s" cannot be opened in write mode';
    /**
     * @errorCode When use csv with file option
     * @see Dcp\ExportCollection::setOutputFilePath
     */
    const EXPC0014 = 'EXPORT : Extract vault file : Caonot copy "%s" to "%s"';
    /**
     * @errorCode Only valid encoding can be used to parameter export
     * @see Dcp\ExportCollection::setOutputFileEncoding
     */
    const EXPC0015 = 'EXPORT : invalid encoding format "%s", valid are %s.';
    /**
     * @errorCode Only one character for CSV separator : generally comma
     * @see Dcp\ExportCollection::setCvsSeparator
     */
    const EXPC0016 = 'EXPORT : Only one character for CSV separator : found "%s".';
    /**
     * @errorCode The output file cannot be written
     * @see Dcp\ExportXmlDocument::writeTo
     */
    const EXPC0100 = 'EXPORT Xml : Cannot write output to file "%s".';
    /**
     * @errorCode The output file cannot be written
     * @see Dcp\ExportXmlDocument::writeTo
     */
    const EXPC0101 = 'EXPORT Xml : Cannot apen output in write mode "%s".';
    /**
     * @errorCode The output file cannot be written
     * @see Dcp\ExportXmlDocument::writeTo
     */
    const EXPC0102 = 'EXPORT Xml : Cannot insert attached files to output  file "%s".';
    /**
     * @errorCode The output file cannot be written
     * @see Dcp\ExportXmlDocument::getXml
     * @see Dcp\ExportXmlDocument::setExportFiles
     */
    const EXPC0103 = 'EXPORT Xml : Cannot export file using "ExportXmlDocument::getXml"';
}
