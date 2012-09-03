<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Errors code used when import document
 * @class ErrorCodeDOC
 * @brief List all error code for DOC
 * @see ErrorCode
 */
class ErrorCodeDOC
{
    /**
     * @errorCode
     * when import document the family reference is required
     */
    const DOC0002 = 'family reference is empty for document "%s"';
    /**
     * @errorCode
     * the reference family must begin with a letter and must contains only alphanum characters
     */
    const DOC0003 = 'syntax error family reference "%s" for document "%s"';
    /**
     * @errorCode
     * the document's reference must begin with a letter and must contains only alphanum characters
     */
    const DOC0004 = 'syntax error document reference "%s" ';
    /**
     * @errorCode
     * the reference family must be exists
     */
    const DOC0005 = 'family reference "%s" not exists for document "%s"';
    /**
     * @errorCode
     * the reference family must be a family document
     */
    const DOC0006 = 'family reference "%s" is not a family "%s"';
    /**
     * @errorCode
     * must have create privilege to import thid kind of document
     */
    const DOC0007 = 'insufficient privileges to import document "%s" of "%s" family ';
    /**
     * @errorCode
     * cannot change inherit family for a document
     */
    const DOC0008 = 'the document "%s" cannot be converted from "%s" to "%s" family ';
    /**
     * @errorCode
     * cannot update fixed document, no alive revision is found
     */
    const DOC0009 = 'the document "%s" (family "%s") is fixed';
    /**
     * @errorCode
     * the document cannot be imported because family is not completed
     */
    const DOC0010 = 'family error detected "%s" for the document "%s" : %s';
    /**
     * @errorCode
     * error in setvalue when import document
     */
    const DOC0100 = 'setValue error "%s" for attribute "%s"';
    /**
     * @errorCode
     * error when inserting file for file attributes
     * @note when file is included in array attribute
     */
    const DOC0101 = 'vault error "%s" to import file "%s" for attribute "%s" in "%s" document';
    /**
     * @errorCode
     * error when inserting file in vault for file attributes
     */
    const DOC0102 = 'vault error "%s" to import file "%s" for attribute "%s" in "%s" document';
    /**
     * @errorCode
     * error in set value for file attributes
     */
    const DOC0103 = 'set value error "%s" to import file "%s" for attribute "%s" in "%s" document';
    /**
     * @errorCode
     * preImport Method detect error (special) for physical id)
     */
    const DOC0104 = 'preImport error in "%s" system document : %s';
    /**
     * @errorCode
     * preImport Method detect error when create it
     * @note when policy import is add
     */
    const DOC0105 = 'preImport error in "%s" document when create it: %s';
    /**
     * @errorCode
     * preImport Method detect error when create it
     * @note when policy import is update
     */
    const DOC0106 = 'preImport error in "%s" document when create it: %s';
    /**
     * @errorCode
     * detect error when create it
     * @note when policy import is add
     */
    const DOC0107 = 'creation error in "%s" document : %s';
    /**
     * @errorCode
     * detect error when create it
     * @note when policy import is update
     */
    const DOC0108 = 'creation error in "%s" document : %s';
    /**
     * @errorCode
     * preImport Method detect error when update it
     * @note when policy import is update
     */
    const DOC0109 = 'preImport error in "%s" document when update it: %s';
    /**
     * @errorCode
     * too many similar document when try update by key ref
     * generaly  a document with same title has been found
     * @note when policy import is update
     */
    const DOC0110 = 'similar document "%s" document when update it';
    /**
     * @errorCode
     * preImport Method detect error when update it
     * @note when logical name is set
     */
    const DOC0111 = 'preImport error in "%s" document when update it: %s';
    /**
     * @errorCode
     * update doc error after postModify method
     */
    const DOC0112 = 'update error in "%s" document : %s';
    /**
     * @errorCode
     * the document cannot be inserted in folder target
     * @note when DOC has defined a folder target
     */
    const DOC0200 = 'cannot insert "%s" document in "%s" folder : %s';
    /**
     * @errorCode
     * the folder target is not found
     * @note when DOC has defined a folder target
     */
    const DOC0201 = '"%s" folder not found. Cannot insert "%s" document';
    /**
     * @errorCode
     * the folder target is not a folder document
     * @note when DOC has defined a folder target
     */
    const DOC0202 = '"%s" folder is not a folder (is is a "%s"). Cannot insert "%s" document';
    /**
     * @errorCode
     * the mask cannot be applied
     */
    const DOC1000 = '"%s" mask is not found , cannot apply it to "%s" document';
    /**
     * @errorCode
     * the mask to apply is not mask
     */
    const DOC1001 = '"%s" document is not a mask  (is is a "%s"), cannot apply it to "%s" document';
    /**
     * @errorCode
     * the family mask attribute is not compatible
     */
    const DOC1002 = '"%s" mask cannot be apply to "%s" document. It is set for "%s" family';
    /**
     * @errorCode
     * the mask cannot be applied from its logical mask
     */
    const DOC1004 = '"%s" mask is not found , cannot apply it to "%s" document';
    /**
     * @errorCode
     * A method call by client must has a specific declaration in the comment part : @apiExpose
     *
     */
    const DOC1100 = 'Method %s::%s() not contains @apiExpose tag comment. Document %s';
    /**
     * @errorCode
     * A controller view method must has a specific declaration in the comment part : @templateController
     *
     */
    const DOC1101 = 'Method %s::%s() not contains @templateController tag comment. Document %s';
}
