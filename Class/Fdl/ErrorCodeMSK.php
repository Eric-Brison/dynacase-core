<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Errors code used to checking masks
 * @class ErrorCodeMSK
 * @see ErrorCode
 * @brief List all error code for mask class definition
 */
class ErrorCodeMSK
{
    /**
     * @errorCode msk_famid must not be empty
     */
    const MSK0001 = 'Family id in mandatory in "%s" mask definition';
    /**
     * @errorCode msk_famid must contain a reference to existing family
     */
    const MSK0002 = 'Family id "%s" not reference a family in in "%s" mask definition';
    /**
     * @errorCode msk_attrids must reference attribut of family.
     */
    const MSK0003 = 'Attribut "%s" is not a reference to "%s" family in "%s" mask definition';
}
