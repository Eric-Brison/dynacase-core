<?php
/*
 * @author Anakeen
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
     * update doc error after postStore method
     * @see Doc::store
     */
    const DOC0112 = 'update error in "%s" document : %s';
    /**
     * @errorCode
     * update doc error after transfert values from old doc
     * @see Doc::store
     */
    const DOC0113 = 'transfertvalues error in document "%s" update: %s';
    /**
     * @errorCode the attribute must exists to get its value
     * @see Doc::getAttributeValue
     */
    const DOC0114 = 'attribute "%s" not exists in document "%s" (family "%s") : cannot get its value';
    /**
     * @errorCode the attribute must exists to set its value
     * @see Doc::setAttributeValue
     */
    const DOC0115 = 'attribute "%s" not exists in document "%s" (family "%s") : cannot set any value';
    /**
     * @errorCode a value cannot be associated to a structured  attribute. It must not be an TAB or FRAME attribute type.
     * @see Doc::getAttributeValue
     */
    const DOC0116 = 'attribute "%s" is a structured attribute in document "%s" (family "%s") : it cannot has any values';
    /**
     * @errorCode a value cannot be set to a structured  attribute. It must not be an TAB or FRAME attribute type.
     * @see Doc::setAttributeValue
     */
    const DOC0117 = 'attribute "%s" is a structured attribute in document "%s" (family "%s") : it cannot set values';
    /**
     * @errorCode try to update a document revised
     * @see Doc::store
     */
    const DOC0118 = 'cannot update fixed document "%s" (#%d)';
    /**
     * @errorCode try to update a document revised
     * @see Doc::store
     */
    const DOC0119 = 'the document "%s" (#%d) became fixed because another revision more recent has been created';
    /**
     * @errorCode when update family parameter
     * @see DocFam::setParam
     */
    const DOC0120 = 'cannot set family parameter "%s". It is not a parameter for "%s" ("%s") family';
    /**
     * @errorCode application tag must not contain \n character
     * @see Doc::addATag
     */
    const DOC0121 = 'cannot add application tag "%s" (document #%d). Application tag must not contain \n character';
    /**
     * @errorCode application tag must not be empty
     * @see Doc::addATag
     */
    const DOC0122 = 'cannot add application tag (document #%d). Application tag must not be empty';
    /**
     * @errorCode when update attribute family default value
     * @see DocFam::setDefValue
     */
    const DOC0123 = 'cannot set default value for "%s". It is not an attribute for "%s" ("%s") family';
    /**
     * @errorCode problems with frame's structure
     * @see Doc::viewbodycard
     */
    const DOC0124 = 'changeframe requested but current frame is empty (current attribute is "%s")';
    /**
     * @errorCode problems with frame's structure
     * @see Doc::viewbodycard
     */
    const DOC0125 = 'changeframe requested but current frame "%s" does not exists (current attribute is "%s")';
    /**
     * @errorCode return of customSearchValues hook must be an array
     * @see Doc::getCustomSearchValues
     */
    const DOC0126 = 'getCustomSearchValues must return an array of string (found "%s")';
    /**
     * @errorCode Dynamic profil reference an attribute which no refers to any document
     * @see DocCtrl::computeDProfil
     */
    const DOC0127 = 'Document with identifier %s not found for attribute %s';
    /**
     * @errorCode Dynamic profil reference an attribute refers to a document. But this document is not an account
     * @see DocCtrl::computeDProfil
     */
    const DOC0128 = 'Document with identifier "%s" from attribute "%s" has no property "us_whatid"';
    /**
     * @errorCode Dynamic profil reference an attribute which refers to an incomplete account
     * @see DocCtrl::computeDProfil
     */
    const DOC0129 = 'Document with identifier "%s" from attribute "%s" has an empty property "us_whatid"';
    /**
     * @errorCode The requested attribute was not found in Doc::getHtmlAttrValue()
     * @see Doc::getHtmlAttrValue
     */
    const DOC0130 = 'Attribute "%s" not found on document "%s" from family "%s"';
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
     * @errorCode The document one is trying to duplicate is invalid.
     * @note An invalid document can be a non-existing document (e.g. obtained with `new_Doc("", "NON_EXISTING")`).
     */
    const DOC0203 = "Cannot duplicate an invalid document";
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
