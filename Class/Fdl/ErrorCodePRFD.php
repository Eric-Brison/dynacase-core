<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Error codes used to checking profid
 * @class ErrorCodePRFD
 * @see ErrorCode
 * @brief List all error code to check family security
 * It is triggered by 'PROFID' keywords
 */
class ErrorCodePRFD
{
    /**
     * @errorCode
     * the profil id must reference a document
     */
    const PRFD0001 = 'profil identifier %s is not found';
    /**
     * @errorCode
     * the profil id must reference a family profil or family itself
     */
    const PRFD0002 = 'profil %s is not a family profil';
}
