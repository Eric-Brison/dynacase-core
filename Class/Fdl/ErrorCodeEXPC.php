<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
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
}
