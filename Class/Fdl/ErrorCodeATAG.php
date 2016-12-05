<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Errors code used to checking ATAGID keyword
 * @class ErrorCodeATAG
 * @brief List all error code for DOCATAG
 */
class ErrorCodeATAG
{
    /**
     * @errorCode Action for set are tag are ADD, DELETE or SET
     * @see \CheckDocATag
     */
    const ATAG0001 = 'DOCATAG: action "%s" is not allowed to modify tag on document "%s". Allowed are %s.';
    /**
     * @errorCode Document to affect the tag is not set in line
     * @see \CheckDocATag
     */
    const ATAG0002 = 'DOCATAG: Cannot set tag "%s". No one document referenced.';
    /**
     * @errorCode Document reference is not valid
     * @see \CheckDocATag
     */
    const ATAG0003 = 'DOCATAG: Cannot set tag. Document "%s" not exists.';
    /**
     * @errorCode Incomplete line for atags
     * @see \CheckDocATag
     */
    const ATAG0004 = 'DOCATAG: Too few information to use tag. At least 4 cells.';
    /**
     * @errorCode Tag value must be a single word
     * @see \CheckDocATag
     */
    const ATAG0005 = 'DOCATAG: Document "%s", tag "%s" must not contains CR';
}
